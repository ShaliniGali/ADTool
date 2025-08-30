<?php
    $base_url = parse_url(($_SERVER['SCRIPT_URI']));
    $make_url = $base_url['scheme']."://".$base_url['host'].":".$base_url['port']."/";
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            html {
                font-family: sans-serif;
                line-height: 1.15;
                -webkit-text-size-adjust: 100%;
                -webkit-tap-highlight-color: transparent;
            }
            body {
                margin: 0;
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
                font-size: 1rem;
                font-weight: 400;
                line-height: 1.5;
                color: #212529;
                text-align: left;
                background-color: #121619 !important;
                color: #6c757d !important;
            }
            .px-3 {
                padding-left: 1rem !important;
            }
            .h-100 {
                height: 100%!important;
            }
            .row {
                display: -ms-flexbox;
                display: flex;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                margin-right: -15px;
                margin-left: -15px;
            }
            .pt-3 {
                padding-top: 1rem!important;
            }
            .d-table {
                display: table!important;
            }
            .w-100 {
                width: 100%!important;
            }
            .pt-4 {
                padding-top: 1.5rem !important;
            }
            .mt-4 {
                margin-top: 1.5rem !important;
            }
            .text-capitalize {
                text-transform: capitalize!important;
            }
            .text-muted {
                color: #6c757d!important;
            }
            h4 {
                display: block;
                margin-block-start: 1.33em;
                margin-block-end: 1.33em;
                margin-inline-start: 0px;
                margin-inline-end: 0px;
                font-weight: bold;
                font-size: 1.5rem;
                margin-top: 0;
                margin-bottom: 0.5rem;
                font-weight: 500;
                line-height: 1.2;
            }
            .text-danger {
                color: #dc3545 !important;
            }
            .mx-5 {
                margin-left: 3rem !important;
            }
        </style>
    </head>
    <body>
        <a href=<?php echo '"'. $make_url . '"'; ?>>
            <img src=<?php echo '"'. $make_url . 'assets/images/Logos/guardian_logo_70x70.png"'; ?>  width="40" height="45" alt="" style="padding:.5rem 1rem;">
        </a>
        <div class="px-3 h-100">
            <div class="row pt-3">
                <div class="d-table w-100" style="height:50vh;">
                    <div style="display: table-cell;vertical-align: middle;">
                        <div style="text-align: center">
                        <svg width="90px" height="80px" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="exclamation-triangle" class="svg-inline--fa fa-exclamation-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                            <path fill="currentColor" d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"></path>
                        </svg>
                        <div class="pt-4 mt-4 text-muted">
                            <h4 class="text-danger"><?php echo $this->t($this->data['dictTitle']); ?></h4>
                            <p><?php echo htmlspecialchars($this->t($this->data['dictDescr'], $this->data['parameters'])); ?><p>
                            Please contact your administrator.
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>