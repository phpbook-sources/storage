<?php namespace PHPBook\Storage;

class Storage {
    
    private $connectionCode;

    private $file;

    public function setConnectionCode(?String $connectionCode): Storage {
    	$this->connectionCode = $connectionCode;
    	return $this;
    }

    public function getConnectionCode(): ?String {
    	return $this->connectionCode;
    }

    public function setFile(String $file): Storage {
    	$this->file = $file;
    	return $this;
    }

    public function getFile(): ?String {
    	return $this->file;
	}

    public function get(): ?String {

    	$connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->getFile($this->getFile());
                
            } catch(\Exception $e) {

                if ($connection->getExceptionCatcher()) {

                    $connection->getExceptionCatcher()($e->getMessage());
                    
                };

                return null;

            };

    	};

    	return null;

    }

    public function write($contents): Bool {

    	$connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->writeFile($this->getFile(), $contents);
                
            } catch(\Exception $e) {

                if ($connection->getExceptionCatcher()) {

                    $connection->getExceptionCatcher()($e->getMessage());
                    
                };

                return false;

            };
    	};

    	return false;

    }

    public function move(String $fileNew): Bool {
        
        $connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->moveFile($this->getFile(), $fileNew);
                
            } catch(\Exception $e) {

                if ($connection->getExceptionCatcher()) {

                    $connection->getExceptionCatcher()($e->getMessage());
                    
                };

                return false;

            };
    	};

        return false;
        
    }

    public function delete(): Bool {

    	$connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->deleteFile($this->getFile());
                
            } catch(\Exception $e) {

                if ($connection->getExceptionCatcher()) {

                    $connection->getExceptionCatcher()($e->getMessage());
                    
                };

                return false;

			};
			
    	};

    	return false;

    }
  
}
