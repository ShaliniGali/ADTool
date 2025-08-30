/**
 * created Sai July 13th 2020
 * Handles the number of login attempts left.
 * Decrements the login attempts if the user fail to login.
 */
$("#recovery_code_form").on("submit", function (event) {
    event.preventDefault();

    var form = "recovery_code_form";
    var submit_button = "recovery_code_submit";

    if ($("#" + form)[0].checkValidity() === false) {

        $("#" + form).addClass("was-validated");
        event.stopPropagation();

    } else {

        $("#" + form).removeClass("was-validated");
        action_button(submit_button, "add");

        var recoveryKey = $("#recovery_key").val();

        $.post("/login/login_recovery_code", {
            Recovery_key: recoveryKey,
            rhombus_token: rhombuscookie()
        }, function (data, status) {
            /**
             * @return: one of the following response
             * 1. success : User is successfully loggedIN
             * 2. error : Key Validation Error
             * 3. failure : Failed to login due to incorrect Key.
             * 4. account_blocked: Blocks account.
             */
            if (data.result['message'] == "success") {
                document.getElementById("login_modal_title").innerHTML = '<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Success</span>';
                document.getElementById("login_modal_body").innerHTML = '<div class="fa-3x pb-4 text-white"><i class="fas fa-cog fa-spin"></i></div>';
                $("#login_modal_button1").addClass("d-none");
                $("#login_modal_button2").addClass("d-none");
                $("#login_modal").modal("show");
                /**
                 * Redirect User to Home page
                 */
                setTimeout(function () {
                    window.location.href = '/';
                }, 2000);

            } else if (data.result['message'] == "error") {
                /**
                * Redirect User to Home page
                * Update Login Attempts
                */
                $("#login_modal_title").html('<span class="text-danger">Enter a valid key</span>');
                $("#login_modal_body").html('<span class="text-muted">You have errors in your key string. Please try again.</span>');
                $("#login_modal_button1").addClass("d-none");
                $("#login_modal_button2").addClass("d-none");
                $("#login_modal").modal("show");
            }
            else if (data.result['message'] == "failure") {
                $("#login_modal_title").html('<span class="text-danger">Key Expired!</span>');
                $("#login_modal_body").html(sanitizeHtml('<span class="text-muted">The key you have entered is expired. Please enter a valid key. You have ' + data.result['login_attempts'] + ' login attempts left.</span>', { allowedAttributes:false, allowedTags:false,}));
                $("#login_modal_button1").addClass("d-none");
                $("#login_modal_button2").addClass("d-none");
                $("#login_modal").modal("show");
            }
            else if (data.result['message'] == "account_blocked") {
                $("#login_modal_title").html('<span class="text-danger">Account Blocked!</span>');
                $("#login_modal_body").html('<span class="text-muted">You have used all your attempts. Your account has been blocked. Please contact the administrator.</span>');
                $("#login_modal_button1").addClass("d-none");
                $("#login_modal_button2").addClass("d-none");
                $("#login_modal").modal("show");
            }
            action_button(submit_button, "remove");
            clear_form(form);
        }, 'json');
    }
});
