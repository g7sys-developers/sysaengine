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
namespace sysaengine\orm;

final class sqlAttributes{
    /**
     * Dados das coluns
     * @var         \Cake\Database\Statement\PDOStatement
     */
    private $stmt;

    /**
     * Recebe o STMT com os dados das colunas
     * name         __construct
     * access       public
     * author       Anderson Arruda < andmarruda@gmail.com >
     * param        \Cake\Database\Statement\PDOStatement $stmt
     * return       void
     */
    public function __construct(\Cake\Database\Statement\PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Retorna os dados dos atributos do banco de dados pra array
     * name         toColumns
     * access       public
     * author       Anderson Arruda < andmarruda@gmail.com >
     * param        
     * return       array
     */
    public function toColumns() : array
    {
        $arr = [];
        while($row=$this->stmt->fetch(\PDO::FETCH_ASSOC)){
            $arr[$row['coluna']] = [
                'type'              => $row['tipo'],
                'notnull'           => !empty($row['attnotnull']) && $row['attnotnull'] == 1,
                'typcategory'       => $row['typcategory'],
                'default'           => empty($row['column_default']) && $row['column_default'] != 0 ? NULL : $row['column_default'],
                'parameter_mode'    => $row['parameter_mode'] ?? NULL,
                'setted_in_save'    => false,
                'value'             => NULL
            ];
        }

        return $arr;
    }
}
?>