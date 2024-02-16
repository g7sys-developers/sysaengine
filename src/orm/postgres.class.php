<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine do Sysadmcom
	* pt-BR: App de sistemas do Sysaengine
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		Sysaengine
	* @name 		vo
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine\orm;

final class postgres
{
	/**
	 * SQL para pegar as classes no postgresql
	 * @var				string
	 */
	private $classObject = 'WITH pg_object_class AS(
		SELECT
			pn.nspname AS schema, pc.relname AS class, pc.relkind::varchar AS type
		FROM
			pg_class pc 
			JOIN pg_namespace pn ON pn.oid=pc.relnamespace
		WHERE
			pc.relkind IN(\'r\', \'v\', \'m\') AND nspname NOT IN(\'pg_catalog\', \'information_schema\')
			
		UNION

		SELECT
			r.specific_schema AS schema, r.routine_name AS class, \'FUNC\'::varchar AS type
		FROM
			information_schema.routines AS r
		WHERE
			r.specific_schema NOT IN(\'pg_catalog\', \'information_schema\'))';

	/**
	 * SQL para pegar dados dos atributos do banco de dados
	 * @var 			array
	 */
	private $attrSql = [
		'r' 	=> 'SELECT
						DISTINCT ON(a.attname, d.table_schema, d.table_name) a.attname AS coluna, a.attnotnull, 
						t.typname AS tipo, t.typcategory, d.table_schema, d.table_name, ic.column_default
					FROM 
						pg_attribute a
						JOIN pg_type t ON t.oid = a.atttypid
						JOIN pg_class c ON c.oid = a.attrelid
						JOIN information_schema.tables d ON d.table_name::name = c.relname
						JOIN information_schema.columns AS ic ON ic.table_schema=d.table_schema AND ic.table_name=d.table_name AND ic.column_name=a.attname
					WHERE 
						d.table_type::text=\'BASE TABLE\' AND d.table_schema=? AND d.table_name=? AND
						a.attnum > 0 AND NOT a.attisdropped AND a.attname <> \'starepdad\' AND a.attname <> \'dtastarepdad\'
						AND d.table_schema::text NOT IN(\'pg_catalog\'::text, \'information_schema\'::text) AND NOT a.attisdropped',

		'm' 	=> 'SELECT
						a.attname AS coluna, a.attnotnull, t.typname AS tipo, t.typcategory, c.nspname AS table_schema, 
						b.relname AS table_name, NULL::character varying AS column_default
					FROM
						pg_catalog.pg_attribute AS a
						JOIN pg_type t ON t.oid = a.atttypid
						JOIN pg_catalog.pg_class AS b ON b.oid=a.attrelid
						JOIN pg_catalog.pg_namespace AS c ON c.oid=b.relnamespace
					WHERE
						b.relkind=\'m\' AND attnum>0 AND nspname=? AND relname=? AND NOT a.attisdropped AND
						c.nspname NOT IN(\'pg_catalog\'::text, \'information_schema\'::text)
					ORDER BY
						attnum',

		'v' 	=> 'SELECT
						DISTINCT ON(a.attname, d.table_schema, d.table_name) a.attname AS coluna, a.attnotnull, 
						t.typname AS tipo, t.typcategory, d.table_schema, d.table_name, ic.column_default
					FROM 
						pg_attribute a
						JOIN pg_type t ON t.oid = a.atttypid
						JOIN pg_class c ON c.oid = a.attrelid
						JOIN information_schema.tables d ON d.table_name::name = c.relname
						JOIN information_schema.columns AS ic ON ic.table_schema=d.table_schema AND ic.table_name=d.table_name AND ic.column_name=a.attname
					WHERE 
						d.table_type::text=\'VIEW\' AND d.table_schema=? AND d.table_name=? AND
						a.attnum > 0 AND NOT a.attisdropped AND a.attname <> \'starepdad\' AND a.attname <> \'dtastarepdad\'
						AND d.table_schema::text NOT IN(\'pg_catalog\'::text, \'information_schema\'::text) AND NOT a.attisdropped',

		'FUNC'	=> 'SELECT
						r.specific_schema, r.routine_name, p.ordinal_position, p.parameter_name AS coluna,
						p.data_type, p.udt_name, t.typname AS tipo, t.typcategory,
						p.parameter_mode, 1 as attnotnull, \'\' as column_default
					FROM 
						information_schema.routines AS r
						JOIN information_schema.parameters AS p ON r.specific_name=p.specific_name
						JOIN pg_type AS t ON t.typname=p.udt_name
					WHERE 
						NOT r.specific_schema NOT IN(\'pg_catalog\', \'information_schema\')
						AND r.routine_schema=? AND r.routine_name=?
					ORDER BY 
						p.ordinal_position'
	];

	/**
	 * SQL para verificar se uma index existe
	 * @var				string
	 */
	private $sqlIndexExiste = '
		SELECT
			pn.nspname AS index_schema, pc.relname AS index_name, pn_tab.nspname AS schemaname, pc_tab.relname AS tablename, pi.indkey
		FROM
			pg_index pi
			JOIN pg_class pc ON pc.oid=pi.indexrelid
			JOIN pg_namespace pn ON pn.oid=pc.relnamespace
			JOIN pg_class pc_tab ON pc_tab.oid=pi.indrelid
			JOIN pg_namespace pn_tab ON pn_tab.oid=pc_tab.relnamespace
		WHERE
			pn_tab.nspname NOT IN(\'pg_toast\', \'pg_catalog\', \'information_schema\') AND
			(pc.relname, pn_tab.nspname, pc_tab.relname) = (?, ?, ?)
		;
	';

	/**
	 * SQL que retorna todas as colunas da index "falta converter o pi.indkey acima para uma array utilizavem no aty do pg_attribute - ver engine.class.php do amaengine"
	 * @var				string
	 */

	/**
	 * Retém os valores pesquisados da informação dos objetos
	 * @var				array
	 */
	private $classDbInfo;

	/**
	 * description 		Implementa a classe com dados para a criação de conexões e etc...
	 * name				__construct
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			string $dbname
	 * return			void
	 */
	public function __construct(string $dbname)
	{
		$this->conn = \sysaengine\sysa::cakeConn($dbname);
	}

	/**
	 * description		Pega a conexão utilizada por essa classe
	 * name				getConn
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			
	 * return			\Cake\Datasource\ConnectionInterface
	 */
	public function getConn() : \Cake\Datasource\ConnectionInterface
	{
		return $this->conn;
	}

	/**
	 * description		Pega informações do objeto do banco de dados "table, view, materialized view ou function"
	 * name				getObjectInfo
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			string $relname
	 * param			string $schema=NULL
	 * return			array
	 */
	public function getObjectInfo(string $relname, ?string $schema=NULL) : array
	{
		$stmt = \sysaengine\sysa::parser($this->conn->execute($this->classObject. ' SELECT * FROM pg_object_class WHERE (schema, class)=(?, ?)', [$schema, $relname]))->rowsToArray();
		if(array_key_exists('none', $stmt[0]))
			throw new \Exception('O objeto do banco de dados não foi encontrado. Schema '. $schema. ' Objeto: '. $relname);

		$this->classDbInfo = ['schema' => $stmt[0]['schema'], 'relname' => $stmt[0]['class'], 'type' => $stmt[0]['type']];
		return $this->classDbInfo;
	}

	/**
	 * description 		Pega dados das colunas do postgresql para gerar o VO dinâmico
	 * name				getColumns
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			
	 * return			array
	 */
	public function getColumns() : sqlAttributes
	{
		$classDbInfo = $this->classDbInfo;
		return new sqlAttributes($this->conn->execute($this->attrSql[$classDbInfo['type']], [$classDbInfo['schema'], $classDbInfo['relname']]));
	}

	/**
	 * description		Verifica se existe uma index com o nome inputado
	 * name				hasIndex
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			string $indexName
	 * return			bool
	 */
	public function hasIndex(string $indexName) : bool
	{
		
	}
}
?>