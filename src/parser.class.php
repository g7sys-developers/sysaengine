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

class parser{
/**
 * description 		PDOStatement contendo o resultado do SQL
 * var 				\PDOStatement
*/
private $statement;

/**
 * description 	    Prepara a class html com os dados que irão ser tratados
 * name 			__construct
 * access			public
 * version			1.0.0
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param 			
 * return 			void
**/
	public function __construct(\PDOStatement $statement)
	{
		$this->statement = $statement;
	}
    
/**
 * description      Retorna as linhas do SQL em array
 * name             rowsToArray
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           array
 */
    public function rowsToArray() : array
    {
        $ret=[];
        while($row=$this->statement->fetch(PDO::FETCH_ASSOC))
            $ret[]=$row;

        if(count($ret) === 0)
            $ret=[['none' => 'Nenhum resultado encontrado!']];

        return $ret;
    }

/**
 * description      Retorna as linhas do SQL em SELECT HTML Combobox
 * name             toComboBox
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $valueFieldName
 * param            string $labelFieldName
 * param            mixed $selectedValue
 * return           string
 */
    public function toComboBox(string $valueFieldName, string $labelFieldName, $selectedValue='') : string
    {
        $ret = '';
        while($row=$this->statement->fetch(PDO::FETCH_ASSOC))
            $ret .= ($selectedValue===$row[$valueFieldName]) ? '<option value="'. $row[$valueFieldName]. '" selected="selected">'. $row[$labelFieldName]. '</option>' : '<option value="'. $row[$valueFieldName]. '">'. $row[$labelFieldName]. '</option>';

        return $ret;
    }

/**
 * Converte o retorno em um option de combobox compatível com a grid 2
 * @name            toComboBoxGrid2
 * @access          public
 * @version         1.0.0
 * @author          Anderson Arruda < andmarruda@gmail.com >
 * @param           string $valueFieldName
 * @param           string $labelFieldName
 * @param           string
 */
    public function toComboBoxGrid2(string $valueFieldName, string $labelFieldName) : array
    {
        $ret = [];
        while($row=$this->statement->fetch(PDO::FETCH_ASSOC))
            $ret[$row[$valueFieldName]] = $row[$labelFieldName];

        return $ret;
    }

/**
 * description      Retorna as linhas do SQL em SELECT HTML Combobox
 * name             toComboBox
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $grid_id
 * return           object
 */
    public function grid2(string $grid_id) : object
    {
        $result = $this->rowsToArray();

        return new class($grid_id, $result) {
            private $arr = ['grid' => []];

            public function __construct(string $grid_id, array $rows)
            {
                $this->arr['grid'][] = [
                    'grid_id' => $grid_id,
                    'datalist' => $rows
                ];
            }

            public function addGrid2(\Cake\Database\Statement\PDOStatement $statement, string $grid_id) : void
            {
                $p = new parser($statement);
                $rows = $p->rowsToArray();

                $this->arr['grid'][]=[
                    'grid_id' => $grid_id,
                    'datalist' => $rows
                ];
            }

            public function __toString()
            {
                return json_encode($this->arr);
            }

            public function outputJson() : void
            {
                header('Content-type: application/json');
                echo (string) $this;
            }
        };
    }
}
?>