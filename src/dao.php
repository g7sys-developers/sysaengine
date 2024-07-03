<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine do Sysadmcom
	* pt-BR: App de sistemas do Sysaengine
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		Sysaengine
	* @name 		vo
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine;

use sysaengine\traits\DaoCommon;
use sysaengine\traits\DaoFunction;
use sysaengine\parser;
use sysaengine\xml;

class dao extends vo{
	use DaoCommon, DaoFunction;

	/**
	 * Uses Index?
	 * @bool
	 */
	protected $useIndex = true;

	/**
	 * Set to use where customized
	 * @version 1.0.0
	 * @author Anderson Arruda < andmarruda@gmail.com >
	 * @param 
	 * @return void
	 */
	public function customIndex() : void
	{
		$this->useIndex = false;
	}

	/**
	 * Select informations from database
	 * 
	 * @access public
	 * @version 2.0.0
	 * @author Anderson Arruda < andmarruda@gmail.com >
	 * @param	array $arguments
	 * @return	array
	 */
	public function select(...$arguments) : array
	{
		if($this->dbObjectInfo['type'] !== 'r' && $this->useIndex)
			throw new \Exception('This class has no implementation to deal with index where for view or materialized view');

		if($this->dbObjectInfo['type'] === 'FUNC')
		{
			return $this->selectFunction(...$arguments);
		}

		return $this->selectCommon(...$arguments);
	}

	/**
	 * Verify if some information exists
	 * 
	 * @access public
	 * @version 2.0.0
	 * @param	array $arguments
	 * @return	bool
	 */
	public function exists(...$arguments) : bool
	{
		if($this->dbObjectInfo['type'] === 'FUNC')
		{
			throw new \Exception('This class has no implementation to deal with exists in function');
		}

		$select = $this->select('count(*) as total', ...$arguments);
		return $select[0]['total'] > 0;
	}

	/**
	 * Retorna html de combobox usando no DAO 1.0
	 * 
	 * @access public
	 * @version 2.0.0
	 * @param	
	 */
	public function selectToComboBox(string $fields, string $where='', string $orderBy='', string $groupBy='', string $selected='') : string
	{
		if($this->dbObjectInfo['type'] === 'FUNC')
		{
			throw new \Exception('This class has no implementation to deal with selectToComboBox in function');
		}

		$args = func_get_args();
		$rows = $this->selectCommon(...$args);
		if(array_key_exists('none', $rows[0]))
			return '';

		$html = '';
		foreach($rows as $row) {
			$v=array_values($row);
			$val=(array_key_exists(0, $v)) ? $v[0] : '';
			$text=(array_key_exists(0, $v)) ? $v[1] : '';
			
			$html .= $val == $selected ? "<option value='$val' selected>$text</option>" : "<option value='$val'>$text</option>";
		}

		return $html;
	}

	/**
	 * Save data in selected table
	 * 
	 * @access public
	 * @version 2.0.0
	 * @author Anderson Arruda < andmarruda@gmail.com >
	 * @param
	 * @return		return boolean
	 */
	public function save() : bool
	{
		if($this->dbObjectInfo['type'] !== 'r')
		{
			throw new \Exception("Is not possible to save data in function, materialized view or view using this class.");
		}

		return false;
	}

	/**
	 * Grid 2 compatible function parser
	 * 
	 * @access public
	 * @version 2.0.0
	 * @param string $fields
	 * @param string $where
	 * @param string $gridId
	 * @param string $orderBy
	 * @param string $groupBy
	 * @param string $selected
	 * @return void
	 */
	public function grid(string $fields, string $where='', string $gridId='', string $orderBy='', string $groupBy='') : void
	{
		if($this->dbObjectInfo['type'] !== 'r' && $this->useIndex)
			throw new \Exception('This class has no implementation to deal with index where for view or materialized view');

		$stmt = $this->selectStatement($fields, $where, $orderBy, $groupBy);
		$parser = new parser($stmt);
		$parser->grid2($gridId)->outputJson();
	}

	/**
	 * Select to form
	 * 
	 * @access public
	 * @version 1.0.0
	 */
	public function selectToForm(string $fields, string $where, string $formId, string $orderBy='', string $groupBy='')
	{
		if($this->dbObjectInfo['type'] === 'f' && $this->useIndex)
			throw new \Exception('This class has no implementation to deal with index where for view or materialized view');

		$rows = $this->selectCommon($fields, $where, $orderBy, $groupBy);
		xml::searchEngineToXmlAll($rows[0], $formId);
	}
}
?>