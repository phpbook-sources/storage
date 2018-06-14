<?php namespace PHPBook\Storage\Configuration;

abstract class Storage {
    
    private static $connection = [];

    private static $default;

    public static function setConnection(String $alias, Connection $connection) {
        Static::$connection[$alias] = $connection;
    }

    public static function getConnection(?String $alias): ?Connection {
        return ($alias and array_key_exists($alias, Static::$connection)) ? Static::$connection[$alias] : (Static::$default ? Static::$connection[Static::$default] : Null);
    }

    public static function getConnections(): Array {
        return Static::$connection;
    }

    public static function setDefault(String $default) {
    	Static::$default = $default;
    }

    public static function getDefault(): ?String {
    	return Static::$default;
    }

}
