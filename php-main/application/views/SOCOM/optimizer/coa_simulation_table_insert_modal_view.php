<style>
.bx--modal-container .bx--modal-header{
  padding-right: 4em;
}

.bx--btn--primary[id^="save"] {
  padding-top: unset !important;
  padding-bottom: unset !important;
}

.down-button-text {
  padding-top: 1rem;
  padding-bottom: 2rem;
}

#coa-table-insert-modal-container.bx--modal-content {
    padding-right: 1rem;
}

</style>


<div data-modal id="coa-table-insert" class="bx--modal" role="dialog"
  aria-modal="true" aria-labelledby="coa-table-insert-label" aria-describedby="coa-table-insert-heading" tabindex="-1">
  <div class="bx--modal-container" style="width: 80%;">
    <div class="bx--modal-header">
      <p class="bx--modal-header__label bx--type-delta" id="coa-table-insert-label"></p>
      <div class="d-flex flex-row">
        <p class="bx--modal-header__heading bx--type-beta" id="coa-table-insert-heading">New Row</p>
            <div id="selected-position-tags">
            </div>
      </div>
      <button class="bx--modal-close" type="button" data-modal-close aria-label="close modal" >
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z"></path></svg>
      </button>
    </div>

    <div class="bx--modal-content ml-auto mr-auto overflow-auto h-100 w-100" id="coa-table-insert-modal-container">
    <div data-loading class="bx--loading">
        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
            <title>Loading</title>
            <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
        </svg>
    </div>
    </div>

    <div class="bx--modal-footer bx--btn-set">
    <button id="close-coa-row-btn" tabindex="0" class="bx--btn bx--btn--secondary" aria-label="close" type="button">Close</button>
      <button id="insert-coa-row-btn" class="bx--btn bx--btn--primary " type="button" data-modal-primary-focus disabled>
      <div>Insert Row</div>
      </button>
    </div>
  </div>
  <!-- Note: focusable span allows for focus wrap feature within Modals -->
  <span tabindex="0"></span>
</div>


<script>
const coa_table_insert_modal = new CarbonComponents.Modal(document.getElementById('coa-table-insert'));
let closeButtonClicked = false;
document.addEventListener('modal-hidden', function(evt) {
    if (evt.target.getAttribute('id') == 'coa-table-insert') {
        $('#coa-output').addClass('is-visible');
        closeButtonClicked = false;
    } else if (evt.target.getAttribute('id') === 'coa-output') {
      overrided_budget_impact_history = {};
      getUserSavedCOA();
    }
});

$('#coa-table-insert .bx--modal-close').on('click', () => {
  closeButtonClicked = true
})
$('#close-row-wss-results').on('click', () => {
  closeButtonClicked = true
})

document.addEventListener('modal-beinghidden', (e) => {
  if (e.target.getAttribute('id') === 'coa-table-insert' && !closeButtonClicked) {
    e.preventDefault();
  }
})

$('#coa-table-insert div.bx--modal-footer > button[aria-label="close"]').on('click',
    function() { 
        $('#coa-output').addClass('is-visible');
        $('#coa-table-insert.bx--modal').removeClass('is-visible');
})
</script>