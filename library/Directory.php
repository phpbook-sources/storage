<?php namespace PHPBook\Storage;

class Directory {
    
    private $connectionCode;

    private $directory;

    public function setConnectionCode(?String $connectionCode): Directory {
    	$this->connectionCode = $connectionCode;
    	return $this;
    }

    public function getConnectionCode(): ?String {
    	return $this->connectionCode;
    }

    public function setDirectory(String $directory): Directory {
    	$this->directory = $directory;
    	return $this;
    }

    public function getDirectory(): ?String {
    	return $this->directory;
	}

    public function write(): Bool {

    	$connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->writeDirectory($this->getDirectory());
                
            } catch(\Exception $e) {

                if ($connection->getExceptionCatcher()) {

                    $connection->getExceptionCatcher()($e->getMessage());
                    
                };

                return false;

            };
    	};

    	return false;

    }

    public function move(String $directoryNew): Bool {
        
        $connection = \PHPBook\Storage\Configuration\Storage::getConnection($this->getConnectionCode());

    	if (($connection) and ($connection->getDriver())) {

			try {
                
                return $connection->getDriver()->moveDirectory($this->getDirectory(), $directoryNew);
                
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
                
                return $connection->getDriver()->deleteDirectory($this->getDirectory());
                
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
