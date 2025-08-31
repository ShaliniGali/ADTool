let subapps_table = {};
let first_load_subapps = true;
let subapps_label_mappings = {};
let subapps_mapping_pattern = 'single_subapp_multiple_controllers';

$('#subapps_tab').on('shown.bs.tab',function(){
    if(first_load_subapps){
        first_load_subapps = false;
        $.post("/facs_manager/get_facs", { facs_type: 'subapps', rhombus_token: rhombuscookie() }, function (data, status) {
            subapps_label_mappings = Object.fromEntries(new Map(data.subapps_alias_list));
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
                },
                {
                    'data': "subapps_alias",
                    'render': function(data, type, row) {
                        let parsed_data = JSON.parse(data);
                        let render_html = '';
                        for(const i in parsed_data){
                            if(parsed_data[i] in subapps_label_mappings){
                                render_html += "<span class='badge badge-pill badge-warning px-2 mr-2'>" + subapps_label_mappings[parsed_data[i]] + "</span>"
                            }
                        }
                        return render_html;
                    }
                },
                { 'defaultContent': "<span class='d-block text-right mr-5'>" +
                    Rhombus_Datatable.edit_button("subapps_table") + "</span>"
                }
            ]
            let datatable_properties = {
                "dom":"<'row'<'col-sm-12 col-md-3'l><'col-sm-12 col-md-4 subapps_mapping_div'><'col-6 col-md-5 px-0'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
                "data": Object.values(data.result),
                "columns": columns,
                "scrollY":"60vh"
            };
    
            Rhombus_Datatable.addDropdown(data.controller_list, $("#subapps_input_name"));
            Rhombus_Datatable.addDropdown(data.subapps_alias_list, $("#subapps_input_subapps_alias"), ()=>{}, false, {multiple:true});

            let subapps_mapping_subapp_list = data.controller_list.slice();
	        subapps_mapping_subapp_list.unshift(['all','ALL']);
            Rhombus_Datatable.addDropdown(subapps_mapping_subapp_list, $("#subapps_mapping_input_controllers"), (value, text)=>{
                if(value.length > 2 && value.includes('all')){
                    $("#subapps_mapping_input_controllers select").val(['all']).trigger('change');
                } else if(value.length == 2 && value[0] == 'all'){
                    $("#subapps_mapping_input_controllers select").val(value[1]).trigger('change');
                }
            }, false, {multiple:true});

            Rhombus_Datatable.addDropdown(data.subapps_alias_list, $("#subapps_mapping_input_subapps"), (selected_subapp, text)=>{
                init_subapp_controller_mapping_callback(data.result, selected_subapp);
            });
            init_subapp_controller_mapping_callback(data.result, data.subapps_alias_list[0][0]);
            $("#subapps_mapping_input_subapps_container").insertBefore("#subapps_mapping_input_controllers_container");

            subapps_table = new Rhombus_Datatable({
                "table_name" : "subapps_table", 
                "datatable_properties" : datatable_properties, 
                "export_div":false,
                "form_ids" :  {"title": "subappsFormModalTitle", "button": "subappsSubmitRecord", "modal": "subappsFormModal", "form": "subappsForm"},
                "error_ids" : ["subapps_error_name"],
                "delete_modal_ids":{ "heading": "subapps_confirmDeleteHeading", "message": "subapps_confirmDeleteMessage", "confirm": "subapps_confirmDeleteBtn", "modal": "subapps_confirmDelete", },
                "overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
                "set_form_values" : function(selected_row_data){
                    $("#subapps_input_id").val(selected_row_data.id);
                    $("#subapps_input_name select").val(selected_row_data.name).trigger('change');
                    $("#subapps_input_subapps_alias select").val(JSON.parse(selected_row_data.subapps_alias)).trigger('change');
                },
                "get_form_values" : function(){
                    return {
                        "id": $("#subapps_input_id").val(),
                        "name": $("#subapps_input_name select").val(),
                        "subapps_alias":'['+$("#subapps_input_subapps_alias select").val().map(Number).toString()+']'
                    };
                },
                "delete_message":function(selected_row_data){ return "Controller Name: " + selected_row_data["name"]},
                "refresh_callback":(data)=>{
                    if('misc' in data){
                        let temp_data = {
                            "result":data["result"], 
                            ...data["misc"]
                        }
                        reload_subapps(temp_data, false);
                    }

                }
            });
            subapps_table.init_table();
            subapps_table.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'subapps'});
            subapps_table.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'subapps'});
        
            $('div.subapps_mapping_div').html('<button class="btn btn-secondary" id="subapps_mapping_button">Map</button>');
            $("#subapps_mappingFormModalTitle").text("Mapping (Modules : Applications)");
        
            $('#subapps_mapping_button').on('click', ()=>{
                $("#subapps_mappingFormModal").modal();
            });
        
            $("#subapps_mappingForm").on('submit', (event)=>{
                event.preventDefault();
                action_button("subapps_mappingSubmitRecord", "add");
                let controllers = $("#subapps_mapping_input_controllers select").val();
                let subapps = $("#subapps_mapping_input_subapps select").val();
                let post_data = {
                    'rhombus_token': rhombuscookie(),
                    'mapping_type': subapps_mapping_pattern
                }
                post_data['subapps'] = (typeof subapps == 'string')?
                    '['+subapps+']':'['+subapps.map(Number).toString()+']';
                
                post_data['controllers'] = (typeof controllers == 'string')?[controllers]:controllers;
                $.post("/facs_manager/subapps_mapping", post_data, (data)=>{
                    reload_subapps(data);
                    action_button("subapps_mappingSubmitRecord", "remove");
                    $("#subapps_mappingFormModal").modal("hide");
                },'json');
            })

            function check_duplicate(submit_type, form_values){
                if(
                    submit_type == "editData" && 
                    form_values["name"] == subapps_table.selectedRowData["name"]
                ){
                    return false;
                };

                return subapps_table.check_record_exist(function(table_data){
                    return (table_data["name"] == form_values["name"])
                });
            }

            // Get references to the radio buttons
            const radio1 = document.getElementById('subapps_mapping_pattern_1');
            const radio2 = document.getElementById('subapps_mapping_pattern_2');
            const radio3 = document.getElementById('subapps_mapping_pattern_3');
            radio1.removeEventListener('change', find_subapps_mapping_pattern_type);
            radio2.removeEventListener('change', find_subapps_mapping_pattern_type);
            radio3.removeEventListener('change', find_subapps_mapping_pattern_type);
            
            radio1.addEventListener('change', () => {
                find_subapps_mapping_pattern_type(data);
                $("#subapp_mapping_alert").addClass('d-none');
            });
            
            radio2.addEventListener('change', () => {
                find_subapps_mapping_pattern_type(data);
                $("#subapp_mapping_alert").addClass('d-none');
            });
            
            radio3.addEventListener('change', () => {
                find_subapps_mapping_pattern_type(data);
                $("#subapp_mapping_alert").removeClass('d-none');
            });

        }, "json");
    } else{
        $.post("/facs_manager/get_facs", { facs_type: 'subapps', rhombus_token: rhombuscookie() }, function (data, status) {
            reload_subapps(data);
        },"json");
    }
});

function find_subapps_mapping_pattern_type(table_data){
	let mapping_pattern_val = $('input[name="subapps_mapping_pattern"]:checked').val();
	let input_ids = ["#subapps_mapping_input_controllers", "#subapps_mapping_input_subapps"];
	for(const i in input_ids){
		$(input_ids[i]).html("");
	}

    if(mapping_pattern_val == "subapps_mapping_pattern_1"){
		subapps_mapping_pattern = 'single_subapp_multiple_controllers';
	} else if(mapping_pattern_val == "subapps_mapping_pattern_2"){
		subapps_mapping_pattern = 'single_controller_multiple_subapps';
	} else{
		subapps_mapping_pattern = 'multiple_subapps_multiple_controllers';
	}
	if(subapps_mapping_pattern == 'single_subapp_multiple_controllers'){
        let subapps_mapping_subapp_list = table_data.controller_list.slice();
        subapps_mapping_subapp_list.unshift(['all','ALL']);
        Rhombus_Datatable.addDropdown(subapps_mapping_subapp_list, $("#subapps_mapping_input_controllers"), (value, text)=>{
            if(value.length > 2 && value.includes('all')){
                $("#subapps_mapping_input_controllers select").val(['all']).trigger('change');
            } else if(value.length == 2 && value[0] == 'all'){
                $("#subapps_mapping_input_controllers select").val(value[1]).trigger('change');
            }
        }, false, {multiple:true});

        Rhombus_Datatable.addDropdown(table_data.subapps_alias_list, $("#subapps_mapping_input_subapps"), (selected_subapp, text)=>{
            init_subapp_controller_mapping_callback(table_data.result, selected_subapp);
        });
        init_subapp_controller_mapping_callback(table_data.result, table_data.subapps_alias_list[0][0]);
        $("#subapps_mapping_input_subapps_container").insertBefore("#subapps_mapping_input_controllers_container");
	} else if(subapps_mapping_pattern == 'single_controller_multiple_subapps'){
		Rhombus_Datatable.addDropdown(table_data.subapps_alias_list, $("#subapps_mapping_input_subapps"), ()=>{}, false, {multiple:true});
		Rhombus_Datatable.addDropdown(table_data.controller_list, $("#subapps_mapping_input_controllers"), (selected_controller, text)=>{
			init_controller_subapp_mapping_callback(table_data.result, selected_controller);
		});
		init_controller_subapp_mapping_callback(table_data.result, table_data.controller_list[0][0]);
        $("#subapps_mapping_input_controllers_container").insertBefore("#subapps_mapping_input_subapps_container");

	} else {
		Rhombus_Datatable.addDropdown(table_data.subapps_alias_list, $("#subapps_mapping_input_subapps"), ()=>{}, false, {multiple:true});

        let subapps_mapping_subapp_list = table_data.controller_list.slice();
		subapps_mapping_subapp_list.unshift(['all','ALL']);
		Rhombus_Datatable.addDropdown(subapps_mapping_subapp_list, $("#subapps_mapping_input_controllers"), (value, text)=>{
			if(value.length > 2 && value.includes('all')){
				$("#subapps_mapping_input_controllers select").val(['all']).trigger('change');
			} else if(value.length == 2 && value[0] == 'all'){
				$("#subapps_mapping_input_controllers select").val(value[1]).trigger('change');
			}
		}, false, {multiple:true});
        $("#subapps_mapping_input_subapps_container").insertBefore("#subapps_mapping_input_controllers_container");
	}
}

function init_subapp_controller_mapping_callback(table_data, selected_subapp){
    let find_controllers = table_data.map((val, index)=>{
        let parsed_subapps_alias = JSON.parse(val['subapps_alias'])
        let val_subapps_alias = parsed_subapps_alias?parsed_subapps_alias:[];
        if(val_subapps_alias.includes(parseInt(selected_subapp))){
            return val.name
        }
    })
    let unique_controllers = [...new Set(find_controllers)];
    let expanded_controllers = unique_controllers.flat().filter((value) => value !== undefined);

    $("#subapps_mapping_input_controllers select").val(expanded_controllers).trigger('change');
}

function init_controller_subapp_mapping_callback(table_data, selected_controller){
	let find_subapps = table_data.map((val, index)=>{
		if(val['name'] == selected_controller){
			return JSON.parse(val.subapps_alias)
		}
	})
	let expanded_subapps = find_subapps.flat().filter((value) => value !== undefined);
	let unique_subapps = [...new Set(expanded_subapps)];
	if(unique_subapps.includes(undefined)){
		unique_subapps = [];
	}
	$("#subapps_mapping_input_subapps select").val(unique_subapps).trigger('change');
}

function reload_subapps(data, refresh=true){
	subapps_mapping_pattern = 'single_subapp_multiple_controllers';
    let input_ids = ["#subapps_input_name", "#subapps_input_subapps_alias", "#subapps_mapping_input_controllers", "#subapps_mapping_input_subapps"];
    for(const i in input_ids){
        $(input_ids[i]).html("");
    }

    	// Get references to the radio buttons
        const radio1 = document.getElementById('subapps_mapping_pattern_1');
        const radio2 = document.getElementById('subapps_mapping_pattern_2');
        const radio3 = document.getElementById('subapps_mapping_pattern_3');
        radio1.removeEventListener('change', find_subapps_mapping_pattern_type);
        radio2.removeEventListener('change', find_subapps_mapping_pattern_type);
        radio3.removeEventListener('change', find_subapps_mapping_pattern_type);
        
        radio1.addEventListener('change', () => {
            find_subapps_mapping_pattern_type(data);
        });
        
        radio2.addEventListener('change', () => {
            find_subapps_mapping_pattern_type(data);
        });
        
        radio3.addEventListener('change', () => {
            find_subapps_mapping_pattern_type(data);
        });

	if(!radio1.checked){
		radio1.checked=true;
	}
    $("#subapp_mapping_alert").addClass('d-none');

    Rhombus_Datatable.addDropdown(data.controller_list, $("#subapps_input_name"));
    Rhombus_Datatable.addDropdown(data.subapps_alias_list, $("#subapps_input_subapps_alias"), ()=>{}, false, {multiple:true});

    let subapps_mapping_subapp_list = data.controller_list.slice();
    subapps_mapping_subapp_list.unshift(['all','ALL']);
    Rhombus_Datatable.addDropdown(subapps_mapping_subapp_list, $("#subapps_mapping_input_controllers"), (value, text)=>{
        if(value.length > 2 && value.includes('all')){
            $("#subapps_mapping_input_controllers select").val(['all']).trigger('change');
        } else if(value.length == 2 && value[0] == 'all'){
            $("#subapps_mapping_input_controllers select").val(value[1]).trigger('change');
        }
    }, false, {multiple:true});

    Rhombus_Datatable.addDropdown(data.subapps_alias_list, $("#subapps_mapping_input_subapps"), (selected_subapp, text)=>{
        init_subapp_controller_mapping_callback(data.result, selected_subapp);
    });
    init_subapp_controller_mapping_callback(data.result, data.subapps_alias_list[0][0]);
    $("#subapps_mapping_input_subapps_container").insertBefore("#subapps_mapping_input_controllers_container");

    subapps_label_mappings = Object.fromEntries(new Map(data.subapps_alias_list));

    if(refresh){
        subapps_table.refreshTable(data);
    }
}

window._rb = {
    first_load_subapps: first_load_subapps
}
