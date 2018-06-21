<?php namespace PHPBook\Storage;

abstract class Local {
    
	public static function getContents(String $file): ?String {
		
		if (is_file($file)) {
			
			return file_get_contents($file);

		};

		return null;

    }
    
}