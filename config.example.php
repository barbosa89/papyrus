<?php

/**
 * Papyrus
 * Config file
 * 
 * Permissions:	chmod 706 fileName.ext
 */

return [

	/**
	 * 
	 * Set the extension to database files
	 * Example:
	 * 			'extension' => '.some'
	 * 
	 */

	'extension'	=>	'.data',
	
	/**
	 * Set the primary key each file
	 * Primary Key: Auto increment integer(int++), 
	 * 				unique integer (int#), 
	 *              unique string (str#)
	 * 				Example: 	[
	 * 								'fileName' => ['name(str#)', 'lastName', 'email'],
	 * 								'otherFileName' => ['id(int++)', 'description'],
	 * 								'otherFileName' => ['dni(int#)', 'name', 'lastname']
	 * 							]
	 * 
	 */

	'files' =>	[
					'users' => ['dni(int#)', 'name', 'lastName']
				]
	];
