let features_table = {};
let first_load_features = true;

$('#features_tab').on('shown.bs.tab',function(){
    if(first_load_features){
        first_load_features = false;
        $.post("/facs_manager/get_facs", { facs_type: 'features', rhombus_token: rhombuscookie() }, function (data, status) {
            let columns = [
                {
                    'data': "id", // This key will come from the initial data
                    'render': function(data, type, row) { // (data) points to the data given from the line above. row points to all the data in that row. eg. row.name or row.key
                        return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>" // The return value is what will be displayed in each cell for the column
                    },
                    'visible':false
                },
                {
                    'data': "name",
                    'render': function(data, type, row) {
                        return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>"
                    }
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

            Rhombus_Datatable.addDropdown(false, $("#features_input_name"), ()=>{}, false, {'data':data.function_list});
        
            features_table = new Rhombus_Datatable({
                "table_name" : "features_table", 
                "datatable_properties" : datatable_properties, 
                "export_div":false,
                "form_ids" :  {"title": "featuresFormModalTitle", "button": "featuresSubmitRecord", "modal": "featuresFormModal", "form": "featuresForm"},
                "error_ids" : ["features_error_name"],
                "delete_modal_ids":{ "heading": "features_confirmDeleteHeading", "message": "features_confirmDeleteMessage", "confirm": "features_confirmDeleteBtn", "modal": "features_confirmDelete", },
                "overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
                "set_form_values" : function(selected_row_data){
                    $("#features_input_id").val(selected_row_data.id);
                    $("#features_input_name select").val(selected_row_data.name).trigger('change');
                },
                "get_form_values" : function(){
                    return {
                        "id": $("#features_input_id").val(),
                        "name": $("#features_input_name select").val(),
                    };
                },
                "delete_message":function(selected_row_data){ return "Sub App Name: " + selected_row_data["name"]},
                "refresh_callback":(data)=>{
                    $('#features_input_name').html("");
                    Rhombus_Datatable.addDropdown(false, $("#features_input_name"), ()=>{}, false, {'data':data.misc});
                }
            });
            features_table.init_table();
            features_table.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'features'});
            features_table.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'features'});
        
            function check_duplicate(submit_type, form_values){
                return features_table.check_record_exist(function(table_data){
                    return (table_data["name"] == form_values["name"])
                });
            }
        }, "json");
    } else{
        $.post("/facs_manager/get_facs", { facs_type: 'features', rhombus_token: rhombuscookie() }, function (data, status) {
            Rhombus_Datatable.addDropdown(false, $("#features_input_name"), ()=>{}, false, {'data':data.function_list});
            features_table.refreshTable(data);
        },"json");
    }
});