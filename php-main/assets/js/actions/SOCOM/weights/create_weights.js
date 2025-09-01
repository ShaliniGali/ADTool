"use strict";

function create_weights_slider_panel(current_tab) {
        
        let criteria = $(`.${current_tab}-crit-sliders`).map(function () {
                        return $(this).attr("crit")
                }).get();
        
        criteria.forEach((name) => {
                let $input = $(`#${current_tab}-criteria-weight_${name}_value`);

                $(`#${current_tab}-criteria-${name}`).ionRangeSlider({
                                prettify_enabled: true,
                                prettify_separator: ",",
                                min: 0,
                                max: 1,
                                from: ( 
                                        $(`#${current_tab}-criteria-${name}`).attr("wt") 
                                                !=  
                                        $(`#${current_tab}-criteria-${name}`).attr("value")) 
                                                ? 
                                        parseFloat($(`#${current_tab}-criteria-${name}`).attr("value")) 
                                                : 
                                        parseFloat($(`#${current_tab}-criteria-${name}`).attr("wt")),
                                step: 0.01,
                                drag_interval: true,
                                grid: true,
                                prettify_enabled: true,
                                onStart: function(data) {
                                        $input.prop("value", data.from);
                                },
                                onChange: function(data) {
                                        $input.prop("value", data.from);
                                }
                        });
                        
                        let instance = $(`#${current_tab}-criteria-${name}`).data("ionRangeSlider");
                        
                        $input.on("input", function() {
                                let val, elem = $(this), origVal = parseFloat(elem.prop("value"));
                                
                                val = origVal.toFixed(2);

                                if (elem.hasClass('bx--text-input--invalid')) {
                                        elem.removeClass('bx--text-input--invalid');
                                        elem.parent().attr('data-invalid', false)
                                        elem.parent().next().empty();
                                }

                                // validate
                                if (val < 0) {
                                        val = 0;
                                        elem.addClass('bx--text-input--invalid');
                                        elem.parent().attr('data-invalid', 'true')
                                        elem.parent().next().html('Value must be > 0');
                                } else if (val > 1) {
                                        val = 1;
                                        elem.addClass('bx--text-input--invalid');
                                        elem.parent().attr('data-invalid', 'true')
                                        elem.parent().next().html('Value must be < 1');
                                } else if (val != origVal) {
                                        elem.addClass('bx--text-input--invalid');
                                        elem.parent().attr('data-invalid', 'true')
                                        elem.parent().next().html('Value must be 2 decimals only');
                                } else {
                                        instance.update({
                                                from: val
                                        });
                                }
                        });

                        $('span.irs,span.irs-single').attr('tabindex', '-1');
                }
        );
}

const refresh_csrf_cookie = () => {
        rb_input_submit_form_cookie();
        setTimeout(refresh_csrf_cookie, 5000);
}

function getTabWeightTotal(type) {
        let sum = 0.0;

        if (['guidance','pom'].findIndex( (elem) => elem  === type ) != -1) {
                $('input.' +  type + '-crit-sliders').each(function () {sum += Number.parseFloat(Number.parseFloat($(this).val()).toFixed(2)); }); 
        }

        return sum;
}

function validateForm(type) {
        let data = {}, result = true;
        if (['guidance','pom'].findIndex( (elem) => elem  === type ) != -1) {
                data.title = $('#text-input-title').val();

                if (data.title.trim().length === 0) {
                        displayToastNotification('error', 'Title must be set');

                        $('#text-input-title').addClass('bx--text-input--invalid');

                        $('li.bx--progress-step--current svg').addClass('bx--progress__warning').attr('viewBox', '0 0 16 16').html(progress_error)

                        result = false;
                } else if ($('#text-input-title').hasClass('bx--text-input--invalid')) {
                        $('#text-input-title').removeClass('bx--text-input--invalid');
                }

                if (getTabWeightTotal(type).toFixed(2) != 1.00) {
                        displayToastNotification('error', 'Weights must add up to 1');
                        
                        $('li.bx--progress-step--current svg').addClass('bx--progress__warning').attr('viewBox', '0 0 16 16').html(progress_error)

                        result = false;
                }

        }
        
        return result;
}

function changeForm(type) {
        if (['guidance','pom','storm'].findIndex( (elem) => elem  === type ) != -1) {
                $('#' + type + '-panel-container').prop('hidden', true);
                let new_type;
                if (type === 'guidance') {
                        new_type = 'pom';
                } else if (type === 'pom') {
                        new_type = 'storm';
                        load_storm_table();
                } else {
                        // do not support going back
                        // the progress indicator would need an update 
                        return false;
                }

                $('#' + new_type + '-panel-container').prop('hidden', false);
                
                progressLevels(type)
        }
}

function progressLevels(type) {
        if (['guidance','pom', 'storm'].findIndex( (elem) => elem  === type ) != -1) {
                let next = $('li.bx--progress-step--current').removeClass('bx--progress-step--current').addClass('bx--progress-step--complete').next();
                        
                $('li.bx--progress-step--complete svg').removeClass('bx--progress__warning').attr('viewBox', '0 0 32 32').html(progress_complete);

                if (next.length > 0) {
                        next.removeClass('bx--progress-step--incomplete').addClass('bx--progress-step--current');
                        next.find('svg').removeClass('bx--progress__warning').attr('viewBox', '0 0 32 32').html(progress_current);
                }
        }
}

function saveForm(type) {
        let data = {SESSION: {}, DESCRIPTION: '', TITLE: ''}

        if (['guidance','pom'].findIndex( (elem) => elem  === type ) != -1) {
                $('input.' +  type + '-crit-sliders').each(
                        function(i, elem) {
                                data['SESSION'][elem.id.replace(/(pom|guidance)-criteria-/, '')] = parseFloat(elem.value);
                        }
                );

                data['DESCRIPTION'] =  $('#'+type+'-text-area-description').val();
        }

        return data;
}

function sendWeight() {
        let data = {
            rhombus_token: rhombuscookie()
        };

        if (!validateForm('guidance') || !validateForm('pom')) {
                return false;
        }

        data['title'] = $('#text-input-title').val();
        
        // Get guidance and POM data
        let guidanceData = saveForm('guidance');
        let pomData = saveForm('pom');
        
        // Add guidance data
        Object.keys(guidanceData.SESSION).forEach(function(key) {
            data['guidance[SESSION][' + key + ']'] = guidanceData.SESSION[key];
        });
        data['guidance[DESCRIPTION]'] = guidanceData.DESCRIPTION;
        
        // Add POM data
        Object.keys(pomData.SESSION).forEach(function(key) {
            data['pom[SESSION][' + key + ']'] = pomData.SESSION[key];
        });
        data['pom[DESCRIPTION]'] = pomData.DESCRIPTION;
        
        return $.post('/socom/resource_constrained_coa/weights/save', 
            data, 
            function(data) {
                
                if (data.status === true) {
                        displayToastNotification('success', 'New weight has been created. Redirecting to Weights List Page.');
                        $('#criteria-list').DataTable().ajax.reload();
                        setTimeout(function() {
                                $('button[data-target=".weight--panel--opt-2"]').trigger('click');
                        }, 3000);
                        
                } else {
                        displayToastNotification('error', data.message); 
                }
             },
            "json"
        ).fail(ajaxFail);
}

function ajaxFail(jqXHR) {
        if (typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.message === 'string') {
                displayToastNotification('error', jqXHR.responseJSON.message);
        } else {
                displayToastNotification('error', 'Unknown error when trying to save weights.');
        }
}

function onReady() {                                                        
        create_weights_slider_panel('guidance');
        create_weights_slider_panel('pom')

        $('#create-guidance-weight').on('click', function() { if (validateForm('guidance') === true) { changeForm('guidance'); }});

        $('#create-pom-weight').on('click', function() { if (validateForm('pom') === true ) {  changeForm('pom'); }});

        $('#create-storm-weight').on('click', function() { progressLevels('storm'); sendWeight(); } );

        

        $('input.guidance-crit-sliders, input.pom-crit-sliders').on('change', function () { 
                let type =  null; 
                
                if ($(this).hasClass('guidance-crit-sliders')) {
                        type = 'guidance';
                } else if ($(this).hasClass('pom-crit-sliders')) {
                        type = 'pom';
                }
                
                if (type != null) {
                        let sum = getTabWeightTotal(type);
        
                        if (sum.toFixed(2) > 1.0 || sum.toFixed(2) < 1.0) {
                                $('#' + type + "-criteria-validation-sum-tag").removeClass('bx--tag--green').addClass('bx--tag--red')
                        } else {
                                $('#' + type + "-criteria-validation-sum-tag").removeClass('bx--tag--red').addClass('bx--tag--green')
                        }
                        $('#' + type + "-criteria-validation-sum-tag-num").html(sum.toFixed(2));
                }
        });
}

$(onReady)

let progress_waiting = `<path d="M7.7 4.7a14.7 14.7 0 00-3 3.1L6.3 9A13.26 13.26 0 018.9 6.3zM4.6 12.3l-1.9-.6A12.51 12.51 0 002 16H4A11.48 11.48 0 014.6 12.3zM2.7 20.4a14.4 14.4 0 002 3.9l1.6-1.2a12.89 12.89 0 01-1.7-3.3zM7.8 27.3a14.4 14.4 0 003.9 2l.6-1.9A12.89 12.89 0 019 25.7zM11.7 2.7l.6 1.9A11.48 11.48 0 0116 4V2A12.51 12.51 0 0011.7 2.7zM24.2 27.3a15.18 15.18 0 003.1-3.1L25.7 23A11.53 11.53 0 0123 25.7zM27.4 19.7l1.9.6A15.47 15.47 0 0030 16H28A11.48 11.48 0 0127.4 19.7zM29.2 11.6a14.4 14.4 0 00-2-3.9L25.6 8.9a12.89 12.89 0 011.7 3.3zM24.1 4.6a14.4 14.4 0 00-3.9-2l-.6 1.9a12.89 12.89 0 013.3 1.7zM20.3 29.3l-.6-1.9A11.48 11.48 0 0116 28v2A21.42 21.42 0 0020.3 29.3z"></path><title></title>`,
progress_current = `<path d="M23.7642 6.8593l1.2851-1.5315A13.976 13.976 0 0020.8672 2.887l-.6836 1.8776A11.9729 11.9729 0 0123.7642 6.8593zM27.81 14l1.9677-.4128A13.8888 13.8888 0 0028.14 9.0457L26.4087 10A12.52 12.52 0 0127.81 14zM20.1836 27.2354l.6836 1.8776a13.976 13.976 0 004.1821-2.4408l-1.2851-1.5315A11.9729 11.9729 0 0120.1836 27.2354zM26.4087 22L28.14 23a14.14 14.14 0 001.6382-4.5872L27.81 18.0659A12.1519 12.1519 0 0126.4087 22zM16 30V2a14 14 0 000 28z"></path><title></title>`,
progress_complete = `<path d="M14 21.414L9 16.413 10.413 15 14 18.586 21.585 11 23 12.415 14 21.414z"></path><path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,26A12,12,0,1,1,28,16,12,12,0,0,1,16,28Z"></path><title></title>`,
progress_error = `<path d="M8,1C4.1,1,1,4.1,1,8s3.1,7,7,7s7-3.1,7-7S11.9,1,8,1z M8,14c-3.3,0-6-2.7-6-6s2.7-6,6-6s6,2.7,6,6S11.3,14,8,14z"></path><path d="M7.5 4H8.5V9H7.5zM8 10.2c-.4 0-.8.3-.8.8s.3.8.8.8c.4 0 .8-.3.8-.8S8.4 10.2 8 10.2z"></path><title></title>`

if (!window._rb) {window._rb={}}
window._rb.create_weights_slider_panel = create_weights_slider_panel;
window._rb.refresh_csrf_cookie = refresh_csrf_cookie;
window._rb.getTabWeightTotal = getTabWeightTotal;
window._rb.validateForm = validateForm;
window._rb.changeForm = changeForm;
window._rb.progressLevels = progressLevels;
window._rb.saveForm = saveForm;
window._rb.sendWeight = sendWeight;
window._rb.ajaxFail = ajaxFail;
window._rb.onReady = onReady;

