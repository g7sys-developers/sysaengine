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
namespace sysaengine\sql_helper;

use sysaengine\conn;
use \PDO;

abstract class postgres
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
						r.specific_schema AS table_schema, r.routine_name AS table_name, p.ordinal_position, p.parameter_name AS coluna,
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
	private $sqlTableIndex = "
		SELECT
			n.nspname AS schema_name,
			i.indexrelid::regclass AS index_name,
			string_agg(a.attname, ',') AS index_columns
		FROM pg_index i
		JOIN pg_attribute a ON a.attrelid = i.indexrelid
		JOIN pg_class c ON c.oid = i.indrelid
		JOIN pg_namespace n ON n.oid = c.relnamespace
		WHERE
			c.relname = ? AND n.nspname = ? AND i.indexrelid::regclass=?
		GROUP BY
			n.nspname, i.indexrelid
		ORDER BY
			index_name";

	/**
	 * Object with database connection
	 * @var				object
	 */
	protected object $conn;

	/**
	 * Stores db object informations
	 * @var array
	 */
	protected array $dbObjectInfo;

	/**
	 * Valores das colunas da tabela carregada
	 * @var             ['colname' => ['type' => string, 'notnull' => boolean, 'typcategory' => string, 'default' => string, 'setted_in_save' => boolean, 'value' => mixed]]
	 */
    protected $cols = [];

	/**
	 * Informação retornada sobre o schema e a class
	 * @var				array
	 */
	private $objectConstraints = [];

	/**
	 * description 		Implementa a classe com dados para a criação de conexões e etc...
	 * name				__construct
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			
	 * return			void
	 */
	public function __construct(
		protected string $schema,
		protected string $relname
	)
	{
		$this->conn = conn::get_conn();
		if(!$this->getObjectInfo($this->relname, $this->schema))
		{
			list($schema, $relname) = [$this->schema, $this->relname];
			throw new \Exception("Database object not founded: $schema.$relname");
		}

		$this->getColumns();
	}

	/**
	 * description		Pega informações do objeto do banco de dados "table, view, materialized view ou function"
	 * name				getObjectInfo
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			
	 * return			bool
	 */
	protected function getObjectInfo() : bool
	{
		$stmt = $this->conn->prepare($this->classObject. ' SELECT * FROM pg_object_class WHERE (schema, class) = (?, ?)');
		$stmt->execute([$this->schema, $this->relname]);
		if($stmt->rowCount() > 0)
		{
			$this->dbObjectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
			return true;
		}

		return false;
	}

	/**
	 * description 		Pega dados das colunas do postgresql para gerar o VO dinâmico
	 * name				getColumns
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			
	 * return			void
	 */
	protected function getColumns() : void
	{
		$stmt = $this->conn->prepare($this->attrSql[$this->dbObjectInfo['type']]);
		$stmt->execute([$this->schema, $this->relname]);
		if($stmt->rowCount() > 0)
		{
			while($attr = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$this->cols[$attr['coluna']] = [
					'type' 				=> $attr['tipo'],
					'notnull'			=> !empty($attr['attrnotnull']) && $row['attnotnull'] == 1,
					'typcategory'		=> $attr['typcategory'],
					'default'			=> empty($attr['column_default']) && $attr['column_default'] != 0 ? NULL : $attr['column_default'],
					'parameter_mode'	=> $attr['parameter_mode'] ?? NULL,
					'setted_in_save'	=> false,
					'value'				=> NULL
				];
			}
		}
	}

	/**
	 * description		Get column name for existing index
	 * name				hasIndex
	 * access			public
	 * author			Anderson Arruda < andmarruda@gmail.com >
	 * param			string $indexName
	 * return			array
	 */
	public function getIndex(string $indexName) : array
	{
		$stmt = $this->conn->prepare($this->sqlTableIndex);
		$stmt->execute([$this->schema, $this->relname, $indexName]);
		if($stmt->rowCount() == 0)
			return [];

		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return explode(',', $data['index_columns']);
	}

	/**
	 * description 		Verify if object exists in database and returns his relkind
	 * name				objectInfo
	 * access			public
	 * author 			Anderson Arruda < anderson@sysborg.com.br >
	 * param			string $schema
	 * param			string $relname
	 * return			null | string
	 */
	public function objectInfo(string $schema, string $relname) : ?string
	{

	}
}
?>