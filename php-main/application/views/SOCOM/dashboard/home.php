<?php

  $this->load->view('templates/essential_javascripts');

    $is_guest = auth_coa_role_guest();
    $is_restricted = auth_coa_role_restricted();
    
    $show_import_upload = !$is_restricted; // all types of users except restricted
    $show_coa_management = !$is_guest && !$is_restricted; // only for users and admins
    
    $description = !$show_import_upload ? 'Access Not Allowed' : '';
    $import_hidden = $show_import_upload ? '' : 'hidden';
    $import_state = !$show_import_upload; // true if restricted
?>
<div class="landing-page pt-3 h-100" style="background-color: #f4f4f4;padding-top: 5em !important;">
    <div class="landing-page-opts ml-auto mr-auto mt-3" style="margin-top: 1em;width:80%">

        <div class="d-flex flex-row">
            <?php $this->load->view('home_block_view',array('label'=>'Account Management','description'=>'',
            'link'=>'/dashboard/myuser',
            'icon'=>'<svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                <defs><style>.cls-1{fill:none;}</style></defs><title>User--Management</title>
                <polygon points="25 13 23.407 16 20 16.414 22.5 18.667 22 22 25 20.125 28 22 27.5 18.667 30 16.414 26.5 16 25 13"/>
                <polygon points="21.414 13.414 25 9.834 25 9.834 28.587 13.416 30 12 25 7 20 12 21.414 13.414"/>
                <polygon points="21.414 8.414 25 4.834 25 4.834 28.587 8.416 30 7 25 2 20 7 21.414 8.414"/>
                <path d="M16,30H14V25a3.0033,3.0033,0,0,0-3-3H7a3.0033,3.0033,0,0,0-3,3v5H2V25a5.0059,5.0059,0,0,1,5-5h4a5.0059,5.0059,0,0,1,5,5Z" transform="translate(0 0)"/>
                <path d="M9,10a3,3,0,1,1-3,3,3,3,0,0,1,3-3M9,8a5,5,0,1,0,5,5A5,5,0,0,0,9,8Z" transform="translate(0 0)"/>
                <rect id="_Transparent_Rectangle" style="fill:none;" width="32" height="32"/>
                </svg>'));
            ?>
            <?php if ($is_admin && ($is_cycle_admin_user || $is_weight_criteria_admin_user)): ?>

                <?php $this->load->view('home_block_view',array('label'=>'Cycle Management','description'=>'',
                        'link'=>'/dashboard/cycles',
                        'class'=>'',
                        'icon'=>'<svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                        <defs><style>.cls-1{fill:none;}</style></defs>
                        <title>stacked</title><path d="M18,30H4a2,2,0,0,1-2-2V14a2,2,0,0,1,2-2H18a2,2,0,0,1,2,2V28A2,2,0,0,1,18,30ZM4,14V28H18V14Z"/>
                        <path d="M25,23H23V9H9V7H23a2,2,0,0,1,2,2Z"/><path d="M30,16H28V4H16V2H28a2,2,0,0,1,2,2Z"/>
                            <rect id="_Transparent_Rectangle" style="fill:none;" width="32" height="32"/>
                        </svg>'));
                ?>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-row" <?=$import_hidden?>>
            <?php if($show_import_upload) : ?>
                <?php $this->load->view('home_block_view',array('label'=>'Import and Upload','description'=>'',
                    'link'=>'/dashboard/import_upload',
                    'description'=> $description,
                    'class'=> '',
                    'state'=> $import_state,
                    'icon'=>'<svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                    <defs><style>.cls-1{fill:none;}</style></defs><title>document--import</title><polygon points="28 19 14.83 19 17.41 16.41 16 15 11 20 16 25 17.41 23.59 14.83 21 28 21 28 19"/>
                    <path d="M24,14V10a1,1,0,0,0-.29-.71l-7-7A1,1,0,0,0,16,2H6A2,2,0,0,0,4,4V28a2,2,0,0,0,2,2H22a2,2,0,0,0,2-2V26H22v2H6V4h8v6a2,2,0,0,0,2,2h6v2Zm-8-4V4.41L21.59,10Z"/>
                    <rect id="_Transparent_Rectangle" style="fill:none;" width="32" height="32"/>
                    </svg>')); 
                ?>
            <?php endif; ?>

            <?php if($show_coa_management) : ?>
                <?php $this->load->view('home_block_view',array('label'=>'COA Management','description'=>'',
                    'link'=> '/dashboard/coa_management',
                    'description'=> $description,
                    'class'=> '',
                    'state'=> $import_state,
                    'icon'=>'<svg id="icon" xmlns="http://www.w3.org/2000/svg" id="icon" viewBox="0 0 32 32">
                    <defs><style>.cls-1{fill:none;}</style></defs><title>share-knowledge</title>
                    <path d="M27,25H21a3,3,0,0,0-3,3v2h2V28a1,1,0,0,1,1-1h6a1,1,0,0,1,1,1v2h2V28A3,3,0,0,0,27,25Z"/>
                    <path d="M20,20a4,4,0,1,0,4-4A4,4,0,0,0,20,20Zm6,0a2,2,0,1,1-2-2A2,2,0,0,1,26,20Z"/><path d="M6,21V20H4v1a7,7,0,0,0,7,7h3V26H11A5,5,0,0,1,6,21Z"/>
                    <rect x="19" y="10" width="7" height="2"/><rect x="19" y="6" width="10" height="2"/><rect x="19" y="2" width="10" height="2"/><path d="M11,11H5a3,3,0,0,0-3,3v2H4V14a1,1,0,0,1,1-1h6a1,1,0,0,1,1,1v2h2V14A3,3,0,0,0,11,11Z"/>
                    <path d="M8,10A4,4,0,1,0,4,6,4,4,0,0,0,8,10ZM8,4A2,2,0,1,1,6,6,2,2,0,0,1,8,4Z"/>
                    <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32"/>
                    </svg>')); 
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>