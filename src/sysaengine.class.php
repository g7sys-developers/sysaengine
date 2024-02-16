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
     * description          Nome das classes e das localizações dos arquivos
     * var                  array
     */
    private static $classPath = [
        'sysaengine\amaengine1\utils'                   => __DIR__.'/amaengine_1_utils/utils.class.php',
        'sysaengine\filecontrol'                        => __DIR__.'/filecontrol.class.php',
        'sysaengine\gcloud'                             => __DIR__.'/gcloud.class.php',
        'sysaengine\parser'                             => __DIR__.'/parser.class.php',
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
        'sysaengine\orm\postgres'                       => __DIR__.'/orm/postgres.class.php'
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
}

spl_autoload_register(function($cls){
    require_once \sysaengine\sysa::getClassPath($cls);
});
?>