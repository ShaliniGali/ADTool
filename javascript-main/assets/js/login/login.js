"use strict";

function display_login_modal(modal, title, body, hidden = [], timeout = null) {
  $("#" + modal + "_title").html(sanitizeHtml(title, { allowedAttributes:false, allowedTags:false,}));
  $("#" + modal + "_body").html(sanitizeHtml(body, { allowedAttributes:false, allowedTags:false,}));
  for (let id of hidden) {
    $("#" + id).addClass("d-none");
  }
  $("#" + modal).modal("show");
  if (Number.isInteger(timeout)) {
    setTimeout(function () {
      window.location.href = '/';
    }, timeout);
  }
}

$('.logo_tilt').tilt({
  glare: true,
  maxGlare: .5,
  scale: 1.1
})

/*
Created: Moheb, June 23rd 2020
Terms of Service agreement checkbox event handler
*/

$("#tos_agreement_checkbox").on('change', function (event) {
  event.preventDefault();
  let submit_btn = $("#rhombus_login_submit");
  if ($(this).is(':checked')) {
    submit_btn.prop('disabled', false);
    submit_btn.css('cursor', 'pointer');
  } else {
    submit_btn.prop('disabled', true);
    submit_btn.css('cursor', 'default');
  }
});

/*
Created: Moheb, June 23rd 2020
Releases the Terms of Service modal from its parent container, so the
modal stays on top of the fade effect
*/

$("#tos_link").on('click', function () {
  $('#tos_moterms_of_service_modal').appendTo("body");
  $("#tos_agreement_checkbox").prop('checked', !$("#tos_agreement_checkbox").is(':checked'));
});






//
//  Sumit 12th September 2019
//  
//

let response_data;
$("#rhombus_login").on("submit", function (event) {
  event.preventDefault();

  let form = "rhombus_login";
  let submit_button = "rhombus_login_submit";

  if ($("#" + form)[0].checkValidity() === false) {
    $("#" + form).addClass("was-validated");
    event.stopPropagation();
  } else {
    $("#" + form).removeClass("was-validated");
    action_button(submit_button, "add");

    let user_email = $("#user_email_login").val();
    let user_password = $("#user_password_login").val();
    let tos_agreement_checkbox = $("#tos_agreement_checkbox").is(':checked');

    login_onload(user_email,user_password,tos_agreement_checkbox,form,submit_button);
  }
});

function login_onload(user_email,user_password,tos_agreement_checkbox,form,submit_button){
  $.post("/login/user_check", {
    tos_agreement_check: tos_agreement_checkbox,
    username: user_email,
    password: user_password,
    rhombus_token: rhombuscookie()
  }, function (data, status) {
    response_data = data;

    action_button(submit_button, "remove");

    if (data.result == "success") {
      display_login_modal(
        'login_modal',
        '<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Successfully authenticated.</span>',
        '<div class="fa-3x pb-4 text-white"><i class="fas fa-cog fa-spin"></i></div>',
        ['login_modal_button1', 'login_modal_button2'],
        2000
      );
    }

    if (data.result == "failed" || data.result == "not_registered" || data.result == "account_rejected") {
      let message = "Invalid user credentials.";
      if (data.message && data.message != "failed") {
        message = 'Invalid user credentials.';
      }
      display_login_modal(
        'login_modal',
        '<span class="text-danger">Failed to authenticate!</span>',
        '<span class="text-muted">' + message + '</span>',
        ['login_modal_button1', 'login_modal_button2']
      );
    }

    if (data.result == "registration_pending_exist") {
      display_login_modal(
        'login_modal',
        '<span class="text-primary">Pending registration.</span>',
        '<span class="text-muted">Account is currently pending admin approval.</span>',
        ['login_modal_button1', 'login_modal_button2']
      );
    }

    if (data.result == "account_blocked") {
      display_login_modal(
        'login_modal',
        '<span class="text-danger">Account Blocked!</span>',
        '<span class="text-muted">Maximum failed login attempts reached.<br><br>Please check your email inbox.</span>',
        ['login_modal_button1', 'login_modal_button2']
      );
    }

    if (data.result == "reset_password") {
      display_login_modal(
        'reset_pwd_modal',
        '<span class="text-danger">Forgot Password?</span>',
        '<span class="text-muted">Would you like to reset your password?<br><br></span>'
      );
      $('#reset_pwd_modal_button2').html("Reset my password").removeClass("btn-primary d-none").addClass("btn-success");
    }

    /**
     * If status changes to reset_password. Force the use to reset password. To keep account secure
     */
    if (data.result == "force_reset_password") {
      display_login_modal(
        'reset_pwd_modal',
        '<span class="text-danger">Reset your password!</span>',
        '<span class="text-muted">There is a password reset request issued to you account already.<br><br>Please check your email for further instructions on how to reset your password.<br><br></span>',
        ['reset_pwd_modal_button1', 'reset_pwd_modal_button2']
      );
    }


    if (data.result == 'require_login_layer') {
      // loginWithGoogle2FA();

      enable_login_registeration_methods(data);
      $('#login_card').addClass("d-none");
      $('#tfa_login').removeClass("d-none");
    }

    if (data.result == "register_login_layer") {

      enable_login_registeration_methods(data);
      generateQR();

      $('#login_card').addClass("d-none");
      $('#tfa_register').removeClass("d-none");
    }

    clear_form(form);

  }, 'json');
}

/**
* Enables and disables buttons
* @param string data 
*/
function enable_login_registeration_methods(data) {
  let google_auth_layer = parseInt(data.layers[0]) || 0;
  let yubikey_layer = parseInt(data.layers[1]) || 0;
  let cac_layer = parseInt(data.layers[2]) || 0;
  let recovery_keys = parseInt(data.layers[3]) || 0;
  let login_token = parseInt(data.layers[4]) || 0;

  $("#yubikey_login").attr("disabled", !yubikey_layer);
  $("#yubikey_register").attr("disabled", !yubikey_layer);

  $("#google_auth_login").attr("disabled", !google_auth_layer);
  $("#google_auth_register").attr("disabled", !google_auth_layer);
  if (google_auth_layer == 0)
    $("#selected_tfa_modal").attr("id", "temp_" + Date.now());

  $("#cac_reader_login").attr("disabled", !cac_layer);
  $("#cac_register").attr("disabled", !cac_layer);

  $("#recovery_code_login").attr("disabled", !recovery_keys);

  $("#login_token").attr("disabled", !login_token);
}



/**
* created Sai July 31 2020
*  Reset  password
* Confirms with the user before sending reset password instructions
*/
$(document).ready(function () {
  $('#reset_pwd_modal_button2').on('click', function (event) {
    event.preventDefault();
    $("#reset_pwd_modal").modal("hide");
    $.post("/login/send_reset_password", {
      dummy: 'dummy',
      rhombus_token: rhombuscookie()
    }, function (data, status) {
      if (data.result == "success") {
        $("#reset_pwd_modal_title").html('<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Password reset!</span>');
        $("#reset_pwd_modal_body").html('<span class="text-muted">Password reset instructions have been sent to your email.<br><br>Please check your email inbox.</span>');
      } else {
        $("#reset_pwd_modal_title").html('<span class="text-white text-capitalize">Error!</span>');
        $("reset_pwd_modal_body").html('<span class="text-muted">Error while resetting your password. Please contact the admin.</span>');
      }
      $("#reset_pwd_modal_button1").addClass("d-none");
      $("#reset_pwd_modal_button2").addClass("d-none");
      $("#reset_pwd_modal").modal("show");

    }, 'json');
  });
});




/**
* created Sai July 16 2020
* 
* Resets recovery code
* 
* @param integer id 
*/
$(document).ready(function () {
  $('#reset_key_modal_button2').on('click', function (event) {
    event.preventDefault();
    $("#reset_key_modal").modal("hide");
    $.post("/login/reset_recovery_codes", {
      dummy: 'dummy',
      rhombus_token: rhombuscookie()
    }, function (data, status) {
      if (data.result == "success") {
        $("#reset_key_modal_title").html('<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Success</span>');
        $("#reset_key_modal_body").html('<span class="text-muted">New keys have been sent. <br><br>Please check your email inbox.</span>');
      } else {
        $("#reset_key_modal_title").html('<span class="text-white text-capitalize">Error!</span>');
        $("#reset_key_modal_body").html('<span class="text-muted">Error while resetting the keys. Please contact the admin.</span>');
      }
      $("#reset_key_modal_button1").addClass("d-none");
      $("#reset_key_modal_button2").addClass("d-none");
      $("#reset_key_modal").modal("show");

    }, 'json');
  });
});



/*
Created: Sai, July 10th 2020
updated: Sai, July 16th 2020
*/
let toggle = 0;
$("#google_auth_login").click(function () {
  //   $("#recovery").addClass("d-none");
  //   if (toggle == 0) {
  //     $("#recovery").addClass("d-none");
  //     $("#token").addClass("d-none");
  //     $("#google_authenticator").removeClass("d-none");
  //     $("#tfa_login").append($("#google_authenticator"));
  //     $('input:text:visible:first').focus();
  //     toggle++;
  //   } else {
  //     $("#google_authenticator").addClass("d-none");
  //     toggle--;
  //   }
  $("#recovery").addClass("d-none");
  $("#token").addClass("d-none");
  $("#yubikey").addClass("d-none");
  $("#google_authenticator").removeClass("d-none");
  $("#tfa_login").append($("#google_authenticator"));
  $('input:text:visible:first').focus();
});




/*
* Created: Sai, July 10th 2020
* Allows user to register google authenticator.
*/
$("#google_auth_register").click(function () {
  $("#recovery").addClass("d-none");
  $("#token").addClass("d-none");
  $("#yubikey").addClass("d-none");
  $("#google_authenticator_register").removeClass("d-none");
  $("#selected_tfa_modal_body").append($("#google_authenticator_register"));
  $("#selected_tfa_modal").modal("show");
  $('input:text:visible:first').focus();
});


/**
* created Sai July 13 2020
* 
* Allows user to login through recovery code.
* Checks if the user has any keys available
* 
* @param integer id 
*/
$("#recovery_code_login").click(function (event) {
  event.preventDefault();
  $("#google_authenticator").addClass("d-none");
  $("#recovery").removeClass("d-none");
  $("#token").addClass("d-none");
  $("#yubikey").addClass("d-none");
  $("#tfa_login").append($("#recovery"));
  $.post("/login/check_key_exist", {
    dummy: 'dummy',
    rhombus_token: rhombuscookie()
  }, function (data, status) {

    if (data.result != "success") {
      $("#reset_key_modal_title").html('<span class="text-danger">No keys Left!</span>');
      $("#reset_key_modal_body").html('<span class="text-muted">All recovery keys associated with this account have expired. <br><br>Would you like to issue new recovery keys?</span>');
      $("#reset_key_modal").modal("show");
      $("#reset_key_modal_button2").html("Send").removeClass("btn-primary d-none").addClass("btn-success");
    }
  }, 'json');
});

/**
* Created: Moheb, July 20th, 2020
* 
* Event handler for Login Token button click 
*/
$("#login_token").click(function (e) {
  $("#google_authenticator").addClass("d-none");
  $("#recovery").addClass("d-none");
  $("#yubikey").addClass("d-none");
  $("#token").removeClass("d-none");
  $("#tfa_login").append($("#token"));
});


/*
Created: Sai, July 14th 2020
Toggle with 2fA
*/
function cancel(form) {
  $("#" + form).addClass("d-none");
}

$("#cac_continue_btn").on("click", function(){
  window.location.href='/cac/auth'
})

function forgot_password_switch(type){
  if(type == "reset"){
    $("#rhombus_login").addClass("d-none");
    $("#forgot_password").removeClass("d-none");
  } else {
    $("#rhombus_login").removeClass("d-none");
    $("#forgot_password").addClass("d-none");
  }
  $("#forgot_password_result").empty();
  $("#forgot_password_email").val("");
}

$("#forgot_password").on("submit", function (event) {
  event.preventDefault();
  let post_data = {
    rhombus_token:rhombuscookie(),
    email:$("#forgot_password_email").val()
  }
  action_button("forgot_password_btn", "add");
  $.post("/login/send_reset_password_by_email",post_data,function(data, status){
    if(data["validation"] == "success" && data["result"] == "success"){
      $("#forgot_password_result").html("An email has been sent. Check your email to reset password.")
      $("#forgot_password_result").addClass("text-success")
      $("#forgot_password_result").removeClass("text-danger")
    } else {
      $("#forgot_password_result").html(sanitizeHtml(data["message"], {allowedTags: false, allowedAttributes: false}))
      $("#forgot_password_result").addClass("text-danger")
      $("#forgot_password_result").removeClass("text-success")
    }
    action_button("forgot_password_btn", "remove");
    $("#forgot_password_email").val("");
  },"json")
})

// Expose functions in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.display_login_modal = display_login_modal;
window._rb.enable_login_registeration_methods = enable_login_registeration_methods;
window._rb.cancel = cancel;
window._rb.forgot_password_switch = forgot_password_switch;
