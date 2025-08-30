<?php
/**
 * Rhombus_url_helper Helper class
 *
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// ------------------------------------------------------------------------

if (! function_exists('php_api_call'))
{
    function php_api_call($request_type, $headers, $params, $url, &$php_api_http_status = false)
    {
        static $jwt = null;

        if ($jwt === null && (
            strpos($url, RHOMBUS_PYTHON_URL.'/socom/') !== false || 
            strpos($url, RHOMBUS_PYTHON_URL.'/optimizer/') !== false ||
            strpos($url, RHOMBUS_PYTHON_URL.'/stream/') !== false
            )
        ) {
            $CI = &get_instance();
            $jwt = $CI->userauthorization->get_token();
        }                                                                                                                                                                                 

        if (isset($jwt->access_token) && (
            strpos($url, RHOMBUS_PYTHON_URL.'/socom/') !== false || 
            strpos($url, RHOMBUS_PYTHON_URL.'/optimizer/') !== false ||
            strpos($url, RHOMBUS_PYTHON_URL.'/stream/') !== false
            )
        ) {
            if (is_array($headers)) {
                $headers[] = "\r\nAuthorization: Bearer " . $jwt->access_token . "\r\n";
            }
            
            if (!is_string($headers)) {
                $headers = '';
                $headers .= "\r\nAuthorization: Bearer " . $jwt->access_token . "\r\n"; 
            } else {
                $headers .= "\r\nAuthorization: Bearer " . $jwt->access_token . "\r\n"; 
            }
        }

        $opts = [
            'http' =>
            [
                'protocol_version' => 1.1,
                'ignore_errors' => true,
                'method' => $request_type,
                'header' => $headers
            ]
        ];

        if ($params!=null) {
            $opts['http']['content'] = $params;
        }

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        
        php_api_debug($url, $params, $headers, $result, $http_response_header);
        php_api_error($url, $http_response_header);
        
        $php_api_http_status = php_api_response_http_status($http_response_header);

        if ($result === false) {
            show_error('Unable to contact API', 500);
        }
        
        return $result;
    }
}

if (!function_exists('php_api_error')) {
    function php_api_error($url, $http_response_header) {
        if (
            isset($http_response_header[0]) && 
            preg_match(
                '/^HTTP\/1\.1\s(\d{3})\s.*$/', 
                $http_response_header[0], 
                $matches
            ) === 1 &&
            isset($matches[1]) &&
            in_array(substr($matches[1], 0, 1), [4,5])
        ) {
            log_message(
                'error', 
                sprintf(
                    'php_api_call request failure URL: %s, HTTP status code: %s',
                    $url, 
                    $matches[1]
                )
            );
            //show_error('Please check logs', 500);
        }
    }
}

if (!function_exists('php_api_response_http_status')) {
    function php_api_response_http_status($http_response_header) {
        $http_status = false;

        if (preg_match(
            '/^HTTP\/1\.1\s(\d{3})\s.*$/',
            $http_response_header[0],
            $matches
        ) === 1) {
            $http_status = $matches[1];
        }

        return $http_status;
    }
}

if (!function_exists('php_api_debug')) {
    function php_api_debug($url, $params, $headers, $result, $http_response_header) {
        if (RB_API_CALL_DEBUG) {
            log_message(
                'error',
                sprintf(
                    'URL: %s',
                    $url
                )
            );
            log_message(
                'error',
                sprintf(
                    'HTTP REQUEST HEADERS: %s',
                    var_export($headers, true)
                )
            );
            log_message(
                'error',
                sprintf(
                    'HTTP REQUEST BODY: %s',
                    var_export($params, true)
                )
            );
            log_message(
                'error',
                sprintf(
                    'HTTP RESPONSE BODY: %s',
                    var_export($result, true)
                )
            );
            if (isset($http_response_header)) {
                $response_header = var_export($http_response_header, true);
            } else {
                $response_header = 'VALUE NOT SET';
            }
            
            log_message(
                'error',
                sprintf(
                    '$http_response_header: %s',
                    $response_header
                )
            );
        }
    }
}

if ( ! function_exists('php_api_call_upload_file'))
{
    function php_api_call_upload_file($file_path, $field = 'file', $content_type = 'application/octet-stream')
    {
        $multipart_boundary = 'guardian_upload'.time();

		$file_contents = file_get_contents($file_path);
		
		$content =  "--".$multipart_boundary."\r\n".
            "Content-Disposition: form-data; name=\"$field\"; filename=\"".basename($file_path)."\"\r\n".
            "Content-Type: $content_type\r\n\r\n".
            $file_contents."\r\n".
            "--".$multipart_boundary."--\r\n";

        $headers =
            "Content-Type: multipart/form-data; boundary=$multipart_boundary\r\n" .
            "Content-Length: ".strlen($content)."\r\n";

        return [$headers, $content];
    }
}
