<?php
/**
	* Este pojeto compõe a biblioteca Sysaengine do Sysadmcom
	*
	* Está atualizado para
	*    PHP 7.4
	*
	* @package      sysaengine
	* @name         html
	* @version      1.0.0
	* @copyright    2020-2025
	* @author       Anderson Arruda < andmarruda@gmail.com >
	*
**/
namespace sysaengine;
use \PDO;
use \PDOStatement;

class html{
/**
 * description 		PDOStatement contendo o resultado do SQL
 * var 				\PDOStatement
*/
private $pdostatement;

/**
 * description 	    Prepara a class html com os dados que irão ser tratados
 * name 			__construct
 * access			public
 * version			1.0.0
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param 			
 * return 			void
**/
	public function __construct(PDOStatement $pdostatement)
	{
		$this->pdostatement = $pdostatement;
	}

/**
 * description 		Converte o statement em datalist para input "Sendo a primeira coluna o data-value e a segunda coluna o texto que irá aparecer"
 * name 			input_datalist
 * access 			public
 * version 			1.0.0
 * author 			Anderson Arruda < andmarruda@gmail.com >
 * param 			
 * return 			string
 */
	public function input_datalist() : string
	{
		if($this->pdostatement->rowCount() == 0)
            return '';

        $opt='';
        while($r=$this->pdostatement->fetch(PDO::FETCH_NUM))
            $opt .= '<option data-value="'. $r[0]. '">'. $r[1]. '</option>';

        return $opt;
	}
}
?>