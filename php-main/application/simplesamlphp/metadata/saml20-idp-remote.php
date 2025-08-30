<?php

/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

$metadata['https://sso.rhombuspower.com/rb_idp/saml2/idp/metadata.php'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'https://sso.rhombuspower.com/rb_idp/saml2/idp/metadata.php',
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://sso.rhombuspower.com/rb_idp/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://sso.rhombuspower.com/rb_idp/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIFLzCCA5egAwIBAgIJAKDJqbbqrt77MA0GCSqGSIb3DQEBCwUAMIGtMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTEWMBQGA1UEBwwNTW91bnRhaW4gVmlldzEbMBkGA1UECgwSUmhvbWJ1cyBQb3dlciBJbmMuMR0wGwYDVQQDDBR3d3cucmhvbWJ1c3Bvd2VyLmNvbTEiMCAGCSqGSIb3DQEJARYTaXRAcmhvbWJ1c3Bvd2VyLmNvbTERMA8GCSqGSIb3DQEJBwwCJycwHhcNMjEwMjI1MDQwNTMyWhcNMzEwMjI1MDQwNTMyWjCBrTELMAkGA1UEBhMCVVMxEzARBgNVBAgMCkNhbGlmb3JuaWExFjAUBgNVBAcMDU1vdW50YWluIFZpZXcxGzAZBgNVBAoMElJob21idXMgUG93ZXIgSW5jLjEdMBsGA1UEAwwUd3d3LnJob21idXNwb3dlci5jb20xIjAgBgkqhkiG9w0BCQEWE2l0QHJob21idXNwb3dlci5jb20xETAPBgkqhkiG9w0BCQcMAicnMIIBojANBgkqhkiG9w0BAQEFAAOCAY8AMIIBigKCAYEAvTl9kCjUJ52AatR285qzZB+Wb5HJTQFhb2+mjK+l3m0HqXrKCx/aRG6QL9y4dHPRFrSe0sW7J2XpieauYRL7yVK0Vb6+eHXvST0oGQXJ6WS+cQ20DJuvnO0mABrLVcpeJheociPvz8znB3MlnRdv15p6XKg8c352k7OdI816XCmZngpxvRxAbyBe3M3AMGwwGDyegDwnYp+IqVJ3Z3xkPF9XLA9nPs2UYqjJXirtz5wkyTk3u6uq/VQWjqjo+gxHMye5IwARD9BPSpV4fSd6mLTOQEUXgCStA9Z4EstAaMJUj5Ily7V8jIS5Dfzn3ujWTLJTSgsfGD9eHT1i3SMPh3rm3RQV+RevGCvhHPYOQngqVtqeL0EhVuiu3/w2reb/Y4J8D8wMCsVKtf3e8UYPUeI8eUCyTyuBo5fNQ7hPHRvN+tUgb7yOGb2nborAd7/d/Tt2tTwoY9oXOaPtvTlZfmFJDz1S8JdE92SWyt8vgxrSuzVEKnNiEzHMhNbghR8tAgMBAAGjUDBOMB0GA1UdDgQWBBSQLMQX8U9+XDg3nFHJ24k/z5oSJzAfBgNVHSMEGDAWgBSQLMQX8U9+XDg3nFHJ24k/z5oSJzAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBgQAKkrP0NiFBbScIgjlVTQpI0HPmhwqgusqg2WIBmxFbmJ/6kj4SSzJo7sF2djYqWYgFPeSQDm88N/hDPi7yObptKbDIWs+5VdUYx2QS1DEaQAikpmnlQk71hj2JhKqSz6Bc5689M8q+smOSZUaux6NKQbE+62G9RwhCBu1xl2bcIawrmtYDooaO9CNWsqUKFhqwgFTyl4jY0Opyu88O7BGUWQNEpAGRO4HoZplMWtG9J8KQSLlzt96kx2/z/QkrEbjVZigXRJr/Kx7zCvtVpCmXnNks2r53001p0EdWI1CnL3R/+7kXW+11odz1WW9TXCWG/5wnkjVHNHTV2886P1kZOvMwxA6c28YaC0LF+k+9G8KJMZivmjGI+RZxBxVp4Wi1G9L4Blern67Aq0fcYTeSJ8fvKy8FiJhdhk2GmgYCfPmypzG7vPw3ZMBMPi5pKDiiloiyxCq9PmGbZXgQ87q2bFnLOz6PzLgOyDlWdc9e1r4vDFkdpfRHFH+IUwCkgUo=',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'redirect.sign' => true,
    'contacts' => [
        [
            'emailAddress' => 'it@rhombuspower.com',
            'contactType' => 'technical',
            'givenName' => 'Rhombus',
            'surName' => 'IT',
        ],
    ],
];