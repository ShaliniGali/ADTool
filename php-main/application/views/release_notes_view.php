<?php
    $page_data = [
        'page_title' => "Release Notes",
        'page_tab' => "Release Notes",
        'page_navbar' => true,
        'page_specific_css' => ['datatables.css', 'select2.css', 'release_notes.css'],
        'compression_name' => trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php')
    ];
    $this->load->view('templates/header_view', $page_data);

    $this->load->view('templates/essential_javascripts');

    $CI = &get_instance();
    $js_files = [
        "datatables" => ["datatables.min.js", 'global'],
        "datatables_features" => ["global/datatables_features.js", 'custom'],
        'select2' => ["select2.full.js", 'global'],
        "release_notes" => ["actions/release_notes.js", 'custom'],
        'socom_p1' => ['actions/p1_socom.js','custom']
    ];

    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<div id="release-notes-main">
</div>

<script>
    const versions = <?= json_encode($versions); ?>;
    $(() => {
        getNotes()
    })
</script>
