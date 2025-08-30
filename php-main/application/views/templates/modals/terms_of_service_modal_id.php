<?php
// include 'terms_and_conditions.php';
if (!function_exists('getToS')) {
    function getToS() {
        $tos = 
        '
        <p> By logging in to this website: </p>
        <ol>
            <li> Users are accessing a U.S. Government information system. </li>
            <li> Information system usage may be monitored, recorded, and subject to audit. </li>
            <li> Unauthorized use of the information system is prohibited and subject to criminal and civil penalties. </li>
            <li> Use of the information system indicates consent to monitoring and recording. </li>
        </ol>
        ';

        return $tos;
    }
}

echo
'
<div class="modal fade tos_modal" id=' . $terms_of_service_modal_id . ' tabindex="-1" role="dialog" aria-labelledby=' . $terms_of_service_modal_title_id . ' aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id=' . $terms_of_service_modal_title_id . '>' . $terms_of_service_modal_title . '</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">' 
                . getToS() .
            '</div>
            <div class="modal-footer small">
                <p class="mr-auto">' . $terms_of_service_modal_footer . '</p>
            </div>
        </div>
    </div>
</div>
';