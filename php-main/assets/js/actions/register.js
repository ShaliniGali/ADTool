/**
 * 
 * Sumit 5th March 2020
 * 
 */

if (!window._rb) window._rb = {}
window._rb.checkPassword = checkPassword;
function checkPassword(p1, p2) {
    return $("#" + p1).val() == $("#" + p2).val();
}

//
//  Sumit 5th March 2020
//  
window._rb.isStrongPwd = isStrongPwd;
function isStrongPwd(password) {
    return /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/.test(password);
}

//
//  Sumit 5th March 2020
//
window._rb.disable_paste = disable_paste;
function disable_paste(id) {
    let myInput = document.getElementById(id);
    if (myInput) {
        myInput.onpaste = function (e) {
            e.preventDefault();
        }
    }
}

disable_paste("user_password_again_register");
disable_paste("user_password_register");

//
//  Sumit 18th September 2019
//  
$("#rhombus_register").on("submit", function (event) {
    event.preventDefault();

    let form = "rhombus_register";
    let submit_button = "rhombus_register_submit";

    let password_check = checkPassword("user_password_register", "user_password_again_register");
    let password_strength = isStrongPwd($("#user_password_register").val());
    let is_valid_name = /^[a-zA-Z\s]*$/.test($("#user_name_register").val());
    action_button(submit_button, "add");

    $.post("/register/validateEmailDomain", {
        email: $("#user_email_register").val(),
        rhombus_token: rhombuscookie()
    }, function (data, status) {
        const isValidEmail = (data.trim() == 'valid');

        if ($("#" + form)[0].checkValidity() === false) {
            $("#" + form).addClass("was-validated");
            event.stopPropagation();

        } else if (!password_strength) {
            $("#" + form).removeClass("was-validated");
            $("#user_password_register_msg").removeClass("d-none");
            event.stopPropagation();

        } else if (!password_check) {
            $("#" + form).removeClass("was-validated");
            $("#user_password_register_msg").addClass("d-none");
            $("#user_password_again_register_msg").removeClass("d-none").html("Password does not match.");
            event.stopPropagation();

        } else if (!isValidEmail) {
            $("#" + form).removeClass("was-validated");
            $("#user_email_register_msg").removeClass("d-none").html("Unauthorized email domain.");
            event.stopPropagation();
        } else if (!is_valid_name) {
            $("#" + form).removeClass("was-validated");
            $("#user_name_register_msg").removeClass("d-none").html("A name may not contain special characters or digits.");
            event.stopPropagation();
        } else {
            $("#" + form).removeClass("was-validated");
            $("#user_password_register_msg").addClass("d-none");
            $("#user_password_again_register_msg").addClass("d-none").html("");
            action_button(submit_button, "add");
			let temp_account_type = undefined;
            if ($('input[name="account_type"]:checked').val() == null) {
                temp_account_type = $("#user_role").val();;
            } else {
                temp_account_type = $('input[name="account_type"]:checked').val();
            }
            if (temp_account_type == undefined) {
                temp_account_type = 'ADMIN'
            }
            let user_data = {
                username: $("#user_email_register").val(),
                account_type: temp_account_type,
                message: $("#user_personal_message").val(),
                name: $("#user_name_register").val(),
            };

            let print_data = '<div class="row pt-5">';
            for (let property in user_data) {
                let temp_prop = null;
                if (property == "username") {
                    temp_prop = 'Username: ';
                } else if (property == "account_type") {
                    temp_prop = 'Account type: ';
                } else if (property == "message") {
                    temp_prop = 'Message: ';
                    if (controller == '/first_admin/create_accounts') {
                        temp_prop = false;
                    }
                }
                if (temp_prop) {
                    print_data += '<div class="col-12"><div class="form-group">';
                    print_data += '<span class="pl-3 pr-2 text-muted small">' + temp_prop + '</span>' + user_data[property];
                    print_data += '</div></div>';
                }
            }
            print_data += '</div>';

            $("#" + form).addClass("d-none");
            //TODO: figure out a way to change the value fo controller
            if (!controller == '/first_admin/create_accounts') {
                print_data += '<div class="col-12"><div class="text-danger small text-center pt-3">We are about to notify Admin about this registration. Do you agree?</div></div>';
            }

            print_data += '<div class="pt-3 text-center col-12"><button type="submit" class="btn btn-success w-100" id="register_confirmation_button">I Confirm!</button></div>';
            $("#register_confirmation").html(sanitizeHtml(print_data, {
                allowedTags: false,
                allowedAttributes: false
            }));

            $("#register_confirmation_button").attr("onclick", "confirm_registration(" + JSON.stringify(user_data) + ",'" + submit_button + "','" + form + "')");
            $("#register_confirmation").removeClass("d-none");

        }
        action_button(submit_button, "remove");
    });
});

//
//  Sumit 6th March 2020
//
window._rb.confirm_registration = confirm_registration;
function confirm_registration(userdata, submit_button, form) {

    userdata.rhombus_token = rhombuscookie();
    userdata.password = $("#user_password_register").val();
    userdata.password_confirmation = $("#user_password_again_register").val();

    let confirmation_button = "register_confirmation_button";

    action_button(confirmation_button, "add");
    $.post(controller, userdata, function (data, status) {
        //
        // Redirect all cases from login page except registeration_pending
        //
        if (data.result == 'validation_failure') {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Invalid Credentials!</span>';
            let body_html = '';
            for (const key of Object.keys(data.message)) {
                body_html += '<span class="text-muted">' + data.message[key] + '</span><br>';
            }
            body_html = body_html.slice(0, -4);
            document.getElementById("login_modal_body").innerHTML = sanitizeHtml(body_html, { allowedAttributes:false, allowedTags:false,});
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // remove spinner
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");

            $("#" + form).removeClass("d-none");
            $("#register_confirmation").addClass("d-none");
        }

        if (data.result == "registeration_pending") {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Success</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">Admin has been notified.<br><br> Your current account registration has been successfully issued. You will receive an email when an admin has approved your request.</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // You don't want to remove modal
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");
            $("#" + form).removeClass("d-none");
            $("#register_confirmation").addClass("d-none");

        }

        if (data.result == "login") {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">Account is already registered. <br><br>OR<br><br> Account registration process is pending to the Admin.</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // remove spinner
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");
            $("#" + form).removeClass("d-none");
            $("#register_confirmation").addClass("d-none");
        }

        if (data.result == "error") {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">Unable to create account. Please provide valid inputs.</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // remove spinner
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");

            $("#" + form).removeClass("d-none");
            $("#register_confirmation").addClass("d-none");
        }


        if (data.result == "account_rejected") {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Account Rejected!</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">The provided email has been rejected.<br><br></span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // remove spinner
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");
            $("#" + form).removeClass("d-none");
            $("#register_confirmation").addClass("d-none");
        }

        if (data.result == "first_success") {
            $("#login_modal_title").html('<span class="text-success">Success!</span>');
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">Account is registered. <br><br>You will be redirected to the login page.</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            //
            // remove spinner
            //
            action_button(confirmation_button, "remove");
            action_button(submit_button, "remove");
            $("#register_confirmation_button").addClass("d-none");

            setTimeout(function () {
                window.location.replace(window.location.href);
            }, 5000);
        }

        //
        // Clear form
        //
        $('input[name="account_type"]').prop('checked', false);
        $("#user_role").val("");
        clear_form(form);

    }, 'json');
}
