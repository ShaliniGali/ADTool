<?php
echo
'
<div class="modal fade" id="' . $confirm_modal_id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="' . $confirm_modal_title_id . '">Modal title</h5>
                <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="right:15px;color:white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="' . $confirm_modal_body_id . '">
            ...
            </div>
            <div class="modal-footer border-0">
            </div>
        </div>
    </div>
</div>
';