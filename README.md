# Papyrus: Storing data in the file system with PHP

## Generalities


The Papyrus package can store information in files, perform queries, updates, and deletes data . All this, based on a primary key or identifier. The information is stored in JSON format.

## Installation

To install via composer (http://getcomposer.org/), place the following in your composer.json file:

    {
        "require": {
            "barbosa/papyrus": "dev-master"
        }
    }

or download package from github.com:

    http://github.com/barbosa89/papyrus

## Configuration

Papyrus need to run an array of configurations and a path to the folder containing the files storage.

#### Configuration array

Create an array of configurations in a file or in the file which instances to Papyrus. The array must have two key, extension and files: 

	<?php
    
    /**
     * Example in a configurations file. 
     */

    return [

            'extension'	=>	'.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
	       ];

Other way, in the file which instances to Papyrus:

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $papyrus = new Papyrus($config);


This extension is for all files:

    'extension' => '.project'


The file structure represents fields in a SQL table , what is in brackets, are the fields of each file:

    'files' => [

        'fileName' => ['primaryKey(int#)', 'field', 'otherField'],
        'otherFileName' => ['primaryKey(str#)', 'field', 'otherField']
		
        ]

The options for primary key:

	Auto increment integer (int++)
	Unique integer (int#)
	Unique string (str#)

#### Absolute storage folder path

Storage files that are configured in the array must be created in a folder chosen by the user, the folder path should be passed to Papyrus:

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    /**
     * Complete example. 
     */

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $path = realpath(__DIR__ . '/storageFolder/');

    $papyrus = new Papyrus($config, $path);

You can also switch configurations through methods:

    <?php 

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

    /**
     * Complete example with setters. 
     */

    $papyrus = new Papyrus();

    $papyrus->setStoragePath(realpath(__DIR__ . '/storageFolder/'));

    $config = [

            'extension' =>  '.project',

            'files' =>  [
                        'users' => ['dni(int#)', 'name', 'lastName']
                        ]
            ];

    $papyrus->loadConfigurations($config);

## Creating of storage files

As example, a file will be created with **registers** name and the extension .data:

    $ touch path/to/storageFolder/registers.data

In the console:

	$ ls -l
    -rwx---rw- 1 user user 494 jul  8 21:10 registers.data

You can add every files that you need.

## Permissions (Only in Linux)

Files in the storage folder (path/to/storageFolder/): 

    chmod 706 fileName.extension

Example:
    
    chmod 706 registers.data

## Quick Start and Examples

For purposes of explaining with examples , it is assumed that there is a file called users with the following structure:

    'files' => [
		'users' => ['dni(int#)', 'name', 'lastName']
		]

Field dni(int#), indicates that is the primary key and a unique integer.

Require the autoload file:

    require 'vendor/autoload.php';

    use \Barbosa\Papyrus\Papyrus;

### Insert


    $values = ['dni' => 123456, 'name' => 'Tony', 'lastName' => 'Stark'];

    $papyrus = new Papyrus();
    $papyrus->insertInto('users')->values($values)->runQuery();


### Select

The where method can only receive a value of array type, which should be the primary key or identifier.

##### Select all content in a file with all fields.


    $papyrus->select()->from('users')->runQuery();


##### Select all content in a file with some fields.


    $papyrus->select('dni, name')->from('users')->runQuery();


##### Select a record with all fields.


    $papyrus->select()->from('users')->where(['dni' => 123456])->runQuery();


##### Select a record with some fields 


    $papyrus->select('dni, name')->from('users')->where(['dni' => 123456])->runQuery();

 
##### Select records without 'Where' clausule, sorted by a field with value ASC or DESC.


    $papyrus->select()->from('users')->orderBy(['name' => 'ASC'])->runQuery();

    $papyrus->select('dni, lastName')->from('users')->orderBy(['lastName' => 'ASC'])->runQuery();


##### Select records without 'Where' clausule but with limitation.


    $papyrus->select()->from('users')->limit(3)->runQuery();

    $papyrus->select()->from('users')->limit(3)->orderBy(['name' => 'DESC'])->runQuery();


### Delete

##### Delete all records 


    $papyrus->deleteFrom('users')->runQuery();


##### Delete a record


    $papyrus->deleteFrom('users')->where(['dni' => 123456])->runQuery();


### Update

The primary key of a record can not be modified.

##### Update all records


    $data = ['name' => 'Tony', 'lastname' => 'Stark'];

    $papyrus->update('users')->set($data)->runQuery();


##### Update a record


    $data = ['name' => 'Tony', 'lastName' => 'The Iron Man'];

    $papyrus->update('users')->set($data)->where(['dni' => 123456])->runQuery();

### Get the data of query


    $papyrus->getRecords();


### Get the status of query


    $papyrus->getStatus();


## Available methods


    Papyrus::select($fields);
    Papyrus::deleteFrom($file = '');
    Papyrus::update($file = '');            
    Papyrus::from($file = '');
    Papyrus::insertInto($file = '');
    Papyrus::set(array $data = null);
    Papyrus::values(array $data = null);
    Papyrus::where(array $conditions = null);
    Papyrus::orderBy(array $order = null);
    Papyrus::limit($limit = 0);
    Papyrus::runQuery();
    Papyrus::getRecords();
    Papyrus::getStatus();
    $papyrus->loadConfigurations(array $config = null);
    $papyrus->setStoragePath($path = '');


## Contribute
1. Check for open issues or open a new issue to start a discussion around a bug or feature.
2. Fork the repository on GitHub to start making your changes.
3. Write one or more tests for the new feature or that expose the bug.
4. Make code changes to implement the feature or fix the bug.
5. Send a pull request to get your changes merged and published.

Thanks...

### [Omar Andrés Barbosa](http://omarbarbosa.com)