<?php

/**
 * Log in a user to vault
 * By default uses constants: VAULT_USERNAME and VAULT_PASSWORD for login
 * 
 * returns the client_token
 */


if ( !function_exists('vault_login')) {
    function vault_login($username=VAULT_USERNAME, $password=VAULT_PASSWORD, $vault_url=VAULT_URL, $request_type = 'POST') {

        $url = $vault_url . '/v1/auth/userpass/login/' . $username;

        $api_params = array(
            'password' => $password
        );

        $headers = [
			'accept: application/json',
			'Content-Type:application/json'
        ];
        $api_response =  php_api_call($request_type, $headers, json_encode($api_params), $url);

        $client_token = json_decode($api_response, true)['auth']['client_token'];

        return $client_token;
    }
}

/**
 * Retrieves the user credentials of user in vault using the username and the client token
 * By default uses the constant VAULT_USERNAME for username
 * Requires client token.
 * 
 * returns user credentials
 */
if ( !function_exists('user_creds')) {
    function user_creds($client_token, $api_params=[], $request_type = 'GET', $username=VAULT_USERNAME, $vault_url=VAULT_URL) {
        $url = $vault_url . '/v1/secret/data/users/' . $username;

        $headers = [
			'accept: application/json',
			'Content-Type:application/json',
            'X-Vault-Token:'.$client_token
        ];

        $api_response =  php_api_call($request_type, $headers, json_encode($api_params), $url);

        return json_decode($api_response, true)['data'];
    }
}

/**
 * Retrieves the database credentials of a user in vault.
 * Requires client token, db_alias and db_user
 * 
 * returns db credentials
 */
if ( !function_exists('get_vault_db_creds')) {
    function get_vault_db_creds($client_token, $db_alias=VAULT_DB_ALIAS, $db_user=VAULT_DB_USER, $api_params=[], $request_type='GET', $vault_url=VAULT_URL) {
		try {
			$payload = user_creds($client_token);
			$result = [];

			for ($i = 0; $i < count($db_alias); $i++) {
				$creds = $payload['data']['databases'][$db_alias[$i]];
				$role = $creds['users'][$db_user[$i]];
				$url = $vault_url . '/v1/database/creds/' . $role['vault_role'];

				$headers = [
					'accept: application/json',
					'Content-Type:application/json',
					'X-Vault-Token:' . $client_token
				];

                do {
					$api_response =  php_api_call($request_type, $headers, json_encode($api_params), $url);
					$db_role_payload = json_decode($api_response, true);
					sleep(1);
				} while (
					$db_role_payload['data']['username'] == '' ||
					$db_role_payload['data']['password'] == '' ||
					$db_role_payload['data']['username'] == NULL ||
					$db_role_payload['data']['password'] == NULL
				);

				$result[] = [
					'host_name' => $creds['host_url'],
					'user_name' => $db_role_payload['data']['username'],
					'password' => $db_role_payload['data']['password'],
					'dbname' => $role['dbname'],
					'lease_duration' => $db_role_payload['lease_duration'],
				];
			}

			return array(
				'vault_db_alias' => VAULT_DB_ALIAS,
				'vault_db_user' => VAULT_DB_USER,
				'APIData' => $result,
				'lastUpdated' => time()
			);

		} catch(Exception $e){
			echo 'Unable to get db creds from vault.';
		}
    }
}


if (!function_exists('load_vault_db_credentials')) {
    function load_vault_db_credentials($checkCache = true) {
        $requiredConstants = array('VAULT_USERNAME', 'VAULT_URL', 'VAULT_PASSWORD', 'VAULT_DB_ALIAS', 'VAULT_DB_USER');

        foreach($requiredConstants as $constant) {
            if (!defined($constant)) {
                echo 'Error: ' . $constant . ' must be defined before load_vault_db_credentials is called.';
                exit;
            }
        }

		if ($checkCache && requires_vault_cache_update()) {
            $isCached = write_vault_db_cache();
            if ($isCached === false) {
				$client_token = vault_login();
            	return get_vault_db_creds($client_token, VAULT_DB_ALIAS, VAULT_DB_USER)['APIData'];
            }
        }
        return read_vault_creds_cache()['APIData'];
    }

    if (!function_exists('get_vault_cache_filepath')) {
        function get_vault_cache_filepath() {
            return sys_get_temp_dir() . '/' . hash('sha256', RHOMBUS_BASE_URL.'vault_creds');
        }
    }

    if (!function_exists('read_vault_creds_cache')) {
        function read_vault_creds_cache() {
            $cache = file_get_contents(get_vault_cache_filepath());
            if ($cache === false) {
                return false;
            }
            $base64decodedCache = base64_decode($cache);
            if ($base64decodedCache !== false) {
                return json_decode($base64decodedCache, TRUE);
            }
            return false;
        }
    }
    
    if (!function_exists('write_vault_db_cache')) {
        function write_vault_db_cache() {
            $tmpfile = fopen(get_vault_cache_filepath(), 'w');
            if ($tmpfile === false) {
                return false;
            }
			$client_token = vault_login();
            $vault_db_creds = get_vault_db_creds($client_token, VAULT_DB_ALIAS, VAULT_DB_USER);

            $isCached = fwrite($tmpfile, base64_encode(json_encode($vault_db_creds)));
            fclose($tmpfile);
            return $isCached;
        }
    }

    if (!function_exists('requires_vault_cache_update')) {
        function requires_vault_cache_update() {
            if (!file_exists(get_vault_cache_filepath())) {
                return true;
            } else {
                $cacheLifetime = 5 * 60; // time in seconds
                $cache = read_vault_creds_cache();
                if ($cache === false) {
                    return $cache;
                }

                return 
                    ($cache['vault_db_alias'] !== VAULT_DB_ALIAS) ||
					($cache['vault_db_user'] !== VAULT_DB_USER) ||
                    ($cache['lastUpdated'] + $cacheLifetime < time()) ||
                    ($cache['APIData'][0] === 'Access forbidden');
            }
        }
    }
}