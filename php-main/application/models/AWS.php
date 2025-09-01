<?php

#[AllowDynamicProperties]
class  AWS extends CI_Model {
	public function __construct() {
		if (!P1_FLAG && ENVIRONMENT === 'development') {
			require_once(FCPATH . 'vendor/autoload.php');
		}
	}

	public function getClient($region, $accessKey = null, $secretKey = null) {
		$config = $this->getConfig($region, $accessKey, $secretKey);

		return new \Aws\S3\S3Client($config);
	}

	public function getConfig($region, $accessKey = null, $secretKey = null) {
		$config = [
			'version' => 'latest',
			'region'  => $region,
			'http' => [
				'verify' => false
			]
		];

		if (isset($accessKey, $secretKey)) {
			$config['credentials'] = [
				'key'    => $accessKey,
				'secret' => $secretKey,
			];
		}

		if (defined('S3_ENDPOINT') && strlen(S3_ENDPOINT) > 0) {
			$config['endpoint'] = S3_ENDPOINT;
			$config['use_path_style_endpoint'] = true;
		}

		return $config;
	}

	/**
	 * Delete file from S3 bucket
	 * 
	 * @param string $file_ful_path
	 * @param string $file_name
	 * @param string $bucket_name
	 * 
	 * @return false|string the objectUrl in s3
	 */
	public function deleteS3File($fileName, $bucketName, $region, $accessKey = null, $secretKey = null) {
		$s3 = $this->getClient($region, $accessKey, $secretKey);

		try {
			$result = $s3->deleteObject(array(
				'Bucket'     => $bucketName,
				'Key'        => $fileName
			));
		} catch (Exception $e) {
			throw $e;
		}

		$file = $this->getS3File($fileName, $bucketName, $region, $accessKey, $secretKey)['body'] ?? true;

		return ($file === true ? true : false);
	}

	/**
	 * Saves file to S3 bucket
	 * 
	 * @param string $file_ful_path
	 * @param string $file_name
	 * @param string $bucket_name
	 * 
	 * @return false|string the objectUrl in s3
	 */
	public function saveS3File($fileFullPath, $fileName, $bucketName, $region, $accessKey = null, $secretKey = null) {
		$s3 = $this->getClient($region, $accessKey, $secretKey);

		// Upload an object by streaming the contents of a file
		// $pathToFile should be absolute path to a file on disk
		try {
			$result = $s3->putObject(array(
				'Bucket'     => $bucketName,
				'Key'        => $fileName,
				'SourceFile' => $fileFullPath
			));
		} catch (Exception $e) {
			throw $e;
		}

		$url = $result['ObjectURL'] ?? false;

		// just return the object key with no bucket in the key.
		if ($url !== false) {
			$url = parse_url($url, PHP_URL_PATH);
			if ($url !== false && strpos($url, '/') === 0) {
				$url = substr($url, 1);
			}

			$temp = explode('/', $url);
			if ($temp[0] === $bucketName) {
				array_shift($temp);
				$url = implode('/', $temp);
			}
		}

		return $url;
	}

	public function getS3File($path, $bucketName, $region, $accessKey = null, $secretKey = null) {
		if ($this->endsWith($path, '.html')) {
			// echo "in 1";
			$extension = 'html';
		}
		if ($this->endsWith($path, '.htm')) {
			// echo "in 2";
			$extension = 'htm';
		}
		if ($this->endsWith($path, '.pdf')) {
			// echo "in 3";
			$extension = 'pdf';
		}

		$s3 = $this->getClient($region, $accessKey, $secretKey);

		try {
			$result = $s3->getObject([
				'Bucket' => $bucketName,
				'Key'    => $path
			]);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		return array("data" => $result['Body']->getContents(), "extension" => $extension);
	}

	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if (!$length) {
			return true;
		}
		return substr($haystack, -$length) === $needle;
	}

	public function checkS3PathExists($path, $bucketName, $region, $accessKey = null, $secretKey = null) {
		$s3 = $this->getClient($region, $accessKey, $secretKey);

		try {
			$result = $s3->listObjects([
				'Bucket' => $bucketName,
				'Prefix' => $path,
			]);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		return isset($result['Contents']);
	}

	function downloadS3File($pathStr) {

		$fileContents = $this->getS3File($pathStr, GUARDIAN_DEEPDIVE_BUCKET, GUARDIAN_DEEPDIVE_REGION, GUARDIAN_ACCESS_KEY, GUARDIAN_SECRET_KEY);
		$filename = substr(strrchr($pathStr, '/'), 1);

		if (gettype($fileContents) == 'string') {
			echo "Access denied. No bucket permission.";
			exit;
		}

		// Generate the server headers
		if ($fileContents['extension'] !== 'pdf') {
			header('Content-Type: text/html');
		} else {
			header('Content-Type: application/pdf');
		}

		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Expires: 0');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($fileContents['data']));
		header('Cache-Control: private, no-transform, no-store, must-revalidate');

		echo $fileContents['data'];
		exit;
	}
}
