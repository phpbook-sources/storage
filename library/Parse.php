<?php namespace PHPBook\Storage;

abstract class Parse {
    
  public static function getByJson(String $contents) {
  
    return json_decode($contents);

  }

  public static function getByXml(String $contents) {
  
    return simplexml_load_string($contents);

  }
    
}