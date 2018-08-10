<?php namespace PHPBook\Storage\Driver;

class AWSS3 extends Adapter {
    	
    private $key;

    private $secret;

    private $region;

	private $bucket;
		
    public function getKey(): String {
    	return $this->key;
    }

    public function setKey(String $key): AWSS3 {
    	$this->key = $key;
    	return $this;
	}

	public function getSecret(): String {
    	return $this->secret;
    }

    public function setSecret(String $secret): AWSS3 {
    	$this->secret = $secret;
    	return $this;
	}
	
	public function getRegion(): String {
    	return $this->region;
    }

    public function setRegion(String $region): AWSS3 {
    	$this->region = $region;
    	return $this;
	}

	public function getBucket(): String {
		return $this->bucket;
	}

	public function setBucket(String $bucket): AWSS3 {
		$this->bucket = $bucket;
		return $this;
	}

	private function resource($file, $contents, $action, $query = null, $headers = []) {
		
		$host_name = $this->getBucket() . '.s3.amazonaws.com';

		if ($action == 'PUT') {

			$content = $contents;

		} else {

			$content = '';

		};

		$content_acl = 'authenticated-read';

		$content_type = '';

		$content_title = $file;

		$aws_service_name = 's3';

		$timestamp = gmdate('Ymd\THis\Z');
		$date = gmdate('Ymd');

		$request_headers = array();
		$request_headers['Content-Length'] = strlen($content);
		$request_headers['Content-Type'] = $content_type;
		$request_headers['Date'] = $timestamp;
		$request_headers['Host'] = $host_name;
		$request_headers['x-amz-acl'] = $content_acl;
		$request_headers['x-amz-content-sha256'] = hash('sha256', $content);
		
		foreach($headers as $header_key => $header_value) {
			$request_headers[$header_key] = $header_value;
		};

		ksort($request_headers);

		$canonical_headers = [];
		foreach($request_headers as $key => $value) {
			$canonical_headers[] = strtolower($key) . ":" . $value;
		}
		$canonical_headers = implode("\n", $canonical_headers);

		$signed_headers = [];
		foreach($request_headers as $key => $value) {
			$signed_headers[] = strtolower($key);
		}
		$signed_headers = implode(";", $signed_headers);

		if ($query) {

			$query_strings = explode('&', $query);

			$query_strings_parse = [];

			foreach($query_strings as $query_string) {

				list($query_string_name, $query_string_value) = explode('=', $query_string);

				$query_strings_parse[] = urlencode($query_string_name) . '=' . urlencode($query_string_value);
			};

			$query = implode('&', $query_strings_parse);

		};

		$canonical_request = [];
		$canonical_request[] = $action;
		$canonical_request[] = "/" . $content_title;
		$canonical_request[] = ($query ? ($query) : '');
		$canonical_request[] = $canonical_headers;
		$canonical_request[] = "";
		$canonical_request[] = $signed_headers;
		$canonical_request[] = hash('sha256', $content);
		$canonical_request = implode("\n", $canonical_request);
		$hashed_canonical_request = hash('sha256', $canonical_request);

		$scope = [];
		$scope[] = $date;
		$scope[] = $this->getRegion();
		$scope[] = $aws_service_name;
		$scope[] = "aws4_request";

		$string_to_sign = [];
		$string_to_sign[] = "AWS4-HMAC-SHA256"; 
		$string_to_sign[] = $timestamp; 
		$string_to_sign[] = implode('/', $scope);
		$string_to_sign[] = $hashed_canonical_request;
		$string_to_sign = implode("\n", $string_to_sign);	

		$kSecret = 'AWS4' . $this->getSecret();
		$kDate = hash_hmac('sha256', $date, $kSecret, true);
		$kRegion = hash_hmac('sha256', $this->getRegion(), $kDate, true);
		$kService = hash_hmac('sha256', $aws_service_name, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		$signature = hash_hmac('sha256', $string_to_sign, $kSigning);

		$authorization = [
			'Credential=' . $this->getKey() . '/' . implode('/', $scope),
			'SignedHeaders=' . $signed_headers,
			'Signature=' . $signature,
		];
		$authorization = 'AWS4-HMAC-SHA256' . ' ' . implode( ',', $authorization);

		$curl_headers = [ 'Authorization: ' . $authorization ];

		foreach($request_headers as $key => $value) {
			$curl_headers[] = $key . ": " . $value;
		};

		$url = 'https://' . $host_name . ($content_title ? '/' . $content_title : '');

		$ch = curl_init($url . ($query ? '?'.$query : ''));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);

		if ($action == 'PUT') {
			if ($content) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			};
		};

		$response = curl_exec($ch);

		$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return [$response, $httpstatus];

	}

    public function getFile(String $file): ?String {

		$location = str_replace(['\\', '/'], '/', $file);

		list($response, $httpstatus) = $this->resource($location, null, 'GET');

		if (($httpstatus >= 200) and ($httpstatus <= 299)) {

			return $response;

		};

		return null;

	}
	
	public function writeFile(String $file, String $contents): Bool {
		
		$location = str_replace(['\\', '/'], '/', $file);
		
		list($response, $httpstatus) = $this->resource($location, $contents, 'PUT');

		if (($httpstatus >= 200) and ($httpstatus <= 299)) {

			return true;

		};

		return false;
		
	}
	
	public function moveFile(String $fileNow, String $fileNew): Bool {

		$locationNow = str_replace(['\\', '/'], '/', $fileNow);

		$locationNew = str_replace(['\\', '/'], '/', $fileNew);

		$get = $this->getFile($locationNow);

		if ($get) {

			$write = $this->writeFile($locationNew, $get);

			if ($write) {

				$delete = $this->deleteFile($locationNow);

				if ($delete) {

					return true;

				} else {

					$this->deleteFile($locationNew);

				};

			};
			
		};	

		return false;

	}

	public function deleteFile(String $file): Bool {
		
		$location = str_replace(['\\', '/'], '/', $file);

		list($response, $httpstatus) = $this->resource($location, null, 'DELETE');

		if (($httpstatus >= 200) and ($httpstatus <= 299)) {

			return true;

		};

		return false;

	}

	public function writeDirectory(String $directory): Bool {

		$location = $directory . (substr($directory, -1) != '/' ? '/' : '');

		$location = str_replace(['\\', '/'], '/', $location);

		list($response, $httpstatus) = $this->resource($location, '', 'PUT');

		if (($httpstatus >= 200) and ($httpstatus <= 299)) {

			return true;

		};

		return false;
		
	}

	public function moveDirectory(String $directoryNow, String $directoryNew): Bool {

		$locationNow = $directoryNow . (substr($directoryNow, -1) != '/' ? '/' : '');

		$locationNow = str_replace(['\\', '/'], '/', $locationNow);

		$locationNew = $directoryNew . (substr($directoryNew, -1) != '/' ? '/' : '');

		$locationNew = str_replace(['\\', '/'], '/', $locationNew);

		while(true) {

			list($response, $httpstatus) = $this->resource('', null, 'GET', 'prefix=' . $locationNow);
			
			if (($httpstatus >= 200) and ($httpstatus <= 299)) {

				$files = json_decode(json_encode(simplexml_load_string($response)), true);
	
				$rewrites = [];
	
				if (array_key_exists('Contents', $files)) {

					if (array_key_exists('Key', $files['Contents'])) {
	
						if ($files['Contents']['Key'] != $files['Prefix']) {
		
							$rewrites[] = $files['Contents']['Key'];
						};
		
					} else {
		
						foreach($files['Contents'] as $content) {
		
							$rewrites[] = $content['Key'];
			
						};
		
					};

				};
	
				if (count($rewrites) == 0) {
	
					break;
	
				};
	
				foreach($rewrites as $rewrite) {
	
					if (substr($rewrite, 0, strlen($locationNow)) == $locationNow) {
						$filename = substr($rewrite, strlen($locationNow));
					};

					list($copyContents, $copyHttpStatus) = $this->resource($locationNew . $filename, '', 'PUT', '', ['x-amz-copy-source' => $this->getBucket() . '/' . $rewrite]);

					if (($copyHttpStatus >= 200) and ($copyHttpStatus <= 299)) {

						$this->deleteFile($rewrite);

					};
					
				};
	
			} else {

				break;

			};
	
		};
		
		return true;
	}

	public function deleteDirectory(String $directory): Bool {
		
		$location = $directory . (substr($directory, -1) != '/' ? '/' : '');

		$location = str_replace(['\\', '/'], '/', $location);

		while(true) {

			list($response, $httpstatus) = $this->resource('', null, 'GET', 'prefix=' . $location);
			
			if (($httpstatus >= 200) and ($httpstatus <= 299)) {

				$files = json_decode(json_encode(simplexml_load_string($response)), true);
	
				$deletes = [];
	
				if (array_key_exists('Contents', $files)) {

					if (array_key_exists('Key', $files['Contents'])) {
	
						if ($files['Contents']['Key'] != $files['Prefix']) {
		
							$deletes[] = $files['Contents']['Key'];
						};
		
					} else {
		
						foreach($files['Contents'] as $content) {
		
							$deletes[] = $content['Key'];
			
						};
		
					};

				};
	
				if (count($deletes) == 0) {
	
					break;
	
				};
	
				foreach($deletes as $delete) {
	
					$this->deleteFile($delete);
					
				};
	
			} else {

				break;

			};
	
		};

		return true;

	}

}