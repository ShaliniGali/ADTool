"use strict";

/**
 * @author Moheb, November 4th, 2020
 * 
 * Returns an array of all the ip fields DOM whose parent is specified by name.
 * @param {DOM} field, the DOM of a single ip input field.
 * @return {void}
 */
function validateIP(field, className, errorId = null) {
    const ip_fields_count = 4;

    if(errorId != null)
        $("#"+errorId).html("");
    if (isValidIP(field.value)){
        let ip_address = field.value.split('.');
        let ip_fields = field.parentElement.getElementsByClassName(className);
        for (let i = 0; i < ip_fields_count; ++i) {
            ip_fields[i].value = ip_address[i];
        }
    } else {
        field.value = field.value.replace(/\D/g, '').substr(0,3);
		if (field.value < 0) {
			field.value = 0;
		}
		if (field.value > 255) {
			field.value = 255;
		}

        // secondary check after truncuate
        let ip_fields = field.parentElement.getElementsByClassName(className);
        let ip_array = [];
        for (let j = 0; j < ip_fields_count; ++j) {
            ip_array[j] = ip_fields[j].value
        }
        let full_ip = ip_array.join('.');
        if(errorId == null || !isValidIP(full_ip)){
             $("#"+errorId).html("Warning: The IP you have given is unrecognized or incomplete");
        }

    }
}

/**
 * Helper func that validates the passed string IP address.
 * @param {String} strIP 
 * @return Boolean
 */
function isValidIP(strIP) {
	return (/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(strIP)) ? true : false;		
}

/**
 * @author Moheb, November 4th, 2020
 * 
 * Returns an array of all the ip fields DOM whose parent is specified by name.
 * @param {string} name, the name attribute of the ip input fields' parent.
 * @return {array}
 */
function getIpFieldsDOMList(name) {
    return document.getElementsByName(name)[0].getElementsByTagName('INPUT');
}

// Expose functions in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.validateIP = validateIP;
window._rb.getIpFieldsDOMList = getIpFieldsDOMList;
