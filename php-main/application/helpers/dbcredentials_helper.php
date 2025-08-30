<?php

if (!function_exists('loadDBCredentials')) {
    function loadDBCredentials($checkCache = true) {
        $requiredConstants = array('RHOMBUS_DATABASES', 'RHOMBUS_BASE_URL', 'RHOMBUS_DB_API_KEY');

        foreach($requiredConstants as $constant) {
            if (!defined($constant)) {
                echo 'Error: ' . $constant . ' must be defined before loadDBCredentials is called.';
                exit;
            }
        }

        if ($checkCache && requiresDBCredentialsCacheUpdate()) {
            $isCached = writeDBCredentialsCache();
            if ($isCached === false) {
                return validateDBAPIResponse(getDBCredentials()['APIData']);
            }
        }
        return validateDBAPIResponse(readDBCredentialsCache()['APIData']);
    }

    if (!function_exists('validateDBAPIResponse')) {
        function validateDBAPIResponse($response) {
            if ($response[0] === 'Access forbidden') {
                echo 'Invalid api request. Contact it@rhombuspower.com';
                exit;
            }
            return $response;
        }
    }

    if (!function_exists('getCacheFilepath')) {
        function getCacheFilepath() {
            return sys_get_temp_dir() . '/' . hash('sha256', RHOMBUS_BASE_URL);
        }
    }

    if (!function_exists('readDBCredentialsCache')) {
        function readDBCredentialsCache() {
            $cache = file_get_contents(getCacheFilepath());
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
    
    if (!function_exists('writeDBCredentialsCache')) {
        function writeDBCredentialsCache() {
            $tmpfile = fopen(getCacheFilepath(), 'w');
            if ($tmpfile === false) {
                return false;
            }
            $isCached = fwrite($tmpfile, base64_encode(json_encode(getDBCredentials())));
            fclose($tmpfile);
            return $isCached;
        }
    }

    if (!function_exists('requiresDBCredentialsCacheUpdate')) {
        function requiresDBCredentialsCacheUpdate() {
            if (!file_exists(getCacheFilepath())) {
                return true;
            } else {
                $cacheLifetime = 5 * 60; // time in seconds
                $cache = readDBCredentialsCache();
                if ($cache === false) {
                    return $cache;
                }
                return 
                    ($cache['requestedDatabases'] !== RHOMBUS_DATABASES) ||
                    ($cache['lastUpdated'] + $cacheLifetime < time()) ||
                    ($cache['APIData'][0] === 'Access forbidden');
            }
        }
    }
}

if (!function_exists('getDBCredentials')) {
    function getDBCredentials() {
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, "https://app.guardian.rhombus.cloud/Api/db_info");
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, array(
            "server_ip" => $_SERVER['SERVER_NAME'],
            "key" => RHOMBUS_DB_API_KEY,
            "db_name" => RHOMBUS_DATABASES
        ));
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        do{
            $curl_result = json_decode(curl_exec($curlSession), true);
            sleep(1);
        } while($curl_result == null);
        return array(
            'requestedDatabases' => RHOMBUS_DATABASES,
            'APIData' => $curl_result,
            'lastUpdated' => time()
        );
    }
}

if (!function_exists('getSSODBCredentials')) {
    function getSSODBCredentials($db_key) {
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, "https://app.guardian.rhombus.cloud/Api/db_info");
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, array(
            "server_ip" => $_SERVER['SERVER_NAME'],
            "key" => RHOMBUS_DB_API_KEY,
            "db_name" => $db_key
        ));
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        do{
            $curl_result = json_decode(curl_exec($curlSession), true);
            sleep(1);
        } while($curl_result == null);
        return array(
            'requestedDatabases' => $db_key,
            'APIData' => $curl_result,
            'lastUpdated' => time()
        );
    }
}
