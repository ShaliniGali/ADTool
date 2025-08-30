<!-- 
    Created: Sai June 10 2020 
-->
<div class="pt-5">
    <h4 class="modal-title col-12 text-center"><em class="fas fa-user-lock fa-2x center-block text-muted"></em></h4>
    <h4 class="text-center mb-5 text-muted">Two-step registration</h4>
    <p class="col-12 modal-title text-center mb-4 text-muted">You are one step away from hopping into your account. Please choose one of the following methods to securely activate to your account.</p>
    <p id="yubikey_notification" class="col-12 modal-title text-center mb-4 text-danger">Admin has requested you to register one of the followings:</p>
    <form class="pb-5 text-center col-12">
        <div class="row px-5">
            <div class="col-12 col-md-6 col-xl-3">
                <button type="button" class="btn btn-secondary auth-button rounded w-100" id="google_auth_register"><em class="fas fa-mobile-alt mr-1"></em> APP Authenticator</button>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <button type="button" class="btn btn-secondary auth-button rounded w-100" id="yubikey_register" onclick="registerYubikey()">Yubikey</button>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <button type="button" class="btn btn-secondary auth-button rounded w-100" id="recovery_register" disabled>Recovery Code</button>
            </div>
        </div>
    </form>
</div>

<!-- WIP: ENABLE GOOGLE AUTHENTICATOR FOR THIS ACCOUNT -->
<div class="text-center d-none" id="google_authenticator_register">
    <div>
        <label class="text-muted">SCAN THE QR CODE WITH YOUR APP AUTHENTICATOR</label>
        <div class="col-12 mb-5 text-muted">
            <img alt="QR image" id="qr_img">
        </div>
        <label class="text-muted enter_code">ENTER THE CODE</label>
        <div class="mb-4 col-12 mt-3">
            <?php
                $input_id_html = '<input class="qr_input" id="qr_code_input_';
                $input_html = '" type="text" maxLength="1" size="3" min="0" max="9" pattern="[0-9]{1}" style="text-align:center; border-radius: 3px"/>';
                $html = '';

                for($i = 0; $i < 6; ++$i) {
                    $html .= $input_id_html . $i . $input_html . ' ';
                }
                echo $html;
            ?>
        </div>
        <div class="col-12 mb-3 mt-2">
            <p class="authentication_success text-muted d-none"> Successfully authenticated. Please enter a new valid QR code <span class="text-danger">once again.</span><p>
        </div>
        <p class="mb-2 text-muted mb-4"><em class="fas fa-mobile-alt mr-2"></em>Install authenticator application (Google Authenticator, Microsoft Authenticator etc), scan the above QR code with your authenticator application and then enter the received code in above input field.</p>
    </div>
</div>