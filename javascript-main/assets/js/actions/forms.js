"use strict";

function single_field_submit(id,controller,key, error_id = ""){
	let temp = id;
	let temp_form = id+"_form";
	let temp_save = id+"_save";

	let temp_cancel = id+"_cancel";
	let temp_cancel_class = "btn-secondary";

	let temp_save_cancel = id+"_save_cancel";	
	let temp_save_class = "btn-success";


	

	$("#"+temp_cancel).addClass(temp_cancel_class);
	$("#"+temp_save).addClass(temp_save_class);

	$("#"+temp).on("input", function() {
	   $("#"+temp_save_cancel).removeClass("d-none");
	   if(this.value==$("#"+temp).attr("org-val")){
	   	$("#"+temp_save_cancel).addClass("d-none");
	   }
	});

	
	$("#"+temp_form).on("submit", function(event){
	  event.preventDefault();
	  $("#"+error_id).empty();

	  if ($("#"+temp_form)[0].checkValidity() === false) {
	  
	      $("#"+temp_form).addClass("was-validated");
	      event.stopPropagation();
	  
	  } else {
	  		$("#"+temp_form).removeClass("was-validated"); 
	  		action_button(temp_save,"add");

		  	let temp_data = {};
			temp_data[key] = $("#"+temp).val();
			temp_data['rhombus_token'] = rhombuscookie();

		  $.post("/"+controller,temp_data, function(data, status){
				  if(data.result == "success"){
					action_button(temp_save,"remove");
					$("#"+temp_save_cancel).addClass("d-none");
					$("#"+temp).attr("org-val",$("#"+temp).val());
				  } else {
					action_button(temp_save,"remove");
					$("#"+error_id).html(sanitizeHtml(data.error,{
						allowedTags: false,
						allowedAttributes: false
					}));
				  }

		  },'json');

	  }

	});

	$("#"+temp_cancel).click(function() {
	  $("#"+temp_save_cancel).addClass("d-none");
	  $("#"+temp).val($("#"+temp).attr("org-val"));
	  action_button(temp_save,"remove"); 
	  $("#"+temp_form).removeClass("was-validated"); 
	  $("#"+error_id).empty();


	});

}




function password_change_submit(id, id_1,id_2,id_3,controller){
	let temp_message = id+"_message";
	let temp_form = id+"_form";
	let temp_save = id+"_save";
	let temp_cancel = id+"_cancel";
	let temp_cancel_class = "btn-secondary";
	let temp_save_class = "btn-success";

	$("#"+temp_cancel).addClass(temp_cancel_class);
	$("#"+temp_save).addClass(temp_save_class);
	
	$("#"+temp_form).on("submit", function(event){
	  event.preventDefault();

	  if ($("#"+temp_form)[0].checkValidity() === false) {
	  
	      $("#"+temp_form).addClass("was-validated");
	      event.stopPropagation();
	  
	  } else {
	  		$("#"+temp_form).removeClass("was-validated"); 
	  		action_button(temp_save,"add");

		  	let temp_data = {};
			temp_data[id_1] = $("#"+id_1).val();
			temp_data[id_2] = $("#"+id_2).val();
			temp_data[id_3] = $("#"+id_3).val();
			temp_data['rhombus_token'] = rhombuscookie();

		  $.post("/"+controller,temp_data, function(data, status){
		  		if(data.result!="success"){
		  			if(data.result=="failure_new_password_match"){
						  $("#"+temp_message).html('<span class="text-danger">Your new password is not matching.</span>');
		  			} else if(data.result == "fail"){
						$("#"+temp_message).html('<span class="text-danger">error</span>');
					} else {
						  $("#"+temp_message).html('<span class="text-danger">Your current password is not right.</span>');
		  			}
		  		} else {
					  $("#"+temp_message).html('<span class="text-success">Your password has been changed.</span>');
		  		}
	        	
	        	action_button(temp_save,"remove");
	   	   },'json');

	  }

	});

	$("#"+temp_cancel).click(function() {
	  clear_form(temp_form);
	  action_button(temp_save,"remove"); 
	  $("#"+temp_form).removeClass("was-validated"); 
	});

}

function notificationSubmit(ids){
	ids.forEach(function(id){
		$("#"+id).change(function() {
			let temp_data = {
				"rhombus_token" : rhombuscookie(),
				"type" : $(this).val(),
				"notification" : $(this)[0].checked? 1 : 0
			};
			// NOTE: window.location.origin removed, route needs to be defined.
			$.post("/Settings/notifications_change", temp_data, function(data, status){
				if(data["result"] == "success")
					$("#notification_result").html("<span class = 'text-success'>Your notifications have been saved</span>");
			},"json");
		});
	});
}



function formatState (state) {
  if (!state.id) {
    return state.text;
  }

  let temp_icon = '';
  if(state.element.value=="1"){
    temp_icon = '<i class="fas fa-lock-open fa-fw mr-3"></i>';
  } else {
    temp_icon = '<i class="fas fa-lock fa-fw mr-3"></i>';
  }
  let $state = $(
    '<span>'+temp_icon + state.text + '</span>'
  );
  return $state;
};

// Expose functions in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.single_field_submit = single_field_submit;
window._rb.password_change_submit = password_change_submit;
window._rb.notificationSubmit = notificationSubmit;
window._rb.formatState = formatState;
