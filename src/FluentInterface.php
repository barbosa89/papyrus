<?php 

/**
 * 
 * FluentInterface
 * 
 * @package		Papyrus
 * @subpackage	FluentInterface
 * @license    	http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
 * @author     	Omar Andrés Barbosa Ortiz
 * @link       	omar.barbosa89.blogspot.com
 * 
 */

namespace Barbosa\Papyrus;

interface FluentInterface
{
	public function select($fields);
	public  function deleteFrom($file = '');
	public  function update($file = '');
	public function from($file = '');
	public  function insertInto($file = '');
	public function set(array $data = null);
	public function values(array $data = null);
	public function where(array $conditions = null);
	public function orderBy(array $order = null);
	public function limit($limit = 0);
	public function runQuery();
}