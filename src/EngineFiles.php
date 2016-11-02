<?php 

/**
 * 
 * EngineFiles class
 * Writing and reading files
 * 
 * @package		Papyrus
 * @subpackage	EngineFiles
 * @license    	http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
 * @author     	Omar Andrés Barbosa Ortiz
 * @link       	http://omarbarbosa.com
 * 
 */

namespace Barbosa\Papyrus;

use Barbosa\Papyrus\Helper;

class EngineFiles
{
	/**
	 * $ext File extension
	 * @var string
	 */
	protected static $ext;

	/**
	 * $storagePath Absolute storagePath
	 * @var string
	 */
	private $storagePath = '';

	/**
	 * $files The files to use
	 * @var array
	 */	
	protected $files = [];

	/**
	 * $headers Fields name and primary key or identifier
	 * @var array
	 */	
	protected $headers = [];

	/**
	 * $status Transaction status
	 * @var boolean
	 */
	protected $status = false;

	/**
	 * __construct Load the configurations
	 * @param array|null $config      
	 * @param string     $storagePath Absolute storage folder path
	 */
	public function __construct(array $config = null, $storagePath = '')
	{
		$this->setStoragePath($storagePath);
		$this->loadConfigurations($config);
	}

	/**
	 * loadConfigurations Invokes methods to configure the files and structures
	 * @param  array|null $config Array with extension key and files key
	 */
	public function loadConfigurations(array $config = null)
	{
		if (!empty($config)) {
			if (array_key_exists('extension', $config)) {
				$this->setExtension($config['extension']);
			}
			
			if (array_key_exists('files', $config)) {
				$this->setFiles(array_keys($config['files']));
				$this->setHeaders($config['files']);
			}
		}
	}

	/**
	 * setStoragePath
	 * @param string $path Absolute storage folder path
	 */
	public function setStoragePath($path = '')
	{
		if (is_dir($path) and is_readable($path)) {
			$this->storagePath = realpath($path);
		}
	}

	/**
	 * setExtension method
	 * Set the ext property
	 * 
	 * @access protected
	 * @param string $extension
	 */
	protected function setExtension($extension = '')
	{
		if (!empty($extension)) {
			self::$ext = $extension;
		}
	}

	/**
	 * setFiles method
	 * Set the files property
	 * 
	 * @access protected
	 * @param array $files
	 */
	protected function setFiles(array $files = null)
	{
		if (!empty($files)) {
			foreach ($files as $file) {
				$path = realpath($this->storagePath . '/' . $file . self::$ext);
				if (file_exists($path) and is_readable($path)) {
					$this->files[$file] = $path;
				}
			}
		}
	}

	/**
	 * setHeaders method
	 * Set the headers property
	 * 
	 * @access protected
	 * @param array $fields
	 */
	protected function setHeaders(array $files = null)		
	{
		if (!empty($files)) {
			foreach ($files as $file => $fields) {
				foreach ($fields as $field) {
					$this->headers[$file][] = $field;
				}
			}
		}
	}

	/**
	 * setStatus method
	 * Set the headers property
	 * 
	 * @access protected
	 * @param array $fields
	 */
	protected function setStatus($bits)		
	{
		if ($bits !== false) {
			$this->status = true;
		}
	}

	/**
	 * write method
	 * Writes in a file in json format
	 * 
	 * @access protected
	 * @param string $file
	 * @param array $data
	 * @param boolean $mode True: Writes (Default), False: Overwrites
	 */
	protected function write($fileName = '', array $data = null, $mode = true)
	{
		if (!empty($fileName)) {
			if ($mode) {
				$arrayData = $this->read($fileName);
				array_push($arrayData, $data);
				$bits = file_put_contents($this->getPath($fileName), json_encode($arrayData));
				$this->setStatus($bits);
			} else {
				$bits = file_put_contents($this->getPath($fileName), json_encode($data));
				$this->setStatus($bits);
			}
		}
	}

	/**
	 * read method
	 * Reads a file and retrieve the results in array format
	 * 
	 * @access protected
	 * @param string $file
	 * 
	 * @return array Results
	 */
	protected function read($fileName = '')
	{
		if ($this->getPath($fileName)) {
			$jsonData = file_get_contents($this->getPath($fileName));
			$arrayData = json_decode($jsonData, true);

			return is_array($arrayData) ? $arrayData : array();
		} else {
			throw new Exception("File error: Check file name or storage.php file.", 1);
		}
	}

	/**
	 * getPath method
	 * Get the full path of a file
	 * 
	 * @access protected
	 * @param string $file
	 * 
	 * @return string Full path
	 */
	protected function getPath($fileName = '')
	{
		if (array_key_exists($fileName, $this->files)) {
			return $this->files[$fileName];
		}
	}
	
	/**
	 * getStatus method
	 * Get the status
	 * 
	 * @access public
	 * 
	 * @return boolean $status
	 */
	public function getStatus()
	{
		return $this->status;
	}
}

