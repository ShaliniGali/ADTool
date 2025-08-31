"use strict"

function access_error(jqXHR, textStatus, errorThrown) {
    if (
        typeof jqXHR.responseJSON === 'object' && 
        typeof jqXHR.responseJSON.type === 'string' && 
        typeof jqXHR.responseJSON.message === 'string'
      ) {
        facs_show_notification(jqXHR.responseJSON.type, jqXHR.responseJSON.message);
    }
}

function facs_show_notification(title, subtitle) {
    let elem = create_notification_elem(
        'facs_error', 
        sanitizeHtml(title, { allowedTags: false, allowedAttributes: false }), 
        sanitizeHtml(subtitle, { allowedTags: false, allowedAttributes: false }));
    
    $(document.body).append(elem[0]);
}

function create_notification_elem(id, title, subtitle) {
    let elem = `<div id="modal-facs" class="fixed-bottom alert alert-warning alert-dismissible fade show" role="alert">
    <strong>${title}</strong> ${subtitle}.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button></div>`;
    
    $('#modal-facs').remove();

    return $(elem);
}

$(function() {
    $.ajaxSetup(
        {
            error: access_error
        }
    )
});

if (!window._rb) window._rb = {
	access_error: access_error,
	facs_show_notification: facs_show_notification,
	create_notification_elem: create_notification_elem
}
