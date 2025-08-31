let apps_table = {};
let first_load_apps = true;
$('#apps_tab').on('shown.bs.tab',function(){
    if(first_load_apps){
        first_load_apps = false;
        $.post("/facs_manager/get_facs", { facs_type: 'apps', rhombus_token: rhombuscookie() }, function (data, status) {
			load_apps(data);
        }, "json");
    } else{
        $.post("/facs_manager/get_facs", { facs_type: 'apps', rhombus_token: rhombuscookie() }, function (data, status) {
            apps_table.refreshTable(data);
        },"json");

    }
});

function load_apps(data){
    let columns = [
        {
            'data': "id", // This key will come from the initial data
            'render': function(data, type, row) { // (data) points to the data given from the line above. row points to all the data in that row. eg. row.name or row.key
                return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>" // The return value is what will be displayed in each cell for the column
            },
            'visible':false
        },
        {
            'data': "label",
            'render': function(data, type, row) {
                return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>"
            }
        },
        {
            'data': "description",
            'render': function(data, type, row) {
                return "<span style='overflow:hidden;text-overflow: ellipsis;white-space: normal;'>" + (data?data:"")  + Rhombus_Datatable.copy_button + "</span>"
            }
        },
        { 'defaultContent': "<span class='mr-5'>" + Rhombus_Datatable.edit_button("apps_table") + "</span"}
    ]
    let datatable_properties = {
        "dom":"<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-3'B><'col-12 col-md-5 px-0'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
        "data": Object.values(data.result),
        "columns": columns,
        "scrollY":"60vh"
    };
    
    apps_table = new Rhombus_Datatable({
        "table_name" : "apps_table", 
        "datatable_properties" : datatable_properties, 
        "export_div": false,
        "form_ids" :  {"title": "appsFormModalTitle", "button": "appsSubmitRecord", "modal": "appsFormModal", "form": "appsForm"},
        "error_ids" : ["apps_error_label"],
        "overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
        "set_form_values" : function(selected_row_data){
            $("#apps_input_id").val(selected_row_data.id);
            $("#apps_input_label").val(selected_row_data.label).trigger('change');
        },
        "get_form_values" : function(){
            return {
                "id": $("#apps_input_id").val(),
                "label": $("#apps_input_label").val(),
            };
        }
    });
    apps_table.init_table();
    apps_table.initialize_submit("", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'apps'});

    function check_duplicate(submit_type, form_values){
        return apps_table.check_record_exist(function(table_data){
            return (table_data["label"] == form_values["label"])
        });
    }
}

if (!window._rb) window._rb = {};
window._rb.load_apps = load_apps;