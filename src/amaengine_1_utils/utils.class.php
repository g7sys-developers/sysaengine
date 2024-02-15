<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: Utilitários para verificação de arquivos do antigo framework
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		sysaengine
	* @name 		utils
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine\amaengine1;

require_once __DIR__ .'/../filecontrol.class.php';
use \sysaengine\filecontrol;

final class utils{
    /**
     * description      Lista todos os arquivos DAO que existem
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dao_path
     * return           array
     */
    public function listaDaos(string $dao_path) : array
    {
        return (new filecontrol())->listarArquivos($dao_path, false, '/^DAO_/');
    }

    /**
     * description      Lista todos os arquivos VO que existem
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $dao_path
     * return           array
     */
    public function listaVos(string $vo_path) : array
    {
        return (new filecontrol())->listarArquivos($vo_path, false, '/^VO_/');
    }
}
?>