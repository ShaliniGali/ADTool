<style>
    .select2-container--default.select2-container--focus .select2-selection--multiple{
        background-color: #1c1c1c !important;
    }

    .select2-search select2-search--inline{
        background-color: #1c1c1c !important;
    }
    
    [data-theme="dark"] .select2-search, .select2-search input, .select2-results, .select2-container--default .select2-selection__rendered{
        background-color: #1c1c1c !important;
    }

    .select2-container--default .select2-selection--multiple{
        background-color: #1c1c1c !important;
        overflow-y: scroll !important;
        max-height: 120px !important;
        height: 100px !important;
    }

    #subapps_modal .select2-container--default .select2-selection--multiple {
        height: 107px !important;
        width: 300px !important;
    }

    .select2-container--default .select2-results__option--selected{
        background-color: #ddd !important;
        color: black !important;
        border: 1px solid black !important;
    }

    .select2-selection__choice{
        margin: 2px;
    }

    .subapp_tag, .subapp_tag_2{
        width: 211px;
        max-width: 211px !important;
        margin-right: 10px;
        font-weight: 600;
        color: white !important;
    }

    #subapps_account_type_save, #subapps_account_type_save_2{
        margin-left:auto !important;
        color: white!important;
    }

    .subapp_wrapper{
        border: 1px solid lightgray;
        border-radius: 5px;
        padding: 4px;
        color: white !important;
    }

    #subapps_modal .bx--label{
        color: white !important;
    }

    #subapps_modal .bx--modal-close svg{
        fill: #ffffff;
    }

</style>

<div data-modal id="subapps_modal" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="subapps_modal-label" aria-describedby="subapps_modal-heading" tabindex="-1">
  <div class="bx--modal-container" style="height: 69%;width: 40%; background:#272727;">
    <div class="bx--modal-header">
      <button class="bx--modal-close" type="button" data-modal-close aria-label="close modal" >
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z"></path></svg>
      </button>
    </div>

    <!-- Note: Modals with content that scrolls, at any viewport, requires `tabindex="0"` on the `bx--modal-content` element -->

    <div class="bx--modal-content" >

        <div class="d-flex flex-column mt-4">
            <div class="mb-1 bx--label medium-label mb-2" style="font-size: 1.25rem;">Requested Apps</div>
            <div id="subapps_account_type_save">
            </div>
            <button class="bx--btn bx--btn--primary" type="button" style="width: 100px;margin-left:auto; margin-right:auto;" id="subapp_save">Register</button>
        </div>

        <div class="d-flex flex-column mt-4">
            <div class="mb-1 bx--label medium-label mb-2" style="font-size: 1.25rem;">Approved Apps</div>
            <div id="subapps_account_type_save_2">
            </div>
            <button class="bx--btn bx--btn--primary" type="button" style="width: 100px;margin-left:auto; margin-right:auto;" id="subapp_save_2">Update</button>
        </div>
    </div>

  </div>
  <!-- Note: focusable span allows for focus wrap feature within Modals -->
  <span tabindex="0"></span>
</div>

<script>
    const subapps_modal = new CarbonComponents.Modal(document.getElementById('subapps_modal'));

    $('.select_subapps').select2({
        placeholder: 'Select an option',
        dropdownParent: '#subapps_modal'
    });
</script>