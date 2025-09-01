"use strict";
// create column toggle
// parameters: array of column names; filter id; datatable instance
// example column array:
// let columns = [{ "data": "id","visible":false },{ "data": "name","visible":true }, { "data": "email","visible":true}, { "data": "password","visible":true }];
// Last updated Sai July 6 2020
// Changed to a class format jan 27 2021 Lea
class RhombusDatatable {

    createColumnSelector(columnName, table, targetDiv) {
        let temp_id = new Date().getTime() + "_dropdown";
        let temp_columnCheck_id = new Date().getTime() + "_columnCheck";
        let toggleHtml = '<div>' +
            '<button type="button" class="btn button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
            '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
            '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
            '</ul>' +
            '</div>';

        targetDiv.html(sanitizeHtml(toggleHtml, { allowedAttributes:false, allowedTags:false,}));

        let count = 0;
        for (let i = 0; i < columnName.length; i++) {
            if (columnName[i].data !== null) {
                document.getElementById(temp_id).innerHTML += '<li class="columnSelect"><label class="checkboxcontainer ml-2 ">' + columnName[i]['data'] + '<input id="' + temp_columnCheck_id + i + '" type="checkbox" aria-label="Checkbox for following text input" value=' + columnName[i]['data'] + '><span class="checkmark mr-2"><span></label></li>';
                count += 1;
            }
        }
        for (let j = 0; j < count; j++) {
            if (columnName[j]['visible'] == false) {
                $("#" + temp_columnCheck_id + j).prop("checked", false);
            } else {
                $("#" + temp_columnCheck_id + j).prop("checked", true);
            }
            document.getElementById(temp_columnCheck_id + j).addEventListener('change', (event) => {
                let colindex = null;
                table.columns().every(function () {
                    if (this.dataSrc() == $(event.target).val())
                        colindex = this.index();
                });
                table.column(colindex).visible($(event.target).prop('checked')).draw();
            });
        }

        $(document).ready(function () {
            $("#" + temp_id).on('click', function (e) {
                e.stopPropagation();
            });
        });

    }


    /**
     * Ian Zablan: Created Aug 7 2020
     * The purpose of this function is to initialize given buttons to open a given form with record data
     * shown on their respective input boxes.
     * 
     * @param {string} editClass 
     * @param {array} modalIds 
     * @param {DataTable} table 
     * @param {array} selectedRowData 
     * @param {function} resetForm 
     * @param {function} getValues 
     * 
     * Additional Notes:
     * post request will send form values  as "editData".
     */
    initEditButtons(editClass, modalIds, table, selectedRowData, resetForm, setValues) {
        let edit = $("." + editClass);
        $.each(edit, function (index, value) {
            value.addEventListener('click', function () {
                resetForm();

                selectedRowData[0] = table.row($(this).parents('tr')).data();

                setValues();

                $("#" + modalIds["title"]).text("Edit Record");
                $("#" + modalIds["button"]).text("Edit Record");
                $("#" + modalIds["modal"]).modal();
            });
        });
    }


    /*
    Ian Zablan: Created 16 July 2020
    The purpose of this is to initialize the delete buttons and modal to allow deletion of record.

    Parameters:
    refresh : bool  // set to false if function will be called for the first time, otherwise set to false.
    deleteButtons : string // class name used for delete buttons
    dataTable : Datatable()  // instance of datatable used
    message : function(data[])   // can be used to access data the selected row's data for the message
    modalIds : Object[] // IDs used for the modal [modal, heading, message, footer, confirm]
    url : string // link to controller's function for deletion. example: /Controller_name/Function_name
    selectedRowData : array[0] // used to keep data from the selected row to delete

    Additional Notes:
    post request will send row id  as "rowId".
    */

    initDeleteFunctionality(refresh, deleteButtons, dataTable, message, modalIds, url, selectedRowData, refreshTable, tableName = function () { return ""; }, include_user_id = false) {
        // show modal on DELETE click

        let tempDelBtns = $("." + deleteButtons);
        for (let i = 0; i < tempDelBtns.length; i++) {
            tempDelBtns[i].addEventListener('click', function () {
                let tableRow = $(this).closest('tr');
                selectedRowData[0] = dataTable.row(tableRow).data();
                $('#' + modalIds["heading"]).text("Are you sure you want to delete?");
                $('#' + modalIds["message"]).html(sanitizeHtml(message(selectedRowData),{ allowedAttributes:false, allowedTags:false,}));
                $("#" + modalIds["modal"]).modal();
            });
        }


        if (!refresh) {
            // send data on CONFIRM DELETE click
            $('#' + modalIds["footer"]).on('click', '#' + modalIds["confirm"], function () {
                let user_id = "";
                if (include_user_id) user_id = selectedRowData[0]['user_id'];
                $.post(url, { rhombus_token: rhombuscookie(), rowId: selectedRowData[0]['id'], tableName: tableName(), user_id: user_id }, function (data, status) {
                    refreshTable(data);
                }, "json");
            });
        }
    }

    // initialize export button and append in target element
    // parameter: table instance, jquery selecter
    // WIP/////-not working
    initExportButton(table, targetId) {
        this.repositionExportButton(targetId);
    }
    // For now, 
    // add this property inside datatable.
    // 'buttons': [
    //     {
    //         "extend":"excel",
    //         "text" : "<i class=\"fas fa-download\"></i>",
    //         "exportOptions": {
    //             "columns":":visible"
    //         }
    //     }
    // ]
    ///////////

    // append export button to parameter: jquery selector | eg. $("#example")
    repositionExportButton(id) {
        $(id).html($('.buttons-excel',$(id).parent()));
    }

    // copy button html
    get showCopyButton() {
        return '<button class = "copyBtn" data-toggle=\"tooltip\" data-placement=\"top\" title=\"copied\"><i class="far fa-copy fa-xs"></i></button>';
    }


    copyToClipboard(text, temp) {
        let dummy = document.createElement("textarea");
        $(temp).append(dummy);
        dummy.value = text;
        dummy.focus();
        dummy.select();
        document.execCommand("copy");
        dummy.remove();
    }
    // enable class "copyBtn" copy to clipboard functionality.
    // important: may need to be called on datatable drawCallback property if button is used inside table.
    // note: enclose only text and copy button in an element, example with span tag.
    // eg. <span> text RBDatatable.showCopyButton() </span>
    initCopyFunctionality() {
        let copyButtons = $(".copyBtn");
        let copyToClipboard = this.copyToClipboard

        for (let i = 0; i < copyButtons.length; i++) {
            copyButtons[i].addEventListener('click', function () {
                let textToCopy = $(this).parent().text();
                copyToClipboard(textToCopy, $(this).parent());
            });
        }
    }

    // initialize copy tooltips with optional add on.
    // parameter: array of jquery selectors
    // important: may need to be called on datatable drawCallback property if button is used inside table.
    initTooltips(tooltips) {

        $(function () {
            if (tooltips) {
                for (let i = 0; i < tooltips.length; i++) {
                    tooltips[i].tooltip({ boundary: 'window' });
                }
            }
            $('.copyBtn').tooltip({
                animated: 'fade',
                placement: 'bottom',
                trigger: 'manual',
                style: 'font-size:12px',
            });
        })

        $(".copyBtn").click(function () {
            let that = $(this);
            that.tooltip('show');
            setTimeout(function () {
                that.tooltip('hide');
            }, 2000);
        });
    }

    // creates a dropdown button using the passed list on the targetId.
    // parameters: array(array(value, label),
    //                   array(value,label). . .), 
    //             jquery selector, 
    //             callback function(selectedValue, selectedText)  -- called when dropdown selected changes
    addDropdown(list, targetId, dropdownCallback, hasTags = true) {
        // table dropdown view
        let tempId = new Date().getTime() + "_dropdown";
        let dropdownHtml = "<select class=\"btn btn-group\" id=\"" + tempId + "\" value=\"\" required>";
        for (let i = 0; i < list.length; i++) {
            dropdownHtml += "<option value = '" + sanitizeHtml(list[i][0], { allowedAttributes:false, allowedTags:false}) + "'>" + sanitizeHtml(list[i][1], { allowedAttributes:false, allowedTags:false}) + "</option>";
        }
        dropdownHtml += "</select>";

        targetId.html(sanitizeHtml(dropdownHtml, { allowedAttributes:false, allowedTags:false,}));
        $('#' + tempId).select2({
            tags: hasTags,
            placeholder: "Please Select..."
        });


        // dropdown functionality
        $('#' + tempId).change(function () {
            let value = $(this).children("option:selected").val();
            let text = $(this).children("option:selected").text();
            dropdownCallback(value, text);
        });
    }

    addRecord(selectedRowData, resetFormValues, formIds) {
        selectedRowData[0] = null;
        resetFormValues();
        $("#" + formIds["title"]).text("Add Record");
        $("#" + formIds["button"]).text("Add Record");
        $("#" + formIds["modal"]).modal();
    }

    /* 
    Ian Zablan: Created 17 July 2020
    The purpose of this function is to initialize a modal's confirm button to overwrite an existing record with form values

    Parameters:
    modalIds : array // IDs of a modal - footer and confirm button [footer] [confirm]
    formValues : function // gives an updated data to be sent to the controller for editing
    duplicateId : function // gives an updated id of record to be overwritten
    url : string // path to the edit function of your controller example: /Controller_name/function_name
    postCallback : function(data) // executed in the post request callback. allows you to include your custom closeForm() and refreshTable()

    Additional Notes:
    post request will send form values  as "editData".
    */
    initOverwriteConfirm(modalIds, formValues, duplicateId, url, postCallback) {
        $('#' + modalIds["footer"]).on('click', '#' + modalIds["confirm"], function () {
            let formData = formValues();
            formData["id"] = duplicateId();
            $.post(url, { rhombus_token: rhombuscookie(), "editData": formData }, function (data, status) {
                postCallback(data);
            }, "json");
        });
    }

}

// Expose class in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.RhombusDatatable = RhombusDatatable;
