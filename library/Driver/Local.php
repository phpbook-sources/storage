<?php namespace PHPBook\Storage\Driver;

class Local extends Adapter  {

    private $directory;

    public function getDirectory(): String {
    	return $this->directory;
    }

    public function setDirectory(String $directory): Local {
    	$this->directory = $directory;
    	return $this;
    }

	public function get(String $file): ?String {
	
		$location = str_replace(['/', '\''], DIRECTORY_SEPARATOR, $this->getDirectory() . DIRECTORY_SEPARATOR . $file);
		
		if (is_file($location)) {

			return file_get_contents($location);

		};

		return null;

	}
	
	public function write(String $file, String $contents): Bool {

		$path = explode('/', str_replace('\'', '/', $file));

		$filename = array_pop($path);

		$location = str_replace(['/', '\''], DIRECTORY_SEPARATOR, $this->getDirectory() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path));

		if (!is_dir($location)) {

			mkdir($location, 0777, true);

		};

		file_put_contents($location . DIRECTORY_SEPARATOR . $filename, $contents);

		return true;
		
	}
	
	public function delete(String $file): Bool {

		$location = str_replace(['/', '\''], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $file);

		if (file_exists($location)) {

			unlink($location);

			return true;

		};

		return false;
		
    }

}