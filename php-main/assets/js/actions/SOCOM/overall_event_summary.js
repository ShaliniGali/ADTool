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

const selectionTextOptions = {
    'false': 'Select All',
    'true': 'Deselect All'
}

const lastSelectedItemsMap = {
    'cap-sponsor': ["ALL"],
    'ad-consensus': ["ALL"],
    'review-status': ["ALL"],
    'aac': ["ALL"]
}

let overallEventSummaryTable;
    
function onReady() {
    // Select DOM elements
    let input_object = get_input_object();
    fetchOverallEventSummaryData(input_object);
};

function fetchOverallEventSummaryData(post_data) {
    $('#overlay-loader').html(overlay_loading_html);
    let url = `/socom/${page}/get_overall_event_summary_data`;
    return $.post(url, 
        {
            rhombus_token: rhombuscookie(),
            ...post_data

        }, 
        function(response) {
            const tableData = [];
            const { overall_sum, overall_sum_approve, data, all_years, final_ad_actions, proposed_disapproved_programs } = response;

            data.forEach(event => {
                let fiscalYears = {}, AD_CONSENSUS = event.AD_CONSENSUS.toUpperCase().replace(/ /g, '_'), fydp_proposed = 0, fydp_granted = 0;
                
                // Debug: Log the event object to see its structure
                console.log('Event object:', event);
                console.log('Event keys:', Object.keys(event));
                
                // Extract fiscal year data from individual properties
                const yearKeys = Object.keys(event).filter(key => /^\d{4}$/.test(key)); // Get year keys like "2024", "2025", etc.
                console.log('Year keys found:', yearKeys);
                
                yearKeys.forEach(fy => {
                    const fy_val = event[fy];
                    console.log(`Processing year ${fy} with value ${fy_val}`);
                    if (AD_CONSENSUS === 'APPROVE_AT_SCALE' &&  
                    Object.values(final_ad_actions[event.EVENT_NAME] ?? {}).length > 0) {
                        let granted_class = (fy_val !== final_ad_actions[event.EVENT_NAME][fy] ? 'ember-cell': '');
                        
                        fiscalYears[fy] = `<span id="${event.EVENT_NAME}_${AD_CONSENSUS}_granted_${fy}" class="fy_granted approve_at_scale ${granted_class}">${final_ad_actions[event.EVENT_NAME][fy]}</span>` + 
                        `<span id="${event.EVENT_NAME}_${AD_CONSENSUS}_proposed_${fy}" class="fy_proposed  d-none">${fy_val}</span>`;
                        fydp_granted += parseInt(final_ad_actions[event.EVENT_NAME][fy]);
                        fydp_proposed += parseInt(fy_val);
                    } else if (AD_CONSENSUS === 'DISAPPROVE' && Object.values(proposed_disapproved_programs[event.EVENT_NAME]).length > 0) {
                        fiscalYears[fy] = `<span id="${event.EVENT_NAME}_${AD_CONSENSUS}_proposed_${fy}" class="fy_granted">${fy_val}</span>` + 
                        `<span id="${event.EVENT_NAME}_${AD_CONSENSUS}_granted_${fy}" class="fy_proposed d-none">${proposed_disapproved_programs[event.EVENT_NAME][fy]}</span>`;
                        fydp_granted += parseInt(fy_val);
                        fydp_proposed += parseInt(proposed_disapproved_programs[event.EVENT_NAME][fy]);
                    } else {
                        fiscalYears[fy] = fy_val;
                        fydp_proposed += parseInt(fy_val);
                    }
                });
                
                console.log('Fiscal years object:', fiscalYears);
                
                let fydp = 0;
                if (AD_CONSENSUS === 'APPROVE_AT_SCALE' && Object.values(final_ad_actions[event.EVENT_NAME] ?? {}).length > 0) {
                    let granted_class = (fydp_granted !== fydp_proposed ? 'ember-cell': '');
                    fydp = `<span  class="fy_granted approve_at_scale ${granted_class}">${fydp_granted}</span>` + 
                        `<span class="fy_proposed d-none ">${fydp_proposed}</span>`;
                } else if (AD_CONSENSUS === 'DISAPPROVE' && Object.values(proposed_disapproved_programs[event.EVENT_NAME]).length > 0) {
                    fydp = `<span  class="fy_granted">${fydp_granted}</span>` + 
                        `<span class="fy_proposed d-none">${fydp_proposed}</span>`;
                } else {
                    fydp = fydp_proposed;
                }
                
                
                tableData.push({
                    EVENT_NAME: `<a href="/socom/${page}/event_summary/${event.EVENT_NAME}">${event.EVENT_NAME}</a>`,
                    ISSUE_CAP_SPONSOR: event.ISSUE_CAP_SPONSOR,
                    EVENT_TITLE: event.EVENT_TITLE,
                    ...fiscalYears,
                    FYDP: fydp,
                    AD_CONSENSUS: event.AD_CONSENSUS,
                    REVIEW_STATUS: event.REVIEW_STATUS || 'Unreviewed'
                })
            });
            updateOverallEventSumTable(overall_sum, overall_sum_approve);
            updateOverallEventSummaryTable(tableData, all_years);
        },
        "json"
    ).fail(function(jqXHR) { 
        displayToastNotification('error', 'Unable to fetch event summary data');
    }).always(function() {
        $('#overlay-loader').html('');
    });
}


// function openDropdownModal(type) {
//     $('#ao-ad-dropdown-view-modal > div.bx--modal.bx--modal-tall').addClass('is-visible');

//     const AO_AD_DATA = {
//         ao: {
//                 headerText: 'AO Recommendation Review',
//                 data: aoData,
//             },
//         ad: {
//                 headerText:'AD Approval Review',
//                 data: adData,
//             },
//     };

//     const { headerText, data } = AO_AD_DATA[type];

//     $('#ao-ad-dropdown-view-modal .bx--modal-header__heading').html(headerText);

//     // Disable/enable dropdown based on user's ao/ad status
//     if ((type === 'ao' && !isAoUser) || (type === 'ad' && !isAdUser)) {
//         $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').prop('disabled', true);
//     } else {
//         $('#ao-ad-dropdown-view-modal #ao-ad-dropdown-selection').prop('disabled', false);
//     }
    
//     $('#ao_ad_type').val(type);

//     let currentSelection = getDropdownSelectionByUserID(data, userId, type);
  
//     let $optionEls = '<option></option>';

//     const dropdownChoices = {
//         zbt_summary: [
//             'Approve as Requested',
//             'Disapprove'
//         ],
//         issue: [
//             'Approve',
//             'Approve at Scale',
//             'Disapprove'
//         ]
//     }[page];

//     dropdownChoices.forEach(option => {
//         $optionEls += `<option ${option === currentSelection ? 'selected' : ''}>${option}</option>`;
//     });

//     $('#ao-ad-dropdown-selection').html($optionEls);

//     $('#ao-ad-dropdown-selection').on('change.dropdown_onchange', function() {
//         saveAOADDropdown()
//     });

//     updateApprovalList(data, type);
// }

// function updateApprovalList(data, type) {
//     const $approvalList = $('#ao-ad-dropdown-list');
//     let $approvalListEls = "";

//     data.forEach(item => {
//         const approvalStatus = type === 'ao' ? item.AO_RECOMENDATION : item.AD_RECOMENDATION;
//         const userId = type === 'ao' ? item.AO_USER_ID : item.AD_USER_ID;
//         if (approvalStatus !== null) {
//             const $emailTag = userEmails[userId] ? `<span class="bx--tag bx--tag--orange">${userEmails[userId]}</span>` : '';

//             $approvalListEls += `<li class='bx--list__item d-flex align-items-center my-2'>
//                                     <div class='d-flex align-items-center'>
//                                         ${approvalStatus}
//                                         ${$emailTag}
//                                     </div>
//                                 </li>`;
//         }
//     });

//     $approvalList.html($approvalListEls);
// } 

// function getDropdownSelectionByUserID(data, userId, type) {
//     const userObject = data.find(item => type === 'ao' ? item.AO_USER_ID == userId : item.AD_USER_ID == userId);

//     let recommendationKey;
//     if (userObject) {
//         recommendationKey = type === 'ao' ? 'AO_RECOMENDATION' : 'AD_RECOMENDATION';
//     }

//     return recommendationKey ? userObject[recommendationKey] : null;
// }

// function updateEventHeaders(eventName) {
//     const eventNameHeaders = $(".event-name-header");

//     eventNameHeaders.html(sanitizeHtml(eventName));
// }

// function updateFYTable(data) {
//     const rowData = [data];

//     if ($.fn.DataTable.isDataTable('#event-fy-table')) {
//         fyTable = $('#event-fy-table').DataTable();
//         fyTable.clear();
//     } else {
//         fyTable = $('#event-fy-table').DataTable({
//             paging: false,
//             searching: false,
//             lengthChange: false,
//             ordering: false,
//             info: false,
//             columnDefs: Object.keys(data).map((key, index) => ({
//                 targets: index,
//                 title: `FY${key}`,
//                 data: function (row) {
//                     return row[key] ?? null;
//                 },
//                 createdCell: function(td, cellData) {
//                     // Highlight red logic only applies to zbt_summary
//                     if (page === 'zbt_summary') {
//                         if (Math.abs(cellData)>999) {
//                             $(td).addClass('highlight-red');
//                         }
//                         else {
//                             $(td).removeClass('highlight-red');
//                         }
//                     }
//                 }
//             })),
//             initComplete: function() {
//                 $('#event-fy-table .sorting_asc').removeClass('sorting_asc');
//             },
//         });
//     }

//     fyTable.rows.add(rowData).draw();
//     // Out of balance warning logic only applies to zbt_summary
//     if (page === 'zbt_summary') {
//         if ($('#event-fy-table .highlight-red').length == 0) {
//             $('#balance-warning').hide();
//         } else {
//             $('#balance-warning').show();
//         }
//     }
// }

// function saveAOADComment() {
//     const selectEventDropdown = $("#select-event-name");
//     const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

//     const comment = sanitizeHtml($(`#ao-ad-comment-textarea`).val(), { allowedAttributes:{}, allowedTags:[]});
//     const type = sanitizeHtml($('#ao_ad_type').val(), { allowedAttributes:{}, allowedTags:[]});

//     $.post(`/socom/${page}/eoc_summary/${type}/comment/save`,
//         {
//             rhombus_token: rhombuscookie(),
//             value: comment,
//             event_id: eventName,
//         }, 
//         function(data) {
//             if (data.status === true) {
//                 $('#ao-ad-comment-view-modal > div.bx--modal.bx--modal-tall').removeClass('is-visible');
//                 $('#ao-ad-comment-textarea').val('');

//                 const updatedResults = data.comments;

//                 if (type === 'ao') {
//                     aoData = updatedResults;
//                 } else {
//                     adData = updatedResults;
//                 }

//                 updateCommentList(updatedResults, type);

//                 displayToastNotification('success', `${typeof type === 'string' ? type.toUpperCase() : type} Comment Save Complete`);
//             }
//         },
//         "json"
//     ).fail(function(jqXHR) { displayToastNotification('error', `Unable to Save ${typeof type === 'string' ? type.toUpperCase() : type} Comment`); });
// }

// function saveAOADDropdown() {
//     const selectEventDropdown = $("#select-event-name");
//     const eventName = sanitizeHtml(selectEventDropdown.val(), { allowedAttributes:{}, allowedTags:[]});

//     const value = sanitizeHtml($(`#ao-ad-dropdown-selection`).val(), { allowedAttributes:{}, allowedTags:[]});
//     const type = sanitizeHtml($('#ao_ad_type').val(), { allowedAttributes:{}, allowedTags:[]});

//     $.post(`/socom/${page}/eoc_summary/${type}/dropdown/save`,
//         {
//             rhombus_token: rhombuscookie(),
//             value: value,
//             type: 'dropdown',
//             event_id: eventName,
//         }, 
//         function(data) {
//             if (data.status === true) {
//                 const updatedResults = data.comments;

//                 if (type === 'ao') {
//                     aoData = updatedResults;
//                 } else {
//                     adData = updatedResults;
//                 }

//                 // Re-render current approvals list with updated values
//                 updateApprovalList(updatedResults, type);

//                 //update event status
//                 updateEventStatus(adData);

//                 displayToastNotification('success', `${typeof type === 'string' ? type.toUpperCase() : type} Dropdown Save Complete`);
//             }
//         },
//         "json"
//     ).fail(function(jqXHR) { displayToastNotification('error', `Unable to Save ${typeof type === 'string' ? type.toUpperCase() : type} Dropdown`); });
// }

// function viewEventJustificationModal() {
//     $("#event-justification-view-modal > div.bx--modal.bx--modal-tall").addClass("is-visible");
// }

function updateOverallEventSumTable(overall_sum, overall_sum_approve) {
    let overallEventSumTable, 
        year_list = JSON.parse(overall_sum['YEAR_LIST']);
    
    // Suppress DataTables warnings globally for this table
    $.fn.dataTable.ext.errMode = 'none';

    if ($.fn.DataTable.isDataTable('#overall-event-sum-table')) {
        overallEventSumTable = $('#overall-event-sum-table').DataTable();
        overallEventSumTable.clear();
    } else {
        let columnDefs = [];
    

        overall_sum['FYDP'] = 0;

        for(let i in year_list) {
            let fyi = (parseInt(i)+1);
            columnDefs.push({
                targets: parseInt(i),
                data: `FY_${fyi}_sum`,
                title: `FY${year_list[i]}`,
                defaultContent: '0'
            })
        }
        
        columnDefs.push({
            targets: 5,
            data: 'FYDP',
            title: 'FYDP',
            defaultContent: '0'
        });

        overallEventSumTable = $(`#overall-event-sum-table`).DataTable({
            paging: false,
            pageLength: 10,
            searching: false,
            lengthChange: false,
            orderable: false,
            ordering: false,
            info: false,
            columnDefs: columnDefs,
            language: {
                emptyTable: "No data available"
            },
            error: function(xhr, error, thrown) {
                // Suppress DataTables warnings
                console.log('DataTable warning suppressed:', thrown);
            }
        });
    }

    overall_sum['FYDP'] = 0;

    delete overall_sum['YEAR_LIST'];
    
    for(let i in year_list) {
        let fyi = (parseInt(i)+1), 
            osvas = parseInt(overall_sum[`FY_${fyi}_sum`]), 
            osva = parseInt(overall_sum_approve[i]?.SUM_DELTA ?? 0);
        
        overall_sum['FYDP'] +=  osvas + osva;
        overall_sum[`FY_${fyi}_sum`] = new Intl.NumberFormat("en-US", {
            style: "decimal",
        }).format((osvas + osva));
    }
    
    overall_sum['FYDP'] = new Intl.NumberFormat("en-US", {
        style: "decimal",
    }).format(overall_sum['FYDP']);

    overallEventSumTable.rows.add([overall_sum]).draw();
}

function updateOverallEventSummaryTable(data, fiscalYears) {
    const status = document.getElementById("review-status-dropdown").hidden;
    let columnDefs=[];
    if ($.fn.DataTable.isDataTable('#overall-event-summary-table')) {
        overallEventSummaryTable = $('#overall-event-summary-table').DataTable();
        overallEventSummaryTable.clear();
    } else {
        if(!status){
        columnDefs = [{
            targets: 0,
            data: "EVENT_NAME",
            title: "Event Name",
        },
        {
            targets: 1,
            data: "ISSUE_CAP_SPONSOR",
            title: "Capability Sponsor",
        },
        {
            targets: 2,
            data: "EVENT_TITLE",
            title: "Event Title",
        },
        ...fiscalYears.map((fiscalYear, index) => {
            return {
                targets: 3 + index,
                data: `${fiscalYear}`,
                title: `FY${fiscalYear} Support`,
            }
        }),
        {
            targets: 3 + fiscalYears.length,
            data: "FYDP",
            title: "FYDP"
        },
        {
            targets: 3 + fiscalYears.length + 1,
            data: "AD_CONSENSUS",
            title: "AD Consensus"
        },
        {
            targets: 3 + fiscalYears.length + 2,
            data: "REVIEW_STATUS",
            title: "Review Status",
            searchable: true
        }
    
        ];
        }else{
         columnDefs = [{
            targets: 0,
            data: "EVENT_NAME",
            title: "Event Name",
        },
        {
            targets: 1,
            data: "ISSUE_CAP_SPONSOR",
            title: "Capability Sponsor",
        },
        {
            targets: 2,
            data: "EVENT_TITLE",
            title: "Event Title",
        },
        ...fiscalYears.map((fiscalYear, index) => {
            return {
                targets: 3 + index,
                data: `${fiscalYear}`,
                title: `FY${fiscalYear} Support`,
            }
        }),
        {
            targets: 3 + fiscalYears.length,
            data: "FYDP",
            title: "FYDP"
        },
    {
            targets: 3 + fiscalYears.length + 1,
            data: "AD_CONSENSUS",
            title: "AD Consensus"
        },
    ];
        }


        overallEventSummaryTable = $(`#overall-event-summary-table`).DataTable({
            paging: true,
            pageLength: 10,
            searching: true,
            lengthChange: true,
            orderable: true,
            ordering: true,
            info: true,
            columnDefs: columnDefs,
            dom:'lrtip',
            lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, 'All']],
            drawCallback: function(settings) {
                if ($('#proposed-granted-toggle-oest').prop('checked')) {
                    $('.fy_granted.approve_at_scale').parent().addClass('ember-cell');
                }
            },
            createdRow: function(row, data, dataIndex) {
                let adConsensusIndex = 3 + fiscalYears.length + 1;
                if (data["AD_CONSENSUS"] === "Not Decided") {
                    $(row).find('td:eq(' + adConsensusIndex + ')').css({
                        'background-color': '#FFBF00',
                        'color': '#000'
                    });
                }
            }        
        });   
    }
    overallEventSummaryTable.rows.add(data).draw();
    applyReviewStatusFilter(fiscalYears);
}

function applyReviewStatusFilter(fiscalYears) {
    let reviewStatusChoices;
    if(page === 'zbt_summary'){
        reviewStatusChoices = [
            'Disapproval Flag',
            'No Disapproval Flag',
            'Unreviewed'
        ];
    }else if(page === 'issue'){
        reviewStatusChoices = [
            'Approval Flag',
            'Approve at Scale Flag',
            'Disapprove Flag',
            'Unreviewed'
        ];
    }
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        let selectedStatuses = $('#review-status').val();
        let reviewStatusIndex = 3 + fiscalYears.length + 2;
        let reviewStatus = data[reviewStatusIndex];
        if (selectedStatuses.includes("ALL")) {
            return true;
        }
        let reviewStatusArray = reviewStatus.split(',').map(s => s.trim());
        return selectedStatuses.some(status => reviewStatusArray.includes(status));
    });
    overallEventSummaryTable.draw();
}

function toggleGrantedOESTable() {
    $('#overall-event-summary-table').DataTable().draw();

    let toggle = document.getElementById("proposed-granted-toggle-oest");
    if (toggle.checked) {
        //Granted
        $('.fy_proposed').addClass('d-none');
        $('.fy_granted').removeClass('d-none');
        $('.fy_granted.approve_at_scale').parent().addClass('ember-cell');
    } else {
        //Proposed
        $('.fy_proposed').removeClass('d-none');
        $('.fy_granted').addClass('d-none');
        if ($('.fy_granted').parent().hasClass('ember-cell')) {
            $('.fy_granted').parent().removeClass('ember-cell');
        }
    }
 }

function get_input_object() {
    let input_object = {};

    if ($('#cap-sponsor').val() != "" && $('#cap-sponsor').val() != null) {
        input_object["cap-sponsor"] = fetch_all_inputs('#cap-sponsor')
    }

    if ($('#ad-consensus').val() != "" && $('#ad-consensus').val() != null) {
        input_object["ad-consensus"] = fetch_all_inputs('#ad-consensus')
    }

    if ($('#review-status').val() != "" && $('#review-status').val() != null) {
        input_object["review-status"] = fetch_all_inputs('#review-status')
    }
    
    if ($('#aac').val() != "" && $('#aac').val() != null) {
        input_object["aac"] = fetch_all_inputs('#aac')
    }
    return input_object;
}

function dropdown_selection(target) {
    const dropdown = $(target);
    const selectionButton = `${target}-selection`;
    const isSelectAll = $(selectionButton).attr('data-select-all') === 'true';
    const stringifyIsSelectAll = isSelectAll.toString();

    $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
    $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);

    if (!isSelectAll) {
        dropdown.val(null).trigger('change');
    } else {
        dropdown.val('ALL').trigger('change');
        if (dropdown.height() > 100) {
            dropdown.css('max-height', '100px');
            dropdown.css('overflow-y', 'auto');
        }
    }
}

//  Serialize all four selects into ?cap-sponsor[]=…&ad-consensus[]=…&…
function buildFilterQS() {
    const params = new URLSearchParams();
    ['cap-sponsor','ad-consensus','review-status','aac'].forEach(name => {

      const vals = $(`#${name}`).val() || [];
      if (vals.length === 1 && vals[0] === 'ALL') return;
      // drop “ALL” if you don’t want that in the URL
      const filtered = vals.filter(v => v !== 'ALL');
      if (filtered.length) {
        // join into one comma‑separated string
        params.set(name, filtered.join(','));
      }
    });
    return params.toString() ? ('?' + params.toString()) : '';
  }

function dropdown_onchange(type, event_id = null) {
    let input_object = {}

    switch(type) {
        case 'cap-sponsor':
        case 'ad-consensus':
        case 'review-status':
        case 'aac':
            if (!$('#proposed-granted-toggle-oest').prop('checked')) {
                $('#proposed-granted-toggle-oest').trigger('click');
            }
            dropdown_all_view(type);

            const newQs = buildFilterQS();
            const newUrl = window.location.pathname + newQs;
            window.history.replaceState(null, '', newUrl);
            sessionStorage.setItem('oesFilters', newQs);

            input_object = get_input_object();

            if (input_object["cap-sponsor"] != null && input_object["cap-sponsor"].length > 0 && 
                input_object["ad-consensus"] != null && input_object["ad-consensus"].length > 0 &&
                input_object["review-status"] != null && input_object["review-status"].length > 0) {
                fetchOverallEventSummaryData(input_object);
            }
            break;
        default:
            break;
    }
}

function selectionHasChanged(id) {
    const lastSelections = lastSelectedItemsMap[id];
    return lastSelections.includes("ALL")
}

function dropdown_all_view(type, id) {
    const dropdown_id = `${type}`;
    const dropdown = $(`#${dropdown_id}`);
    let allElement = $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`);
    if (allElement.length) {
        allElement = allElement[0].outerHTML;
    }
    
    let selected_values = dropdown.val();
    let isSelectAll = !(selected_values.length === 0);
    const selectionButton = `#${dropdown_id}-selection`;
  
    const stringifyIsSelectAll = isSelectAll.toString();   

    const changeSelectAll = $(selectionButton).attr('data-select-all') !== selectionTextOptions[stringifyIsSelectAll];
    if (changeSelectAll) {        
        $(selectionButton).attr('data-select-all', (!isSelectAll).toString());
        $(selectionButton).html(selectionTextOptions[stringifyIsSelectAll]);
    }
    
    if(selectionHasChanged(type) && selected_values.length > 0){
        const allIndex = selected_values.indexOf("ALL");
        if (allIndex !== -1) {
            selected_values.splice(allIndex, 1);
        }
        $(`ul#select2-${dropdown_id}-container > li[title="ALL"]`).remove()
    }
    else if (selected_values.includes("ALL")){
        selected_values = ['ALL'];
        const otherSelected = $(`ul#select2-${dropdown_id}-container > li.select2-selection__choice`);
        otherSelected.remove()
        $(`ul#select2-${dropdown_id}-container`).prepend(allElement)
    }
    lastSelectedItemsMap[type] = selected_values;
    dropdown.val(selected_values)
}

function fetch_all_inputs(id) {
    let select2val =  $(id).val();

    if(select2val.includes('ALL') && select2val.length > 0){
        return $(`${id} > option`).map(function(){
            if(this.value != 'ALL' && this.value != ''){
                return this.value;
            }
        }).get();
    }
    return select2val;
}

function getCurrentFormattedTime() {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day}-${hours}-${minutes}-${seconds}`;
}

async function export_results() {
    // let event_data = await fetchEventSummaryData();
    let event_data = await fetchEventSummaryData();


    let export_data = event_data.events;


    let headerList = [
        "EVENT_NAME",
        "EOC_CODE",
        "PROGRAM_GROUP",
        "CAPABILITY_SPONSOR_CODE",
        "ASSESSMENT_AREA_CODE",
        "RESOURCE_CATEGORY_CODE",
        "SPECIAL_PROJECT_CODE",
        "OSD_PROGRAM_ELEMENT_CODE",
    ]
    
    let yearsList = [...event_data.all_years];
    
    let yearsHeader = yearsList.map(year => `FY${year}`);

    let allHeaders = [...headerList, ...yearsHeader, "FYDP"];

    // Filter export_data to include only the keys that are in the header list
    export_data = export_data.map(row => {
        let filteredRow = {};
        allHeaders.forEach(headerKey => {
            if (row.hasOwnProperty(headerKey)) {
                filteredRow[headerKey] = row[headerKey];
            }
        });
        return filteredRow;
    });

    let fileName = `EventSummary_${getCurrentFormattedTime()}.xlsx`;

    /* make the worksheet */
    let ws = XLSX.utils.json_to_sheet(export_data, { 
        header: allHeaders
    });

    /* add to workbook */
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    /* generate an XLSX file */
    XLSX.writeFile(wb, fileName);
}


function fetchEventSummaryData() {
    let table_data = overallEventSummaryTable.data().toArray();
    
    let event_names = table_data.map(function(item) {
        let doc = new DOMParser().parseFromString(item.EVENT_NAME, 'text/html');
        let link = doc.querySelector('a');
        return link.textContent;
    });

    event_names = [...new Set(event_names)];

    // $('#overlay-loader').html(overlay_loading_html);
    return new Promise((resolve, reject) => {
        $.post(`/socom/${page}/get_exported_event_summary_data`,
            {
                rhombus_token: rhombuscookie(),
                event_names,
            }, 
            function(response) {
                const { data } = response;
    
                if ((!data?.events || data?.events?.length === 0)) {
                    displayToastNotification('error', response.detail ?? 'No event data found.');
                    return;
                }

                resolve(data);
            },
            "json"
        ).fail(function(jqXHR) { 
            displayToastNotification('error', 'Unable to fetch event summary data');
        }).always(function() {
            // $('#overlay-loader').html('');
        });
    });
}

$(onReady); 
  
if (!window._rb) window._rb = {}
window._rb.onReady = onReady;