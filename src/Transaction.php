<?php 

/**
 *
 * Transaction class
 * Runs operations of queries, inserts, updates and deletes data in the file system 
 * 
 * @package		Papyrus
 * @subpackage	Transaction
 * @license    	http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
 * @author     	Omar Andrés Barbosa Ortiz
 * @link       	omar.barbosa89.blogspot.com
 *
 */

namespace Barbosa\Papyrus;

use Barbosa\Papyrus\EngineFiles;
use Barbosa\Papyrus\Helper;

class Transaction extends EngineFiles 
{
	/**
	 * $records Data set
	 * @var array
	 */
	protected $records;

	/**
	 * get method
	 * Runs queries
	 * 
	 * @access protected
	 * @param string $fileName
	 * @param array $fields
	 * @param array $where
	 * @param int $limit
	 * @param string $orderBy
	 */
	protected function get($fileName = '', array $fields = null, array $where = null, $limit = 0, $orderBy = '')
	{
		$tempData = [];
		if (!empty($where)) {
			if (empty($fields)) {
				$this->records = $this->match($fileName, $where, $this->read($fileName));
			} else {
				$tempData = $this->match($fileName, $where, $this->read($fileName));
				if (!empty($tempData)) {
					$this->records = $this->excludeFields($tempData, $fields);
				}
			}
		} else {
			if (empty($fields)) {
				$this->records = $this->read($fileName);
				$this->sortCollectionBy($this->records, $orderBy);				
				$this->records = $this->applyLimit($this->records, $limit);
			} else {
				$this->records = $this->read($fileName);
				$this->sortCollectionBy($this->records, $orderBy);	
				if (!empty($this->records)) {
					$this->records = $this->excludeFields($this->applyLimit($this->records, $limit), $fields);
				}			
			}			
		}
	}

	/**
	 * add method
	 * Inserts a new record
	 * 
	 * @access protected
	 * @param string $fileName
	 * @param array $data
	 */
	protected function add($fileName = '', array $data = null)
	{
		if (array_key_exists($fileName, $this->files)) {
			$key = $this->getPrimaryKey($fileName);
			if (!empty($key) and $this->validateKey($key, $data) == true) {
				$structure = $this->getStructure($fileName, $key[1]);
				$data = $this->prepareRows($data, $this->getStructure($fileName, $key[1]));
				if ($key[1] == 'str#' or $key[1] == 'int#') {
					$this->get($fileName, [], [$key[2] => $data[$key[2]]]);

					if (empty($this->records)) {
						$this->write($fileName, $data);
						$this->status = true;
					}
				} elseif ($key[1] == 'int++') {
					$latest = $this->getLatest($fileName, $key[2]);
					$data = $this->prepareAutoIncrementData($data, $key[2], $latest, $structure);
					$this->write($fileName, $data);
					$this->status = true;
				}
			}
		}
	}

	/**
	 * add method
	 * Updates a record or all records
	 * 
	 * @access protected
	 * @param string $fileName
	 * @param array $data
	 * @param array $where
	 */
	protected function edit($fileName = '', array $data = null, array $where = null)
	{
		$id = array_keys($where);
		if (!empty($where)) {
			$this->get($fileName, [], $where);
			
			if (!empty($this->records)) {
				$this->get($fileName);
				$key = $this->getPrimaryKey($fileName);
				$structure = $this->getStructure($fileName, $key[1]);

				if ($key[1] == 'str#' or $key[1] == 'int#') {
					$data = array_filter($this->prepareRows($data, $structure));
				} elseif ($key[1] == 'int++') {
					$data = array_filter($this->prepareAutoIncrementData(
																		$data, 
																		$id[0], 
																		$where[$id[0]] - 1, 
																		$structure
																		));
				}
				foreach ($this->records as $record => $rows) {
					if ($rows[$id[0]] === $where[$id[0]] and $key[2] === $where[$id[0]]) {
						foreach ($data as $key => $value) {
							$this->records[$record][$key] = $value;
						}
					}
				}
				$this->write($fileName, $this->records, false);
				$this->status = true;
			}
		} else {
			$this->get($fileName);	
			$key = $this->getPrimaryKey($fileName);	
			if (!array_key_exists($key[2], $data)) {
				foreach ($this->records as $record => $rows) {
					foreach ($rows as $keyRows => $valueRows) {
						foreach ($data as $field => $value) {
							if ($field === $keyRows) {
								$this->records[$record][$keyRows] = $value;
							}
						}
					}
				}
				$this->write($fileName, $this->records, false);
				$this->status = true;	
			} 	
		}
	}

	/**
	 * remove method
	 * Deletes a record or all records
	 * 
	 * @access protected
	 * @param string $fileName
	 * @param array $where
	 */
	protected function remove($fileName = '', array $where = null)
	{
		if (!empty($where)) {
			$this->get($fileName);
			$key = $this->getPrimaryKey($fileName);	
			foreach ($this->records as $record => $rows) {
				foreach ($rows as $keyRows => $valueRows) {
					foreach ($where as $field => $value) {
						if ($field === $keyRows and $field === $key[2] and $value === $valueRows) {
							unset($this->records[$record]);
						}
					}
				}
			}
			$this->write($fileName, $this->records, false);
			$this->status = true;	
		} else {
			$this->write($fileName, [], false);
			$this->status = true;	
		}
	}

	/**
	 * getPrimaryKey method
	 * Get the primaries keys structure or identifiers
	 * 
	 * @access private
	 * @param string $fileName
	 * 
	 * @return array
	 */
	private function getPrimaryKey($fileName = '')
	{
		foreach ($this->headers[$fileName] as $fields) {
			preg_match("/[a-z]+\((str#|int\+\+|int#)\)/", $fields, $coincidence);
			if (!empty($coincidence)) {
				$key = str_replace("(" . $coincidence[1] . ")", "", $coincidence[0]);	
				array_push($coincidence, $key);
				$identifiers = $coincidence;
			}
		}	

		return empty($identifiers) ? [] : $identifiers;
	}


	/**
	 * getPrimaryKey method
	 * Validates a primary key or identifier
	 * 
	 * @access private
	 * @param array $key
	 * @param array $data
	 * 
	 * @return boolean
	 */
	private function validateKey(array $key = null, array $data = null)
	{
		$valid = false;
		if (!empty($key) and !empty($data)) {	
			if (array_key_exists($key[2], $data)) {
				switch ($key[1]) {
					case 'str#':
						if (is_string($data[$key[2]])) {
							$valid = true;
						}
						break;
					case 'int#':
						if (is_int($data[$key[2]])) {
							$valid = true;
						}
						break;
				}

				return $valid;
			}
		}
	}

	/**
	 * getLatest method
	 * Get the last record for auto increment data insertion
	 * 
	 * @access private
	 * @param string $fileName
	 * @param string $key
	 * 
	 * @return int
	 */
	private function getLatest($fileName = '', $key = '')
	{
		if (!empty($fileName) and !empty($key)) {
			$this->get($fileName);
			$record = array_pop($this->records);
			
			return  empty($record) ? 0 : $record[$key];
		}
	}


	/**
	 * match method
	 * Matches a record by conditions
	 * 
	 * @access private
	 * @param string $fileName
	 * @param array $where
	 * @param array $records
	 * 
	 * @return array $matched
	 */
	private function match($fileName = '', array $where, array $records = null)
	{
		$matched = [];
		$id = array_keys($where);
		if (!empty($records)) {
			$key = $this->getPrimaryKey($fileName);	
			if (array_key_exists($key[2], $where)) {
				foreach ($records as $record) {
					foreach ($record as $field => $value) {
						if ($field === $id[0] and $value === $where[$id[0]]) {
							$matched = $record;
						}
					}
				}
			}
		}

		return $matched;
	}

	/**
	 * excludeFields method
	 * Remove fields in records by conditions
	 * 
	 * @access private
	 * @param array $data
	 * @param array $fields
	 * 
	 * @return array $required
	 */
	private function excludeFields(array $data = null, array $fields = null)
	{
		$required = [];
		if (!empty($data) and !empty($fields)) {
			if (Helper::checkArray($data) == 1) {
				$noRequired = array_diff(array_keys($data), $fields);
				foreach ($noRequired as $field) {
					unset($data[$field]);
				}

				$required = $data;
			} else {
				foreach ($data as $records) {
					$noRequired = array_diff(array_keys($records), $fields);
					foreach ($noRequired as $field) {
						unset($records[$field]);
					}

					$required[] = $records;
				}
			}

			return $required;
		}
		
	}

	/**
	 * getStructure method
	 * Get the file structure
	 * 
	 * @access private
	 * @param string $fileName
	 * @param string $mark
	 * 
	 * @return array $structure
	 */
	private function getStructure($fileName = '', $mark = '')
	{
		$structure = [];
		if (!empty($fileName) and !empty($mark)) {
			foreach ($this->headers[$fileName] as $header) {
				$structure[] = str_replace("(" . $mark . ")", "", $header);
			}
		}

		return $structure;
	}

	/**
	 * prepareRows method
	 * Remove fields that do not exist in the file structure
	 * 
	 * @access private
	 * @param array $data
	 * @param array $structure
	 * 
	 * @return array $newData
	 */
	private function prepareRows(array $data = null, array $structure = null)
	{
		$newData = [];
		if (!empty($data) and !empty($structure)) {
			$data = $this->excludeFields($data, $structure);
			$emptyFields = array_values(array_diff($structure, array_keys($data)));

			foreach ($emptyFields as $field) {
				$data[$field] = null;
			}

			foreach ($structure as $field) {
				foreach ($data as $key => $value) {
					if ($field === $key) {
						$newData[$field] = $value;
					}
				}
			}
			
			return $newData;
		}
	}

	/**
	 * prepareAutoIncrementData method
	 * Prepares a record with auto increment
	 * 
	 * @access private
	 * @param array $data
	 * @param string $key
	 * @param int $latest
	 * @param array $structure
	 * 
	 * @return array $newData
	 */
	private function prepareAutoIncrementData(array $data = null, $key = '', $latest = 0, array $structure = null)
	{
		if (!empty($data)) {
			if (array_key_exists($key, $data)) {
				unset($data[$key]);
			}
			$data[$key] = $latest + 1;

			return $this->prepareRows($data, $structure);
		}
	}

	/**
	 * sortCollectionBy method
	 * Sorts data records
	 * 
	 * @access private
	 * @param array $data
	 * @param string $orderBy
	 */
	private function sortCollectionBy(&$array, $orderBy)
	{
		$temp = [];
		if (!empty($array) and !empty($orderBy)) {
			$field = array_keys($orderBy);
			$order = ($orderBy[$field[0]] === 'DESC') ? SORT_DESC : SORT_ASC;
			if (array_key_exists($field[0], $array[0])) {
				foreach ($array as $key => $value) {
					$temp[$key] = strtolower($value[$field[0]]);
				}

				array_multisort($temp, $order, $array);
			}
		}
	}

	/**
	 * applyLimit method
	 * Limit the data
	 * 
	 * @access private
	 * @param array $data
	 * @param int $limit
	 * 
	 * @return array $limitedData
	 */
	private function applyLimit($data = '', $limit)
	{	
		return ($limit > 0) ? array_slice($data, 0, $limit) : $data;
	}

	/**
	 * getRecords method
	 * Get the records
	 * 
	 * @access public
	 * 
	 * @return array $records
	 */
	public function getRecords()
	{
		return $this->records;
	}
}