<?php namespace PHPBook\Storage;

class FileStage {
    
    public static $Stage_Clear = '@ClearFile';

    public static $Stage_Keep = '@KeepFile';
    
    private $statement;

    private $fileName;

    private $filePath;

    private $connectionCode;
    
    public function __construct(String $statement, String $fileName, String $filePath, ?String $connectionCode = Null) {

        $this->statement = $statement;

        $this->fileName = $fileName;

        $this->filePath = $filePath;

        $this->connectionCode = $connectionCode;

    }

    public function statement(): String {

        return $this->statement;

    }

    public function filename(): String {

        return $this->fileName;

    }

    public function hash(): String {

        return md5($this->contents());
        
    }


    public function persist(): Bool {       

        switch($this->statement) {
            case Static::$Stage_Clear:
                    return (new \PHPBook\Storage\Storage)
                        ->setConnectionCode($this->connectionCode)
                        ->setFile($this->filePath . DIRECTORY_SEPARATOR . $this->fileName)
                        ->delete();
                break;
            case Static::$Stage_Keep:
                    return true;
                break;
            default:
                    return (new \PHPBook\Storage\Storage)
                        ->setConnectionCode($this->connectionCode)
                        ->setFile($this->filePath . DIRECTORY_SEPARATOR . $this->fileName)
                        ->write($this->statement);
                break;
        };

    }

    public function contents(): ?String {

        switch($this->statement) {
            case Static::$Stage_Clear:
                    return Null;
                break;
            case Static::$Stage_Keep:
                    return (new \PHPBook\Storage\Storage)
                        ->setConnectionCode($this->connectionCode)
                        ->setFile($this->filePath . DIRECTORY_SEPARATOR . $this->fileName)
                        ->get();
                break;
            default:
                    return $this->statement;
                break;
        };

    }

    public function mime(): ?String {

        switch($this->statement) {
            case Static::$Stage_Clear:
                    return Null;
                break;
            case Static::$Stage_Keep:
                    $contents = (new \PHPBook\Storage\Storage)
                        ->setConnectionCode($this->connectionCode)
                        ->setFile($this->filePath . DIRECTORY_SEPARATOR . $this->fileName)
                        ->get();
                    if ($contents) {
                        $mimeType = new \finfo(FILEINFO_MIME);
                        $fileMimeType = explode(';', $mimeType->buffer($contents))[0];
                        return $fileMimeType;
                    } else {
                        return Null;
                    };                    
                break;
            default:
                    $mimeType = new \finfo(FILEINFO_MIME);
                    $fileMimeType = explode(';', $mimeType->buffer($this->statement))[0];
                    return $fileMimeType;
                break;
        };

    }
    
}