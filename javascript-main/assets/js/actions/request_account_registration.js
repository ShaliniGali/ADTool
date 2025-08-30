"use strict";

function request(req) {
    action_button('reqaccess', "add");
    $.post('/sso/requestRegistration', {
        rhombus_token: rhombuscookie(),
        req: req
    }, function(data, status){
        $('.px-3.h-100').html(sanitizeHtml(data.message,{
            allowedTags: false,
            allowedAttributes: false
        }));
    },"json");
}

function request_rb_kc() {
    action_button('reqaccess', "add");
    $.post('/rb_kc/requestRegistration', {
        rhombus_token: rhombuscookie()
    }, function(data, status){
        $('.px-3.h-100').html(sanitizeHtml(data.message,{
            allowedTags: false,
            allowedAttributes: false
        }));
    },"json");
}

function request_rb_p1() {
    action_button('reqaccess', "add");
    $.post('/rb_p1/requestRegistration', {
        rhombus_token: rhombuscookie()
    }, function(data, status){
        $('.px-3.h-100').html(sanitizeHtml(data.message,{
            allowedTags: false,
            allowedAttributes: false
        }));
    },"json");
}

// Expose functions in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}
window._rb.request = request;
window._rb.request_rb_kc = request_rb_kc;
window._rb.request_rb_p1 = request_rb_p1
