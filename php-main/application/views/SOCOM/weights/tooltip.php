
<?php
if(!function_exists('renderTooltip')){
    function renderTooltip($criteria, $description, $id_postfix=1) {
    $criteriaHtml = '<span>' . htmlspecialchars($criteria, ENT_QUOTES, 'UTF-8') . '</span>';
    if (!empty($description)) {
        return '<div style="display: flex; align-items: center; gap: 5px;" data-bs-toggle="tooltip" data-bs-placement="top" title="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'">' . $criteriaHtml . '</div>';
    }
    return $criteriaHtml;
    }
}

?>

