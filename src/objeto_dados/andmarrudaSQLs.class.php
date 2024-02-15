<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: Verificações de valores inputados no DAO baseado 100% nas premissas do PostgreSQL
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		sysaengine\dao
	* @name 		verifications
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine\dao;

final class andmarrudaSQLs{
    /**
     * Código retirado do Github do Sysborg - https://github.com/sysborg/PostgreSQL-UTILS/blob/main/listing_objects.sql
     */
	const LISTINING_OBJECTS = "SELECT
        pn.nspname, pc.relname, CASE WHEN pc.relkind='r' THEN 'TABLE' WHEN pc.relkind='v' THEN 'VIEW' WHEN pc.relkind='m' THEN 'MATERIALIZED VIEW' ELSE 'DESCONHECIDO' END::varchar AS object_type
    FROM
        pg_catalog.pg_class pc
        JOIN pg_catalog.pg_namespace pn ON pn.oid=pc.relnamespace
    WHERE
        pn.nspname NOT IN('pg_catalog', 'information_schema') AND pc.relkind IN('r', 'v', 'm')
    UNION
    SELECT 
        pn.nspname, pp.proname, 'FUNCTION'::varchar AS object_type
    FROM 
        pg_proc pp
        JOIN pg_namespace pn ON pn.oid=pp.pronamespace
    WHERE
        pn.nspname NOT IN('pg_catalog', 'information_schema')";
}
?>