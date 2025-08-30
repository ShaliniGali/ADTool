/**
 * Display success or error message
 * @param {'error' | 'success'} status 
 * @param {string} message 
 * @param {number} timeout 
 */
let toastCounter = 0;
function displayToastNotification(status, message, timeout=3000) {
	status = sanitizeHtml(status, { allowedAttributes: {}, allowedTags: []});
	
	$('.socom-toast-notification').not('#socom-toast-notification-' + status).addClass('d-none');
	$('#socom-toast-notification-' + status + ' p.bx--inline-notification__title').html(sanitizeHtml(message, { allowedAttributes: {}, allowedTags: []}));
	$('#socom-toast-notification-' + status).removeClass('d-none');
	if(timeout) {
		toastCounter++;
		setTimeout(
			() => {
				if( (--toastCounter <= 0) && !$('#socom-toast-notification-' + status).hasClass('d-none')) {
					$('#socom-toast-notification-' + status).addClass('d-none');
					$('#socom-toast-notification-' + status + ' p.bx--inline-notification__title').html('');
				}
			},
			timeout
		);
	}
}

if (!window._rb) window._rb = {};
window._rb.displayToastNotification = displayToastNotification