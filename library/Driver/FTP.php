<?php namespace PHPBook\Storage\Driver;

class FTP extends Adapter  {
    	
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

	public function connect() {

		$connection = @ftp_connect($this->getHost(), $this->getPort());
		
		if ($this->getUser()) {

			ftp_pasv($connection, TRUE);

			$auth = @ftp_login($connection, $this->getUser(), $this->getPassword());

		} else {

			$auth = true;

		};
		
		if ($connection) {

			if ($auth) {

				return $connection;

			};

		};

		return false;

	}

	public function disconnect($connection) {

		ftp_close($connection);

	}

	public function get(String $file): ?String {
		
		$location = str_replace(['\'', '/'], '/', $file);
	
		$connection = $this->connect();
	
		if ($connection) {

			ob_start();

			@ftp_get($connection, 'php://output', '/'. $location, FTP_BINARY);

			$data = ob_get_contents();

			ob_end_clean();
			
			$this->disconnect($connection);

			return $data;

		};

		$this->disconnect($connection);
	
		return null;
		
	}
	
	public function write(String $file, String $contents): Bool {

		$tmpFile = tmpfile();

		fwrite($tmpFile, $contents);

		rewind($tmpFile);

		$tmpMetaData = stream_get_meta_data($tmpFile);

		$remotefile = str_replace(['\'', '/'], '/', $file);
		
		$connection = $this->connect();
		
		if ($connection) {

			$subdiretories = explode('/', $remotefile);

			array_pop($subdiretories);

			$levels_directories = '/';
			
			ftp_chdir($connection, '/');
			
			foreach($subdiretories as $directoy) {
				$levels_directories .= $directoy . '/';
				if (!@ftp_chdir($connection, $directoy)) {
					@ftp_mkdir($connection, $directoy);
				};
			};

			if (@ftp_put($connection, '/' . $remotefile, $tmpMetaData['uri'], FTP_BINARY)) {

				$this->disconnect($connection);

				return true;

			};

		};

		$this->disconnect($connection);

		return false;

	}
	
	public function delete(String $file): Bool {

		$remotefile = str_replace(['\'', '/'], '/', $file);
		
		$connection = $this->connect();
		
		if ($connection) {

			if (@ftp_delete($connection, '/' . $remotefile)) {
			
				$this->disconnect($connection);

				return true;

			};

		};

		$this->disconnect($connection);

		return false;

    }

}