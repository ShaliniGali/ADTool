<?php
$base_url = parse_url(($_SERVER['SCRIPT_URI']));
$make_url = $base_url['scheme']."://".$base_url['host'].":".$base_url['port']."/";

$config = [
    /*
     * When multiple authentication sources are defined, you can specify one to use by default
     * in order to authenticate users. In order to do that, you just need to name it "default"
     * here. That authentication source will be used by default then when a user reaches the
     * SimpleSAMLphp installation from the web browser, without passing through the API.
     *
     * If you already have named your auth source with a different name, you don't need to change
     * it in order to use it as a default. Just create an alias by the end of this file:
     *
     * $config['default'] = &$config['your_auth_source'];
     */

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    /**
     * IDP template, copy and modify appropriately to add idps.
     */
    md5($make_url) => [
        'saml:SP',
        'entityID' => null,
        'privatekey' => getenv('RB_SAML_CERT_PRIVATE'),
        'certificate' => getenv('RB_SAML_CERT_PUBLIC'),
        'idp' => getenv('RB_SAML_IDP') ?? null,
        'discoURL' => null,
        'redirect.validate' => true,
        'redirect.sign' => true,
        'assertion.encryption' => true,
        'nameid.encryption' => true,
        'saml20.sign.assertion' => true,
        'sign.authnrequest' => true,
    ],
];
