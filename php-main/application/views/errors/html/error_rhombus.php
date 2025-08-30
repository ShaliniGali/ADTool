<?php
$CI = &get_instance();
$CI->load->library('minify');

$page_navbar = false;
$compression_name = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
?>

<!DOCTYPE html>
<html class="h-100" lang="en">

<head>

    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title><?php echo $heading; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="robots" content="noindex,nofollow">

    <link rel="apple-touch-icon" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_70x70.png">
    <link rel="apple-touch-icon" sizes="70x70" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_70x70.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_144x144.png">
    <link rel="icon" type="image/x-icon" href="<?php echo base_url(); ?>assets/images/Logos/favicon.ico" />
    <link rel="icon" type="image/png" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo.png" />

    <?php if(!P1_FLAG) : ?>
        <script async src="https://app.guardian.rhombus.cloud/assets/js_compress/tc_rhombus.min.js" rhombus-tc></script>
    <?php endif; ?>

    <?php
    $essential_css = array("bootstrap.css", "bootstrap-grid.css", "bootstrap-reboot.css", "fontawesome_all.css", "dark-mode-login.css", "rhombus-login.css");
    $essential_css_msg = "essential_css_login";
    $essential_css = preg_filter('/^/', 'essential/', $essential_css);

    $CI->minify->add_css($essential_css, "essential_css");
    echo $CI->minify->deploy_css(FALSE, 'auto', "essential_css");
    ?>
</head>

<body id="main-container" class="h-100" <?php if ($page_navbar == false) {
                                            echo 'background="' . base_url() . 'assets/images/background.jpg" style="background-size: cover;"';
                                        } else {
                                            echo 'style="padding-top: 70px;margin-bottom: -70px;"';
                                        } ?>>

    <div class="container h-100">
        <div class="row h-100" id="particles-js">
            <div class="col-sm-12 align-self-center" style="z-index: 1;">
                <div class="card card-block shadow-lg">
                    <div id="login_card" class="py-5 mt-5">
                        <div class="row no-gutters pt-md-5">
                            <div class="d-none d-md-block col-sm-10 offset-sm-1 col-md-6 offset-md-0 col-lg-4 offset-lg-1  align-self-center">
                                <div class="col-sm-6 offset-sm-3 col-md-10 offset-md-1 text-center">
                                    <img src="<?php echo base_url() . 'assets/images/Logos/guardian_logo.png'; ?>" alt="" class="w-100 px-5 pb-5 pb-md-0 logo_tilt">
                                </div>
                            </div>

                            <div class="col-sm-12 text-center text-muted offset-sm-0 col-md-6 offset-md-0 col-lg-5 offset-lg-1 align-self-center">
                                <div class="h1 text-danger mb-4"><?=$heading ?></div>
                                <div><?= $message ?>
                                </div>
                            </div>
                            <div class="col-12  pt-5 mt-2" id="copy_rights">
                                <div class="form-group text-muted small text-center mt-md-5">
                                    GUARDIAN © 2011-<?php echo date('Y'); ?> Rhombus Power Inc. <span class="text-success pl-2"><span class="fab fa-product-hunt mr-2"></span><?php echo RHOMBUS_PROJECT_NAME; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <?php
    $CI->load->view('templates/essential_javascripts');
    ?>

    <?php
    $js_files = array();
    $js_files['particles'] = ["particles.js", 'global'];
    $js_files['particle'] = ["actions/particle.js", 'custom'];

    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
    ?>


    <?php

    $CI->load->view('templates/close_view');

    ?>
