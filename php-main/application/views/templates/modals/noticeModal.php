<?php
  echo '

  <div class="modal fade" id="' . $basic_modal_id . '" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id = "' . $modal_header_id . '">
                ' . $modal_header_value . '
            </div>
            <div class="modal-body" id = "' . $modal_body_id . '">
                ' . $modal_body_value . '
            </div>
            <div class="modal-footer" id ="' . $modal_footer_id . '">
                <button type="button" class="btn btn-dark" data-dismiss="modal" id = "' . $cancel_button_id . '">Cancel</button>
                <button type="button" class="btn btn-light" data-dismiss="modal" id = "' . $continue_button_id . '">' . $button_string . '</a>
            </div>
        </div>
    </div>
  </div>
    ';
