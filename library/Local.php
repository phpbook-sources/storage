<?php namespace PHPBook\Storage;

class Local {
    
	public function getContents(String $file): ?String {
		
		if (is_file($file)) {
			
			return file_get_contents($file);

		};

		return null;

    }
    
}