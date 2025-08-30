<?php
echo
'
<div class="modal fade" id="' . $basic_modal_id . '"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog '.$modal_size.'" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="' . $basic_modal_title_id . '"></h5>
                <button id = "close-btn" type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="right:15px;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="' . $basic_modal_body_id . '">
                ' . $modal_form_html . '
            </div>
        </div>
    </div>
</div>
';