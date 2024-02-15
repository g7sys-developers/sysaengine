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

require_once __DIR__. '/../mongodb/vendor/autoload.php';

class mongodb{
/**
 * usuario e senha do mongodb
 * @var     array
 */
    private $conexao = [
        'default' => [
            'user' => 'usermcdb',
            'pass' => 'CwvD78sS',
            'host' => 'cluster0.73ve4ol.mongodb.net'
        ]
    ];

/**
 * conexão do mongodb
 * @var     \MongoDB\Client
 */
private $conn;

/**
 * armazena todos os banco de dados existentes
 * @var     array
 */
private $dbExistentes;

/**
 * banco de dados selecionado
 * @var     \MongoDB\Database
 */
private $dbSelecionado;

/**
 * description      Gera a URI de conexão do mongodb
 * name             uri
 * access           private
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $conexao='default'
 * return           string
 */
    private function uri(string $conexao='default') : string
    {
        return "mongodb+srv://{$this->conexao[$conexao]['user']}:{$this->conexao[$conexao]['pass']}@{$this->conexao[$conexao]['host']}/?retryWrites=true&w=majority";
    }

/**
 * description      Pega a conexão com o mongodb
 * name             conectar
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $conexao='default'
 * return           object
 */
    public function conectar(string $conexao='default') : \MongoDB\Client
    {
        $this->conn = new \MongoDB\Client($this->uri($conexao));
        $this->dbExistentes = $this->conn->listDatabaseNames();
        return $this->conn;
    }

/**
 * description      Retorna o objeto com o banco de dados selecionado
 * name             selDB
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * @return          \MongoDB\Database
 */
    public function selDB(string $dbname) : \MongoDB\Database
    {
        if(!in_array($dbname, $this->dbExistentes->getArrayCopy())){
            throw new \Exception("O database {$dbname} não foi encontrado!");
        }

        $this->dbSelecionado = $this->conn->$dbname;
        return $this->dbSelecionado;
    }
}
?>