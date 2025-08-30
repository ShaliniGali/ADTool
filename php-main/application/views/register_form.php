<form id="rhombus_register" class="needs-validation py-5 px-3" novalidate>
  <div class="form-row">

    <div class="col-md-12 mb-3 pb-4">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Email"><em class="far fa-envelope"></em></span>
        </div>
        <input type="email" class="form-control border-0" id="user_email_register" placeholder="Enter email" value="" required>

      </div>
      <div class="d-none text-danger small pt-1" id="user_email_register_msg"></div>
    </div>

    <div class="col-md-12 mb-3 pb-4">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Name"><em class="far fa-user"></em></span>
        </div>
        <input type="text" class="form-control border-0" name="user_name_register" id="user_name_register" placeholder="Enter name" value="" required>
      </div>
      <div class="d-none text-danger small pt-1" id="user_name_register_msg"></div>
      <div class="valid-feedback"></div>
    </div>

    <div class="col-md-12 mb-3 pb-4">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Password"><em class="fas fa-lock"></em></span>
        </div>
        <input type="password" class="form-control border-0" id="user_password_register" style="border-radius:1px;" placeholder="Enter password" autocomplete="off" value="" required>

        <div class="input-group-append show-password">
          <span class="input-group-text text-muted bg-dark border-dark input_icons">
            <a href="" class="text-muted input_icons"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
          </span>
        </div>
      </div>
      <div class="d-none text-danger small pt-1" id="user_password_register_msg">
        i) at least one upper case letter (A – Z).<br>
        ii) at least one lower case letter(a-z).<br>
        iii) at least one digit (0 – 9).<br>
        iv) at least one special characters of !@#$%&*()
      </div>
    </div>


    <div class="col-md-12 mb-3 pb-4">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Retype Password"><em class="fas fa-lock"></em></span>
        </div>
        <input type="password" class="form-control border-0" id="user_password_again_register" style="border-radius:1px;" placeholder="Enter password again" autocomplete="off" value="" required>

        <div class="input-group-append show-password">
          <span class="input-group-text text-muted bg-dark border-dark input_icons">
            <a href="" class="text-muted input_icons"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
          </span>
        </div>
      </div>
      <div class="d-none text-danger small pt-1" id="user_password_again_register_msg"></div>
    </div>


    <div class="col-md-12 d-inline-flex">

      <div class="d-none text-danger small pt-1" id="account_type_register_msg"></div>
      <?php
      echo $this->useraccounttype->generateAccountTypeMenu();
      ?>

    </div>
    <div class="col-md-12 mb-4">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Your message"><em class="fas fa-comment-alt"></em></span>
        </div>
        <textarea class="form-control border-0" style="color:#6f6f6f; background-color:#2e2e2e;" id="user_personal_message" rows="3" placeholder="Describe yourself here..." required></textarea>
      </div>
      <div class="valid-feedback"></div>
    </div>
  </div>
  <button class="btn btn-success mt-4 w-100" type="submit" id="rhombus_register_submit">REGISTER</button>

</form>



<div id="register_confirmation"></div>


<script> const controller = "/register/create_account"; </script>