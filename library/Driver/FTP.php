<?php namespace PHPBook\Storage\Driver;

class FTP extends Adapter  {
    	
    private $connection;

    private $host;

    private $port;

    private $directory;

    private $user;

    private $password;

    public function getHost(): String {
    	return $this->host;
    }

    public function setHost(String $host): FTP {
    	$this->host = $host;
    	return $this;
    }

    public function getPort(): Int {
    	return $this->port;
    }

    public function setPort(Int $port): FTP {
    	$this->port = $port;
    	return $this;
    }

    public function getDirectory(): String {
    	return $this->directory;
    }

    public function setDirectory(String $directory): FTP {
    	$this->directory = $directory;
    	return $this;
    }

	public function getUser(): ?String {
		return $this->user;
	}

	public function setUser(String $user): FTP {
		$this->user = $user;
		return $this;
	}

	public function getPassword(): ?String {
		return $this->password;
	}

	public function setPassword(String $password): FTP {
		$this->password = $password;
		return $this;
	}

	public function connection() {

		if ($this->connection) {

			return $this->connection;

		} else {

			$connection = @ftp_connect($this->getHost(), $this->getPort());
		
			if ($this->getUser()) {

				$auth = @ftp_login($connection, $this->getUser(), $this->getPassword());

			} else {

				$auth = true;

			};

			$this->connection = false;
			
			if ($connection) {

				if ($auth) {

					ftp_pasv($connection, TRUE);

					$this->connection = $connection;

				} else {

					ftp_close($connection);

				};

			};

			if (!$this->connection) {

				throw new \Exception('cannot connect the ftp server ' . $this->getHost());

			};

			return $this->connection;

		};		

	}

	public function disconnect() {

		ftp_close($this->connection);

		$this->connection = false;

	}

	public function getFile(String $file): ?String {
		
		$location = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $file);
	
		$connection = $this->connection();

		ftp_chdir($connection, '/');
	
		if ($connection) {

			ob_start();

			@ftp_get($connection, 'php://output', '/'. $location, FTP_BINARY);

			$data = ob_get_contents();

			ob_end_clean();
			
			$this->disconnect();

			return $data;

		};

		$this->disconnect();
	
		return null;
		
	}
	
	public function writeFile(String $file, String $contents): Bool {

		$tmpFile = tmpfile();

		fwrite($tmpFile, $contents);

		rewind($tmpFile);

		$tmpMetaData = stream_get_meta_data($tmpFile);

		$remotefile = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $file);
		
		$connection = $this->connection();

		ftp_chdir($connection, '/');
		
		if ($connection) {
			
			$subdiretories = explode('/', $remotefile);

			$file_name = array_pop($subdiretories);

			$levels_directories = '/';
			
			ftp_chdir($connection, '/');
			
			foreach($subdiretories as $directoy) {
				$levels_directories .= $directoy . '/';
				if (!@ftp_chdir($connection, $directoy)) {
					if (($directoy) and (strlen($directoy))) {
						ftp_mkdir($connection, $directoy);
						ftp_chdir($connection, $directoy);
					};
				};
			};

			ftp_chdir($connection, '/');

			if (@ftp_put($connection, $remotefile, $tmpMetaData['uri'], FTP_BINARY)) {
				
				$this->disconnect();

				return true;

			};

		};

		$this->disconnect();

		return false;

	}
	
	public function moveFile(String $fileNow, String $fileNew): Bool {

		$get = $this->getFile($fileNow);

		if ($get) {

			$write = $this->writeFile($fileNew, $get);

			if ($write) {

				$delete = $this->deleteFile($fileNow);

				if ($delete) {

					return true;

				} else {

					$this->deleteFile($fileNew);

				};

			};
			
		};	

		return false;

	}

	public function deleteFile(String $file): Bool {

		$remotefile = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $file);
		
		$connection = $this->connection();

		ftp_chdir($connection, '/');
		
		if ($connection) {

			if (@ftp_delete($connection, '/' . $remotefile)) {
			
				$this->disconnect();

				return true;

			};

		};

		$this->disconnect();

		return false;

	}
	
	public function writeDirectory(String $directory): Bool {

		$connection = $this->connection();

		ftp_chdir($connection, '/');
		
		$subdiretories = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $directory);

		if ($connection) {

			$subdiretories = explode('/', $subdiretories);

			$levels_directories = '/';
			
			ftp_chdir($connection, '/');
			
			foreach($subdiretories as $directoy) {
				$levels_directories .= $directoy . '/';
				if (!@ftp_chdir($connection, $directoy)) {
					$createdir = @ftp_mkdir($connection, $directoy);
					if ($createdir === false) {
						$this->disconnect();
						return false;
					};
				};
			};

			$this->disconnect();

			return true;

		};

		$this->disconnect();

		return false;
		
	}

	public function moveDirectory(String $directoryNow, String $directoryNew): Bool {
		
		$subdirectoryNow = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $directoryNow);

		$subdirectoryNew = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $directoryNew);

		$connection = $this->connection();

		ftp_chdir($connection, '/');

		if ($connection) {

			if (@ftp_rename($connection, $subdirectoryNow, $subdirectoryNew)) {

				$this->disconnect();

				return true;

			} else {

				$this->disconnect();

				return false;
				
			};

		};

		$this->disconnect();

		return false;
		
	}

	public function deleteDirectory(String $directory): Bool {

		$connection = $this->connection();

		ftp_chdir($connection, '/');

		$subdiretories = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $directory);

		$recursiveDeleteDirectory = function($directory) use ($connection, &$recursiveDeleteDirectory) {

			if ($connection) {

				if (@ftp_delete($connection, $directory) === false) {

				    if ($children = @ftp_nlist($connection, $directory)) {
				      	foreach ($children as $subitem) {
							if (($subitem != $directory . '/..') and ($subitem != $directory . '/.')) {
								$recursiveDeleteDirectory($subitem);
							};
				      	};
				    };

				    if (@ftp_rmdir($connection, $directory)) {

				    	return true;

				    };

				} else {

					return true;

				};

			};

			return false;

		};

		$returns = $recursiveDeleteDirectory($subdiretories);
		
		$this->disconnect();

		return $returns;

	}

	public function getDirectoryFiles(String $directory): ?Array {
		
		$connection = $this->connection();

		$subdiretories = str_replace(['\\', '/'], '/', $this->getDirectory() . DIRECTORY_SEPARATOR . $directory);

		$files = ftp_nlist($connection, $subdiretories);

		$filenames = [];

		foreach($files as $filepath) {

			$filename = basename($filepath);

			if (($filename != '..') and ($filename != '.')) {

				$filenames[] = $filename;

			};

		};

		$this->disconnect();

		return $filenames;

	}

}