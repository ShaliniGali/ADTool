<?php

#[AllowDynamicProperties]
class LoginLayers {
    const LayerOn = '1';
    const LayerOff = '0';
    const GoogleAuthenticator = 0;
    const Yubikey = 1;
    const CAC = 2;
    const RecoveryCode = 3;
    const LoginToken = 4;
}