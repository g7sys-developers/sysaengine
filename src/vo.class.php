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
namespace sysaengine;

class vo{
/**
 * Nome da conexão do banco de dados do VO
 * @var             string
 */
    protected $dsn;

/**
 * Nome do schema que contém a tabela
 * @var            string
 */
    protected $schema;

/**
 * Nome da tabela, função, view, etc...
 * @var             string
 */
    protected $relname;

/**
 * Conexão com o banco de dados através do cakephp
 * @var             \Cake\Datasource\ConnectionInterface
 */
    protected $conn;

/**
 * Valores das colunas da tabela carregada
 * @var             ['colname' => ['type' => string, 'notnull' => boolean, 'typcategory' => string, 'default' => string, 'setted_in_save' => boolean, 'value' => mixed]]
 */
    protected $cols = [];

/**
 * Informação retornada sobre o schema e a class
 * @var				array
 */
	private $classObject = [];

/**
 * Armazena a classe de metadados
 * @var 			\sysaengine\metadata
 */
	protected $metadado;

/**
 * description      Acessa a conexão e pega as informações relevantes para o uso do VO
 * name             __construct
 * access           public
 * author           Anderson Arruda < andmarruda@gmail.com >
 * param            string $dsn
 * param            string $schema
 * param            string $table
 * return           void
 */
    public function __construct(string $dsn, string $schema, string $relname)
    {
		$this->metadado = sysa::getMetaData($dsn);
        $this->conn = $this->metadado->getConn();
		$this->classObject = $this->metadado->getObjectInfo($schema, $relname);
		$this->schema = $this->classObject['schema'];
		$this->relname = $this->classObject['relname'];

		$this->cols = $this->metadado->getColumns()->toColumns();
    }

/**
 * description 		Adicionado sistema intermediário para frvd_set antigo do DAO/VO 1.0
 * name				__call
 * access			public
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param			string $name
 * param			array $arguments
 * return			mixed
 */
public function __call($name, $arguments)
{
	$colname = preg_replace('/^(frvd_set_)|(fm_get_)/', '', $name);
	if(preg_match('/^frvd_set_/', $name)){
		$this->$colname = $arguments[0];
		$this->cols[$colname]['setted_in_save'] = $arguments[1] ?? true;
	}
	
	if(preg_match('/^fm_get_/', $name))
		return $this->$colname;
}

/**
 * description		Pega os valores setados posteriormente no VO
 * name				__get
 * access			public
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param			string $name
 * return			mixed
 */
	public function __get($name)
	{
		return $this->cols[$name]['value'] ?? NULL;
	}

/**
 * description 		Seta os valores as colunas do VO
 * name				__set
 * access			public
 * author			Anderson Arruda < andmarruda@gmail.com >
 * param			string $name
 * param			mixed $value
 * return			void
 */
	public function __set($name, $value)
	{
		if(!array_key_exists($name, $this->cols))
			throw new \Exception('Não foi encontrada a coluna '. $name. ' na classe '. $this->schema. '.'. $this->relname);

		if(!is_null($value) && !is_array($value)){
			if(in_array($this->cols[$name]['type'], ['int4', 'int2', 'int8']) && !preg_match('/^[0-9]*$/', $value)){
				throw new \Exception('A coluna '. $name. ' espera valor int, array ou nulo. O tipo '. gettype($value). ' foi setado');
			}

			if(in_array($this->cols[$name]['type'], ['float4', 'float8', 'numeric']) && !preg_match('/^[+-]?([0-9]*[.])?[0-9]+$/', $value)){
				throw new \Exception('A coluna '. $name. ' espera valor float, array ou nulo. O tipo '. gettype($value). ' foi setado');
			}

			if($this->cols[$name]['type']=='bool')
				$value = (int) ((bool) $value);
		}

		$this->cols[$name]['value'] = $value;
		$this->cols[$name]['setted_in_save'] = true;
	}
}
?>