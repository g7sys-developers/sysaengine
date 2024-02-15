<?php
/**
	* Este pojeto compõe a biblioteca do Sysadmcom - MCFranqueadora
	* 
	* Está atualizado para
	*    PHP 8.0
	*
	* @name         conn
	* @version      1.0.0
	* @copyright    2021-2031
	* @author       Anderson Arruda < andmarruda@gmail.com >
	* 
	*    Contribuídores (ordem alfabética)
	*       Anderson Arruda < andmarruda@gmail.com >
	*
**/

namespace sysaengine;
use \PDO;

if(session_status()!=PHP_SESSION_ACTIVE)
	session_start();

final class query{
/**
 * description      Instâncias PDO com a conexão dos respectivos banco de dados
 * var              ['dbname' => instanceof PDO]
 */
	private static $pdos = [];

/**
 * description      Nome do aplicativo
 * var              string
 */
    private static $appname = 'Sysadmcom';

/**
 * description      Padrões de atributos de comportamento do PDO
 * var              array
 */
    private static $attrs = [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::ATTR_PERSISTENT => FALSE];

/**
 * description      Se a conexão com o banco de dados está com transação ativa
 * var              array[dbname => bool]
 */
    private static $in_transaction = [];
	
/**
 * description      Informações de conexão com o banco de dados
 * var              ['dbname' => ['host' => string, 'port' => int, 'driver' => string, 'user' => string, 'pass' => string]]
 */
	private static $databases = [
        'ribeiraogg' => [
            'host'      => '127.0.0.1',
            'port'      => 5432,
            'driver'    => 'pgsql',
            'user'      => 'usermcdb',
            'pass'      => 'CwvD78sS'
        ],

        'cuiabagg' => [
            'host'      => '127.0.0.1',
            'port'      => 5432,
            'driver'    => 'pgsql',
            'user'      => 'usermcdb',
            'pass'      => 'CwvD78sS'
        ],

        'treinogg' => [
            'host'      => '127.0.0.1',
            'port'      => 5432,
            'driver'    => 'pgsql',
            'user'      => 'usermcdb',
            'pass'      => 'CwvD78sS'
        ]
    ];
	
/**
 * description      Para evitar que a classe seja instanciada de maneira natural, coloca-se o construct como private e força-se ser SINGLETON em junção com outros padrões
 * name             __construct
 * access           private
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           void
 */
	private function __construct(){}

/**
 * description      Gera o DSN Data Source Name para a conexão
 * name             dsn
 * access           private
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * return           string
 */
    private static function dsn(string $dbname) : string
    {
        $appname = self::$appname;
        if(isset($_SESSION['sysadmcom']) && isset($_SESSION['sysadmcom']['nomusu']))
            $appname .= '_'.substr($_SESSION['sysadmcom']['nomusu'], 0, 45);

        switch(self::$databases[$dbname]['driver']){
            case 'pgsql':
                return 'pgsql:host='. self::$databases[$dbname]['host'] .' port='. self::$databases[$dbname]['port'] .' dbname='. $dbname .' application_name='. $appname;
            break;

            case 'mysql':
                return 'mysql:host='. self::$databases[$dbname]['host'] .';port='. self::$databases[$dbname]['port'] .';dbname='. $dbname;
            break;

            case 'oracle':
                return 'oci:dbname=//'. self::$databases[$dbname]['host'] .':'. self::$databases[$dbname]['port'] .'/'. $dbname;
            break;
        }
    }

/**
 * description      Inica uma transaction
 * name             transaction
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * return           bool
 */
    public static function transaction(string $dbname) : bool
    {
        if((self::$in_transaction[$dbname]) ?? false)
            return false;

        if(!isset(self::$pdos[$dbname]))
            self::get_conn($dbname);

        self::$in_transaction[$dbname] = true;
        return self::$pdos[$dbname]->beginTransaction();
    }

/**
 * description      RollBack
 * name             rollBack
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * return           bool
 */
    public static function rollBack(string $dbname) : bool
    {
        if(!self::$in_transaction[$dbname])
            return false;

        if(!isset(self::$pdos[$dbname]))
            self::get_conn($dbname);

        self::$in_transaction[$dbname] = false;
        return self::$pdos[$dbname]->rollBack();
    }

/**
 * description      Commit na transação
 * name             commit
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * return           bool
 */
    public static function commit(string $dbname) : bool
    {
        if(!self::$in_transaction[$dbname])
            return false;

        if(!isset(self::$pdos[$dbname]))
            self::get_conn($dbname);

        self::$in_transaction[$dbname] = false;
        return self::$pdos[$dbname]->commit();
    }

/**
 * description      Pega informações da constante MODO_TESTE
 * name             get_modo_teste
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           string
 */
    public static function get_modo_teste()
    {
        $ret = false;
        try{
            $ret = @constant('MODO_TESTE');
        } catch(Exception $err){} 
        finally{
            return $ret;
        }
    }

/**
 * description      Gera conexão utilizando PDO
 * name             get_conn
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dbname
 * param            ?array $attrs
 * return           PDO
 */
    public static function get_conn(string $dbname, ?array $attrs=NULL) : PDO
    {
        if(!isset(self::$databases[$dbname]))
            throw new Exception('Banco de dados não encontrado!');

        if(!isset(self::$pdos[$dbname])){
            $attrs = isset($attrs) ? array_merge(self::$attrs, $attrs) : self::$attrs;
            self::$pdos[$dbname] = new PDO(self::dsn($dbname), self::$databases[$dbname]['user'], self::$databases[$dbname]['pass'], $attrs);
            $modo_debug = self::get_modo_teste();
            if(!is_null($modo_debug) && $modo_debug)
                self::$pdos[$dbname]->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        }

        return self::$pdos[$dbname];
    }

/**
 * description      Retorna debug do connection
 * name             conn_infos
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           array
 */
    public static function conn_infos() : array
    {
        $in_transaction = array_filter(self::$in_transaction, function($val){
            return $val;
        });

        $last_errors = [];
        foreach(self::$pdos as $db => $pdo)
            $last_errors[$db] = $pdo->errorInfo();

        return [
            'banco_dados_conectados' => array_keys(self::$pdos),
            'banco_dados_transaction_ativa' => array_keys($in_transaction),
            'banco_dados_erros' => $last_errors
        ];
    }

/**
 * description      Para o modo debug do amaengine 2.0
 * name             modo_teste_off
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           void
 */
public static function modo_teste_off() : void
{
    foreach(self::$pdos as $pdo)
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try{
        define('MODO_TESTE', false);
    } catch(Exception $err){}
}

/**
 * description      Inicía o modo debug do amaengine 2.0
 * name             modo_teste_on
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           void
 */
    public static function modo_teste_on() : void
    {
        foreach(self::$pdos as $pdo)
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        try{
            define('MODO_TESTE', true);
        } catch(Exception $err){}
    }

/**
 * description      Gera debug de informações da conexão para proteção de dados
 * name             __debugInfo
 * access           public
 * version          1.0.0
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            
 * return           array
 */
    public function __debugInfo()
    {
        return self::conn_infos();
    }
}
?>