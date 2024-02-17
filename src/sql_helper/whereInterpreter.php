<?php
/**
	* @package 		Sysaengine G7Sys
	* @updated To 	PHP 8.3
	* @name 		whereInterpreter
	* @version 		1.9.0
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
	* 
	*    Contribuídores (ordem alfabética)
	*        Anderson Matheus Arruda < andmarruda at gmail dot com>
	*
**/

namespace sysaengine\sql_helper;

final class whereInterpreter{
	/**
	 * @descripiton 	Operadores de SQL que não necessitam de bind
	 * @var 			array
	*/
	private static $not_binded = array('IS NULL', 'IS NOT NULL');

	/**
	 * @description 	Operadores comuns de SQL
	 * @var 			array
	*/
	private static $common_operators = array('=', '>', '<', '!=', '<>', '>=', '<=', ' IN', ' ANY', ' ALL', ' ILIKE ', ' NOT ILIKE ', ' NOT LIKE ', ' LIKE ', ' NOT BETWEEN ', ' BETWEEN ');

	/**
	 * @description 	Operadores especiais para reconhecimento de strings
	 * @var 			array
	*/
	private static $special_operators = array(' ILIKE ', ' NOT ILIKE ', ' NOT LIKE ', ' LIKE ');

	/**
	 * @var array
	*/
	private static $whereSearchEngine;

	/**
	 * @var array
	*/
	private static $custom_query_where = ['where' => '', 'binds' => []];

	/**
	 * @var string
	*/
	private static $last_logical_operator;

	/**
	 * @var string
	*/
	private static $alias_custom_query_where;

	/**
	 * @description 		Gera where através do xml do searchEngine
	 * @Name:				searchEngineWhere
	 * @Access:				public
	 * @Param				array $xml
	 * @return				void
	*/
	public static function searchEngineWhere($xml)
	{
		if(is_array($xml)){
			$key=key($xml);
			if(array_key_exists('dependence', $xml[$key])){
				foreach($xml[$key]['dependence'] as $dependence){
					$xml2=xml::searchEngineXml($dependence);
					self::searchEngineWhere($xml2);
				}
			}
			$x=(!is_array(self::$whereSearchEngine) || sizeof(self::$whereSearchEngine)==0) ? 0 : sizeof(self::$whereSearchEngine);
			self::$whereSearchEngine[$x]=array(
				'column' => $xml[$key]['id'],
				'default_value' => $xml[$key]['default_value'],
				'id' => $xml[$key]['id']
			);
		}
	}

	/**
	 * @Description:		retorna valor de array do searchEngine
	 * @Name				get_whereSearchEngine
	 * @Access				public
	 * @param				
	 * @return				array['column' => string, 'default_value' => string]
	*/
	public static function get_whereSearchEngine(){
		$temp=self::$whereSearchEngine;
		self::$whereSearchEngine=array();
		return $temp;
	}

	/**
	 * @Description:		Modifica orderby e adiciona grouped by
	 * @Name:				addGroupedByInOrderBy
	 * @Access:			public
	 * @Author:			Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param:				mixed array string $groupedBy
	 * @param:				string $orderby
	 * @return:			string
	*/
	public static function addGroupedByInOrderBy($groupedBy, $orderBy){
		$groupby = (is_array($groupedBy)) ? implode(', ', $groupedBy) : $groupedBy;
		return (is_null($orderBy)) ? $groupby : $groupby. ', '. $orderBy;
	}

	/**
	 * @Description:		Cria um where através de um array baseado nos índices/constraints do banco de dados
	 * @Name:				arrayToIndex
	 * @Access:				public
	 * @Author:				Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param:				array $indexArray
	 * @return:				string
	*/
	public static function arrayToIndex(?array $indexArray) : array
	{
		if(is_null($indexArray))
			return ['where' => NULL, 'fields' => NULL];

		$whereFields = [];

		foreach($indexArray as $key => $value){
			$whereFields[] = ['field' => $value, 'like' => false];
			$indexArray[$key] = $value.'=?';
		}

		$where = implode(' AND ', $indexArray);

		return ['where' => $where, 'fields' => $whereFields];
	}

	/**
	 * @Description:		Executa interpretação do where
	 * @Name:				execute
	 * @Access:				public
	 * @Author:				Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param:				string $where
	 * @param 				array $colunas "colunas do DAO"
	 * @return:				array['where' => string, 'fields' => array]
	*/
	public static function execute(string $where, ?array $colunas=NULL) : array
	{
		$cols = array_keys($colunas);
		$regex = '/(?=\\b|[\\s\\"\\\'])'. implode('(?![\\w_0-9])|(?=\\b|[\\s\\"\\\'])',$cols). '(?![\\w_0-9])/i';
		preg_match_all($regex, $where, $finded_columns, PREG_OFFSET_CAPTURE);
		$finded_columns = $finded_columns[0];
		$max = sizeof($finded_columns);

		$regex_common_operator = '/'. implode('|', self::$common_operators). '/i';
		$regex_not_binded = '/'. implode('|', self::$not_binded). '/';
		$regex_special_operator = '/'. implode('|', self::$special_operators). '/i';
		$binds = [];

		for($x=0; $x<$max; $x++){
			$y=$x+1;
			$info = ($max == $y) ? substr($where, $finded_columns[$x][1], strlen($where)) : substr($where, $finded_columns[$x][1], ($finded_columns[$y][1] - $finded_columns[$x][1]));
			if(preg_match($regex_common_operator, $info) && !preg_match($regex_not_binded, $info)){
				$bind_number = substr_count($info, '?');
				$like = (preg_match($regex_special_operator, $info));
				for($z=0; $z<$bind_number; $z++){
					$value = is_array($colunas[$finded_columns[$x][0]]['value']) ? $colunas[$finded_columns[$x][0]]['value'][$z] : $colunas[$finded_columns[$x][0]]['value'];
					$binds[] = $like ? '%'. $value. '%' : $value;
				}
			}
		}

		return ['where' => $where, 'binds' => $binds];
	}

	/**
	 * @Description:		Executa interpretação do where
	 * @Name:				execute
	 * @Access:			public
	 * @Author:			Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param:				string $fields
	 * @return:			array['where' => string, 'binds' => array]
	*/
	public static function where_binds(array $fields) : array
	{
		$binds = [];
		$where = '';

		foreach($fields as $field){
			if(isset($_REQUEST[$field]) || (isset($_SESSION['sysadmcom']['acs'. $field]) && !isset($_REQUEST[$field]) && !$_SESSION['sysadmcom']['acs'. $field]) || $fields=='codemp'){
				$where.=$field.'=? AND ';
				array_push($binds, ($_REQUEST[$field] ?? $_SESSION['sysadmcom'][$field]));
			}
		}

		$where=rtrim($where, ' AND ');

		return ['where' => $where, 'binds' => $binds];
	}

	/**
	 * @description 		Seta uma alias para as colunas do where do custom query
	 * @Name 				alias_cq_where
	 * @access 			public
	 * @version 			1.0.0
	 * @author 			Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param 				string $alias
	 * @return 			self
	*/
	public static function alias_cq_where(string $alias)
	{
		self::$alias_custom_query_where = (is_null($alias) || $alias == '') ? '' : $alias. '.';
		return __CLASS__;
	}

	/**
	 * @description 		Adiciona um campo com segurança a um where para custom query
	 * @Name 				add_security_field_to_cq
	 * @access 				public
	 * @version 			1.0.0
	 * @author 				Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param 				string $fieldname
	 * @param 				mixed $value
	 * @param 				string $operator
	 * @param 				string $logical_operator
	 * @return 				self
	*/
	public static function add_to_cq_where(string $fieldname, &$value, string $operator, string $logical_operator, bool $verify_security=true)
	{
		$operator = trim($operator);
		$logical_operator = trim($logical_operator);
		if(!in_array($operator, self::$common_operators)){
			//apresentação de erro avisando que não pode prosseguir pois o operador não é reconhecido pelo whereGenerator, caso seja uma nova implantação do PostgreSQL, deve adicionar o operador na lista de operadores conhecidos
		}

		if(!in_array($logical_operator, ['AND', 'OR'])){
			//apresentação de erro avisando que não pode prosseguir pois o operador lógico não é reconhecido pelo whereGenerator, caso seja uma nova implantação do PostgreSQL, deve adicionar o operador na lista de operadores conhecidos
		}

		$value = $verify_security && isset($_SESSION['sysadmcom']['acs'. $fieldname]) && !$_SESSION['sysadmcom']['acs'. $fieldname] ? $_SESSION['sysadmcom'][$fieldname] : $value;
		if(!is_null($value) && $value!='' && strlen($value)>0){
			self::$custom_query_where['where'] .= self::$alias_custom_query_where. $fieldname. $operator. '? '. $logical_operator. ' ';
			array_push(self::$custom_query_where['binds'], $value);
			self::$last_logical_operator = $logical_operator;
		}

		return __CLASS__;
	}

	/**
	 * @description 		Retorna informações contidas no custom_query_where
	 * @Name 				add_security_field_to_cq
	 * @access 				public
	 * @version 			1.0.0
	 * @author 				Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param 				string $fieldname
	 * @param 				mixed $value
	 * @param 				string $operator
	 * @param 				string $logical_operator
	 * @return 				array
	*/
	public static function output_cq_where() : array
	{
		$ret = self::$custom_query_where;
		$ret['where'] = rtrim($ret['where'], ' '. self::$last_logical_operator. ' ');
		self::$custom_query_where = ['where' => '', 'binds' => []];
		self::$last_logical_operator = '';
		self::$alias_custom_query_where = '';
		return $ret;
	}

	/**
	 * @description 			Converte um conjunto de array em binds
	 * @name 				array_to_binds
	 * @access 				public
	 * @version 				1.0.0
	 * @author 				Anderson Matheus Arruda < andmarruda at gmail dot com >
	 * @param 				array $arr
	 * @return 				array['str_bind' => string, 'binds' => array]
	 */
	public static function array_to_binds(array $arr) : array
	{
    	return [
			'str_bind' => rtrim(str_repeat('?, ', count($arr)), ', '),
			'binds' => $arr
		];
	}
}