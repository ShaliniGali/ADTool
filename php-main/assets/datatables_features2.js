
// Changed to a class format jan 27 2021 Lea
// Revised Feb 2021 Ian
class Rhombus_Datatable {

    // DATATABLES IMPROVE //---------------------------------------------------------////

    /**
     * 
     * @param {object} constructor_params 
     * 
     * table_name => Element id where the table will be appended. Same id used for datatable_card in html view page
     * 
     * datatable_properties => Constructor properties for the datatable. Important keys are "data" and columns.
     *              "data" => initial data for the data table. 
     *              "columns" => Definition for the table columns. Array of Objects, each object representing a column.
     *                          See test_datatable.js for an example.
     * 
     * tooltips => Array of Jquery string, for additional basic tooltip setup. eg. ["#sample_id", ".sample_class"]
     * 
     * form_ids => Ids for the modal form used in add and edit functionality. Important keys are ["title":"","button":"","modal","form"]
     * 
     * error_ids => element ids for the form error display eg. ["error_sample", "error_name"]
     * 
     * delete_modal_ids => Ids for the modal used in delete functionality. Important keys are ["heading":"","message":"","confirm","modal"]
     * 
     * overwrite_modal_ids => Ids for the modal used in duplicate/overwrite functionality. Important keys are ["modal":"","confirm":""]
     * 
     * delete_message => Function for a custom delete message, It has one parameter for the data of the row to be deleted. The return should be a string
     * 
     * get_form_values => Function for getting the values of the form. The return should be an Object which will be the data sent to the post requests of add and edit
     * 
     * set_form_values => Function for setting the values of the form. It has one parameter for the data of the row to be edited. This will be used for the edit functionality
     * 
     */
    constructor(constructor_params){
        this.table_name = constructor_params["table_name"];

        this.datatable_properties = {...this.default_datatable_properties, ...constructor_params["datatable_properties"]};

        this.export_div = "export_div" in constructor_params ? constructor_params["export_div"] : "div.exportDiv";
        this.tooltips = "tooltips" in constructor_params ? constructor_params["tooltips"] : [".rowBtn"];
        this.form_ids = constructor_params["form_ids"];
        this.error_ids = constructor_params["error_ids"];
        this.delete_modal_ids = constructor_params["delete_modal_ids"];
        this.overwrite_modal_ids = constructor_params["overwrite_modal_ids"];

        if("delete_message" in constructor_params)
            this.delete_message = constructor_params["delete_message"];
        else{
            this.delete_message = function(row_data){
                return "Deleting record of " + JSON.stringify(row_data["id"]);
            };
        }
 
        this.get_form_values = constructor_params["get_form_values"];
        this.set_form_values = constructor_params["set_form_values"];
        this.selectedRowData = null;
        this.duplicate_id = null;
        this.additional_reset_form = constructor_params["additional_reset_form"];
        this.refresh_callback = "refresh_callback" in constructor_params ? constructor_params["refresh_callback"] : ()=>{};

        if(this.export_div == false) {
            this.datatable_properties.buttons = [];
        }
        this.add_record_button_text = "add_record_button_text" in constructor_params ? constructor_params["add_record_button_text"] : "Add Record"
    }

    /////////////////////////////////
    // Datatable Default Setup
    /////////////////////////////////

    default_datatable_properties = {
        "dom":"<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-6 col-md-3'f><'col-3 col-md-1 toolbar'><'col-3 col-md-1 exportDiv'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
        'language': {
            'searchPlaceholder': "Search Records",
            'search': "",
        },
        "deferRender":true,
        "scrollY":"70vh",
        "scrollCollapse":true,
        "scroller":true,
        "buttons" : [
            {
                "extend": "excel",
                "text": "<i class=\"fas fa-download\"></i>",
                "exportOptions": {
                    "columns": ":visible"
                }
            }
        ]
    };

    ////////////////////////////////
    // Datatable Buttons
    ////////////////////////////////

    static copy_button = "<button class = 'copyBtn' onclick='Rhombus_Datatable.copyToClipboard($(this))' data-toggle='tooltip' data-placement='top' title='copied'><i class='far fa-copy fa-xs'></i></button>";
    static delete_button = function(instance_name){ return "<button class = 'rowBtn' onclick='"+instance_name+".delete_record($(this))' data-toggle='tooltip' data-placement='top' title='Delete'><i class='far fa-trash-alt'></i></button>";}
    static edit_button = function(instance_name){return "<button class = 'rowBtn' onclick='"+instance_name+".edit_record($(this))' data-toggle='tooltip' data-placement='top' title='Edit'><i class='far fa-edit'></i></button>";}

    /////////////////////////////
    // Static Functions
    /////////////////////////////

    /**
     * Copies the text inside the button's parent element to clipboard
     * eg. <span>Text to Copy<button id = "copy" onclick="copyToClipboard($(this))"></button></span>
     * 
     * @param {jquery} e element of copy button
     */
    static copyToClipboard(e) {

        let dummy = document.createElement("textarea");
        e.parent().append(dummy);
        dummy.value = e.parent().text();
        dummy.focus();
        dummy.select();
        document.execCommand("copy");
        dummy.remove();
    }

    /**
     * Initialize tooltips for copy buttons with option to add on.
     * @param {array} tooltips Array of jquery selector strings eg. [".rowBtn","#extra_tooltip"]
     */
    static initTooltips(tooltips = []) {

        $(function () {
            if (tooltips) {
                for (let i = 0; i < tooltips.length; i++) {
                    $(tooltips[i]).tooltip({ boundary: 'window' });
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

    /**
     * Append Datatable export button to a given element
     * 
     * @param {jquery} id jquery selector where the button will be appended
     * Default id is exportDiv, initialized in the default dom for the datatable properties if used: 
     *      Rhombus_Datatable.default_datatable_properties["dom"]
     */
    repositionExportButton(id = this.export_div) {
        $(id).html($('.buttons-excel',$(id).parent()));
    }

    /**
     * 
     * Creates a dropdown button using the list on the select_id.
     * 
     * @param {array} list Properties for the list of options of the dropdown
     *          [
     *              {value, label},
     *              {value, label},
     *              ...    
     *          ]
     * @param {jquery} select_id 
     * @param {function} dropdownCallback Called when dropdown selected changes. Uses (selected_value, selected_text) for parameters
     * @param {bool} create_option States whether the user will be able to create new options or will only be able to choose from the existing list
     */
    static addDropdown(list, select_id, dropdownCallback=()=>{}, create_option = true, select2_options = {}) {
        // table dropdown view
        let tempId = new Date().getTime() + "_dropdown";
        let dropdownHtml = "<select class=\"btn btn-group\" id=\"" + tempId + "\" value=\"\" required>";
        if(list){
            for (let i = 0; i < list.length; i++) {
                dropdownHtml += "<option value = '" + list[i][0] + "'>" + list[i][1] + "</option>";
            }
        }
        dropdownHtml += "</select>";

        select_id.html(dropdownHtml);
        $('#' + tempId).select2({
            tags: create_option,
            placeholder: "Please Select...",
            ...select2_options
        });


        // dropdown functionality
        $('#' + tempId).change(function () {
            let value = $(this).val();
            let text = $(this).children("option:selected").text();
            dropdownCallback(value, text);
        });
    }

    ////////////////////////////////
    // Instance Functions
    ////////////////////////////////

    /**
     * Initialize the Datatable
     * 
     * Call this function after creating your class instance
     * IMPORTANT: if setup_add_record is used, call init_table after
     */
    init_table(){


        this.data_table = $("#"+this.table_name).DataTable(this.datatable_properties);

        if(this.export_div != false){
            this.repositionExportButton();
        }

        if(this.tooltips)
            Rhombus_Datatable.initTooltips(this.tooltips);
    }

    /**
     * Sets up the button and the functionality for adding record. 
     * IMPORTANT: Call this function before init_table
     */
    setup_add_record(){
        let instance = this;
        this.datatable_properties.buttons.push({
            text: instance.add_record_button_text,
            action: function(e, dt, node, config){
                instance.selectedRowData = null;
                instance.resetFormValues();
                $("#"+instance.form_ids["title"]).text("Add Record");
                $("#"+instance.form_ids["button"]).text("Save");
                $("#"+instance.form_ids["button"]).val("addData");
                $("#"+instance.form_ids["modal"]).modal();
            }
        });
    }

    /**
     * Sets up the form for editing the record.
     * @param {jquery} button Button that called the function
     */
    edit_record(button){
        this.resetFormValues();

        this.selectedRowData = this.get_row_data(button);

        this.set_form_values(this.selectedRowData);

        $("#" + this.form_ids["title"]).text("Edit Record");
        $("#" + this.form_ids["button"]).text("Save");
        $("#" + this.form_ids["button"]).val("editData");
        $("#" + this.form_ids["modal"]).modal();
    }

 
    /**
     * Sets up the modal for confirm delete
     * @param {jquery} button Button that called the function
     * @param {*} del_message OPTIONAL: allows you to set up your own custom delete message, 
     *                                  has one parameter that contains the data of the record to be deleted:
     *                                  eg. function(selected_row_data){return "Deleting data: "JSON.stringify(selected_row_data)}
     *                                  This is better initialized in the instance construction: ("delete_message")
     */
    delete_record(button, del_message = this.delete_message){
        // show modal on DELETE click
        this.selectedRowData = this.get_row_data(button);

        $('#' + this.delete_modal_ids["heading"]).text("Are you sure you want to delete?");
        $('#' + this.delete_modal_ids["message"]).html(del_message(this.selectedRowData));
        $("#" + this.delete_modal_ids["modal"]).modal();
    }

    /**
     * Initialize the confirm delete functionality
     * 
     * @param {String} main_url Path that the confirm delete will send its data, eg. "/Controller/delete_record"
     * @param {Array} add_row_data OPTIONAL: By default, it will only send the record id, use this to include more data from the deleting record eg. ["column1", "column2"]
     * @param {Object} extra_post_data OPTIONAL: Add additional data to send to the post request eg. {"sample_data1": sample_function(), "sample_data2", "sample_string"}
     */
    initialize_delete(main_url, add_row_data = false, extra_post_data = false){
        let instance = this;
        let delete_modals = this.delete_modal_ids
        $("#"+delete_modals["confirm"])[0].addEventListener('click', function(){
            action_button(delete_modals["confirm"], "add");
            let post_data = {
                "rhombus_token" : rhombuscookie(),
                "rowId" : instance.selectedRowData['id']
            };
            if(add_row_data){
                add_row_data.forEach(function(row_data_name){
                    post_data[row_data_name] = instance.selectedRowData[row_data_name];
                });
            }
            if(extra_post_data){
                Object.keys(extra_post_data).forEach(function(extra_data_name){
                    post_data[extra_data_name] = (typeof extra_post_data[extra_data_name] === "function" ? extra_post_data[extra_data_name]() : extra_post_data[extra_data_name]);
                });
            }

            $.post(main_url, post_data, function(data, status) {
                $("#"+delete_modals["modal"]).modal("hide");
                action_button(delete_modals["confirm"], "remove");
                instance.refreshTable(data);
            }, "json");
        });
    }

    /**
     * Initialize the overwrite functionality
     * 
     * @param {String} url Path that the overwrite will send its data, usually the same url for editing. eg. "/Controller/edit_record"
     * @param {Object} overwrite_modals OPTIONAL: element ids for the overwrite modal (required key: "confirm")
     *                                              if ommited, must be declared in instance construction: "overwrite_modal_ids"
     */
    initialize_overwrite(url, overwrite_modals = this.overwrite_modal_ids){
        let instance = this;
        $("#"+overwrite_modals["confirm"])[0].addEventListener("click", function(){
            action_button(overwrite_modals["confirm"], "add");
            let formData = instance.get_form_values();
            formData["id"] = instance.duplicate_id;
            $.post(url, { rhombus_token: rhombuscookie(), "editData": formData }, function(data, status) {
                action_button(overwrite_modals["confirm"], "remove");
                instance.closeForm();
                instance.refreshTable(data);
            }, "json");
        });
    }

    /**
     * 
     * @param {String} add_url Path that the add functionality will send its data to. eg. "/Controller/add_record"
     * @param {String} edit_url Path that the edit functionality will send its data to. eg. "/Controller/edit_record"
     * @param {Function} check_unique_data(submit_type, form_values) OPTIONAL: Allows you to set up how to check for existing record, for the overwrite functionality.
     *                                                  Usually used with check_record_exist
     * @param {Array} add_row_data OPTIONAL: Use this to include more data from the selected record.
     *                                          This might be used for columns that should not be updated eg. ["column1", "column2"]
     * @param {Object} extra_post_data OPTIONAL: Add additional data to send to the post request eg. {"sample_data1": sample_function(), "sample_data2", "sample_string"}
     * @param {Object} overwrite_modals OPTIONAL: element id for the overwrite modal (required key: "modal")
     *                                              if ommited, must be declared in instance construction: "overwrite_modal_ids"
     */
    initialize_submit(add_url, edit_url, check_unique_data = function(){ return false; }, add_row_data = false, extra_post_data = false, overwrite_modals = this.overwrite_modal_ids){
        let instance = this;
        $("#"+instance.form_ids["form"]).on("submit", function(event) {
            event.preventDefault();
            action_button(instance.form_ids["button"], "add");

            // get form data
            instance.formValues = instance.get_form_values();
            let submit_type = $("#" + instance.form_ids["button"]).val();
            let post_data = {
                rhombus_token: rhombuscookie()
            };
            post_data[submit_type] = instance.formValues;
            let submit_url = submit_type == "addData"?add_url:edit_url;

            if(submit_type == "editData"){
                if(add_row_data){
                    add_row_data.forEach(function(row_data_name){
                        post_data[submit_type][row_data_name] = instance.selectedRowData[row_data_name];
                    });
                }
            }
   
            if(extra_post_data){
                Object.keys(extra_post_data).forEach(function(extra_data_name){
                    post_data[extra_data_name] = (typeof extra_post_data[extra_data_name] === "function" ? extra_post_data[extra_data_name]() : extra_post_data[extra_data_name]);
                });
            }

            // check unique data
            if(check_unique_data(submit_type, instance.formValues)) {
                action_button(instance.form_ids["button"], "remove");
                $("#"+overwrite_modals["modal"]).modal()
                return;
            }

            // send data
            $.post(submit_url, post_data, function(data, status) {
                action_button(instance.form_ids["button"], "remove");
                if (data["validation"] == "success") {
                    instance.closeForm();
                    instance.refreshTable(data);
                } else {
                    instance.showErrorMessages(data["errors"])
                }
            }, "json");
        });
    }

    /**
     * Iterate through each record of the table using the conditions set in custom_check. 
     * Will return true if at least one passes the check.
     * 
     * @param {function} custom_check This function will be called on each record.
     *                              It has one parameter for the data of the record.
     *                              This function should return either true or false.
     *  
     * eg. check_record_exist(function(table_data){
     *          return (table_data["user_id"] == form_values["user_id"] && table_data["type"] == form_values["type"])
     *     });
     */
    check_record_exist(custom_check){
        let instance = this;
        let filteredData = instance.data_table
        .rows()
        .indexes()
        .filter(function(value, index){
            let table_values = instance.data_table.row(value).data();
            let result = custom_check(table_values);
            if(result){
                instance.duplicate_id = table_values["id"];
                return true;
            } else {
                return false;
            }
        });
        return filteredData.any();
    }

    /**
     * Reload the table with new data
     * 
     * @param {Object} tableData New data to display in the table
     */
    refreshTable(tableData) {
        this.data_table.clear();
        this.data_table.rows.add(Object.values(tableData['result'])).draw();

        if(this.tooltips)
            Rhombus_Datatable.initTooltips(this.tooltips);
        
        this.refresh_callback(tableData);
    }

    /**
     * Creates a column selector that allows the user to toggle the visibility of the columns
     * 
     * @param {Array} columnName Array of Objects. Each Object represent a column and uses the keys "data" and "visible". "data" must match one of the Object keys supplied in the datatable.
     *                          eg. [{ "data": "id","visible":false },{ "data": "name","visible":true }, { "data": "email","visible":true}, { "data": "password","visible":true }]
     * @param {Jquery} targetDiv Jquery selector where the column selector will be displayed. By default using "div.toolbar" which is used in the default_datatable_properties
     */
    createColumnSelector(columnName, targetDiv = "div.toolbar") {
        let table = this.data_table;
        let temp_id = new Date().getTime() + "_dropdown_column_selector";
        let temp_columnCheck_id = new Date().getTime() + "_columnCheck";
        let toggleHtml = '<div>' +
            '<button type="button" class="btn btn-secondary button_column_selector" data-toggle="dropdown"><i class="fas fa-cog"></i></button>' +
            '<ul class="dropdown-menu keep-open" id="' + temp_id + '">' +
            '<li><label><span class="ml-3 mb-3" style="font-weight: bold;">Filter<span></label></li>' +
            '</ul>' +
            '</div>';

        $(targetDiv).html(toggleHtml);

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
                table.columns().every(function() {
                    if (this.dataSrc() == $(event.target).val())
                        colindex = this.index();
                });
                table.column(colindex).visible($(event.target).prop('checked')).draw();
            });
        }

        $(document).ready(function() {
            $("#" + temp_id).on('click', function(e) {
                e.stopPropagation();
            });
        });
    }

    /**
     * Resets the values of the form
     */
    resetFormValues(form = this.form_ids, error = this.error_ids, additional_reset = this.additional_reset_form) {
        clear_form(form["form"]);
        if(error){
            error.forEach(function(value) {
                $("#" + value).html("");
            });
        }
        if(additional_reset)
            additional_reset();
    };

    /**
     * Close form and reset its values
     */
    closeForm(){
        this.resetFormValues();
        $("#"+this.form_ids["modal"]).modal("hide");
    }
    
    /**
     * Display error messages
     * 
     * @param {Object} errors Error messages to be displayed. The Object key will be the element id where the error will be appended.
     */
    showErrorMessages(errors) {
        this.error_ids.forEach(function(value) {
            $("#" + value).html(sanitizeHtml(errors[value], { allowedAttributes:false, allowedTags:false,}));
        });
    };

    /**
     * Gets the data from a row based on a jquery element provided
     * 
     * @param {*} button jquery of button or any element under a datatable row
     * @returns Data associated with the datatable row
     * 
     */
    get_row_data(button){
        let tr = ( button.closest('tr').hasClass('child') )? button.closest('tr').prev('tr'): button.closest('tr');
        return this.data_table.row(tr).data();
    }
}

// Expose class in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.Rhombus_Datatable = Rhombus_Datatable;
window._rb.copyToClipboard = Rhombus_Datatable.copyToClipboard;
window._rb.initTooltips = Rhombus_Datatable.initTooltips;
window._rb.addDropdown = Rhombus_Datatable.addDropdown;
window._rb.edit_button = Rhombus_Datatable.edit_button;
window._rb.delete_button = Rhombus_Datatable.delete_button;
window._rb.copy_button = Rhombus_Datatable.copy_button;
