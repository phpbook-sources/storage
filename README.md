    
+ [About Storage](#about-storage)
+ [Composer Install](#composer-install)
+ [Declare Configurations](#declare-configurations)
+ [Manager](#manager)

### About Storage

- A lightweight storage PHP library available for AWSS3, FTP, LOCAL.

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
	
	// get
	$contents = (new \PHPBook\Storage\Storage)
			->setConnectionCode('other')
			->setFile('path/in/storage/to/file/file.jpeg')
			->get();

	if ($contents) {
		//contents
	};
			
	// write and overwrite
	$boolean = (new \PHPBook\Storage\Storage)
			->setFile('path/in/storage/to/file/file.jpeg')
			->write($contents);

	if ($boolean) {
		//done
	};

	// write and overwrite from local file path
	$boolean = (new \PHPBook\Storage\Storage)
			->setFile('path/in/storage/to/file/file.jpeg')
			->write(\PHPBook\Storage\Local::getContents('absolute/local/file/path'));

	if ($boolean) {
		//done
	};

	// delete
	$boolean = (new \PHPBook\Storage\Storage)
			->setConnectionCode('other')
			->setFile('path/in/storage/to/file/file.jpeg')
			->delete();

	if ($boolean) {
		//done
	};
		
```