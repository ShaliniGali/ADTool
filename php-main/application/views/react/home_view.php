<?php

$js_files = array();
    $js_files['jquery'] = ['jquery.min.js','global'];
    
    

    $CI =& get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <link rel="icon" href="/static/favicon.ico" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <link rel="apple-touch-icon" href="/static/images/guardian_logo_70x70.png" />
        <link rel="manifest" href="/static/manifest.json" />
        <link rel="stylesheet" href="/static/css/all.min.css" />
        <title>GUARDIAN SSO</title>
        <script>
            let tile_data = JSON.parse('<?= $tile_data; ?>');
            let user_data = JSON.parse('<?= $user_data; ?>');
            ;(window.USER_DATA = {
                fullName: user_data.first_name,
                email: user_data.email,
                department: 'R&D',
            }),
                (window.APPS_DATA = tile_data)
        </script>
        <script defer="defer" src="/static/js/main.js"></script>
        <link href="/static/css/main.css" rel="stylesheet" />
    </head>
    <body>
        <noscript>You need to enable JavaScript to run this app.</noscript>
        <div id="root"></div>
    </body>
</html>
