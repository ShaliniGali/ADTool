<?php
echo
'
<div class="modal fade" id="' . $basic_modal_id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="' . $basic_modal_title_id . '">Modal title</h5>
                <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="right:15px;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="' . $basic_modal_body_id . '">
                ...
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" id="' . $basic_modal_button_1_id . '" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="' . $basic_modal_button_2_id . '" >Okay</button>
            </div>
        </div>
    </div>
</div>
';