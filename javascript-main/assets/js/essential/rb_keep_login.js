"use strict";

//toggle for user access
let userUsedThePage = false
let timeoutInterval = null


//on mouse move set user touch to true
$(document).ready(function () {
    let idleInterval = setInterval(function () {
        if (userUsedThePage) {
            userUsedThePage = false;
            $.post('/login/nothing',{'rhombus_token': rhombuscookie()});
        } else {
            if (timeoutInterval == null) {
                $('#timeout_modal').modal('show')
                let count = 60;
                $('#timeout_time_countdown').html("Timeout in: " + sanitizeHtml((count--), {allowedTags: false, allowedAttributes: false}) + " sec")
                timeoutInterval = setInterval(function () {
                    $('#timeout_time_countdown').html("Timeout in: " + sanitizeHtml((count--), {allowedTags: false, allowedAttributes: false}) + " sec")
                    if (count < 1) {
                        $('#user_timeout_no_button').addClass('d-none')
                        $('#timeout_time_countdown').html("Reload the page to continue")
                        $('#user_timeout_continue_button').html('Reload');
                        $('#user_timeout_continue_button').on('click', function () {
                            window.location.reload();
                        })
                        clearInterval(idleInterval)
                        clearInterval(timeoutInterval)
                    }
                }, 1000);
            }
        }
        userUsedThePage = false;
    }, (timeout_max_time - 1) * 60000); // max time minutes - 1
    
    $('#user_timeout_continue_button').on('click', function () {

        $.post('/login/nothing',{'rhombus_token': rhombuscookie()});

        clearInterval(timeoutInterval)

        timeoutInterval = null;

    });

    $(this).mousemove(function (e) {
        userUsedThePage = true;
    });

    $(this).keydown(function (e) {
        userUsedThePage = true;
    });
    $(this).click(function (e) {
        userUsedThePage = true;
    });
    $(this).scroll(function (e) {
        userUsedThePage = true;
    });

});
