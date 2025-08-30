<!-- 
    Created: Sai June 10 2020
-->
<div class="pt-5">
  <h4 class="modal-title col-12 text-center"><em class="fas fa-user-lock fa-2x center-block text-muted"></em></h4>
  <h4 class="text-center mb-5 text-muted">Two-step verification</h4>
  <p class="col-12 modal-title text-center mb-4 text-muted">You are one step away from hopping into your account. Please choose one of the following methods to securely login into your account.</p>
  <form class="pb-5 text-center col-12">
        <div class="row px-5 justify-content-center">
        <div class="col-4">
        <button type="button" onclick="$('#cac_modal_modal').modal()" class="btn btn-secondary auth-button rounded w-100" id="cac_reader_login">CAC</button>
        </div>
        <div class="col-4">
        <button type="button" class="btn btn-secondary auth-button rounded w-100" id="recovery_code_login">Recovery Code</button>
        </div>
        <div class="col-4">
        <button type="button" class="btn btn-secondary auth-button rounded w-100" id="login_token" onclick="sendLoginToken()">Email Login Token</button>
        </div>
  </div>
  </form>
</div>



<!-- LOGIN USING RECOVERY CODE -->
<div class="text-center d-none" id="recovery">
  <form data-toggle="validator" id = "recovery_code_form" role="form" class="needs-validation pb-4 form-container col-12" novalidate>
    <label class="text-muted mb-4">ENTER YOUR 16-DIGIT RECOVERY CODE</label>
    <div class="mb-4">
      <input type="text" id="recovery_key" class="text-center bg-white form-control" pattern=".{16}" required>
      <div class="invalid-feedback">Please enter a valid recovery code</div>
    </div>
    <div class="col-12 mb-4 mt-2 form-group">
      <button class="btn btn-success  tfa-buttonsncol-mb-4 col-3" type="submit" id="recovery_code_submit"><em class="fa fa-unlock-alt mr-2"></em> Secure Login</button>
      <button class="btn btn-dark tfa-buttons col-mb-4 col-3" type="button" onclick="cancel('recovery')" >Cancel</button>
    </div>
    <p class="mb-2 text-muted mb-4 small"><em class="fas fa-envelope-open mr-2"></em>Recovery codes were issued upon registration. If all the recovery codes are used, the UI will automatically send you another set of recovery codes. Please search in your registered email inbox.</p>
  </form>
</div>


<!-- LOGIN USING LOGIN TOKEN -->
<div class="text-center d-none" id="token">
  <p class="text-success d-none" id="email-confirm-message"><em class="fas fa-envelope-open mr-2"></em>We have sent you a 16-digit token to your registered email. Please enter the token in below. <br>If you haven't received the token, <a href="#" onclick="sendLoginToken()" id="send-token-again">click here</a> to receive a new token.</p>
  <form data-toggle="validator" id = "token_code_form" role="form" class="needs-validation pb-4 form-container col-12" novalidate>
    <label class="text-muted mb-4">ENTER A LOGIN TOKEN</label>
    <div class="mb-4">
        <input type="text" id="token_key" class="text-center bg-white form-control" pattern=".{16}" required>
        <div class="invalid-feedback">Invalid login token.</div>
    </div>
    <div class="col-12 mb-4 mt-2 form-group">
        <button class="btn btn-success  tfa-buttonsncol-mb-4 col-3" type="button" id="token_code_submit" onclick="authenticateLoginToken()" disabled><em class="fa fa-unlock-alt mr-2"></em> Submit</button>
        <!-- <button class="btn btn-info  tfa-buttonsncol-mb-4 col-3" type="button" id="token_code_send_email" onclick="sendLoginToken()"><i class="fas fa-envelope-open mr-2"></i> Email token</button> -->
        <button class="btn btn-dark tfa-buttons col-mb-4 col-3" type="button" onclick="cancel('token')" >Cancel</button>
    </div>
    <!-- <p class="mb-2 text-muted mb-4 small"><i class="fas fa-envelope-open mr-2"></i>Please click the email token button to receive a one-time login token to your registered email.</p> -->
  </form>
</div>