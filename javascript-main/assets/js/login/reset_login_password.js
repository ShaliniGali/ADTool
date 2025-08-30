"use strict"

/**
 * Created Sai July 31st 2020
 */
/**
 * 
 * Check Password match
 * 
 * @param {string} newPassword
 * @param {string} confirmNewPassword
 */
function passwordMatch(newPassword, confirmNewPassword) {
  const password1 = $("#" + newPassword).val();
  const password2 = $("#" + confirmNewPassword).val();

  if (password1 != password2) {
    return false;
  } else {
    return true;
  }
}

/**
 * check if password is strong
 * @param {string} password 
 */
function passwordStrong(password) {
  var regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/;
  var validPassword = regExp.test(password);
  return validPassword;
}

/**
 * validates the password reset form and updates the password.
 */
$("#rhombus_password_reset").on("submit", function (event) {
  event.preventDefault();
  var form = "rhombus_password_reset";
  var submit_button = "rhombus_password_reset_submit";
  var password_check = passwordMatch("user_password_reset", "user_password_reset_confirm");
  var password_strength = passwordStrong($("#user_password_reset").val());
  if ($("#" + form)[0].checkValidity() === false) {
    $("#" + form).addClass("was-validated");
    event.stopPropagation();
  }
  else if (!password_strength) {
    $("#" + form).removeClass("was-validated");
    $("#user_password_reset_msg").removeClass("d-none");
    event.stopPropagation();
  } 
  else if (!password_check) {
    $("#" + form).removeClass("was-validated");
    $("#user_password_reset_msg").addClass("d-none");
    $("#user_password_reset_confirm_msg").removeClass("d-none").html("Password does not match");
    event.stopPropagation();
  }
  else {
    $("#" + form).removeClass("was-validated");
    $("#user_password_reset_msg").addClass("d-none");
    $("#user_password_reset_confirm_msg").addClass("d-none").html("");
    action_button(submit_button, "add");
    var password = $("#user_password_reset").val();
    var confirmPassword = $("#user_password_reset_confirm").val();
    $.post("/login/confirm_reset_password", { username: username, Password: password, ConfirmPassword: confirmPassword, rhombus_token: rhombuscookie() }, function (data, status) {

      if (data.result == "password_used") {
        $("#reset_password_modal_title").html('<span class="text-danger">Failure!</span>');
        $("#reset_password_modal_body").html('<span class="text-muted">This password is already used. Please use a different password</span>');
        $("#reset_password_modal").modal("show");
      }
      else if(data.result == "success"){
        $("#reset-card" ).remove();
        $("#success-card").html("<h3 class='mt-3 mb-5 text-success'><i class='fa fa-check-circle pr-3' aria-hidden=true></i>Success</h3><br><br><h4 class=text-muted>Your password has been successfully reset. Please check your email for confirmation.<br> <a href='/'> <button class='btn btn-success mt-5 mb-5' type='button'>Take me to Login <i class='fas fa-arrow-circle-right'></i></button></a></h4>");
      }
      else if(data.result == "error"){
        $("#reset_password_modal_title").html('<span class="text-danger">Error</span>');
        $("#reset_password_modal_body").html('<span class="text-muted">There are errors in your password string. Please re-type your new password. </span>');
        $("#reset_password_modal").modal("show");
      }
      $("#reset_password_modal_button1").addClass("d-none");
      $("#reset_password_modal_button2").addClass("d-none");
      action_button(submit_button, "remove");
      $("#" + form).removeClass("d-none");
    clear_form(form);
    }, 'json');
  }
});

window._rb = {};
window._rb.passwordMatch = passwordMatch;
window._rb.passwordStrong = passwordStrong;