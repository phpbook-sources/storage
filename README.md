    
+ [About Storage](#about-storage)
+ [Composer Install](#composer-install)
+ [Declare Configurations](#declare-configurations)
+ [Manager](#manager)
+ [Validation](#validation)
+ [Parse](#parse)
+ [File Stage](#file-stage)

### About Storage

- A lightweight storage PHP library available for AWSS3, FTP, LOCAL.
- Requires PHP Extension FINFO.

### Composer Install

	composer require phpbook/storage

### Declare Configurations

```php

/********************************************
 * 
 *  Declare Configurations
 * 
 * ******************************************/

//Driver connection AWSS3

\PHPBook\Storage\Configuration\Storage::setConnection('backups', 
	(new \PHPBook\Storage\Configuration\Connection)
		->setName('Backups')
		->setExceptionCatcher(function(String $message) {
			//the PHPBook Storage does not throw exceptions, but you can take it here
			//you can store $message in database or something else
		})
		->setDriver((new \PHPBook\Storage\Driver\AWSS3)
			->setKey('key')
			->setSecret('secret')
			->setRegion('region')
			->setBucket('bucket'))
);

//Driver connection FTP

\PHPBook\Storage\Configuration\Storage::setConnection('other',
	(new \PHPBook\Storage\Configuration\Connection)
		->setName('Other')
		->setExceptionCatcher(function(String $message) {
			//the PHPBook Storage does not throw exceptions, but you can take it here
			//you can store $message in database or something else
		})
		->setDriver((new \PHPBook\Storage\Driver\FTP)
			->setHost('host')
			->setPort(21)
			->setDirectory('ftp/root/path')
			->setUser('user')
			->setPassword('password'))
);

//Driver connection LOCAL

\PHPBook\Storage\Configuration\Storage::setConnection('main',
	(new \PHPBook\Storage\Configuration\Connection)
		->setName('Main')
		->setExceptionCatcher(function(String $message) {
			//the PHPBook Storage does not throw exceptions, but you can take it here
			//you can store $message in database or something else
		})
		->setDriver((new \PHPBook\Storage\Driver\LOCAL)
			->setDirectory('local/root/path'))
);

//Set default connection by connection alias

\PHPBook\Storage\Configuration\Storage::setDefault('main');

//Getting connections

$connections = \PHPBook\Storage\Configuration\Storage::getConnections();

foreach($connections as $code => $connection) {

	$connection->getName(); 

	$connection->getDriver();

};

?>
```

### Manager

```php

	//Connection code is not required if you set default connection
	
	// get file
	$contents = (new \PHPBook\Storage\Storage)
			->setConnectionCode('other')
			->setFile('path/in/storage/to/file/file.jpeg')
			->get();

	if ($contents) {
		//contents not null
	};
			
	// write and overwrite file, auto create missing directories
	$boolean = (new \PHPBook\Storage\Storage)
			->setFile('path/in/storage/to/file/file.jpeg')
			->write($contents);

	if ($boolean) {
		//done
	};

	// write and overwrite file from local file path, auto create missing directories
	$boolean = (new \PHPBook\Storage\Storage)
			->setFile('path/in/storage/to/file/file.jpeg')
			->write(\PHPBook\Storage\Local::getContents('absolute/local/file/path'));

	if ($boolean) {
		//done
	};

	// move file, auto create missing directories
	$boolean = (new \PHPBook\Storage\Storage)
			->setFile('path/in/storage/to/file/file.jpeg')
			->move('path/in/storage/to/file/file-rename.jpeg');

	if ($boolean) {
		//done
	};

	// delete file
	$boolean = (new \PHPBook\Storage\Storage)
			->setConnectionCode('other')
			->setFile('path/in/storage/to/file/file-rename.jpeg')
			->delete();

	if ($boolean) {
		//done
	};

	// get directory file names array
	$contents = (new \PHPBook\Storage\Directory)
			->setConnectionCode('other')
			->setDirectory('path/in/storage/to/get/file/names/array')
			->files();

	if ($contents) {
		//contents not null
	};

	// write directory, auto create missing parents directories
	$boolean = (new \PHPBook\Storage\Directory)
			->setDirectory('path/in/storage/to/files')
			->write();

	if ($boolean) {
		//done
	};

	// move directory, auto create missing parents directories
	$boolean = (new \PHPBook\Storage\Directory)
			->setDirectory('path/in/storage/to/files')
			->move('new/path/in/storage/to/files');

	if ($boolean) {
		//done
	};

	// delete directory and all its contents recursively
	$boolean = (new \PHPBook\Storage\Directory)
			->setDirectory('path/in/storage/to/files')
			->delete();

	if ($boolean) {
		//done
	};

```

### Validation

```php

	$contents = 'file-buffer-contents';

	$mimes = ['image']; //or ['image/jpeg', 'image/png']
	
	$sizeKiloBytes = 150;

	if (\PHPBook\Storage\Validation::isInMimeTypes($contents, $mimes)) {
		//file is ok
	};

	if (\PHPBook\Storage\Validation::isInLimitsKilobytes($contents, $sizeKiloBytes)) {
		//file is ok
	};

```

### Parse

```php

	//getting string contents to objects

	$item = \PHPBook\Storage\Parse::getByJson($stringJson);

	$item = \PHPBook\Storage\Parse::getByXml($stringXML);

```

### File Stage

```php

// you can create a file stage to upload a file, clear the current file or simply keep the file data untouch
	
	/* default file schema */
	$fileName = 'my-file.jpeg';
	$filePath = 'my/file/path';
	$connectionCode = 'my-connection-code'; //for default, use null or suppress the parameter

	/* Uploads a file */
	$statement = 'binary'; //binary contents to upload
	$fileStage = new \PHPBook\Storage\FileStage($statement, $fileName, $filePath, $connectionCode);

	/* Clear a file if exists */
	$statement = \PHPBook\Storage\FileStage::$Stage_Clear; //statement to clear the current file if exists
	$fileStage = new \PHPBook\Storage\FileStage($statement, $fileName, $filePath, $connectionCode);

	/* Keep a file untouch or without file */
	$statement = \PHPBook\Storage\FileStage::$Stage_Keep; //statement to keep the current file or keep without file
	$fileStage = new \PHPBook\Storage\FileStage($statement, $fileName, $filePath, $connectionCode);

	/* Prepare a statement string or binary content that is encoded */
	$statement = \PHPBook\Storage\FileStage::GetStatementOrBinaryDecoded('statement or encoded binary'); //returns the statement string or binary content decoded
	$fileStage = new \PHPBook\Storage\FileStage($statement, $fileName, $filePath, $connectionCode);

	/* Get file statement */
	$fileName = $fileStage->statement();

	/* After stage the file changes, you can persist, returns true or false to the operation */
	/* When is upload the binary contents, the phpbook uploads the file */
	/* When is stage clear, the phpbook clear the file */
	/* When is stage keep, nothing changes */
	$boolean = $fileStage->persist();

	/* If you need, you can get the file stage contents. */
	/* When is upload the binary, the phpbook retrieves the stage binary contents or null. */
	/* When is stage clear, the phpbook retrieves null. */
	/* When is stage keep, the phpbook retrieves the current file if exists otherwise returns null. */
	$contents = $fileStage->contents();

	/* Get file mime */
	/* When is upload the binary, the phpbook retrieves the mime binary contents or null. */
	/* When is stage clear, the phpbook retrieves null. */
	/* When is stage keep, the phpbook retrieves the current file mime if exists otherwise returns null. */
	$mime = $fileStage->mime();

	/* Get file hash */
	/* When is upload the binary, the phpbook retrieves the hash binary contents. */
	/* When is stage clear, the phpbook retrieves null. */
	/* When is stage keep, the phpbook retrieves the current file hash if exists otherwise returns null. */
	$mime = $fileStage->hash();

	/* Get file mime */
	/* When is upload the binary, the phpbook retrieves the mime binary contents or null. */
	/* When is stage clear, the phpbook retrieves null. */
	/* When is stage keep, the phpbook retrieves the current file mime if exists otherwise returns null. */
	$fileName = $fileStage->filename();

	/* The purpose of this implementation in PHP is a comprehensive guide to handling the files in your requests,
	uploading, removing or keeping the file information as it currently stands. */

//Implementation example

	class Customer {
		
		private static $Photo_Path = 'my/file/path/to/photo';

		private $id;

		private $name;

		private $photo;

		public function __construct(String $name, String $photo) {

			$this->id; //generate key

			$this->edit($name, $photo);
		}

		public function edit(String $name, String $photo) {

			$this->name = $name;

			$this->photo = new \PHPBook\Storage\FileStage($photo, $this->getId(), Static::$Photo_Path);

		}

		public function getId(): Int {

			return $this->id;

		}

		public function getName(): String {

			return $this->name;

		}

		public function getPhoto(): ?String {

			if (!$this->photo) {

				$this->photo = new \PHPBook\Storage\FileStage(\PHPBook\Storage\FileStage::$Stage_Keep, $this->getId(), Static::$Photo_Path);

			};

			return $this->photo->contents();
		}

		public function save() {

			//store id and name in the database

			//store the stage photo in the storage
			if ($this->photo) {

				$this->photo->persist();
				
			};

		}


	}

```