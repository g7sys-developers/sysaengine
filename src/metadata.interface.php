<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine do Sysadmcom
	* pt-BR: App de sistemas do Sysaengine
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		Sysaengine
	* @name 		metadata
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine;

interface metadata{
	public function __construct(string $dbname);
    public function getColumns() : \sysaengine\orm\sqlAttributes;
	public function getConn() : \Cake\Datasource\ConnectionInterface;
	public function getObjectInfo(string $relname, ?string $schema=NULL) : array;
}
?>