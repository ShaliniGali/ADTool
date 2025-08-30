"use strict";

const overlay_loading_html = `
<div class="bx--loading-overlay" style="z-index: 10000;">
<div data-loading class="bx--loading">
  <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
    <title>Loading</title>
    <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
  </svg>
</div>
</div>`;
let all_fy_years = [], year_array = [];
let fyTable;
let fundingLinesTable;
let finalAdGrantedTable;
let finalAdReviewTable;
let editor_table;

let program_breakdown_approval_default_options = {
    'zbt_summary' : 'zbt_all',
    'issue' : 'issue_all',
}

let output_table = {
    fundingLines: {},
    finalAdGranted: {},
    finalAdReview: {},
}
function onReady() {
    // Select DOM elements
    const selectEventDropdown = $("#select-event-name");
    const aoRecBtn = $("#ao-rec-btn");
    const aoCommentBtn = $("#ao-comment-btn");
    const adApprovalBtn = $("#ad-approval-btn");
    const adCommentBtn = $("#ad-comment-btn");
    const finalAdApprovalBtn = $("#final-ad-action-btn");
    const eventJustificationBtn = $("#event-justification-view-more-btn");

    // Add the following event listeners
    selectEventDropdown.on('change', handleChange);

    // Show overall event summary breadcrumb if issue
    showBreadcrumb();

    aoRecBtn.on('click', function() {
        openDropdownModal('ao')
    });
    aoCommentBtn.on('click', function() {
        openCommentModal('ao')
    });
    adApprovalBtn.on('click', function() {
        openDropdownModal('ad')
    });
    adCommentBtn.on('click', function() {
        openCommentModal('ad')
    });
    finalAdApprovalBtn.on('click', function() {
        openDropdownModal('final_ad')
    });
    eventJustificationBtn.on('click', function() {
        viewEventJustificationModal()
    })

    // Grab event name and update data on page
    const selectedEvent = selectEventDropdown.val();
    updateEventHeaders(selectedEvent);
    fetchEventData(selectedEvent);
    fetchAOADData(selectedEvent);
};

function showBreadcrumb() {
    $('#event-summary-breadcrumb').attr('hidden', false);
}

function fetchEventData(eventName) {
    $('#overlay-loader').html(overlay_loading_html);
    let url = `/socom/${page}/get_event_summary_data/${eventName}`;
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            const { events, event_title, event_justification, all_years, event_status } = response;

            year_array  = all_fy_years = all_years;
            console.log(events);
            if (!Array.isArray(events) || events.length === 0) {
                displayToastNotification('error', response.detail ?? `No event found with event name: '${eventName}'`);
                return;
            }

            // Update Event Title and Event Justification
            const eventTitleHeader = $("#event-title-header");
            eventTitleHeader.html(sanitizeHtml(event_title));

            const eventJustificationText = $("#event-justification-short-text");
            const eventJustificationModalText = $("#event-justification-text");
            const eventJustificationBtn = $("#event-justification-view-more-btn");

            eventJustificationModalText.html(sanitizeHtml(event_justification));

            if (event_justification && event_justification.length > 200) {
                let shortJustification = `${event_justification.substring(0, 200)}...`;

                eventJustificationBtn.show();
                eventJustificationText.html(sanitizeHtml(shortJustification));
                eventJustificationModalText.html(sanitizeHtml(event_justification));
            } else {
                eventJustificationBtn.hide();
                eventJustificationText.html(sanitizeHtml(event_justification));
                eventJustificationModalText.html(sanitizeHtml(''));
            }

            // Calculate sums of FY values
            const { totalSums, tableData } = formatEventTableData(events);

            updateFYTable(totalSums);
            updateFundingLinesTable(tableData, all_years, '#event-funding-lines-table', 'fundingLines');
            updatereviewStatus(response.review_status)
            
            if (event_status != undefined && event_status == 'Approve at Scale') {
                fetchFinalADData(eventName);
            } else {
                $('#proposed-granted-toggle').parent().addClass('d-none');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to fetch event summary data');
    }).always(function() {
        $('#overlay-loader').html('');
    });
}

function updatereviewStatus(reviewStatus){

    if (!reviewStatus) {
        $('#review-status-text').html('Unreviewed');
    } else {
        $('#review-status-text').html(reviewStatus);
    }


}
function formatEventTableData(events) {

    // Calculate sums of FY values
    const totalSums = {};
    const tableData = [];

    events.forEach(event => {


        const fiscalYears = event.FISCAL_YEAR;
        let fydp = 0
        for (const year in fiscalYears) {
            if (!totalSums[year]) {
                totalSums[year] = 0;
            }
            totalSums[year] += fiscalYears[year];
            fydp += fiscalYears[year]
        }

        tableData.push({
            EOC_CODE: `<a href="/socom/${page}/program_breakdown?cs=${encodeURIComponent(event.CAPABILITY_SPONSOR_CODE)}&` +
                        `ass-area=${encodeURIComponent(event.ASSESSMENT_AREA_CODE)}&program-group=${encodeURIComponent(event.PROGRAM_GROUP)}&` +
                        `program-code=${encodeURIComponent(event.PROGRAM_CODE)}&approval=${encodeURIComponent(program_breakdown_approval_default_options[page])}">
                        ${event.EOC_CODE}
                        </a>`,
            PROGRAM_GROUP: event.PROGRAM_GROUP,
            PROGRAM_CODE: event.PROGRAM_CODE,
            CAPABILITY_SPONSOR_CODE: event.CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE: event.ASSESSMENT_AREA_CODE,
            RESOURCE_CATEGORY_CODE: event.RESOURCE_CATEGORY_CODE,
            SPECIAL_PROJECT_CODE: event.SPECIAL_PROJECT_CODE,
            OSD_PROGRAM_ELEMENT_CODE: event.OSD_PROGRAM_ELEMENT_CODE,
            ...event.FISCAL_YEAR,
            FYDP: fydp
        })
    });

    let fydpSum = 0;
    Object.values(totalSums).forEach(value => {
        fydpSum += value;
    });

    totalSums['DP'] = fydpSum;

    return {
        'totalSums': totalSums,
        'tableData': tableData
    }
}


function formatFinalADGrantedTableData(grantedTableData, all_years) {

    // Calculate sums of FY values
    const tableData = [];

    grantedTableData.forEach(event => {
        const fiscalYears = {}
        for (const year_idx in all_years) {
            fiscalYears[all_years[year_idx]] = event[all_years[year_idx]];
        }

        tableData.push({
            EOC_CODE: `<a href="/socom/${page}/program_breakdown?cs=${event.CAPABILITY_SPONSOR_CODE}&` +
                        `ass-area=${event.ASSESSMENT_AREA_CODE}&program-group=${event.PROGRAM_GROUP}&` +
                        `program-code=${event.PROGRAM_CODE}">${event.EOC_CODE}</a>`,
            PROGRAM_GROUP: event.PROGRAM_GROUP,
            PROGRAM_CODE: event.PROGRAM_CODE,
            CAPABILITY_SPONSOR_CODE: event.CAPABILITY_SPONSOR_CODE,
            ASSESSMENT_AREA_CODE: event.ASSESSMENT_AREA_CODE,
            RESOURCE_CATEGORY_CODE: event.RESOURCE_CATEGORY_CODE,
            SPECIAL_PROJECT_CODE: event.SPECIAL_PROJECT_CODE,
            OSD_PROGRAM_ELEMENT_CODE: event.OSD_PROGRAM_ELEMENT_CODE,
            ...fiscalYears,
            FYDP: event.FYDP
        })
    });
    return tableData
}

function fetchFinalADData(eventName) {
    let url = `/socom/${page}/final_ad_action/${eventName}/get`;
    $('#overlay-loader').html(overlay_loading_html);
    $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            const { tableData, all_years } = response;
            if (tableData !== undefined  && tableData !== null && all_years !== undefined && all_years !== null) {
                let grantedTableData = JSON.parse(tableData);
                let finalAdTableData = formatFinalADGrantedTableData(grantedTableData, all_years);
                updateFundingLinesTable(finalAdTableData, all_years, '#event-final-ad-granted-table', 'finalAdGranted');
                $('#proposed-granted-toggle').parent().removeClass('d-none');
            }else {
                $('#proposed-granted-toggle').parent().addClass('d-none');
            }
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to fetch final ad data');
    }).always(function() {
        $('#overlay-loader').html('');
    });
}

function fetchAOADData(eventName) {
    let url = `/socom/${page}/get_ao_ad_data/${eventName}`;
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
        }, 
        function(response) {
            const { AO_DATA, AD_DATA, FINAL_AD_DATA } = response;
            aoData = AO_DATA;
            adData = AD_DATA;
            finalAdData = FINAL_AD_DATA;
            updateEventStatus(finalAdData);
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to fetch ao/ad data');
    });
}

function handleChange() {
    const selectedEvent = $(this).val();

    updateEventHeaders(selectedEvent);
    fetchEventData(selectedEvent);
    fetchAOADData(selectedEvent);
}

function openCommentModal(type) {
    $('#ao-ad-comment-view-modal').data('is_ao_ad', type);
    $('#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

    $('#ao-ad-comment-view-modal .bx--modal-header__heading').html(type === 'ao' ? 'AO Comment Review' : 'AD Comment Review');

    // Disable/enable comment save button based on user's ao/ad status
    if ((type === 'ao' && !isAoUser) || (type === 'ad' && !isAdUser)) {
        $('#ao-ad-comment-view-modal button.edit_button').prop('disabled', true);
    } else {
        $('#ao-ad-comment-view-modal button.edit_button').prop('disabled', false);
    }
    
    $('#ao_ad_type').val(type);
    
    let data = type === 'ao' ? aoData : adData;

    updateCommentList(data, type);
}

function updateCommentList(data, type) {
    const $commentList = $('#ao-ad-comment-list');
    let $commentListEls = "";

    data.forEach(item => {
        const commentText = type === 'ao' ? item.AO_COMMENT : item.AD_COMMENT;
        const userId = type === 'ao' ? item.AO_USER_ID : item.AD_USER_ID;
        if (commentText !== null && commentText.length > 0) {
            const $emailTag = userEmails[userId] ? `<span class="bx--tag bx--tag--orange" style="white-space: nowrap;">${userEmails[userId]}</span>` : '';
            const trashIcon = item.SHOW_CAN == 1 ? `<i class="fas fa-trash mx-2 delete-icon-comment-es" style="cursor: pointer;"></i>` : '';

            $commentListEls += `<li class='bx--list__item d-flex align-items-center my-2' data-id='${item.ID}'>
                                    <div class='d-flex d-flex align-items-center'>
                                        <span class='mr-2' style='flex: 1'>${commentText}</span>
                                        ${$emailTag}
                                        ${trashIcon}
                                    </div>
                                </li>`;
            // $commentListArr.push($($commentListEls).data('id', item.ID));
        }
    });

    $commentList.html($commentListEls);
}

function openDropdownModal(type) {
    $('#ao-ad-dropdown-view-modal').data('is_ao_ad', type);
    $('#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

    const AO_AD_DATA = {
        ao: {
                headerText: 'AO Recommendation Review',
                data: aoData,
            },
        ad: {
                headerText: 'AD Approval Review',
                data: adData,
            },
        final_ad: {
                headerText: 'Final AD Action',
                data: finalAdData,
            }
    };

    const { headerText, data } = AO_AD_DATA[type];

    $('#ao-ad-dropdown-view-modal .bx--modal-header__heading').html(headerText);

    // Disable/enable dropdown based on user's ao/ad status
    if ((type === 'ao' && !isAoUser) || (type === 'ad' && !isAdUser)) {
        $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').prop('disabled', true);
    } else {
        $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').prop('disabled', false);
    }
    
    $('#ao_ad_type').val(type);

    let currentSelection = null;
    
    if (type === 'ao' || type === 'ad') {
        currentSelection = getDropdownSelection(data, userId, type);
    } else if (type === 'final_ad') {
        currentSelection = getFinalADDropdownSelection(data);
        if (currentSelection) {
            let finalAdDropdown = $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection');
            finalAdDropdown.prop('disabled', true);
        }
    }
  
    let $optionEls = '<option></option>';


    dropdownChoices.forEach(option => {
        $optionEls += `<option ${option === currentSelection ? 'selected' : ''}>${option}</option>`;
    });

    const dropdownEl = $('#ao-ad-dropdown-selection');

    dropdownEl.html($optionEls);

    // Remove any existing event handlers before re-binding new ones
    dropdownEl.off('change.dropdown_onchange');

    dropdownEl.on('change.dropdown_onchange', function() {
        if (type === 'ao' || type === 'ad') {
            saveAOADDropdown();
        } else if (type === 'final_ad') {
            openFinalADConfirmationModal(currentSelection);
        }
    });

    updateApprovalList(data, type);
}

function updateApprovalList(data, type) {
    const $approvalList = $('#ao-ad-dropdown-list');
    let $approvalListEls = "";

    data.forEach(item => {
        const approvalStatus = type === 'ao' ? item.AO_RECOMENDATION : item.AD_RECOMENDATION;
        const userId = type === 'ao' ? item.AO_USER_ID : item.AD_USER_ID;
        if (approvalStatus !== null) {
            const $emailTag = userEmails[userId] ? `<span class="bx--tag bx--tag--orange">${userEmails[userId]}</span>` : '';
            const trashIcon = item.SHOW_CAN == 1 ? `<i class="fas fa-trash mx-2 delete-icon-dropdown-es" style="cursor: pointer;"></i>` : '';

            $approvalListEls += `<li class='bx--list__item d-flex align-items-center my-2' data-id='${item.ID}'>
                                    <div class='d-flex align-items-center'>
                                        ${approvalStatus}
                                        ${$emailTag}
                                        ${trashIcon}
                                    </div>
                                </li>`;
            
            // $approvalArr.push($($approvalListEls).data('id', item.ID));
        }
    });

    $approvalList.html($approvalListEls);
} 

function getDropdownSelection(data, userId, type) {
    const userObject = data.find(item => type === 'ao' ? item.AO_USER_ID == userId : item.AD_USER_ID == userId);

    let recommendationKey;
    if (userObject) {
        recommendationKey = type === 'ao' ? 'AO_RECOMENDATION' : 'AD_RECOMENDATION';
    }

    return recommendationKey ? userObject[recommendationKey] : null;
}

function getFinalADDropdownSelection(data) {
    return data[0]?.AD_RECOMENDATION ?? null;
}

function updateEventStatus(data) {
    let currentSelection = getFinalADDropdownSelection(data);

    if (!currentSelection) {
        $('#event-status-text').html('Not Decided');
    } else {
        $('#event-status-text').html(currentSelection);
    }
}

function updateEventHeaders(eventName) {
    const eventNameHeaders = $(".event-name-header");

    eventNameHeaders.html(sanitizeHtml(eventName));
}

function updateFYTable(data) {
    const rowData = [data];

    if ($.fn.DataTable.isDataTable('#event-fy-table')) {
        fyTable = $('#event-fy-table').DataTable();
        fyTable.clear();
    } else {
        fyTable = $('#event-fy-table').DataTable({
            paging: false,
            searching: false,
            lengthChange: false,
            ordering: false,
            info: false,
            columnDefs: Object.keys(data).map((key, index) => ({
                targets: index,
                title: `FY${key}`,
                data: function (row) {
                    return row[key] ?? null;
                },
                createdCell: function(td, cellData) {
                    // Highlight red logic only applies to zbt_summary
                    if (page === 'zbt_summary') {
                        if (Math.abs(cellData)>999) {
                            $(td).addClass('highlight-red');
                        }
                        else {
                            $(td).removeClass('highlight-red');
                        }
                    }
                }
            })),
            initComplete: function() {
                $('#event-fy-table .sorting_asc').removeClass('sorting_asc');
            },
        });
    }

    fyTable.rows.add(rowData).draw();
    // Out of balance warning logic only applies to zbt_summary
    if (page === 'zbt_summary') {
        if ($('#event-fy-table .highlight-red').length == 0) {
            $('#balance-warning').hide();
        } else {
            $('#balance-warning').show();
        }
    }
}

function saveAOADComment() {
    const selectEventDropdown = $("#select-event-name");
    const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

    const comment = sanitizeHtml($(`#ao-ad-comment-textarea`).val(), { allowedAttributes:{}, allowedTags:[]});
    const type = sanitizeHtml($('#ao_ad_type').val(), { allowedAttributes:{}, allowedTags:[]});

    $.post(`/socom/${page}/eoc_summary/${type}/comment/save`,
        {
            rhombus_token: rhombuscookie(),
            value: comment,
            event_id: eventName,
        }, 
        function(data) {
            if (data.status === true) {
                $('#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
                $('#ao-ad-comment-textarea').val('');

                const updatedResults = data.comments;

                if (type === 'ao') {
                    aoData = updatedResults;
                } else {
                    adData = updatedResults;
                }

                updateCommentList(updatedResults, type);

                displayToastNotification('success', `${typeof type === 'string' ? type.toUpperCase() : type} Comment Save Complete`);
            }
        },
        "json"
    ).fail(function(jqXHR) { displayToastNotification('error', `Unable to Save ${typeof type === 'string' ? type.toUpperCase() : type} Comment`); });
}

function saveAOADDropdown() {
    const selectEventDropdown = $("#select-event-name");
    const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

    const value = sanitizeHtml($(`#ao-ad-dropdown-selection`).val(), { allowedAttributes:{}, allowedTags:[]});
    const type = sanitizeHtml($('#ao_ad_type').val(), { allowedAttributes:{}, allowedTags:[]});

    return new Promise((resolve, reject) => {
        $.post(`/socom/${page}/eoc_summary/${type}/dropdown/save`,
            {
                rhombus_token: rhombuscookie(),
                value: value,
                type: 'dropdown',
                event_id: eventName,
            }, 
            function(data) {
                if (data.status === true) {
                    const updatedResults = data.comments;

                    if (type === 'ao') {
                        aoData = updatedResults;
                    } else if (type === 'ad') {
                        adData = updatedResults;
                    } else if (type === 'final_ad') {
                        finalAdData = updatedResults;
                    }

                    // Re-render current approvals list with updated values
                    updateApprovalList(updatedResults, type);

                    // Update event status (only for issue page)
                    updateEventStatus(finalAdData);

                    const typeMap = {
                        'ao': 'AO Recommendation',
                        'ad': 'AD Approval',
                        'final_ad': 'Final AD Action'
                    }

                    displayToastNotification('success', `${typeMap[type]} Save Complete`);
                    resolve(data);

                    $("#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
                }
            },
            "json"
        ).fail(function(jqXHR) { displayToastNotification('error', `Unable to Save ${typeof type === 'string' ? type.toUpperCase() : type} Dropdown`); });
    })
}



function saveApproveTableData(id, callback = null) {
    // get event name
    const selectEventDropdown = $("#select-event-name");
    const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

    // get funding lines
    const fundingLinesData = $(id).DataTable().data().toArray();
    const approveTableData = formatApproveTable(eventName, fundingLinesData);

    return new Promise((resolve, reject) => {
        $('#overlay-loader').html(overlay_loading_html);
        $.post(`/socom/${page}/final_ad_action/${eventName}/save`,
            {
                rhombus_token: rhombuscookie(),
                approve_table_data: JSON.stringify(approveTableData)
            }, 
            function(data) {
                if (callback !=  null) {
                    callback(approveTableData, all_fy_years);
                }
                resolve(data);
            },
            "json"
        )
        .fail(function(jqXHR) { 
            displayToastNotification('error', `Unable to Save ${typeof type === 'string' ? type.toUpperCase() : type} Dropdown`); 
        }).always(function() {
            $('#overlay-loader').html('');
        });

    })
}


function formatApproveTable(eventId, fundingLines) {
    let approveTableData = [];
    fundingLines.forEach(row => {
        let newRow = {};
        Object.keys(row).forEach(key => {
            if (key === 'EOC_CODE' && row[key].includes('<a')) {
                let doc = new DOMParser().parseFromString(row[key], 'text/html');
                let tagValue = doc.querySelector('a').textContent;
                newRow[key] = tagValue;
            } else {
                const onlyNumbers = /^-?\d+$/;
                if (onlyNumbers.test(row[key])) {
                    newRow[key] = parseInt(row[key]);
                }
                else {
                    newRow[key] = row[key];
                }
            }
        });
        newRow['GEARS'] = `<button class="bx--btn bx--btn--ghost bx--btn--sm coa-gear-row-btn">
            <i class="fa fa-cog" aria-hidden="true"></i>
            </button>`;
        newRow['EVENT_NAME'] = eventId;
        newRow['DT_RowId'] = [ 
            newRow['GEARS'],
            newRow['EOC_CODE'], newRow['PROGRAM_GROUP'], newRow['CAPABILITY_SPONSOR_CODE'], 
            newRow['ASSESSMENT_AREA_CODE'], newRow['RESOURCE_CATEGORY_CODE'],  
            newRow['SPECIAL_PROJECT_CODE'],  newRow['OSD_PROGRAM_ELEMENT_CODE']
        ].join('_');
        approveTableData.push(newRow);
    });
    return approveTableData;
}

function openFinalADGrantedTableModal() {
    $('#final-ad-granted-table-view-modal').find('.bx--modal-container').css('width', '97vw');
    $("#final-ad-granted-table-view-modal > div.bx--modal.bx--modal-tall").addClass("is-visible");
}

function viewEventJustificationModal() {
    $("#event-justification-view-modal > div.bx--modal.bx--modal-tall").addClass("is-visible");
}

function updateFundingLinesTable(data, fiscalYears, id, table, editable = false, showgear=false) {
    if ($.fn.DataTable.isDataTable(id)) {
        output_table[table] = $(id).DataTable();
        output_table[table].clear();
    } else {
        let ti = 0, gearColumn;
        if (showgear === true) {
            gearColumn = {
                targets: ti,
                data: "GEARS",
                title: ""
            };
            ti++;
        }

        let columnDefs = [{
            targets: 0+ti,
            data: "EOC_CODE",
            title: "EOC Code",
        },
        {
            targets: 1+ti,
            data: "PROGRAM_GROUP",
            title: "Program Group",
        },
        {
            targets: 2+ti,
            data: "CAPABILITY_SPONSOR_CODE",
            title: "Capability Sponsor Code",
        },
        {
            targets: 3+ti,
            data: "ASSESSMENT_AREA_CODE",
            title: "Assessment Area Code",
        },
        {
            targets: 4+ti,
            data: "RESOURCE_CATEGORY_CODE",
            title: "Resource Category Code",
        },
        {
            targets: 5+ti,
            data: "SPECIAL_PROJECT_CODE",
            title: "Special Project",
        },
        {
            targets: 6+ti,
            data: "OSD_PROGRAM_ELEMENT_CODE",
            title: "OSD Program Element",
        },
        ...fiscalYears.map((fiscalYear, index) => {
            return {
                targets: 7+ti + index,
                data: `${fiscalYear}`,
                title: `FY${fiscalYear}`,
                createdCell: function (cell, cellData, rowData, row, col) {
                    if (table === 'finalAdGranted') {
                        if (output_table['fundingLines'].cell(row, col).data() !== cellData) {
                            addClassToEditedCell($(cell));
                        }
                    }
                }
            }
        }),
        {
            targets: 7+ti + fiscalYears.length,
            data: "FYDP",
            title: "FYDP",
            createdCell: function (cell, cellData, rowData, row, col) {
                if (table === 'finalAdGranted') {
                    if (output_table['fundingLines'].cell(row, col).data() !== cellData) {
                        addClassToEditedCell($(cell));
                    }
                }
            }
        },
        {
            targets: 7+ti + fiscalYears.length + 1,
            data: "PROGRAM_CODE",
            title: "Program Code",
            visible: false
        }];

        if (editable) {
            columnDefs.push({
                targets: 7+ti + fiscalYears.length + 2,
                data: "DT_RowId",
                title: "DT_RowId",
                visible: false
            })
        }

        if (showgear === true) {
            columnDefs.push(gearColumn);
        }

        output_table[table] = $(id).DataTable({
            paging: false,
            searching: false,
            lengthChange: false,
            orderable: true,
            ordering: true,
            info: false,
            responsive: true,
            columnDefs: columnDefs         
        });   
    }
    output_table[table].rows.add(data).draw();
}

function openFinalADConfirmationModal(prevSelection) {
    let adModal = $("#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall");
    let confirmModal = $('#final-ad-confirm-modal');
    let confirmButton = $('#final-ad-confirm-btn');
    let cancelButton = $('#final-ad-cancel-btn');
    let closeButton = $('#final-ad-confirm-close-btn');
    let dropdown = $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection');
    let selection = dropdown.val();

    confirmModal.addClass('is-visible');
    $('#final-ad-action').text(selection);

    // Remove any existing event handlers before re-binding new ones
    cancelButton.off('click');
    closeButton.off('click');
    confirmButton.off('click');

    cancelButton.on('click', function() {
        // Revert to previous selection
        dropdown.val(prevSelection).trigger('change');
        confirmModal.removeClass('is-visible');
    });

    closeButton.on('click', function() {
        // Revert to previous selection
        dropdown.val(prevSelection).trigger('change');
        confirmModal.removeClass('is-visible');
    });

    confirmButton.on('click', function() {
        saveAOADDropdown().then((data) => {
            if (data?.status === true) {
                confirmModal.removeClass('is-visible');
                dropdown.prop('disabled', true);
                if (data.comments[0]?.AD_RECOMENDATION === 'Approve at Scale') { 
                    saveApproveTableData('#event-funding-lines-table', (approveTableData, all_fy_years) => {
                        updateFundingLinesTable(approveTableData, all_fy_years, '#final-ad-review-table', 'finalAdReview', true, true);
                        
                        let editor_columns = all_fy_years.map(year => {
                            return {
                                name: year,
                                attr: {
                                    type: "number"
                                  }
                            }
                        })
                        initFinalADReviewTable('final-ad-review-table', editor_columns);
                        onReadyGears();
                        openFinalADGrantedTableModal();
                    });
                }
            } else {
                // Revert to previous selection
                dropdown.val(prevSelection).trigger('change');
            }
        });
    });
}

function openFinalADReivewConfirmationModal() {
    let confirmModal = $('#final-ad-review-confirm-modal');
    let confirmButton = $('#final-ad-review-confirm-btn');
    let cancelButton = $('#final-ad-review-cancel-btn');
    let closeButton = $('#final-ad-review-confirm-close-btn');

    confirmModal.addClass('is-visible');
   
    // Remove any existing event handlers before re-binding new ones
    cancelButton.off('click');
    closeButton.off('click');
    confirmButton.off('click');

    cancelButton.on('click', function() {
        confirmModal.removeClass('is-visible');
    });

    closeButton.on('click', function() {
        confirmModal.removeClass('is-visible');
    });

    confirmButton.on('click', function() {
        saveFinalADReviewTable().then(() => {
            confirmModal.removeClass('is-visible');
        });
    });
}

function toggleGrantedTable() {
    if(!$(`#proposed-granted-toggle`).is(':checked')) {
        showHideGrantedTable('hide');
    }
    else {
        showHideGrantedTable('show');
    }
}

function showHideGrantedTable(action) {
    if (action == 'show') {
        $(`#funding-lines-header`).addClass('d-none');
        $(`#event-funding-lines-table`).addClass('d-none');

        $(`#final-ad-granted-header`).removeClass('d-none');
        $(`#event-final-ad-granted-table`).removeClass('d-none');
    } else {
        $(`#funding-lines-header`).removeClass('d-none');
        $(`#event-funding-lines-table`).removeClass('d-none');

        $(`#final-ad-granted-header`).addClass('d-none');
        $(`#event-final-ad-granted-table`).addClass('d-none');
    }
}

function saveFinalADReviewTable() {
    return saveApproveTableData('#final-ad-review-table', () => {
        displayToastNotification('success', `Final AD Review Save Complete`);
        $('#final-ad-granted-table-view-modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
        
        //update granted table
        const selectEventDropdown = $("#select-event-name");
        const selectedEvent = selectEventDropdown.val();
        fetchFinalADData(selectedEvent);
    });
}

function setUpEditor(id, editor_columns) {
    if(P1_FLAG=='1'){
        editor_table = new Editor({ 
            fields: editor_columns,
            table: `#${id}`
        });
    }
    else{
        editor_table = new DataTable.Editor({
            fields: editor_columns,
            table: `#${id}`
        });
    }
}

function initFinalADReviewTable(
    id,
    editor_columns
) {
    setUpEditor(id, editor_columns);
    let editable_column = editor_columns.map( v => {
        return `FY${v['name']}`;
    })
    let editedCell;
    $(`#${id}`).on('click', 'tbody td:not(:first-child)', function (e) {
        editedCell = this;
        let index =  output_table['finalAdReview'].cell( editedCell ).index();
        let editedHeader =  output_table['finalAdReview'].column(index.column).header().textContent.trim();

        $(`#DTE_Field_${editedHeader}`).removeClass('bx--text-input--invalid');
        $(".invalid-text").remove();
        if (editable_column.includes(editedHeader)) {
                editor_table.inline(this);
        }
    });

    editor_table.on('preSubmit', function (e, data, action) {
        let editrow = Object.keys(data.data)[0];
        let editColumn = Object.keys(data.data[editrow])[0];
        const newCellValue = data.data[editrow][editColumn];
        if (!validateEditCell(editColumn, newCellValue)) {
            const invalidMessage = {
                year: "Must only include integers."

            }
            const invalidText = !isNaN(parseInt(editColumn)) ? invalidMessage['year'] : invalidMessage[editColumn];
            const invalidInput = `<div class="invalid-text">${invalidText}</div>`
            $(`#DTE_Field_${editColumn}`).addClass('bx--text-input--invalid');
            if (!$(`.DTE_Field_Name_${editColumn}`).find('.invalid-text').length) {               
                $(`.DTE_Field_Name_${editColumn}`).append(invalidInput);
            } else {
                !$(`.DTE_Field_Name_${editColumn} .invalid-text`).text(invalidText)
            }
            return false;
        }
    })
    .on('postEdit' , function ( e, json, data ) {
        // update table value
        addClassToEditedCell($(editedCell));

        let index = output_table['finalAdReview'].cell( editedCell ).index();
        let editRowIndex = index['row'];

        updateFYDPColumn(output_table['finalAdReview'], editRowIndex);
    });
}

function updateFYDPColumn(tableObject, rowIndex) {
    let fydpColumnIndex = getHeaderColumnIndex(tableObject, 'FYDP');
    let startFYColumnIndex = fydpColumnIndex - 5;
    let updatedValue = 0;
    for (let i = startFYColumnIndex; i < fydpColumnIndex; i++) {
        updatedValue += parseInt(tableObject.cell(rowIndex, i).data());
    }

    // update FYDP cell
    tableObject.cell(rowIndex, fydpColumnIndex).data(updatedValue).draw();
    addClassToEditedCell($(tableObject.cell(rowIndex, fydpColumnIndex).node()));
}

function validateEditCell(header, value) {
    const onlyNumbers = /^-?\d+$/;
    if (value !== ""  && !isNaN(parseInt(header))) {
        return onlyNumbers.test(value);
    }
}

function getHeaderColumnIndex(tableObject, header) {
    let headerTexts = tableObject.columns().header().map(function (data, index) {
        return data.innerHTML;
    }).toArray();

    return headerTexts.indexOf(header);
}
function updateTable(tableObject, rowIndex, columnIndex, value) {
    tableObject.cell(grandTotalRowIndex, columnIndex).data(grandTotalCellValue).draw(false);
}

function addClassToEditedCell(cell) {
    cell.addClass('ember-cell');
}

$(onReady); 

$(document).on('click', '.delete-icon-comment-es', function(){
    let $item = $(this).closest('li');
    const itemId = $item.data('id');
    // const type = $(this).hasClass('delete-icon-comment-es') ? 'program' : 'event';
    // $item.remove();
    const selectEventDropdown = $("#select-event-name");
    const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

    const is_ao_ad = $('#ao-ad-comment-view-modal').data('is_ao_ad');
    let url;

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/comment/delete';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/comment/delete';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/comment/delete';
        } else if (is_ao_ad === 'ad') {
            url= '/socom/issue/eoc_summary/ad/comment/delete';
        }
    }

    if (url === undefined ||
        eventName.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Comment Delete Error');
        return false;
    }

    $.ajax({
        url,
        type: 'POST',
        data: { id: itemId, rhombus_token: () => { return rhombuscookie(); }, event_id: eventName },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                console.log("deletion sucessful");
                $item.remove();
                fetchAOADData(eventName);
            } else {
                alert('Failed to delete the item. Please try again.');
            }
        },
        error: function () {
            alert('An error occurred while deleting the item.');
        },
    });
});

$(document).on('click', '.delete-icon-dropdown-es', function(){
    let $item = $(this).closest('li');
    // console.log($item, $item.data('id'))
    const itemId = $item.data('id');
    // const type = $(this).hasClass('delete-icon-dropdown-es') ? 'program' : 'event';
    // $item.remove();
    const selectEventDropdown = $("#select-event-name");
    const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

    let is_ao_ad = $('#ao-ad-dropdown-view-modal').data('is_ao_ad');
    let url;

    if (page === 'zbt_summary') {
        if (is_ao_ad === 'ao') {
            url = '/socom/zbt_summary/eoc_summary/ao/dropdown/delete';
        } else if (is_ao_ad === 'ad') {
            url = '/socom/zbt_summary/eoc_summary/ad/dropdown/delete';
        } else if (is_ao_ad === 'final_ad') {
            url= '/socom/zbt_summary/eoc_summary/final_ad/dropdown/delete';
        }
    } else if (page === 'issue') {
        if (is_ao_ad === 'ao') {
            url = '/socom/issue/eoc_summary/ao/dropdown/delete';
        } else if (is_ao_ad === 'ad') {
            url= '/socom/issue/eoc_summary/ad/dropdown/delete';
        } else if (is_ao_ad === 'final_ad') {
            url= '/socom/issue/eoc_summary/final_ad/dropdown/delete';
        }
    }

    if (url === undefined ||
        eventName.trim().length === 0
    ) {
        displayToastNotification('error', 'AO or AD Dropdown Delete Error');
        return false;
    }

    $.ajax({
        url,
        type: 'POST',
        data: { id: itemId, rhombus_token: () => { return rhombuscookie() }, event_id: eventName },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                console.log("deletion sucessful");
                $item.remove();
                fetchAOADData(eventName);
            } else {
                alert('Failed to delete the item. Please try again.');
            }
        },
        error: function () {
            alert('An error occurred while deleting the item.');
        },
    });
});

if (!window._rb) window._rb = {}
window._rb.onReady = onReady;