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
use \Exception;

if(session_status()!=PHP_SESSION_ACTIVE)
	session_start();

final class conn{

    /**
     * description      Instâncias PDO com a conexão dos respectivos banco de dados
     * var              ['dbname' => instanceof PDO]
     */
	private static $pdo;

    /**
     * description      Nome do aplicativo
     * var              string
     */
    private static $appname = 'G7Sys';

    /**
     * description      Padrões de atributos de comportamento do PDO
     * var              array
     */
    private static $attrs = [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::ATTR_PERSISTENT => FALSE];

    /**
     * description      Se a conexão com o banco de dados está com transação ativa
     * var              array[dbname => bool]
     */
    private static $in_transaction = false;

    /**
     * Flag to say that is on debug mode or not
     * @var boolean
     */
    private static $debugMode = false;
	
    /**
     * Sets amaengine in sysaengien
     * name             setAmaengine
     * access           public
     * author           Anderson Arruda < anderson@sysborg.com.br >]
     * @param           object $amaengine
     * @return          void
     */
    public static function setAmaengine(string $amaengine) {
        require_once($amaengine);
    }

    /**
     * Return database informations
     * @return array
     */
    public static function databaseInfo()
    {
        return [
            \amaengine::env('DB_HOST'),
            \amaengine::env('DB_PORT'),
            \amaengine::env('DB_NAME'),
            \amaengine::env('DB_USER'),
            \amaengine::env('DB_PASS')
        ];
    }

    /**
     * description      Gera o DSN Data Source Name para a conexão
     * name             dsn
     * access           private
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dbname
     * return           string
     */
    private static function dsn() : string
    {
        $appname = self::$appname;
        if(isset($_SESSION['sysadmcom']) && isset($_SESSION['sysadmcom']['nomusu']))
            $appname .= '_'.substr($_SESSION['sysadmcom']['nomusu'], 0, 45);

        list($host, $port, $name, $user, $pass) = self::databaseInfo();

        return "pgsql:host=$host port=$port dbname=$name application_name=$appname";
    }

    /**
     * description      Retorna o DSN compatível com o CAKEPHP
     * name             cakePhpDSN
     * access           public
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dbname
     * return           string
     */
    public static function cakePhpDSN() : string
    {
        list($host, $port, $name, $user, $pass) = self::databaseInfo();
        return "postgres://$user:$pass@$host:$port/$name";
    }

    /**
     * description      Inica uma transaction
     * name             transaction
     * access           public
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dbname
     * return           bool
     */
    public static function transaction() : bool
    {
        if((self::$in_transaction) ?? false)
            return false;

        if(!isset(self::$pdo))
            self::get_conn();

        self::$in_transaction = true;
        return self::$pdo->beginTransaction();
    }

    /**
     * description      RollBack
     * name             rollBack
     * access           public
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dbname
     * return           bool
     */
    public static function rollBack() : bool
    {
        if(!self::$in_transaction)
            return false;

        self::$in_transaction = false;
        return self::$pdo->rollBack();
    }

    /**
     * description      Commit na transação
     * name             commit
     * access           public
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dbname
     * return           bool
     */
    public static function commit() : bool
    {
        if(!self::$in_transaction)
            return false;

        self::$in_transaction = false;
        return self::$pdo->commit();
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
    public static function get_conn(?array $attrs=NULL) : PDO
    {
        try {
            list($host, $port, $name, $user, $pass) = self::databaseInfo();
            if(!isset(self::$pdo)){
                $attrs = isset($attrs) ? array_merge(self::$attrs, $attrs) : self::$attrs;
                self::$pdo = new PDO(self::dsn($dbname), $user, $pass, $attrs);

                if(self::$debugMode) self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            }

            return self::$pdo;
        } catch(Exception $e)
        {
            var_dump($e); die;
        }
        
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
        self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        self::$debugMode = false;
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
        
        self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        self::$debugMode = true;
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