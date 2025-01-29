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
use sysaengine\sql_helper\postgres;

class vo extends postgres{
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
  public function __construct(
		string $schema,
		string $relname,
		?array $many=NULL,
		?bool $customIndex=NULL
	) {
		parent::__construct($schema, $relname);

		if ($many) $this->many($many);
		if ($customIndex) $this->customIndex();
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
		if (preg_match('/^frvd_set_/', $name)) {
			$this->$colname = $arguments[0];
			$this->cols[$colname]['setted_in_save'] = $arguments[1] ?? true;
		}
		
		if (preg_match('/^fm_get_/', $name))
			return $this->$colname;

		if ($name === 'many') {
			if (!is_array ($arguments[0])) return;

			foreach ($arguments[0] as $colname => $value) {
				if (array_key_exists($colname, $this->cols)) {
					$this->$colname = $value;
					$this->cols[$colname]['setted_in_save'] = true;
				}
			}
		}
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

	/**
	 * Retorna uma array com os valores necessarios para enviar ao save
	 * 
	 * @access public
	 * @version 1.0.0
	 * @return array
	 */
	public function saveInfo() : array
	{
		$ret = [];
		foreach ($this->cols as $colname => $col)
		{
			if ($col['setted_in_save'] || ($col['notnull'] && !is_null($col['default'])))
			{
				$ret['cols'][] = $colname;
				$ret['updateCols'] .= "$colname = EXCLUDED.$colname, ";
				$ret['valuesInsert'][] = $col['value'];
			}
		}

		$ret['values'] = rtrim(str_repeat('?, ', count($ret['cols'])), ', ');
		$ret['cols'] = implode(', ', $ret['cols']);
		$ret['updateCols'] = rtrim($ret['updateCols'], ', ');

		return $ret;
	}
}
?>