<?php namespace PHPBook\Storage;

class Validation {

    public static function isInMimeTypes(String $contents, Array $mimeTypes): Bool {

        $finfo = new \finfo(FILEINFO_MIME);

        $fileMimeType = explode(';', $finfo->buffer($contents))[0];

        $fileType = explode('/', $fileMimeType)[0];

        foreach($mimeTypes as $validationMimeType) {
            
            if (($fileType == $validationMimeType) or ($fileMimeType == $validationMimeType)) {

                return true;

            };

        };

        return false;

    }

    public static function isInLimitsKilobytes(String $contents, Int $limits): Bool {

        return strlen($contents) / 1024 <= $limits;

    }

}