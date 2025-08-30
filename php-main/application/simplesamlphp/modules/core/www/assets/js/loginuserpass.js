$(document).ready(function () {
    $('#submit_button').on('click', function () {
        $(this).attr('disabled', 'disabled');
        $(this).html(sanitizeHtml($(this).data('processing'), { allowedAttributes:false, allowedTags:false,}));
        $(this).parents('form').submit();
    });
});
