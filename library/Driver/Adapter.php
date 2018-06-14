<?php namespace PHPBook\Storage\Driver;

abstract class Adapter {
    
    public abstract function get(String $file): ?String;

    public abstract function write(String $file, String $contents): Bool;

    public abstract function delete(String $file): Bool;

}