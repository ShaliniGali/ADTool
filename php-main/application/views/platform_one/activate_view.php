<?php
$page_data['page_title'] = "Activate";
$page_data['page_tab'] = "Activate";
$page_data['page_navbar'] = true;
$page_data['page_specific_css'] = array();
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);

$decrypt_data = encrypted_string($hash, "decode");
// if admin changes the account type
$decrypt_data['account_type'] = $account_type;


if (empty($decrypt_data)) {
  redirect($base_url);
}
//
// Link expires in 5 mins
//
if (($decrypt_data['time'] + 5 * 60) < time()) {
  redirect($base_url);
}



// Activates user
if ($decrypt_data['type'] == "admin_verify") {
  $decrypt_data['enableLoginLayer'] = $login_layer;
  $decrypt_data['tfa'] = $tfa;
}
if (($decrypt_data['time'] + 5 * 60) > time()) {
  $this->load->view('templates/essential_javascripts');
  echo('<script>rhombus_dark_mode("dark","switch_false"); </script>');
  if ($this->Platform_One_model->user_activate($decrypt_data, $decrypt_data['type'])) { ?>
    <div class="px-3 align-self-center" style="  min-height: 80%;  min-height: 80vh; display: flex; align-items: center;">
      <div class="text-center w-100">
        <div class="lead pb-3"><em class="fas fa-check-circle m-2 fa-4x d-block" style=" vertical-align: middle;"></em> <?php echo $decrypt_data['email']; ?></div>
        <div class="text-muted">The account has been activated successfully.<br><br> <?php if ($decrypt_data['type'] == "self_verify") {
                                                                                            echo '<button class="btn text-black"  style="background-color:#2279a7" onclick=\'location.href = " '. base_url() . ' "\'>Login Now</button>';
                                                                                          } else {
                                                                                            echo 'A notification has been sent to the account email.';
                                                                                          } ?></div>
      </div>
    <?php } else { ?>
      <div class="px-3 align-self-center" style="  min-height: 80%;  min-height: 80vh; display: flex; align-items: center;">
        <div class="text-center w-100">
          <div class="lead"><em class="fas fa-exclamation-triangle m-2 fa-4x d-block" style=" vertical-align: middle;"></em>  <?php echo $decrypt_data['email']; ?></div>
          <div class="text-muted pt-3">The account was not activated. The activation link has expired.<br><br>
        <?php } ?>
      <?php } ?>
        </div>
      </div>


<?php
$this->load->view('templates/close_view');
?>