<form id="rhombus_login" class="needs-validation py-5 px-3" novalidate>
	<div class="form-row">
		<div class="col-md-12 mb-3 pb-4">
			<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Email"><em class="far fa-envelope"></em></span>
			</div>
			<input type="email" class="form-control border-0" name="user_email_login" id="user_email_login" placeholder="Enter email" value="" required>
			</div>
			<div class="valid-feedback"></div>
		</div>
		<div class="col-md-12 mb-3 pb-4">
			<div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Password"><em class="fas fa-lock"></em></span>
                </div>
                
                <input type="password" class="form-control border-0" name="user_password_login" id="user_password_login" style="border-radius:1px;" placeholder="Enter password" value="" autocomplete="off" required>
                
                <div class="input-group-append show-password">
                    <span class="input-group-text text-muted bg-dark border-dark input_icons">
                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
			<div class="valid-feedback"></div>
		</div>
		<div class="col-md-12 mb-2">
			<label class="checkboxcontainer text-muted small">
				<span id="tos_link" class="pl-3 d-block" data-toggle="modal" data-target="#terms_of_service_modal">I agree to the Rhombus Power Terms of Service and Privacy Policy</span>
				<input class="form-check-input" type="checkbox" value="" id="tos_agreement_checkbox">
				<span class="checkmark"></span>
			</label>
		</div>
	</div>
	<button class="btn btn-success mt-4 w-100 mb-4" type="submit" id="rhombus_login_submit" style="cursor:default" disabled>LOGIN</button>
	<a href = "#" class="small" onclick="forgot_password_switch('reset')">Forgot Password?</a>
</form>
<form id = "forgot_password" class="needs-validation py-5 px-3 d-none">
	<div class="form-row">
		<div class="col-md-12 mb-3 pb-4">
			<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Email"><em class="far fa-envelope"></em></span>
			</div>
			<input type="email" id="forgot_password_email" class="form-control border-0" placeholder="Enter email" value="" required>
			</div>
			<div class="valid-feedback"></div>
		</div>
	</div>
	<div id="forgot_password_result"></div>
	<button id="forgot_password_btn" class="btn btn-success mt-4 w-100 mb-4" type="submit" style="cursor:default">Reset Password</button>
	<a href = "#" class="small" onclick="forgot_password_switch('login')">Go back</a>
</form>