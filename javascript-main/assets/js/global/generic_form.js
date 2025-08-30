"use strict";

// Sai 
// 01 July 2020
//paramaters: formId, SumbitButtonId, Data, controllerpath,custom conformation msg and modal
// example function call genericForm(form_register,submit,generate_account_data,url,event,message,modal);
// callback optional
//////////////////Function/////////////////////
// This function is used in following files. //
// 1. generate_account.js                    //
// 2. Product.js                             //
// Please ensure all those components are    //
// working fine if you had made any changes. //
///////////////////////////////////////////////


function genericForm_submit(form_generic,submit_button,data,url,e,msg,modal,form_popup,callback) {
    e.preventDefault();
    let form = String(form_generic[0].id);
    if (form_generic[0].checkValidity() === false) {
        (form_generic).addClass("was-validated");
        e.stopPropagation();
    } else {
        form_generic.removeClass("was-validated");
        action_button(submit_button, "add");
        $.post(url, { Data: data, rhombus_token: rhombuscookie() }, function (data, status) {
            genericForm_submit_data(data,submit_button,msg,modal,form_popup,form,callback);
        }, 'json');
    }
}

function genericForm_submit_data(data,submit_button,msg,modal,form_popup,form,callback){
    if (data.result == "success") {
        $("#"+modal+"_title").html('<p style="color:white"><i class="fa fa-check-circle mr-3" aria-hidden="true style="font-size: 1.5em background-color:"green";></i>Success!<p>');
        $("#"+modal+"_body").html(sanitizeHtml('</div><span class=style="color:white">'+msg['Success_message']+'</span>', {allowedTags: false, allowedAttributes: false}));
        if(form_popup){closeForm(form_popup)};
    }
    else {
        let message = msg['Failure_message'];
        if (data.message !== undefined) {
            message = data.message;
        }
        $("#"+modal+"_title").html('<p style="color:white"><i class="fa fa-exclamation-triangle mr-3" aria-hidden="true  style="font-size: 1.5em;"></i>Something went wrong!</p>');
        $("#"+modal+"_body").html(sanitizeHtml('</div><span class=style="color:white">'+message+'</span>',{
            allowedTags: false,
            allowedAttributes: false
        }));
    } 
    action_button(submit_button, "remove");
    if(typeof callback === 'function'&& callback){ // checks if callback exists
        callback("#"+modal);
    }else{
        $("#"+modal).modal("show");
    }
    clear_form(form);
}

function closeForm(form_popup_modal)
{
    $("#"+form_popup_modal).modal("hide");
}

// Expose class in window in order to make it reachable in Jest + jsdom.
if (!window._rb) window._rb = {};
window._rb.genericForm_submit = genericForm_submit;
window._rb.closeForm = closeForm;
