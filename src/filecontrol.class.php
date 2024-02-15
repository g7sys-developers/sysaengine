<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas que controla arquivos relacionados ao Sysaengine ou uploads
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		sysaengine
	* @name 		filecontrol
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine;

class filecontrol{
/**
 * description 			Caminho base para os arquivos do amaengine 2
 * access 				private
 * author 				Anderson Arruda < andmarruda@gmail.com >
 * var 					string
 */
	private $base_path = 'sysadmcom.com.br/sysadmcom/apis/amaengine2/';
	
/**
 * description 			Caminho para os arquivos auxiliares do Amaengine 2
 * access 				protected
 * Author 				Anderson Arruda < andmarruda@gmail.com >
 * var 					['module' => 'path']
 */
	private $path = [
		'js' => 'js/'
	];

/**
 * description 			Pega o caminho externo para arquivos auxiliares do Amaengine 2
 * access 				public
 * version 				1.0.0
 * author 				Anderson Arruda < andmarruda@gmail.com >
 * param 				?string $module				
 * return 				string
 */
	public function module_path(?string $module) : string
	{
		return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http'). '://'. $this->base_path. (is_null($module) ? '' : ($this->path[$module] ?? ''));
	}

/**
 * description       	Lista todos os arquivos de um diretório com possibilidade de filtrar
 * access				public
 * version				1.0.0
 * author				Anderson Arruda < andmarruda@gmail.com >
 * param 				string $path - "caminho para os arquivos"
 * param                bool $listaDiretorio - "Verifica se lista diretório ou não"
 * param                string $filtro - "regex para filtrar os arquivos encontrados"
 * return				array
**/
	public function listarArquivos(string $path, bool $listaDiretorio=false, ?string $filtro) : ?array
	{
		if(is_dir($path)){
            $arquivos = scandir($path);
            if(!is_null($filtro)){
                $arquivos =  array_filter($arquivos, function($name) use($path, $filtro, $listaDiretorio){
                    return preg_match($filtro, $name) && ($listaDiretorio && is_dir($path. $name) || !$listaDiretorio && !is_dir($path. $name));
                });
            }

            return $arquivos;
        }

        return null;
	}
}
?>