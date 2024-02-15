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

class history{
/**
 * description 		PDOStatement contendo o resultado do SQL
 * var 				\PDOStatement
*/
private $conn;

/**
 * description 	    Prepara a class html com os dados que irão ser tratados
 * name 			__construct
 * access			public
 * version			1.0.0
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param 			
 * return 			void
**/
	public function __construct(string $dbname)
	{
        $this->conn = sysa::cakeConn($dbname);
	}
    
/**
 * description      Insere um histórico
 * name             insertHistory
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            int $id_history_type
 * param            string $history
 * param            int $codigo_usuario
 * param            int $id_system
 * param            ?string $reference_table
 * param            ?string $reference_column
 * param            ?string $reference_value
 * param            ?array $json_history
 * return           bool
 */
    public function insertHistory(int $id_history_type, string $history, int $codigo_usuario, int $id_system, ?string $reference_table, ?string $reference_column, ?string $reference_value, ?array $json_history) : bool
    {
        $sql = 'INSERT INTO history.history(id_history_type, history, ip, codigo_usuario, user_agent, id_system, reference_table, reference_column, reference_value, json_history) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $b=[$id_history_type, $history, $_SERVER['REMOTE_ADDR'], $codigo_usuario, $_SERVER['HTTP_USER_AGENT'], $id_system, $reference_table, $reference_column, $reference_value, json_encode($json_history)];
        try{
            $this->conn->execute($sql, $b);
            return true;
        } catch(\Exception $e){
            return false;
        }
    }
}
?>