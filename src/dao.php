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
		if($this->dbObjectInfo['type'] === 'FUNC')
		{
			return $this->selectFunction(...$arguments);
		}

		if($this->dbObjectInfo['type'] !== 'r' && $this->customIndex())
			throw new \Exception('This class has no implementation to deal with index where for view or materialized view');

		return $this->selectCommon(...$arguments);
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
}
?>