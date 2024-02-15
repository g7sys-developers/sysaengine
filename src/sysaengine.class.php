<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine do Sysadmcom
	* pt-BR: App de sistemas do Sysaengine
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		Sysaengine
	* @name 		sysa
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine;
require_once __DIR__. '/conn.class.php';
require_once __DIR__. '/../cakephp4/vendor/autoload.php';

use \Cake\Datasource\ConnectionManager AS cm;
use \Cake\Mailer\TransportFactory;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\AssociationCollection;
use Cake\ORM\BehaviorRegistry;
use Cake\ORM\Table;

Configure::write('App.namespace', '~');

final class sysa{
    /**
     * description          URL base para o Sysaengine
     * var                  string
     */
    private static $urlBase = 'https://sysadmcom.com.br/sysadmcom/apis/sysaengine/';

    /**
     * description          URL base para o Sysadmcom
     * var                  string
     */
    const URL_SYSADMCOM = 'https://sysadmcom.com.br/sysadmcom/versoes/';

    /**
     * description          Caminho local para a pasta de apis disponíveis
     * var                  string
     */
    private static $apiPath = '/var/www/html/sysadmcom/apis/';

    /**
     * description          apis proibidas de serem invocadas
     * var                  array
     */
    private static $deniedAPIs = [
        'amaengine2',
        'cakephp4',
        'googleapi',
        'novo_cakephp4',
        'phpmailer',
        'sysaengine'
    ];

    /**
     * description 			Nome do banco de dados conectado pelo sistema
     * var 					string
     */
	private static $dbConnected;

    /**
     * description          Nome da aplicação que está utilizando o Sysaengine
     * var                  string
     */
    private static $appName;

    /**
     * description          Nome das classes e das localizações dos arquivos
     * var                  array
     */
    private static $classPath = [
        'sysaengine\amaengine1\utils'                   => __DIR__.'/amaengine_1_utils/utils.class.php',
        'sysaengine\filecontrol'                        => __DIR__.'/filecontrol.class.php',
        'sysaengine\gcloud'                             => __DIR__.'/gcloud.class.php',
        'sysaengine\parser'                             => __DIR__.'/parser.class.php',
        'sysaengine\parserORM'                          => __DIR__.'/parserORM.class.php',
        'sysaengine\autentiquev2\common'                => __DIR__.'/autentique/common.class.php',
        'sysaengine\history'                            => __DIR__.'/history.class.php',
        'sysaengine\upload'                             => __DIR__.'/upload.class.php',
        'sysaengine\galeria'                            => __DIR__.'/galeria.class.php',
        'sysaengine\validacaoEmail'                     => __DIR__.'/validacaoEmail.class.php',
        'sysaengine\utils'                              => __DIR__.'/utils.class.php',
        'sysaengine\validacaoCelular'                   => __DIR__.'/validacaoCelular.class.php',
        'sysborg\strUtil'                               => __DIR__.'/../PHPUsefulFunctions/strUtil.class.php',
        'sysaengine\vo'                                 => __DIR__.'/vo.class.php',
        'sysaengine\dao'                                => __DIR__.'/dao.class.php',
        'sysaengine\orm\postgres'                       => __DIR__.'/orm/postgres.class.php',
        'sysaengine\metadata'                           => __DIR__.'/metadata.interface.php',
        'sysaengine\orm\sqlAttributes'                  => __DIR__.'/orm/sqlAttributes.class.php',
        'sysaengine\mongodb'                            => __DIR__.'/mongodb.class.php'
    ];

    /**
     * description          Nome dos meses em ordem de seus números
     * var                  array
     */
    private static $mesNome = [
        '1' => 'Janeiro',
        '2' => 'Fevereiro',
        '3' => 'Março',
        '4' => 'Abril',
        '5' => 'Maio',
        '6' => 'Junho',
        '7' => 'Julho',
        '8' => 'Agosto',
        '9' => 'Setembro',
        '10' => 'Outubro',
        '11' => 'Novembro',
        '12' => 'Dezembro'
    ];

    /**
     * description          Classe de metadados para DAO/VO dinâmico e qualquer outra classe que precisar
     * var                  array
     */
    private static $metadataClass = [
        'postgres' => \sysaengine\orm\postgres::class, //postgresql
        'mysql'    => '', //mysql  "precisa ser desenvolvido caso precise"
        'dblib'    => '', //mssql  "precisa ser desenvolvido caso precise"
        'oci'      => '', //oracle "precisa ser desenvolvido caso precise"
    ];

    /**
     * description          Chama apis externas pré instaladas
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string apiName
     * return               void
     */
    public static function invocaApi(string $apiName) : void
    {
        $path = self::$apiPath. '/'. $apiName;
        if(in_array($apiName, self::$deniedAPIs) || !is_dir($path))
            return;

        require $path. '/vendor/autoload.php';
    }

    /**
     * description          Retorna a url base
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               string
     */
    public static function getUrlBase() : string
    {
        return self::$urlBase;
    }

    /**
     * Invoca a classe SheetJS
     * @access              public
     * @version             1.0.0
     * @author              Anderson Arruda < andmarruda@gmail.com >
     * @param               
     * @return              string
     */
    public static function getSheetJS() : string
    {
        return '<script type="text/javascript" src="'.self::$urlBase.'/js/SheetJS/xlsx.full.min.js"></script>';
    }

    /**
     * description          Converte o mês para seu texto em portugues
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                int $numeroMes
     * return               string
     */
    public static function nomeMesPTBr(int $numeroMes) : string
    {
        return self::$mesNome[$numeroMes] ?? '';
    }

    /**
     * description          Retorna o nome da alias através do nome do banco de dados
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $dbname
     * return               string
     */
    public static function dbNameToAlias(string $dbname) : string
    {
        $i = array_search($dbname, self::$alias);
        return ($i === false) ? '' : $i;
    }

    /**
     * description          Pega o nome do banco de dados conectado
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               string
     */
    public static function getDBName() : ?string
    {
        return self::$dbConnected;
    }

    /**
     * description          Pega o nome da aplicação utilizando o sysaengine
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               string
     */
    public static function getAppName() : ?string
    {
        return self::$appName;
    }

    /**
     * description          Seta o nome do banco de dados conectado
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $dbName
     * return               void
     */
    public static function setDBName(?string $dbname) : void
    {
        self::$dbConnected = $dbname ?? 'ribeiraogg';
    }

    /**
     * description          Seta o nome da aplicação utilizando o sysaengine
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $appName
     * return               void
     */
    public static function setAppName(string $appname) : void
    {
        self::$appName = $appname;
    }

    /**
     * description          Pega o path para a class
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $cls
     * return               string
     */
    public static function getClassPath(string $cls) : string
    {
        if(!isset(self::$classPath[$cls]))
            throw new \Exception('A classe '. $cls. ' não foi encontrada. Nem seu path foi encontrado para autoload! Verifique a lista de arquivos do sysaengine\\sysa.');

        return self::$classPath[$cls];
    }

    /**
     * description          Da saída de array em formato JSON
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               never
     */
    public static function outputJson(array $output)
    {
        try{ ob_clean(); } catch(Exception $err){}

        header('Content-type: application/json');
        echo json_encode($output);
        die;
    }

    /**
     * description          Pega o caminho para a versão release
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               string
     */
    public static function pegaSysPath() : string
    {
        return self::URL_SYSADMCOM. self::pegaVersaoAtual(1);
    }

     /**
     * description          Pega o caminho para a versão beta
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                int $id_system
     * return               string
     */
    public static function pegaSysBetaPath() : string
    {
        return self::URL_SYSADMCOM. self::pegaVersaoBeta(1);
    }

    /**
     * description          Pega a versão release do sistema escolhido
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                int $id_system
     * return               string
     */
    public static function pegaVersaoAtual(int $id_system) : string
    {
        $sql = 'SELECT * FROM development.sysadmcom_versao WHERE id_system=? AND status_atualizacao';
        $conn = self::cakeConn();
        $stmt = $conn->execute($sql, [$id_system]);
        if($stmt->rowCount() == 0)
            return '';

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['versao'];
    }

    /**
     * description          Pega a versão beta do sistema escolhido
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                int $id_system
     * return               string
     */
    public static function pegaVersaoBeta(int $id_system) : string
    {
        $sql = 'SELECT * FROM development.sysadmcom_versao WHERE id_system=? AND status_beta_version ORDER BY id_sysadmcom_versao DESC LIMIT 1';
        $conn = self::cakeConn();
        $stmt = $conn->execute($sql, [$id_system]);
        if($stmt->rowCount() == 0)
            return '';

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['versao'];
    }

    /**
     * description          Pega sistema de ajuda para acesso de metadados do banco de dados
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $dbname
     * @return              ?\sysaengine\metadata
     */
    public static function getMetaData(string $dbname) : ?\sysaengine\metadata
    {
        $dsn = conn::cakePhpDSN($dbname);
        preg_match('/^.*(?=:\/\/)/', $dsn, $match);
        return new self::$metadataClass[$match[0]]($dbname);
    }

    /**
     * description          Gera a conexão com o CakePHP - estilo custom query
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                ?string $dbname
     * param                bool $logging
     * return               \Cake\Datasource\ConnectionInterface
     */
    public static function cakeConn(?string $dbname=NULL, bool $logging=false) : \Cake\Datasource\ConnectionInterface
    {
        $dbname = is_null($dbname) ? self::getDBName() : $dbname;
        if(is_null($dbname))
            throw new \Exception('Não é possível conectar em um banco de dados sem setar o nome do mesmo! Verifique a utilização do nome do banco de dados e tente novamente mais tarde!');

        if(is_null(cm::getConfig($dbname)))
            cm::setConfig($dbname, ['url' => conn::cakePhpDSN($dbname)]);

        $conn = cm::get($dbname);

        return $conn;
    }

    /**
     * description          Cria uma classe anônima que retorna uma Table do cakephp
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $table
     * return               object
     */
    public static function table(string $table, ?string  $dbname=NULL) : object
    {
        return new class($table, self::cakeConn($dbname)) extends Table {
            public function __construct(string $tableName, \Cake\Database\Connection $conn)
            {
                $this->_table=$tableName;
                $this->_connection = $conn;
                $this->_alias = 'ribeiraogg';
                $this->_behaviors = new BehaviorRegistry();
                $this->_behaviors->setTable($this);
                $this->_associations = new AssociationCollection();
            }
        };
    }

    /**
     * description          Retorna a classe parser correspondente ao método de entrada
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                object $obj
     * return               object | false
     */
    public static function parser(object $obj)
    {
        if($obj instanceof \Cake\Database\Statement\PDOStatement)
            return new parser($obj);

        if($obj instanceof \Cake\ORM\Query)
            return new parserORM($obj);

        return false;
    }

    /**
     * description          Generates configuration for mailer
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $email
     * param                string $className
     * return
     */
    public static function configEmail(string $email, string $className='Smtp') : string
    {
        $conn = self::cakeConn('ribeiraogg');
        $sql = 'SELECT * FROM development.cadastro_email WHERE username=? LIMIT 1';
        $stmt = $conn->execute($sql, [$email]);
        if($stmt->rowCount() == 0)
            throw new \Exception('Email não encontrado!');

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        TransportFactory::setConfig($email, [
            'host' => ($row['smtp_secure']=='ssl') ? $row['smtp_secure']. '://'. $row['host'] : $row['host'],
            'port' => $row['port'],
            'username' => $row['username'],
            'password' => $row['password_email'],
            'className' => $className,
            'tls' => $row['smtp_secure']=='tls' ? true : NULL
        ]);

        return $email;
    }
}

spl_autoload_register(function($cls){
    require_once \sysaengine\sysa::getClassPath($cls);
});
?>