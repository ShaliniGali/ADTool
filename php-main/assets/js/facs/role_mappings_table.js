let role_mappings_table = {};
let first_load_role_mappings = true;
let label_mappings = {};
let mapping_pattern = 'single_role_multiple_subapp';

$('#role_mappings_tab').on('shown.bs.tab',function(){
    if(first_load_role_mappings){
        first_load_role_mappings = false;
        $.post("/facs_manager/get_facs", { facs_type: 'role_mappings', rhombus_token: rhombuscookie() }, function (data, status) {
			load_role_mappings(data);
        }, "json");
    } else{
        $.post("/facs_manager/get_facs", { facs_type: 'role_mappings', rhombus_token: rhombuscookie() }, function (data, status) {
            reload_role_mappings(data);
        },"json");

    }
});

function load_role_mappings(data) {
	label_mappings = {
		'apps': Object.fromEntries(new Map(data.app_list)),
		'subapps': Object.fromEntries(new Map(data.subapp_list)),
		'features': Object.fromEntries(new Map(data.feature_list)),
		'roles': Object.fromEntries(new Map(data.roles_list)),
		'subapps_alias_list': Object.fromEntries(new Map(data.subapps_alias_list)),
		'subapps_mapping':data.subapps_mapping
	};
	let columns = [
		{
			'data': "id", // This key will come from the initial data
			'render': function(data, type, row) { // (data) points to the data given from the line above. row points to all the data in that row. eg. row.name or row.key
				return "<span>" + data  + Rhombus_Datatable.copy_button + "</span>" // The return value is what will be displayed in each cell for the column
			},
			'visible':false
		},
		{
			'data': "subapps",
			'render': function(data, type, row) {
				let parsed_data = JSON.parse(label_mappings.subapps_mapping[row["subapp_id"]]);
				let render_html = '';
				for(const i in parsed_data){
					if(i%2 == 0 && i !=0){
						render_html += "<br />"
					}
					if(parsed_data[i] in label_mappings.subapps_alias_list){
						render_html += "<span class='badge badge-pill badge-warning px-2 mr-2'>" + label_mappings.subapps_alias_list[parsed_data[i]] + "</span>"
					}
				}
				return render_html;
			}
		},
		{
			'data': "subapp_id",
			'render': function(data, type, row) {
				return "<span>" + label_mappings.subapps[data]  + Rhombus_Datatable.copy_button + "</span>"
			}
		},
		{
			'data': "feature_id",
			'render': function(data, type, row) {
				return "<span>" + label_mappings.features[data]  + Rhombus_Datatable.copy_button + "</span>"
			}
		},
		{
			'data': "user_role_id",
			'render': function(data, type, row) {
				let parsed_data = JSON.parse(data);
				let render_html = '';
				for(const i in parsed_data){
					if(i%2 == 0 && i !=0){
						render_html += "<br />"
					}
					if(parsed_data[i] in label_mappings.roles){
						render_html += "<span class='badge badge-pill badge-primary px-2 mr-2'>" + label_mappings.roles[parsed_data[i]] + "</span>"
					}
				}
				return render_html;
			}
		},
		{ 'defaultContent': "<span class='d-block text-right mr-5'>" +
			Rhombus_Datatable.edit_button("role_mappings_table") + "</span>"
		}
	]
	let datatable_properties = {
		"dom":"<'row'<'col-sm-12 col-md-3'l><'col-sm-12 col-md-4 autopop_container'><'col-6 col-md-5 px-0'f>>" +
		"<'row'<'col-sm-12'tr>>" +
		"<'row'<'col-sm-5'i><'col-sm-7 text-right'p>>",
		"data": Object.values(data.result),
		"columns": columns,
		"scrollY":"60vh"
	};


	Rhombus_Datatable.addDropdown(data.app_list, $("#role_mappings_input_app"));
	Rhombus_Datatable.addDropdown(data.subapp_list, $("#role_mappings_input_subapp"));
	Rhombus_Datatable.addDropdown(data.feature_list, $("#role_mappings_input_feature"));
	Rhombus_Datatable.addDropdown(data.roles_list, $("#role_mappings_input_user_roles"), ()=>{}, false, {multiple:true});

	let autopop_subapp_list = data.subapps_alias_list.slice();
	autopop_subapp_list.unshift(['all','ALL']);
	Rhombus_Datatable.addDropdown(autopop_subapp_list, $("#autopop_input_subapps"), (value, text)=>{
		if(value.length > 2 && value.includes('all')){
			$("#autopop_input_subapps select").val(['all']).trigger('change');
		} else if(value.length == 2 && value[0] == 'all'){
			$("#autopop_input_subapps select").val(value[1]).trigger('change');
		}
	}, false, {multiple:true});

	Rhombus_Datatable.addDropdown(data.roles_list, $("#autopop_input_user_roles"), (selected_role, text)=>{
		init_role_subapp_mapping_callback(data.result, selected_role);
	});
	init_role_subapp_mapping_callback(data.result, data.roles_list[0][0]);
	$("#autopop_input_user_roles_container").insertBefore("#autopop_input_subapps_container");

	role_mappings_table = new Rhombus_Datatable({
		"table_name" : "role_mappings_table", 
		"datatable_properties" : datatable_properties, 
		"export_div": false,
		"form_ids" :  {"title": "role_mappingsFormModalTitle", "button": "role_mappingsSubmitRecord", "modal": "role_mappingsFormModal", "form": "role_mappingsForm"},
		"error_ids" : ["role_mappings_error_name"],
		"delete_modal_ids":{ "heading": "role_mappings_confirmDeleteHeading", "message": "role_mappings_confirmDeleteMessage", "confirm": "role_mappings_confirmDeleteBtn", "modal": "role_mappings_confirmDelete", },
		"overwrite_modal_ids":{ "modal": "duplicateRecord", "confirm": "confirmDuplicateRecordBtn" },
		"set_form_values" : function(selected_row_data){
			$("#role_mappings_input_id").val(selected_row_data.id);
			$("#role_mappings_input_app select").val(selected_row_data.app_id).trigger('change');
			$("#role_mappings_input_subapp select").val(selected_row_data.subapp_id).trigger('change');
			$("#role_mappings_input_feature select").val(selected_row_data.feature_id).trigger('change');
			$("#role_mappings_input_user_roles select").val(JSON.parse(selected_row_data.user_role_id)).trigger('change');
		},
		"get_form_values" : function(){
			return {
				'id': parseInt($("#role_mappings_input_id").val()),
				'app_id': parseInt($("#role_mappings_input_app select").val()),
				'subapp_id': parseInt($("#role_mappings_input_subapp select").val()),
				'feature_id': parseInt($("#role_mappings_input_feature select").val()),
				'user_role_id': '['+$("#role_mappings_input_user_roles select").val().map(Number).toString()+']'
			};
		},
		"delete_message":function(selected_row_data){ return "This will clear all roles for Role Mapping ID: " + selected_row_data["id"]},
		"refresh_callback":(data)=>{
			if('misc' in data){
				let temp_data = {
					"result":data["result"], 
					...data["misc"]
				}
				reload_role_mappings(temp_data, false);
			}

		}
	});
	role_mappings_table.init_table();
	role_mappings_table.initialize_delete("/facs_manager/delete_facs", false, {'facs_type':'role_mappings'});
	role_mappings_table.initialize_submit("/facs_manager/add_facs", "/facs_manager/edit_facs", check_duplicate, false, {'facs_type':'role_mappings'});
	$('div.autopop_container').html('<button class="btn btn-secondary" id="autopop_button">Map</button>');
	$("#autopopFormModalTitle").text("Mapping (Applications : Access Roles)");

	$('#autopop_button').on('click', ()=>{
        $("#autopopFormModal").modal();
	});

	$("#autopopForm").on('submit', (event)=>{
		event.preventDefault();
		action_button("autopopSubmitRecord", "add");
		let user_roles = $("#autopop_input_user_roles select").val();
		let subapps = $("#autopop_input_subapps select").val();
		let post_data = {
			'rhombus_token': rhombuscookie(),
			'mapping_type': mapping_pattern
		}
		post_data['user_roles'] = (typeof user_roles == 'string')?
			'['+user_roles+']':'['+user_roles.map(Number).toString()+']';
		
		post_data['subapps'] = (typeof subapps == 'string')?[subapps]:subapps;
		
		$.post("/facs_manager/autopop", post_data, (data)=>{
			reload_role_mappings(data);
			action_button("autopopSubmitRecord", "remove");
			$("#autopopFormModal").modal("hide");
		},'json');
	})

	function check_duplicate(submit_type, form_values){
		if(submit_type == "editData" && 
			form_values["app_id"] == role_mappings_table.selectedRowData["app_id"] &&
			form_values["subapp_id"] == role_mappings_table.selectedRowData["subapp_id"] &&
			form_values["feature_id"] == role_mappings_table.selectedRowData["feature_id"] &&
			compare_arrays(JSON.parse(form_values["user_role_id"]), JSON.parse(role_mappings_table.selectedRowData["user_role_id"]))
		)   return true;
	
		if(
			submit_type == "editData" && 
			form_values["app_id"] == role_mappings_table.selectedRowData["app_id"] &&
			form_values["subapp_id"] == role_mappings_table.selectedRowData["subapp_id"] &&
			form_values["feature_id"] == role_mappings_table.selectedRowData["feature_id"]
		)   return false;
	
		return role_mappings_table.check_record_exist(function(table_data){
			return (
				form_values["app_id"] == table_data["app_id"] &&
				form_values["subapp_id"] == table_data["subapp_id"] &&
				form_values["feature_id"] == table_data["feature_id"]
			)
		});
	}

	function compare_arrays(array1, array2){
		let array2Sorted = array2.slice().sort();
		return array1.length === array2.length && array1.slice().sort().every(function(value, index) {
			return value === array2Sorted[index];
		});
	}

	// Get references to the radio buttons
	const radio1 = document.getElementById('mapping_pattern_1');
	const radio2 = document.getElementById('mapping_pattern_2');
	const radio3 = document.getElementById('mapping_pattern_3');
	radio1.removeEventListener('change', find_mapping_pattern_type);
	radio2.removeEventListener('change', find_mapping_pattern_type);
	radio3.removeEventListener('change', find_mapping_pattern_type);
	
	radio1.addEventListener('change', () => {
		find_mapping_pattern_type(data);
		$("#autopop_mapping_alert").addClass('d-none');
	});
	
	radio2.addEventListener('change', () => {
		find_mapping_pattern_type(data);
		$("#autopop_mapping_alert").addClass('d-none');
	});
	
	radio3.addEventListener('change', () => {
		find_mapping_pattern_type(data);
		$("#autopop_mapping_alert").removeClass('d-none');
	});
}

function find_mapping_pattern_type(table_data){
	let mapping_pattern_val = $('input[name="mapping_pattern"]:checked').val();
	let input_ids = ["#autopop_input_user_roles", "#autopop_input_subapps"];
	for(const i in input_ids){
		$(input_ids[i]).html("");
	}

	if(mapping_pattern_val == "mapping_pattern_1"){
		mapping_pattern = 'single_role_multiple_subapp';
	} else if(mapping_pattern_val == "mapping_pattern_2"){
		mapping_pattern = 'single_subapp_multiple_role';
	} else{
		mapping_pattern = 'multiple_role_multiple_subapp';
	}
	if(mapping_pattern == 'single_role_multiple_subapp'){
		let autopop_subapp_list = table_data.subapps_alias_list.slice();
		autopop_subapp_list.unshift(['all','ALL']);
		Rhombus_Datatable.addDropdown(autopop_subapp_list, $("#autopop_input_subapps"), (value, text)=>{
			if(value.length > 2 && value.includes('all')){
				$("#autopop_input_subapps select").val(['all']).trigger('change');
			} else if(value.length == 2 && value[0] == 'all'){
				$("#autopop_input_subapps select").val(value[1]).trigger('change');
			}
		}, false, {multiple:true});

		Rhombus_Datatable.addDropdown(table_data.roles_list, $("#autopop_input_user_roles"), (selected_role, text)=>{
			init_role_subapp_mapping_callback(table_data.result, selected_role);
		});
		init_role_subapp_mapping_callback(table_data.result, table_data.roles_list[0][0]);
		$("#autopop_input_user_roles_container").insertBefore("#autopop_input_subapps_container");
	} else if(mapping_pattern == 'single_subapp_multiple_role'){
		Rhombus_Datatable.addDropdown(table_data.roles_list, $("#autopop_input_user_roles"), ()=>{}, false, {multiple:true});
		Rhombus_Datatable.addDropdown(table_data.subapps_alias_list, $("#autopop_input_subapps"), (selected_subapp, text)=>{
			init_subapp_role_mapping_callback(table_data.result, selected_subapp);
		});
		init_subapp_role_mapping_callback(table_data.result, table_data.subapps_alias_list[0][0]);
		$("#autopop_input_subapps_container").insertBefore("#autopop_input_user_roles_container");
	} else {
		Rhombus_Datatable.addDropdown(table_data.roles_list, $("#autopop_input_user_roles"), ()=>{}, false, {multiple:true});

		let autopop_subapp_list = table_data.subapps_alias_list.slice();
		autopop_subapp_list.unshift(['all','ALL']);
		Rhombus_Datatable.addDropdown(autopop_subapp_list, $("#autopop_input_subapps"), (value, text)=>{
			if(value.length > 2 && value.includes('all')){
				$("#autopop_input_subapps select").val(['all']).trigger('change');
			} else if(value.length == 2 && value[0] == 'all'){
				$("#autopop_input_subapps select").val(value[1]).trigger('change');
			}
		}, false, {multiple:true});
		$("#autopop_input_user_roles_container").insertBefore("#autopop_input_subapps_container");
	}
}

function init_role_subapp_mapping_callback(table_data, selected_role){
    let find_subapps = table_data.map((val, index)=>{
        if(JSON.parse(val['user_role_id']).includes(parseInt(selected_role))){// found controller
            let related_subapps_alias = label_mappings['subapps_mapping'][val.subapp_id];
            return JSON.parse(related_subapps_alias)
        }
    })
    let expanded_subapps = find_subapps.flat().filter((value) => value !== undefined);
    let unique_subapps = [...new Set(expanded_subapps)];
    if(unique_subapps.includes(undefined)){
        unique_subapps = [];
    }

    $("#autopop_input_subapps select").val(unique_subapps).trigger('change');
}

function init_subapp_role_mapping_callback(table_data, selected_subapp){
    let find_roles = table_data.map((val, index)=>{
        let related_subapps_alias = label_mappings['subapps_mapping'][val.subapp_id];

        if(JSON.parse(related_subapps_alias).includes(parseInt(selected_subapp))){
            return JSON.parse(val.user_role_id)
        }
    })
    let expanded_roles = find_roles.flat().filter((value) => value !== undefined);
    let unique_roles = [...new Set(expanded_roles)];
    if(unique_roles.includes(undefined)){
        unique_roles = [];
    }
    $("#autopop_input_user_roles select").val(unique_roles).trigger('change');
}

function reload_role_mappings(data, refresh=true){
	mapping_pattern = 'single_role_multiple_subapp';
    let input_ids = ["#role_mappings_input_app", "#role_mappings_input_subapp", "#role_mappings_input_feature", "#role_mappings_input_user_roles", "#autopop_input_user_roles", "#autopop_input_subapps"];
    for(const i in input_ids){
        $(input_ids[i]).html("");
    }
	  
	// Get references to the radio buttons
	const radio1 = document.getElementById('mapping_pattern_1');
	const radio2 = document.getElementById('mapping_pattern_2');
	const radio3 = document.getElementById('mapping_pattern_3');

	radio1.removeEventListener('change', find_mapping_pattern_type);
	radio2.removeEventListener('change', find_mapping_pattern_type);
	radio3.removeEventListener('change', find_mapping_pattern_type);
	
	radio1.addEventListener('change', () => {
		find_mapping_pattern_type(data);
	});
	
	radio2.addEventListener('change', () => {
		find_mapping_pattern_type(data);
	});
	
	radio3.addEventListener('change', () => {
		find_mapping_pattern_type(data);
	});

	if(!radio1.checked){
		radio1.checked=true;
	}
	$("#autopop_mapping_alert").addClass('d-none');

    Rhombus_Datatable.addDropdown(data.app_list, $("#role_mappings_input_app"));
    Rhombus_Datatable.addDropdown(data.subapp_list, $("#role_mappings_input_subapp"));
    Rhombus_Datatable.addDropdown(data.feature_list, $("#role_mappings_input_feature"));
    Rhombus_Datatable.addDropdown(data.roles_list, $("#role_mappings_input_user_roles"), ()=>{}, false, {multiple:true});

	let autopop_subapp_list = data.subapps_alias_list.slice();
	autopop_subapp_list.unshift(['all','ALL']);
	Rhombus_Datatable.addDropdown(autopop_subapp_list, $("#autopop_input_subapps"), (value, text)=>{
		if(value.length > 2 && value.includes('all')){
			$("#autopop_input_subapps select").val(['all']).trigger('change');
		} else if(value.length == 2 && value[0] == 'all'){
			$("#autopop_input_subapps select").val(value[1]).trigger('change');
		}
	}, false, {multiple:true});

	Rhombus_Datatable.addDropdown(data.roles_list, $("#autopop_input_user_roles"), (selected_role, text)=>{
		init_role_subapp_mapping_callback(data.result, selected_role);
	});
	init_role_subapp_mapping_callback(data.result, data.roles_list[0][0]);
	$("#autopop_input_user_roles_container").insertBefore("#autopop_input_subapps_container");

    label_mappings = {
        'apps': Object.fromEntries(new Map(data.app_list)),
        'subapps': Object.fromEntries(new Map(data.subapp_list)),
        'features': Object.fromEntries(new Map(data.feature_list)),
        'roles': Object.fromEntries(new Map(data.roles_list)),
		'subapps_alias_list': Object.fromEntries(new Map(data.subapps_alias_list)),
		'subapps_mapping':data.subapps_mapping
    };

	if(refresh){
		role_mappings_table.refreshTable(data);
    }
    
}
