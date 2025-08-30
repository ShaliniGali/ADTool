"use strict";

let expiryDate;

/**
 * created: Sai August 7th 2020
 */
$(document).ready(function () {
    rhombus_dark_mode("dark", "switch_false");
    window.history.forward();
    let requestedAccountType = $('#account_type').html();
    let selectedAccountType = requestedAccountType;

    $('#user_role').select2({ width: '20%' }).val(sanitizeHtml(requestedAccountType, { allowedAttributes:false, allowedTags:false})).trigger('change');
    $('#admin_expiry').prop("disabled", true);
    $('#admin_expiry').addClass("d-none");
    $('#disp_expiry').hide();
    $('#activate_tfa').prop('checked', true);

    if ($('#activate_tfa').prop('checked')) {
        enable2faGroup();
    } else {
        disable2faGroup();
    }

    if (requestedAccountType == "ADMIN" || requestedAccountType == "MODERATOR") {
        enableExpiry();
    }

    $('#user_role').on('select2:select', function (e) {
        selectedAccountType = e.params.data.text;
        $('#account_type').html(sanitizeHtml(selectedAccountType, {allowedTags: false, allowedAttributes: false}));
        if (selectedAccountType == "ADMIN" || selectedAccountType == "MODERATOR") {
            $('#admin_expiry').removeClass("d-none");
            enableExpiry();
        } else {
            $('#admin_expiry').val('');
            $('#disp_expiry').hide();
            $('#admin_expiry').addClass("d-none");
            $('#admin_expiry').prop("disabled", true);
        }
    })

    let loginLayer = "Yes";
    $("#activate_tfa").on('change', function (event) {
        event.preventDefault();
        if ($(this).is(':checked')) {
            loginLayer = "Yes";
            enable2faGroup();
        } else {
            loginLayer = "No";
            disable2faGroup();
        }
    });

    /**
     * Validates the input calls activate_register method in Login controller
     */
    $('#activate_register').on("submit", function (event) {
        event.preventDefault();
        let form = "activate_register";
        let submit_button = "activate_register_submit";
        let validTfa = validateTfaLayer();
    
        if ($("#" + form)[0].checkValidity() === false) {
            $("#" + form).addClass("was-validated");
            event.stopPropagation();
        } else if ($('#activate_tfa').prop('checked') && !validTfa) {
            $("#" + form).removeClass("was-validated");
            $("#valid_tfa_message").removeClass("d-none");
        }
        else {
            $("#" + form).removeClass("was-validated");
            $("#valid_tfa_message").addClass("d-none");
            action_button(submit_button, "add");
            let tfaGroup = {
                gAuth: $('#activate_Gauth').prop('checked') ? "Yes" : "No",
                yubikey: $('#activate_Yubikey').prop('checked') ? "Yes" : "No",
                cac: $('#activate_CAC').prop('checked') ? "Yes" : "No"
            }
            if (typeof siteUrlData !== 'undefined'){
                $.post("/account_manager/encrypt_data", { "data": siteUrlData, rhombus_token: rhombuscookie() }, function (data, status) {
                    let encryptedData = data["result"];
                    $.post("/login/activate_register", { SiteURL: encryptedData, ExpiryDate: expiryDate, AccountType: selectedAccountType, EnableLoginLayer: loginLayer, TFAGroup: tfaGroup, rhombus_token: rhombuscookie() }, function (data, status) {
                        if (data.result == "error") {
                            $("#error_modal_title").html('<span class="text-muted">Failure!</span>');
                            $("#error_modal_body").html('<span class="text-muted">Please provide valid inputs.</span>');
                            $("#error_modal_button1").addClass("d-none");
                            $("#error_modal_button2").addClass("d-none");
                            $("#error_modal").modal("show");
                            action_button(submit_button, "remove");
                        } else {
                            // $('body').html(data.result);
                            //show success close modal, refresh table
                            $("#formModal").modal("hide");
                            updateTable();
                        }
                    }, 'json');
                },"json");
            } else {
                $.post("/login/activate_register", { SiteURL: siteUrl, ExpiryDate: expiryDate, AccountType: selectedAccountType, EnableLoginLayer: loginLayer, TFAGroup: tfaGroup, rhombus_token: rhombuscookie() }, function (data, status) {
                    if (data.result == "error") {
                        $("#error_modal_title").html('<span class="text-muted">Failure!</span>');
                        $("#error_modal_body").html('<span class="text-muted">Please provide valid inputs.</span>');
                        $("#error_modal_button1").addClass("d-none");
                        $("#error_modal_button2").addClass("d-none");
                        $("#error_modal").modal("show");
                        action_button(submit_button, "remove");
                    } else {
                        $('body').html(sanitizeHtml(data.result,{
                            allowedTags: false,
                            allowedAttributes: false
                        }));
                    }
                }, 'json');
            }
        }
    });

    /**
    * Gives the admin the privilage to reject the account.
    */
    $('#reject_register_submit').on("click", function (event) {
        $('#account_reject_modal_title').html("Are you sure you want to reject this account?");
        $('#account_reject_modal_body').html("This process cannot be undone");
        if($("#formModal").length != 0)
            $("#formModal").modal("hide");
        $('#account_reject_modal').modal("show");
        event.preventDefault();
    });

    $('#account_reject_submit').on('click', function () {
        action_button('account_reject_submit', "add");

        if(typeof siteUrlData !== 'undefined'){
            $.post("/account_manager/encrypt_data", { "data": siteUrlData, rhombus_token: rhombuscookie() }, function (data, status) {
                let encryptedData = data["result"];
                $.post("/register/reject_register", { SiteURL: encryptedData, rhombus_token: rhombuscookie() }, function (data, status) {
                    if (data.result == "success") {
        
        
                        $('#confirm_reject_modal_title').html("Account Rejected");
                        $('#confirm_reject_modal_body').html("This account has been rejected.");
                        $("#account_reject_modal").modal("hide");
                        $('#confirm_reject_modal').modal("show");
        
                    } else if (data.result == "failure") {
                        $('#confirm_reject_modal_title').html("Something went wrong");
                        $('#confirm_reject_modal_body').html("Rejection unsuccessful.");
                        $("#account_reject_modal").modal("hide");
                        $('#confirm_reject_modal').modal("show");

                    }
                    action_button('account_reject_submit', "remove");
                    updateTable();
                }, 'json');
            },"json");
        } else {
            $.post("/register/reject_register", { SiteURL: siteUrl, rhombus_token: rhombuscookie() }, function (data, status) {
                if (data.result == "success") {
                    $('#confirm_reject_modal_title').html("Account Rejected");
                    $('#confirm_reject_modal_body').html("This account has been rejected.");
                    $("#account_reject_modal").modal("hide");
                    $('#confirm_reject_modal').modal("show");
    
                    setTimeout(function () {
                        window.location.href = "/register_activate/activate/" + siteUrl;
                    }, 2000);
                } else if (data.result == "failure") {
                    $('#confirm_reject_modal_title').html("Something went wrong");
                    $('#confirm_reject_modal_body').html("Rejection unsuccessful.");
                    $("#account_reject_modal").modal("hide");
                    $('#confirm_reject_modal').modal("show");
    
                    setTimeout(function () {
                        window.location.href = "/register_activate/activate/" + siteUrl;
                    }, 2000);
                }
                action_button('account_reject_submit', "remove");
            }, 'json');
        }
        

    })
});

function enable2faGroup() {
	$('#activate_Gauth').attr("disabled", false);
	$('#activate_Yubikey').attr("disabled", false);
	$('#activate_CAC').attr("disabled", false);
	$('#tfa_group').removeClass('d-none');
}

function disable2faGroup() {
	$('#activate_Gauth').attr("disabled", true);
	$('#activate_Yubikey').attr("disabled", true);
	$('#activate_CAC').attr("disabled", true);
	$('#activate_Gauth').prop('checked', false);
	$('#activate_Yubikey').prop('checked', false);
	$('#activate_CAC').prop('checked', false);
	$('#tfa_group').addClass('d-none');
}

function validateTfaLayer() {
	return $('#activate_Gauth').prop('checked') || $('#activate_Yubikey').prop('checked') || $('#activate_CAC').prop('checked')
}

/** 
 * Enables date picker if the account type is admin or moderator.
 * Displays number of days an admin or moderator are valid.
 * @param {string} expiryDate
 */
function enableExpiry() {
	$('#admin_expiry').removeClass("d-none");
    $('#admin_expiry').prop("disabled", false);
    $('#admin_expiry').datepicker({ startDate: new Date(), autoclose: true, todayHighlight: true })
        .on("input change", function (e) {
            /**
             * reset days to 0, if date changes
             */
            let daysValid = 0;
            let today = new Date();
            let dd = String(today.getDate()).padStart(2, '0');
            let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            let yyyy = today.getFullYear();
            today = mm + '/' + dd + '/' + yyyy;
            expiryDate = e.target.value;

            let oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
            let expiringDate = new Date(e.target.value);
            let presentDate = new Date(today);

            /**
             * calculated the number of days the admin or moderator is valid
             */
            daysValid = Math.abs((expiringDate.getTime() - presentDate.getTime()) / (oneDay));

            $('#disp_expiry').show();
            $('#disp_expiry').html(sanitizeHtml("Account type changes to USER account type in " + daysValid + " days.", {allowedTags: false, allowedAttributes: false}));
        });
}
