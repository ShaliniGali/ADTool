"use strict";
/**
 * Created: Sai August 11 2020
 * Updated: Ian Sept 2020
 */
 let siteUrlData = {};
 let siteUrl;
 let accountManagerTable;
 let RBDatatable = new RhombusDatatable();
 let globalDeleteRow = {
     'id': null,
     'email': null
 };

 function getGlobalDeleteRow(){
     return globalDeleteRow;
 }

 function setGlobalDeleteRow(curr_id,curr_email){
    globalDeleteRow['id'] = curr_id;
    globalDeleteRow['email'] = curr_email;
 }

 function renderUserApps(apps, css) {
    let render_html = "";
    for(const i in apps){
        render_html += "<div class='badge-text d-flex flex-row justify-content-center mb-1'><span class='" + css + " badge badge-pill mr-2'>" + apps[i] + "</span></div>"
    }

    return render_html;
 }
 

 function constructSSODatatable(data) {
     $('#app_name > span').html(app_name);

     let columnsDef = null;
     if(HAS_SUBAPPS == '1'){
        columnsDef = [
            {"width": "22%", "targets": 1},
            {"width": "10%", "targets": 2},
            {
                "render": function (data, type, row) {
                    return renderUserApps(
                        row.requested_apps, 
                        'badge-info'
                    );
                },
                "targets": 5
            },
            {
                "render": function (data, type, row) {
                    return renderUserApps(
                        row.active_apps, 
                        'badge-success'
                    );
                },
                "targets": 6
            },  
            {
                "render": function (data, type, row) {
                    return "<button class = 'btn btn-success subapps' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Register Subapps\"><i class=\"fas fa-edit\"></i></button>";
                },
                "targets": 7
            },
            {
                "render": function (data, type, row) {
                    return "<button class = 'rowBtn' id = 'delete' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\"><i class=\"far fa-trash-alt\"></i></button>";
                },
                "targets": 8
            },
        ];
     }
     else{
        columnsDef = [
            {"width": "22%", "targets": 1},
            {"width": "10%", "targets": 2},
            {
                "render": function (data, type, row) {
                    return renderUserApps(
                        row.requested_apps, 
                        ['badge-info', 'text-info border border-info badge-light', 'requested'], 
                        app_name
                    );
                },
                "className": "requested_user",
                "targets": 5
            },
            {
                "render": function (data, type, row) {
                    return renderUserApps(
                        row.active_apps, 
                        ['badge-success', 'text-success border border-success badge-light', 'approved'],
                        app_name
                    );
                },
                "className": "approved_user",
                "targets": 6
            },    
            {
                "render": function (data, type, row) {
                    return account_type_select;
                },
                "targets": 7
            },
            {
                "render": function (data, type, row) {
                    return "<input type='text' autocomplete='off'  name='admin_expiry' class='form-control admin_expiry'  placeholder='Expiration date' disabled>";
                },
                "targets": 8
            },
            {
                "render": function (data, type, row) {
                    if (row.status == "Active")
                        return "<button class = 'btn btn-success save' data-toggle=\"tooltip\" data-placement=\"top\" title=\"save\" disabled>Save</button>";
                    else
                        return "<button class = 'btn btn-success register' data-toggle=\"tooltip\" data-placement=\"top\" title=\"register\" disabled>Register</button>";
                },
                "targets": 9
            },
            {
                "render": function (data, type, row) {
                    return "<button class = 'btn btn-success subapps' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Register Subapps\"><i class=\"fas fa-edit\"></i></button>";
                },
                "targets": 10
            },
            {
                "render": function (data, type, row) {
                    return "<button class = 'rowBtn' id = 'delete' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\"><i class=\"far fa-trash-alt\"></i></button>";
                },
                "targets": 11
            },
        ];
     }
     accountManagerTable = $('#accountManagerTable').DataTable({
 
         "data": Object.values(data),
         'dom': "<'row'<'col-sm-12'l><'col-md-2'><'col-sm-12 col-md-5 text-center'B><'col-sm-12 col-md-3 text-right'f><'col-sm-12 col-md-1 toolbar'><'col-sm-12 col-md-1 exportDiv'>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-12 col-md-1' i><'col-12 col-md-11 text-right'p>>",
         'columns': columns.map(function (el) {
             let o;
             if (el.data != null) {
                 // Assigns copy button to each value except delete and update
                 o = Object.assign({}, el);
                 o.render = function (data, type, full) {
                     if (data) { return "<span>" + data + RBDatatable.showCopyButton + "</span>" } else { return " "; }
                 }
             } else {
                 o = null;
             }
             return o;
         }),
         "deferRender": false,
         "scrollY": "55vh",
         "scrollCollapse": true,
         "paging": false,
         // "bSort": false,
         "order": [
             [2, "desc"]
         ],
         "columnDefs" : columnsDef,
         "fnDrawCallback": function (settings, json) {
 
             RBDatatable.initCopyFunctionality();
             RBDatatable.initTooltips([$(".rowBtn")]);
             $('[data-toggle="tooltip"]').tooltip();
 
 
         },
         buttons: {
             buttons: [{
                 "extend": "excel",
                 "text": "<i class=\"fas fa-download\"></i>",
                 "exportOptions": {
                     "columns": ":visible"
                 }
             }]
         },
        createdRow: function(row, data, index) {
            
            row.cells.item(0).innerHTML = `
            <div style="white-space: nowrap;"><i class="fas fa-envelope"></i> <span class="pl-3">${data.email}<button class="copyBtn" data-toggle="tooltip" data-placement="top" title="" data-original-title="copied"><i class="far fa-copy fa-xs"></i></button></span></div>`;
        },
     });
     /**
      * Initiates column toggle functionality
      */
     RBDatatable.createColumnSelector(columns, accountManagerTable, $("div.toolbar"));
     RBDatatable.repositionExportButton($("div.exportDiv"));
 
     /**
      * populating dropdowns and 
      */
      initDefaultValues();
     /**
      * initializing edit, register and delete  options
      */
     initalizeEditOptions();
     initRegisterButtons();
     initSubappsButtons();
 
     function initSubappsButtons(){
        let subapp = $(".subapps");

        $.each(subapp, function (index, value) {
            value.addEventListener('click', function () {
                let selectedRowData = accountManagerTable.row($(this).parents('tr')).data();
				let parentTr = $(this).closest('tr');
                subapps_modal.show()
                initSubappsModal(selectedRowData, parentTr);
            })
        })
     }

     function initSubappsModal(selectedRowData, parentTr){
        $('#subapps_account_type_save').html('');
        let htmlLoad = '';
        selectedRowData['requested_apps'].forEach(element => {
            let accountSubString = account_type_select;
            let currSubstring = selectedRowData['account_type_subapp'][element];
            accountSubString = accountSubString.replace('value="'+currSubstring+'"','value="'+currSubstring+'" selected');
            htmlLoad+= '<div class="d-flex flex-row my-1 subapp_wrapper"><div class="subapp_tag">'+element+'</div><div width="100%" id="select-wrapper-'+element.replace(/[\.,]/g, "").replaceAll('&', '').replaceAll(' ','-')+'">'+accountSubString+'</div></div>';
        });
        $('#subapps_account_type_save').html(htmlLoad);

        let htmlLoad2 = '';
        selectedRowData['active_apps'].forEach(element => {
            let accountSubString = account_type_select;
            let currSubstring = selectedRowData['account_type_subapp'][element];
            accountSubString = accountSubString.replace('value="'+currSubstring+'"','value="'+currSubstring+'" selected');
            htmlLoad2+= '<div class="d-flex flex-row my-1 subapp_wrapper"><div class="subapp_tag_2">'+element+'</div><div width="100%" id="select-wrapper-'+element.replace(/[\.,]/g, "").replaceAll('&', '').replaceAll(' ','-')+'">'+accountSubString+'</div></div>';
        });

        $('#subapps_account_type_save_2').html(htmlLoad2);

        $('#subapp_save').unbind('click');
        $('#subapp_save').click(function(){
            registerSSOUser(selectedRowData, parentTr).done(function(response) {
				postRegisterSubapps('.subapp_tag', selectedRowData);
			});
        });

        $('#subapp_save_2').unbind('click');
        $('#subapp_save_2').click(function(){
            postRegisterSubapps('.subapp_tag_2',selectedRowData)
        });
     }

     function postRegisterSubapps(className,selectedRowData){
        let payloadSub = {};
        $(className).each( function(i) {
            let subId = $(className)[i].innerHTML.replace(/[\.,]/g, "").replaceAll(/&amp;/g, '').replaceAll(' ','-');
            payloadSub[subId] = {
                'label': $(className)[i].innerHTML,
                'type': $('#select-wrapper-'+subId+' select').val()
            }
        })
        $.post("/account_manager/registerSubappsType", {
            'email': selectedRowData["email"],
            'payloadSub': payloadSub,
            'rhombus_token': rhombuscookie()
        }, function (data, status) {
            subapps_modal.hide()
            if (data.status == "success") {
                $("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-check-circle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Success!<p>');
                $("#account_update_modal_body").html(sanitizeHtml('<span>User ' + selectedRowData["email"] + ' roles changed successfully.</span>', { allowedAttributes:false, allowedTags:false,}));
            } else {
                $("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-exclamation-triangle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Failure!<p>');
                $("#account_update_modal_body").html(sanitizeHtml('<span>Failed to save ' + selectedRowData["email"] + '.</span>', { allowedAttributes:false, allowedTags:false,}));
            }
            $("#account_update_modal_button2").addClass("d-none");
            $("#account_update_modal").modal("show");
        }, 'json');
     }
     /**
      * initialize register button functionality, show modal
      */
     function initRegisterButtons() {
         let register = $(".register");
 
         $.each(register, function (index, value) {
             value.addEventListener('click', function () {
                let parentTr = $(this).closest('tr');
 
                 let selectedRowData = accountManagerTable.row($(this).parents('tr')).data();
                 $("#input_email").html(sanitizeHtml(selectedRowData["email"], { allowedAttributes:false, allowedTags:false,}));
                 $("#input_notes").empty();
 
                 siteUrlData = {
                     "id": selectedRowData["id"],
                     "email": selectedRowData["email"],
                     "type": "admin_verify"
                 };
                 $('#registerSSOBtn').unbind('click');
                 $('#registerSSOBtn').click(function(){
					registerSSOUser(selectedRowData, parentTr);
                 });
 
                 $('#sso-confrimation').html(sanitizeHtml('Confirm registration for the user ' + selectedRowData["email"] + '?', { allowedAttributes:false, allowedTags:false,}));
                 $("#registerSSO").modal();
             });
         });
     }
 }

 function registerSSOUser(selectedRowData, parentTr) {
	return $.post("/account_manager/registerSSOUser", {
		id: selectedRowData["id"],
		email: selectedRowData["email"],
		account_type: parentTr.find('.account_type').val(),
		rhombus_token: rhombuscookie()
	}, function (data, status) {
		if (data.status == "success") {
			$("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-check-circle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Success!<p>');
			$("#account_update_modal_body").html(sanitizeHtml('<span>User ' + selectedRowData["email"] + ' registered successfully.</span>', { allowedAttributes:false, allowedTags:false,}));
			updateTable();
		} else {
			$("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-exclamation-triangle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Failure!<p>');
			$("#account_update_modal_body").html(sanitizeHtml('<span>Failed to register ' + selectedRowData["email"] + '.</span>', { allowedAttributes:false, allowedTags:false,}));
		}
		$("#registerSSO").modal('hide');
		$("#account_update_modal_button1").addClass("d-none");
		$("#account_update_modal_button2").addClass("d-none");
		$("#account_update_modal").modal("show");

	}, 'json');
 }
 
 function constructAccountsDatatable(data) {
     accountManagerTable = $('#accountManagerTable').DataTable({
 
         "data": Object.values(data),
         'dom': "<'row'<'col-sm-12'l><'col-md-2'><'col-sm-12 col-md-5 text-center'B><'col-sm-12 col-md-3 text-right'f><'col-sm-12 col-md-1 toolbar'><'col-sm-12 col-md-1 exportDiv'>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-12 col-md-1' i><'col-12 col-md-11 text-right'p>>",
         'columns': columns.map(function (el) {
             let o;
             if (el.data != null) {
                 // Assigns copy button to each value except delete and update
                 o = Object.assign({}, el);
                 o.render = function (data, type, full) {
                     if (data) { return "<span>" + data + RBDatatable.showCopyButton + "</span>" } else { return " "; }
                 }
             } else {
                 o = null;
             }
             return o;
         }),
         "deferRender": true,
         "scrollY": "55vh",
         "scrollCollapse": true,
         "paging": false,
         // "bSort": false,
         "order": [
             [2, "desc"]
         ],
         "columnDefs": [
             {
                 "render": function (data, type, row) {
                     return account_type_select;
                 },
                 "targets": 5
             },
             {
                 "render": function (data, type, row) {
                     return "<input type='text' autocomplete='off'  name='admin_expiry' class='form-control admin_expiry'  placeholder='Expiration date' disabled>";
                 },
                 "targets": 6
             },
             {
                 "render": function (data, type, row) {
                     if (row.status == "Active")
                         return "<button class = 'btn btn-success save' data-toggle=\"tooltip\" data-placement=\"top\" title=\"save\" disabled>Save</button>";
                     else
                         return "<button class = 'btn btn-success register' data-toggle=\"tooltip\" data-placement=\"top\" title=\"register\">Register</button>";
                 },
                 "targets": 7
             },
             {
                 "render": function (data, type, row) {
                     return "<button class = 'rowBtn' id = 'delete' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\"><i class=\"far fa-trash-alt\"></i></button>";
                 },
                 "targets": 8
             }
         ],
         "fnDrawCallback": function (settings, json) {
 
             RBDatatable.initCopyFunctionality();
             RBDatatable.initTooltips([$(".rowBtn")]);
             $('[data-toggle="tooltip"]').tooltip();
 
 
         },
         buttons: {
             buttons: [{
                 "extend": "excel",
                 "text": "<i class=\"fas fa-download\"></i>",
                 "exportOptions": {
                     "columns": ":visible"
                 }
             }]
         }
     });
     /**
      * Initiates column toggle functionality
      */
     RBDatatable.createColumnSelector(columns, accountManagerTable, $("div.toolbar"));
     RBDatatable.repositionExportButton($("div.exportDiv"));
     /**
      * populating dropdowns and 
      */
     initDefaultValues();
     /**
      * initializing edit, register and delete  options
      */
     initalizeEditOptions();
     initRegisterButtons();
 
     /**
      * initialize register button functionality, show modal
      */
     function initRegisterButtons() {
         let register = $(".register");
 
         $.each(register, function (index, value) {
             value.addEventListener('click', function () {
 
                 let selectedRowData = accountManagerTable.row($(this).parents('tr')).data();
                 $("#input_email").html(sanitizeHtml(selectedRowData["email"], { allowedAttributes:false, allowedTags:false,}));
                 $("#account_type").html(sanitizeHtml(selectedRowData["account_type"], { allowedAttributes:false, allowedTags:false,}));
                 $("#input_notes").empty();
                 $("#admin_expiry").val(selectedRowData["admin_expiry"]);
 
                 siteUrlData = {
                     "id": selectedRowData["id"],
                     "email": selectedRowData["email"],
                     "type": "admin_verify",
                     "account_type": selectedRowData["account_type"]
                 };
 
                 $("#formModal").modal();
             });
         });
     }
 }
 
 $(document).ready(function () {
     rhombus_dark_mode("dark", "switch_false");
     /**
      * Get active accounts details
      */
     if (sso !== false) {
         constructSSODatatable(sso);
     } else {
         constructAccountsDatatable(accountData);
     }
      
 });
 
 /**
  * Update the table append modified data
  */
 function updateTable() {
     $('#update').modal("show");
     $.post("/account_manager/getAccountData", { rhombus_token: rhombuscookie(), rb_empty: 'rb_empty' }, function (data, status) {
         /**
          * Re-initialize editoptions for new data
          */
         accountManagerTable.clear();
         accountManagerTable.rows.add(Object.values(data));
         accountManagerTable.columns.adjust().draw();
         initDefaultValues();
         initalizeEditOptions();
 
     }, "json");
 }
 
 /**
  * populates the data table with default values
  */
 
 function initDefaultValues() {
     $('#accountManagerTable > tbody  > tr').each(function (index, tr) {
         let expiry = accountManagerTable.row(tr).data()['admin_expiry'];
 
         let accountType = accountManagerTable.row(tr).data()['account_type'];
         let tableRow = $(this).closest('tr');
         tableRow.find('.account_type').select2({ width: '20%' }).val(accountType).trigger('change');
         if(tableRow.find('.account_type').val() != null)
            tableRow.find('.register').prop("disabled", false);
         /**
          * if expiry date is null show nothing
          */
         (expiry) ? tableRow.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true }).datepicker('setDate', new Date(expiry)) : tableRow.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true });
 
         (!facs && accountType != "USER") ? tableRow.find('.admin_expiry').prop("disabled", false) : tableRow.find('.admin_expiry').prop("disabled", true);
         if (facs || accountType == "USER") {
             tableRow.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true }).datepicker('setDate', null)
         } else {
             tableRow.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true }).datepicker('setDate', new Date(expiry))
         }
     });
 }
 
 
 /**
  * Initializes edit and delete options
  */
 function initalizeEditOptions() {
 
     $('.account_type').on('select2:select', function (e) {
         /**
          * select2 account type
          */
         let selected_row_data = accountManagerTable.row($(this).parents('tr')).data();
         let parentTr = $(this).closest('tr');
         /**
          * disable 'save' button if admin chooses existing account type 
          */
         parentTr.find('.register').prop("disabled", false);
         if (facs || e.target.value == "USER") {
             (e.target.value != selected_row_data['account_type']) ? parentTr.find('.save').prop("disabled", false) : parentTr.find('.save').prop("disabled", true);
             parentTr.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true }).datepicker('setDate', null);
             parentTr.find('.admin_expiry').prop("disabled", true);
         } else {
             parentTr.find('.admin_expiry').datepicker({ startDate: "+2d", autoclose: true, todayHighlight: true }).datepicker('setDate', selected_row_data['admin_expiry']);
             (e.target.value != selected_row_data['account_type']) ? parentTr.find('.save').prop("disabled", false) : parentTr.find('.save').prop("disabled", true);
             parentTr.find('.admin_expiry').prop("disabled", false);
         }
     })
 
 
     $('.admin_expiry').on("input change", function (e) {
         let selected_row_data = accountManagerTable.row($(this).parents('tr')).data();
         let parentTr = $(this).closest('tr');
         /**
          * disable 'save' button if admin chooses existing expiry date
          */
         (e.target.value != selected_row_data['admin_expiry']) ? parentTr.find('.save').prop("disabled", false) : parentTr.find('.save').prop("disabled", true);
     });
 
 
     $("#accountManagerTable tr .save").on('click', function (e) {
 
         let selected_row_data = accountManagerTable.row($(this).parents('tr')).data();
         let parentTr = $(this).closest('tr');
         let account_type = parentTr.find('.account_type').children("option:selected").val();
         let expiry_date = parentTr.find('.admin_expiry').val();
         let id = selected_row_data['id'];
 
         /**
          * if account type or expiry date is null append previous date.
          */
         if (account_type == null || account_type == "") { account_type = selected_row_data['account_type'] };
         if (expiry_date == null || expiry_date == "") { expiry_date = selected_row_data['admin_expiry'] };
 
         let type = 'user';
         if (sso !== false) {
             type = 'sso';
         }

         $.post("/account_manager/updateUser", { Id: id, ExpiryDate: expiry_date, AccountType: account_type, type, rhombus_token: rhombuscookie() }, function (data, status) {
             if (data.result == "success") {
                 $("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-check-circle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Success!<p>');
                 $("#account_update_modal_body").html('<span>Updated Successfully</span>');
                 $("#account_update_modal").modal("show");
                 /**
                  * updates the table and appends new data
                  */
                 updateTable();
             } else {
                $("#account_update_modal_title").html('<p style="color:white"><i class="fa fa-times-circle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"red";></i>Error!<p>');
                $("#account_update_modal_body").html('<span>Invalid or missing expiry date</span>');
                $("#account_update_modal").modal("show");
             }
             $("#account_update_modal_button1").addClass("d-none");
             $("#account_update_modal_button2").addClass("d-none");
         }, 'json');
 
     });
 
     $('#accountManagerTable tbody').on('click', '#delete', function () {
         /**
          * Fetches the seleted row data
          */
         let selected_row_data = accountManagerTable.row($(this).parents('tr')).data();
         $('#account_delete_modal_title').html("Are you sure you want to delete?");
         $('#account_delete_modal_body').html("This process cannot be undone");
         /**
          * Asks for confirmation before deleting
          */
          setGlobalDeleteRow(selected_row_data['id'],selected_row_data['email']);
         $('#account_delete_modal').modal("show");
     });
 
     /**
      * deletes the account marking status is 'Delete'
      */
     $('#account_delete_submit').click(function () {
        let type = 'user';
         if (sso !== false) {
             type = 'sso';
         }
         
         let delete_obj = getGlobalDeleteRow();
         $.post("/account_manager/deleteAccount", {id:delete_obj['id'],email:delete_obj['email'], rhombus_token: rhombuscookie(), type: type}, function (data, status) {
             if (data.result == "success") {
                 $('#confirm_delete_modal_title').html("Deleted!");
                 $('#confirm_delete_modal_body').html("Account has been deleted successfully.");
                 $("#account_delete_modal").modal("hide");
                 $('#confirm_delete_modal').modal("show");
                 /**
                  * updates the table and appends new data
                  */
                 updateTable();
             }
         }, "json");
     });
 }


 if (!window._rb) window._rb = {
    setGlobalDeleteRow: setGlobalDeleteRow,
    constructSSODatatable: constructSSODatatable,
    constructAccountsDatatable: constructAccountsDatatable,
    initalizeEditOptions: initalizeEditOptions,
    renderUserApps: renderUserApps
 }