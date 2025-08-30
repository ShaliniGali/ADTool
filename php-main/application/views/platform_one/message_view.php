
<div class="px-3 h-100">
    <div class="row pt-3">
        <?php if ($template === 1): ?>
            <?= printout_message(
                '<i class="text-muted fas fa-exclamation-triangle fa-5x"></i>', 
                '<h4 class="pt-4 mt-4 text-capitalize text-muted">Failed to login! <br><br> Please contact Rhombus Power Platform One administrator.</h4>'); ?>
        <?php elseif ($template === 2): ?>
            <?= printout_message(
                '<i class="text-muted fas fa-exclamation-triangle fa-5x"></i>', 
                '<h4 class="pt-4 mt-4 text-muted">Account pending a Rhombus Power administrator\'s approval.</h4>'); ?>
        <?php elseif ($template === 3): ?>
            <?= printout_message(
                '<i class="text-muted fas fa-exclamation-triangle fa-5x"></i>', 
                '<h4 class="pt-4 mt-4 text-muted">Failed To Login! <br><br> Account is not registered yet.<br><br>You may request an account from a Rhombus Power administrator.</h4>') 
                . '<div class="row justify-content-center w-100"><button id="reqaccess" class="btn btn-success mt-4" onclick="request_rb_p1()">Request Account</button></div>'; ?>
        <?php endif; ?>       
    </div>
</div>

<?php
$this->load->view('templates/essential_javascripts');
$this->load->view('templates/close_view');
?>