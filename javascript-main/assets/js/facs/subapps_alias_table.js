let subapps_alias_table = {};
let first_load_subapps_alias = true;
$('#subapps_alias_tab').on('shown.bs.tab',function(){
    if(first_load_subapps_alias){
        first_load_subapps_alias = false;
        $.post("/facs_manager/get_facs", { facs_type: 'subapps_alias', rhombus_token: rhombuscookie() }, function (data, status) {
			load_subapps_alias(data);
        }, "json");
    } else{
        $.post("/facs_manager/get_facs", { facs_type: 'subapps_alias', rhombus_token: rhombuscookie() }, function (data, status) {
            subapps_alias_table.refreshTable(data);
        },"json");

    }
});

function load_subapps_alias(data){
    let columns = [
        {
            'data': "id", // This key will come from the initial data
            'render': function(data, type, row) { // (data) points to the data given from the line above. row points to all the data in that row. eg. row.name or row.key
                return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>" // The return value is what will be displayed in each cell for the column
            },
            'visible':false
        },
        {
            'data': "alias_name",
            'render': function(data, type, row) {
                return "<span class='badge badge-pill badge-warning px-2 mr-2'>" + data + "</span>"
            }
        },
        { 'defaultContent': "<span class='d-block text-right mr-5'><span class='mr-4'>" +
            Rhombus_Datatable.delete_button("subapps_alias_table") + "</span> <span>" +
            Rhombus_Datatable.edit_button("subapps_alias_table") + "</span></span>"
        }
    ]
    let datatable_properties = {
        "dom":"<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-6 col-md-5 px-0'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
        "data": Object.values(data.result),
        "columns": columns,
        "scrollY":"60vh"
    };

    subapps_alias_table = new Rhombus_Datatable({
        "table_name" : "subapps_alias_table", 
        "datatable_properties" : datatable_properties, 
        "export_div":false,
        "form_ids" :  {"title": "subapps_aliasFormModalTitle", "button": "subapps_aliasSubmitRecord", "modal": "subapps_aliasFormModal", "form": "subapps_aliasForm"},
        "error_ids" : ["subapps_alias_error_name"],
        "delete_modal_ids":{ "heading": "subapps_alias_confirmDeleteHeading", "message": "subapps_alias_confirmDeleteMessage", "confirm": "subapps_alias_confirmDeleteBtn", "modal": "subapps_alias_confirmDelete", },
        "overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
        "set_form_values" : function(selected_row_data){
            $("#subapps_alias_input_id").val(selected_row_data.id);
            $("#subapps_alias_input_alias_name").val(selected_row_data.alias_name);
        },
        "get_form_values" : function(){
            return {
                "id": $("#subapps_alias_input_id").val(),
                "alias_name": $("#subapps_alias_input_alias_name").val(),
            };
        },
        "delete_message":function(selected_row_data){ return "Application-Tag: " + selected_row_data["alias_name"]},
        "add_record_button_text":"Create Application-Tag"
    });
    subapps_alias_table.setup_add_record();
    subapps_alias_table.init_table();
    subapps_alias_table.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'subapps_alias'});
    subapps_alias_table.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'subapps_alias'});

    function check_duplicate(submit_type, form_values){
        return subapps_alias_table.check_record_exist(function(table_data){
            return (table_data["alias_name"].toUpperCase() == form_values["alias_name"].toUpperCase())
        });
    }
}