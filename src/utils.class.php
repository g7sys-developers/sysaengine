<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: Funções úteis para o framework proprietário Sysaengine dentre elas strTemplateEngine e outros
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		sysaengine
	* @name 		sysaengine\utils
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/
namespace sysaengine;
final class utils{
    /**
     * description      Motor de controle de template de strings simples e ágil baseado na idéia do Blade do Laravel
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $string
     * param            array $variables
     * return           string
     */
    public function strTemplateEngine(string $string, array $variables) : string
    {
        preg_match_all('/(\{\{[0-9a-zA-Z\_\-]{1,}\}\})/', $string, $matches);
        if(count($matches[0]) > 0){
            $string = preg_replace('/(\{\{[0-9a-zA-Z\_\-]{1,}\}\})/', '%s', $string);
            $vals=[];
            foreach($matches[0] as $p){
                $k = str_replace(['{', '}'], '', $p);
                $k = trim($k);
                array_push($vals, ($variables[$k] ?? ''));
            }

            return sprintf($string, ...$vals);
        }

        return $string;
    }
}
?>