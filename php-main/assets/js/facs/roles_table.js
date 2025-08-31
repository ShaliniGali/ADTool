rhombus_dark_mode("dark", "switch_false");

let columns = [
    {
        'data': "id", // This key will come from the initial data
        'render': function(data, type, row) { // (data) points to the data given from the line above. row points to all the data in that row. eg. row.name or row.key
            return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>" // The return value is what will be displayed in each cell for the column
        },
        "visible": false
    },
    {
        'data': "name",
        'render': function(data, type, row) {
            return "<span class='badge badge-pill badge-primary px-2 mr-2'>" + data + "</span>"
        }
    },
    { 'defaultContent': "<span class='d-block text-right mr-5'><span class='mr-4'>" +
        Rhombus_Datatable.delete_button("role_table") + "</span> <span>" +
        Rhombus_Datatable.edit_button("role_table") + "</span></span>"
    }
]
let datatable_properties = {
    "dom":"<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-12 col-md-5 px-0'f>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
    "data": Object.values(roles),
    "columns": columns,
    "scrollY":"70vh"
};

let role_table = new Rhombus_Datatable({
    "table_name" : "roles_table", 
    "datatable_properties" : datatable_properties, 
    "export_div":false,
    "form_ids" :  {"title": "rolesFormModalTitle", "button": "rolesSubmitRecord", "modal": "rolesFormModal", "form": "rolesForm"},
    "error_ids" : ["roles_error_name"],
    "delete_modal_ids":{ "heading": "roles_confirmDeleteHeading", "message": "roles_confirmDeleteMessage", "confirm": "roles_confirmDeleteBtn", "modal": "roles_confirmDelete", },
    "overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
    "set_form_values" : function(selected_row_data){
        $("#roles_input_id").val(selected_row_data.id);
        $("#roles_input_name").val(selected_row_data.name);
    },
    "get_form_values" : function(){
        return {
            "id": $("#roles_input_id").val(),
            "name": $("#roles_input_name").val(),
        };
    },
    "delete_message":function(selected_row_data){ return "Role: " + selected_row_data["name"]},
    "add_record_button_text":"Create Role"
});
role_table.setup_add_record();
role_table.init_table();
role_table.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'roles'});
role_table.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'roles'});

function check_duplicate(submit_type, form_values){
    return role_table.check_record_exist(function(table_data){
        return (table_data["name"] == form_values["name"])
    });
}

if (!window._rb) window._rb = {};
window._rb.role_table = role_table;