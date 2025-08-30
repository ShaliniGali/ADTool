<?php

namespace Aws\S3;

class S3Client {

	public function deleteObject($param) {
		if (!isset($param['Bucket'], $param['Key'])) throw new \ErrorException;

		return true;
	}

	public function putObject($param) {
		if (is_null($param['Bucket'])) throw new \Exception;
		$param['Body'] = 'test';
		return $param;
	}
	public function getObject($param) {
		if (is_null($param['Bucket'])) throw new \Exception;
		$param['Body'] = new class {
			public function getContents() {
				return 'file_contents';
			}
			public function __toString() {
				return 'file_contents';
			}
		};
		return $param;
	}
}
