
function storm_weighted_based(datatable, showCols, hideCols, elem, reload=true) {
    let showElem, hideElem;

    if (elem == undefined || elem.checked && elem.id == 'r-storm') {
        showElem = '#show-weighted-score';
        hideElem = '#show-storm-score';

        $('#weighted_row').removeClass('d-flex').addClass('d-none').hide('slow');
    }

    if (elem.checked && elem.id == 'r-w') {
        showElem = '#show-storm-score';
        hideElem = '#show-weighted-score';

        if ($('#fp-weight-sel option').length === 0) {
            displayToastNotification('error', 'Please create weights by clicking the "Weights Builder" button.', 5000);
        }

        $('#weighted_row').removeClass('d-none').addClass('d-flex').show('slow');
    }

    $(showElem).addClass('d-none').removeClass('d-flex');
    $(hideElem).removeClass('d-none').addClass('d-flex');
    $(datatable).DataTable().columns(showCols).visible(true);
    $(datatable).DataTable().columns(hideCols).visible(false);
    if (reload === true) {
        $(datatable).DataTable().ajax.reload();
    }
}

if (!window._rb) { window._rb = {}; }
window._rb.storm_weighted_based = storm_weighted_based;