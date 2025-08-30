<?php
echo
'
<div class="modal fade" tabindex="-1" role="dialog" id="' . $delete_modal_id . '">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="' . $title . '">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="' . $message . '"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal" id="' . $cancel . '">Close</button>
                <button type="button" class="btn btn-light" id="' . $delete . '">Delete</button>
            </div>
        </div>
    </div>
</div>
';