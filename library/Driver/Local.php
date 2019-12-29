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

	public function getFile(String $file): ?String {
	
		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory() . DIRECTORY_SEPARATOR . $file);
		
		if (is_file($location)) {

			return file_get_contents($location);

		};

		return null;

	}
	
	public function writeFile(String $file, String $contents): Bool {

		$path = explode('/', str_replace('\\', '/', $file));

		$filename = array_pop($path);

		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path));

		if (!is_dir($location)) {

			$dirsok = mkdir($location, 0777, true);

		} else {

			$dirsok = true;

		};

		if ($dirsok) {

			file_put_contents($location . DIRECTORY_SEPARATOR . $filename, $contents);

			return true;

		};

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

		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $file);

		if (file_exists($location)) {

			unlink($location);

			return true;

		};

		return false;
		
	}

	public function writeDirectory(String $directory): Bool {

		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $directory);
		
		if (!is_dir($location)) {

			return mkdir($location, 0777, true);

		} else {

			return true;

		};

		return false;
		
	}

	public function moveDirectory(String $directoryNow, String $directoryNew): Bool {

		$locationNow = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $directoryNow);

		$locationNew = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $directoryNew);

		if ((is_dir($locationNow)) and (!file_exists($locationNew))) {

			return rename($locationNow, $locationNew);

		};

		return false;
		
	}

	public function deleteDirectory(String $directory): Bool {
		
		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $directory);

		$deleteDirectory = function($directory) use(&$deleteDirectory) {

			if (is_dir($directory)) {

				$objects = scandir($directory); 
	
				foreach ($objects as $object) { 
					  if ($object != "." && $object != "..") { 
						if (is_dir($directory."/".$object)) {
							$deleteDirectory($directory."/".$object);
						} else {
							unlink($directory."/".$object); 
						};
					};	  
				};
	
				rmdir($directory); 
	
			};

		};

		$deleteDirectory($location);

		return true;

	}

	public function getDirectoryFiles(String $directory): ?Array {

		$filenames = [];

		$location = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->getDirectory()  . DIRECTORY_SEPARATOR . $directory);

		foreach(scandir($location) as $filename) {
		    
		    if (($filename != '..') and ($filename != '.')) {

				$filenames[] = $filename;

			};
		}

		return $filenames;

	}

}