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
use \Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class sysa{
    /**
     * Class data configuration
     * @var                 array
     */
    private static $config = [
        'url'       => NULL,
        'dbname'    => NULL,
        'port'      => NULL,
        'host'      => NULL,
        'user'      => NULL,
        'pass'      => NULL,
        'logPath'   => NULL,
        'logName'   => NULL,
        'smtp_host' => NULL,
        'smtp_port' => NULL,
        'smtp_user' => NULL,
        'smtp_pass' => NULL,
        'smtp_from' => NULL,
        'smtp_from_name' => NULL,
        'smtp_secure' => NULL,
        'kingsms_token' => NULL,
    ];

    /**
     * description          Nome das classes e das localizações dos arquivos
     * var                  array
     */
    private static $classPath = [
        'sysaengine\amaengine1\utils'                   => __DIR__.'/amaengine_1_utils/utils.class.php',
        'sysaengine\filecontrol'                        => __DIR__.'/filecontrol.class.php',
        'sysaengine\buckets\gcloud'                             => __DIR__.'/buckets/gcloud.class.php',
        'sysaengine\parser'                             => __DIR__.'/parser.class.php',
        'sysaengine\autentiquev2\common'                => __DIR__.'/autentique/common.class.php',
        'sysaengine\history'                            => __DIR__.'/history.class.php',
        'sysaengine\upload'                             => __DIR__.'/upload.class.php',
        'sysaengine\galeria'                            => __DIR__.'/galeria.class.php',
        'sysaengine\validacaoEmail'                     => __DIR__.'/validacaoEmail.class.php',
        'sysaengine\utils'                              => __DIR__.'/utils.php',
        'sysaengine\validacaoCelular'                   => __DIR__.'/validacaoCelular.class.php',
        'sysborg\strUtil'                               => __DIR__.'/../PHPUsefulFunctions/strUtil.class.php',
        'sysaengine\vo'                                 => __DIR__.'/vo.php',
        'sysaengine\dao'                                => __DIR__.'/dao.php',
        'sysaengine\log'                                => __DIR__.'/log.php',
        'sysaengine\sql_helper\postgres'                => __DIR__.'/sql_helper/postgres.php',
        'sysaengine\conn'                               => __DIR__.'/conn.php',
        'sysaengine\traits\DaoCommon'                   => __DIR__.'/traits/DaoCommon.php',
        'sysaengine\traits\DaoFunction'                 => __DIR__.'/traits/DaoFunction.php',
        'sysaengine\sql_helper\whereInterpreter'        => __DIR__.'/sql_helper/whereInterpreter.php',
        'sysaengine\router\Router'                      => __DIR__.'/router/Router.php',
        'sysaengine\ResponseFacade'                     => __DIR__.'/ResponseFacade.php',
        'sysaengine\buckets\DOS3'                       => __DIR__.'/buckets/DOS3.php',
        'sysaengine\buckets\bucketInterface'            => __DIR__.'/buckets/bucketInterface.php',
        'sysaengine\xml'                                => __DIR__.'/xml.php',
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
     * description          Retorna a url base
     * access               public
     * version              1.0.0
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                
     * return               string
     */
    public static function getUrlBase() : string
    {
        return self::$config['url'];
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
        return '<script type="text/javascript" src="'.self::$config['url'].'/js/SheetJS/xlsx.full.min.js"></script>';
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
     * description          Pega o path para a class
     * access               public
     * author               Anderson Arruda < andmarruda@gmail.com >
     * param                string $cls
     * return               string
     */
    public static function getClassPath(string $cls) : ?string
    {
        if(!isset(self::$classPath[$cls]))
            return null;

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
     * Get kingsms token
     * 
     * @return string
     */
    public static function getKingsmsToken() : string
    {
        return self::$config['kingsms_token'];
    }

    /**
     * Generated static call to set values a single time
     * 
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if(!array_key_exists($name, self::$config))
            throw new Exception("Configuration $name not founded");

        if(!is_null(self::$config[$name]))
            return;

        self::$config[$name] = $arguments[0];
    }

    /**
     * Get dbdata 
     * 
     * @return array
     */
    public static function getDbData() : array
    {
        return [
            self::$config['host'],
            self::$config['port'],
            self::$config['dbname'],
            self::$config['user'],
            self::$config['pass']
        ];
    }

    /**
     * Get log information
     * 
     * @return array
     */
    public static function getLogData() : array
    {
        return [
            self::$config['logPath'],
            self::$config['logName']
        ];
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
        if($obj instanceof \PDOStatement)
            return new parser($obj);

        return false;
    }

    /**
     * Envia email
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public static function sendMail(string $to, string $subject, string $body) : bool
    {
        $mail = new PHPMailer(true);
        preg_match('/^.*(?=@)/', $to, $toName);
        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = self::$config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$config['smtp_user'];
            $mail->Password   = self::$config['smtp_pass'];
            $mail->SMTPSecure = self::$config['smtp_secure'];
            $mail->Port       = self::$config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(self::$config['smtp_from'], self::$config['smtp_from_name']);
            $mail->addAddress($to, $toName[0]);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $body;

            $mail->send();
            return true;
        } catch (\Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return false;
    }
}

spl_autoload_register(function($cls){
    $class = sysa::getClassPath($cls);
    if($class)
        require_once $class;
});
?>