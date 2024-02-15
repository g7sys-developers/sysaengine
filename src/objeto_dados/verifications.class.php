<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: Verificações de valores inputados no DAO baseado 100% nas premissas do PostgreSQL
	*
	* Está atualizado para
	*    PHP 7.4
	*
	* @package 		sysaengine\dao
	* @name 		verifications
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine\dao;

final class verifications{
	private $regExCategory = [
        'D' => [
            'date' => '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}/$',
            'time' => '',
            'timestamp' => '',
            'timestamptz' => '',
            'timetz' => '',
            'time_stamp' => ''
        ],
        'N' => [
            'int2' => '/^[0-9]$/',
            'int4' => '/^[0-9]$/',
            'int8' => '/^[0-9]$/',
            'oid' => '/^[0-9]$/',
            'float4' => '/^[0-9\.]$/',
            'float8' => '/^[0-9\.]$/',
            'numeric' => '/^[0-9\.]$/'
        ]
    ];

    private $intMaxLength = [
        'int2' => [-32768, 32767],
        'int4' => [-2147483648, 2147483647],
        'int8' => [-9223372036854775808, 9223372036854775807]
    ];
}
?>