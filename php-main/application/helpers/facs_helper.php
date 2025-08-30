<?php

if ( !function_exists('facs_api_call')) {
    function facs_api_call($service, $api_params = null, $login = true, $request_type = 'POST') {
        static $login_token = null;

        $url = sprintf("%s/facs/%s", RB_FACS_URL, $service);
        
		$headers = [
			'accept: application/json',
			'Content-Type:application/json'
        ];

        if ($login === true) {
            if ($login_token === null) {
                $login_token = login_facs_api_call();
            }
            
            $headers[] = sprintf(
                'Authorization: Bearer %s',
                $login_token
            );
        }

        $api_response =  php_api_call($request_type, $headers, json_encode($api_params), $url);

        return $api_response;
    }
}

if (!function_exists('login_facs_api_call'))
{
    function login_facs_api_call()
    {
        $api_params = ['key' => RB_FACS_API_KEY];

        $login_token = json_decode(facs_api_call('login', $api_params, false), true)['token'] ?? null;
        
        if (strlen(trim($login_token)) === 0) {
            log_message(
                'error',
                sprintf(
                    'Unable to obtain the login token. $api_params: %s',
                    var_export($api_params, true)
                )
            );
        }

        return $login_token;
    }
}

if (!function_exists('hasaccess_facs_api_call')) {
    function hasaccess_facs_api_call($app_name, $subapp_name, $feature_name, $user_roles)
    {
        $api_params = [
            "app_name" => $app_name,
            "subapp_name" => $subapp_name,
            "feature_name" => $feature_name,
            "user_roles" => [$user_roles],
            "overall" => 'false'
        ];
        
        return facs_api_call('hasaccess', $api_params, true);
    }
}