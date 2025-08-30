function enableButton(btn_id, enable = true) {
    if (enable) {
        $('#' + btn_id).prop('disabled', false);
        $('#' + btn_id).css('cursor', 'pointer');
    } else {
        $('#' + btn_id).prop('disabled', true);
        $('#' + btn_id).css('cursor', 'default');
    }
}

$('#token_key').attr('maxlength', 16);

$('#token_key').on('input paste change', function() {
    this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    enableButton('token_code_submit', (this.value.length == 16));
});

function authenticateLoginToken() {
    action_button("token_code_submit","add");
    $.post("/login_token/authenticateLoginToken", {
        token: $('#token_key').val(),
        rhombus_token: rhombuscookie()
    }, function (data, status) {
        if (data.status == 'success') {
            document.getElementById("login_modal_title").innerHTML = '<span class="text-success"><i class="fa fa-check-circle pr-3" aria-hidden="true"></i>Success</span>';
            document.getElementById("login_modal_body").innerHTML = '<div class="fa-3x pb-4 text-white"><i class="fas fa-cog fa-spin"></i></div>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            setTimeout(function () {
                window.location.href = '/';
            }, 2000);
        }
        else if (data.status == 'failure') {
            $('#token_key').val('');
            enableButton('token_code_submit', false);
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">' + data.message + '</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            $('#login_modal').on('hidden.bs.modal', function () {
                $('input:text:visible:first').focus();
            })
        }
        else if (data.status == 'max_attempts_reached') {
            $('#token_key').val('');
            enableButton('token_code_submit', false);
            $('#token_key').prop('disabled', true);
            document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
            document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">' + data.message + '</span>';
            $("#login_modal_button1").addClass("d-none");
            $("#login_modal_button2").addClass("d-none");
            $("#login_modal").modal("show");
            setTimeout(function () {
                window.location.href = '/';
            }, 1000);
          
        }
        action_button("token_code_submit", "remove");
    }, 'json'); 
}

function sendLoginToken() {
    action_button("login_token", "add");
    $("#send-token-again").css("pointer-events","none");
    $.post("/login_token/generateLoginToken", {
        dummy: 'dummy',
        rhombus_token: rhombuscookie()
    }, function (data, status) {
        action_button("login_token", "remove");
        $("#email-confirm-message").removeClass("d-none");
        $("#send-token-again").css("pointer-events","fill");
    }, 'json');
}
