<?php

/**
 *
 * Helper 
 * 
 * @package		Papyrus
 * @subpackage	Helper
 * @license    	http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
 * @author     	Omar Andrés Barbosa Ortiz
 * @link       	http://omarbarbosa.com
 *
 */

namespace Barbosa\Papyrus;

class Helper
{

	/**
	 * cleanBlanks method
	 * Remove blanks
	 * 
	 * @access public
	 * @param string | array $data
	 * 
	 * @return string | array $cleanedData
	 */
	public static function cleanBlanks($data)
	{
		switch (gettype($data)) {
			case 'string':
				$cleaned = str_replace(" ","", $data);
				break;

			case 'array':
				foreach ($data as $key => $value) {
					$cleaned[] = str_replace(" ","", $value);
				}
				break;
		}

		return $cleaned;
	}

	/**
	 * checkArray method
	 * Check the array type, single or nested
	 * 
	 * @access public
	 * @param array $array
	 * 
	 * @return int $level
	 */
	public static function checkArray(array $array = null)
	{
		$level = 1;
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$level = 2;
				break;
			}
		}

		return $level;
	}
}