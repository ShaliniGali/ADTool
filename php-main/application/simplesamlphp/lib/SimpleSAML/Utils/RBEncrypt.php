<?php
declare(strict_types=1);

namespace SimpleSAML\Utils;

class RBEncrypt
{
    private $cipher;
    private $key;
    private $iv;

    public function __construct(string $cipher = 'AES-256-CBC') {
        if (!in_array(strtolower($cipher), openssl_get_cipher_methods(true))) {
            throw new Exception('Fatal: Invalid OpenSSL cipher.');
        }
        $this->cipher = $cipher;
        $this->key = hash('sha256', getenv('RB_SAML_ADMIN_PASSWORD'));
        if (!isset($this->key)) {
            throw new Exception('Fatal: Invalid OpenSSL key.');
        }
        $this->iv = substr(hash('sha256', getenv('RB_SAML_SECRET_SALT')), 0, openssl_cipher_iv_length($this->cipher));
        if (!isset($this->iv) || strlen($this->iv) !== openssl_cipher_iv_length($this->cipher)) {
            throw new Exception('Fatal: Invalid OpenSSL IV.');
        }
    }

    public function encrypt($data) {
        return rawurlencode(base64_encode(bin2hex(openssl_encrypt($data, $this->cipher, $this->key, 0, $this->iv))));
    }

    public function decrypt($data) {
        return openssl_decrypt(hex2bin(base64_decode(rawurldecode($data))), $this->cipher, $this->key, 0, $this->iv);
    }
}
