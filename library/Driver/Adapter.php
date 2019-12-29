<?php namespace PHPBook\Storage\Driver;

abstract class Adapter {
    
    public abstract function getFile(String $file): ?String;

    public abstract function writeFile(String $file, String $contents): Bool;

    public abstract function moveFile(String $fileNow, String $fileNew): Bool;

    public abstract function deleteFile(String $file): Bool;

    public abstract function writeDirectory(String $directory): Bool;

    public abstract function moveDirectory(String $directoryNow, String $directoryNew): Bool;

    public abstract function deleteDirectory(String $directory): Bool;

    public abstract function getDirectoryFiles(String $directory): ?Array;
}