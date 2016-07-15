<?php 

/**
 *
 * Papyrus
 * Storing data in the file system
 *
 * @package		Papyrus
 * @subpackage	Papyrus
 * @license    	http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
 * @author     	Omar Andrés Barbosa Ortiz
 * @link       	http://omarbarbosa.com
 *
 */
namespace Barbosa\Papyrus;

use Barbosa\Papyrus\Transaction;
use Barbosa\Papyrus\FluentInterface;
use Barbosa\Papyrus\Helper;

class Papyrus extends Transaction implements FluentInterface
{
	/**
	 * $fields Fields selected
	 * @var array
	 */
	private $fields = [];

	/**
	 * $file File selected
	 * @var string
	 */
	private $file = '';

	/**
	 * $where Query conditions, only by primary key or identifier 
	 * @var array
	 */
	private $where = [];

	/**
	 * $data Data to insert or update
	 * @var array
	 */
	private $data = [];

	/**
	 * $limit Maximun redords
	 * @var int
	 */
	private $limit = 0;

	/**
	 * $orderBy Order of query result by primary key or identifier
	 * @var array
	 */
	private $orderBy = [];

	/**
	 * $transactionType
	 * Types: reading (r), writing (w), deleting (d), updating (u)
	 * @var string
	 */
	private $transactionType = '';	

	/**
	 * select method
	 * Set the fields
	 * 
	 * @access public
	 * @param string $fields
	 * 
	 * @return Papyrus
	 */
	public function select($fields = '')
	{
		if (is_string($fields)) {
			if (!empty($fields)) {
				$tempFields = Helper::cleanBlanks(explode(",", $fields));
				$this->fields = $tempFields;
			}
		} 

		return $this;
	}

	/**
	 * from method
	 * setFile alias
	 * 
	 * @access public
	 * @param string $file
	 * 
	 * @return Papyrus
	 */
	public function from($file = '')
	{
		$this->setFile($file);
		$this->transactionType = empty($this->file) ? '' : 'r';

		return $this;
	}
	
	/**
	 * into method
	 * setFile alias
	 * 
	 * @access public
	 * @param string $file
	 * 
	 * @return Papyrus
	 */
	public  function insertInto($file = '')
	{
		$this->setFile($file);
		$this->transactionType = empty($this->file) ? '' : 'w';

		return $this;
	}

	/**
	 * deleteFrom method
	 * setFile alias
	 * 
	 * @access public
	 * @param string $file
	 * 
	 * @return Papyrus
	 */
	public  function deleteFrom($file = '')
	{
		$this->setFile($file);
		$this->transactionType = empty($this->file) ? '' : 'd';

		return $this;		
	}

	/**
	 * update method
	 * setFile alias
	 * 
	 * @access public
	 * @param string $file
	 * 
	 * @return Papyrus
	 */
	public  function update($file = '')
	{
		$this->setFile($file);
		$this->transactionType = empty($this->file) ? '' : 'u';

		return $this;		
	}


	/**
	 * set method
	 * setData alias
	 * 
	 * @access public
	 * @param array $data
	 * 
	 * @return Papyrus
	 */
	public function set(array $data = null)
	{
		$this->setData($data);

		return $this;
	}

	/**
	 * values method
	 * setData alias
	 * 
	 * @access public
	 * @param array $data
	 * 
	 * @return Papyrus
	 */
	public function values(array $data = null)
	{
		$this->setData($data);

		return $this;
	}

	/**
	 * where method
	 * Set the where property, query conditions, only by primary key or identifier
	 * 
	 * @access public
	 * @param array $conditions
	 * 
	 * @return Papyrus
	 */
	public function where(array $conditions = null)
	{
		if (count($conditions) == 1) {
			foreach ($conditions as $field => $value) {
				$this->where[$field] = $value;
			}
		}

		return $this;
	}

	/**
	 * limit method
	 * Set the limit property
	 * 
	 * @access public
	 * @param integer $limit
	 * 
	 * @return Papyrus
	 */
	public function limit($limit = 0)
	{
		if (is_int($limit)) {
			$this->limit = (int) $limit;
		}

		return $this;
	}

	/**
	 * orderBy method
	 * Set the orderBy property, only by a field
	 * 
	 * @access public
	 * @param array $order
	 * 
	 * @return Papyrus
	 */
	public function orderBy(array $order = null)
	{
		if (!empty($order) and count($order) == 1) {
			foreach ($order as $key => $value) {
				if ($value === 'ASC' or $value === 'DESC') {
					$this->orderBy[$key] = $value;
				}
			}
		}

		return $this;
	}

	/**
	 * setFile method
	 * Set the file property, this is the file name
	 * 
	 * @access private
	 * @param string $file
	 */
	private function setFile($file = '')
	{
		$file = Helper::cleanBlanks($file);
		if (is_string($file) and !empty($file)) {
			$path = $this->getPath($file);
			if (file_exists($path) and is_readable($path)) {
				$this->file = $file;
			}
		}
	}

	/**
	 * setData method
	 * Set the data property, data for storage or update in the file system
	 * 
	 * @access private
	 * @param array $data
	 */
	private function setData(array $data = null)
	{
		if (!empty($data) and Helper::checkArray($data) == 1) {
			foreach ($data as $key => $value) {
				$this->data[$key] = $value;
			}
		}
	}

	/**
	 * runQuery method
	 * Invoke methods to run the query
	 * 
	 * @access public
	 * 
	 * @return Papyrus
	 */
	public function runQuery()
	{	
		if ($this->transactionType) {
			switch ($this->transactionType) {
				case 'r':
					if (!empty($this->file)) {
						$this->get($this->file, $this->fields, $this->where, $this->limit, $this->orderBy);
					}
					break;

				case 'w':
					if (!empty($this->file) and !empty($this->data)) {
						$this->add($this->file, $this->data);
					}
					break;

				case 'd':
					if (!empty($this->file)) {
						$this->remove($this->file, $this->where);
					}
					break;

				case 'u':
					if (!empty($this->file) and !empty($this->data)) {
						$this->edit($this->file, $this->data, $this->where);
					}
					break;
			}
		}

		return $this;
	}
}