<div class="modal fade" id="timeout_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog " role="document">
        <div class="modal-content bg-light rounded">
            <div class="modal-header border-0">
                <h5 class="modal-title bg-light text-center w-100">Timeout Alert!</h5>
            </div>
            <div class="modal-body bg-light text-center">
                The maximum session time is <?= RHOMBUS_SSO_TIMEOUT?> minutes.<br>
                <text id="timeout_time_countdown">Timeout in:sec</text><br>
                Would you like to continue?
            </div>
            <div class="modal-footer bg-light border-0">
                <button id="user_timeout_no_button"  onclick="window.location='/login/logout'" type="button" class="btn btn-secondary" data-dismiss="modal">No, log out</button>
                <button id="user_timeout_continue_button" type="button" class="btn btn-primary" data-dismiss="modal">Yes, keep me logged in</button>
            </div>
        </div>
    </div>
</div>
