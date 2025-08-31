let notificationName;

function setNotificationName(name) {
    notificationName = name;
}

function hideNotification() {
    $(`#success-${notificationName},#error-${notificationName},#success-list-${notificationName},#error-list-${notificationName}`).addClass('d-none');
}

function showNotification(msg, type, list=false) {
    if (type !== 'error' && type !== 'success') {
        return false;
    }
    if (list === true) {
        type += '-list';
    }

    let elem = $(`#${type}-${notificationName}`);
    elem.find('p.bx--inline-notification__subtitle').html(sanitizeHtml(msg, {allowedAttributes:{}, allowedTags:[]}))
    elem.removeClass('d-none');
}

function ajaxFail(jqXHR, message) {
    hideNotification();
    if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.message === 'string') {
        showNotification(jqXHR.responseJSON.message, 'error');
    } else {
        showNotification(message, 'error');
    }
}

if (!window._rb) window._rb = {
    setNotificationName: setNotificationName,
    hideNotification: hideNotification,
    showNotification: showNotification,
    ajaxFail: ajaxFail
}