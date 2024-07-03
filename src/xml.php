<?php

namespace sysaengine;
use \DomDocument;

class xml {
  /**
   * description      Converte um array em XML
   * access           public
   * version          1.0.0
   * return           void
   */
  public static function searchEngineToXmlAll(array $row, string $id_form='', bool $reset=false) : void
  {
		$dom = new DomDocument();
		$searchEngine_field=$dom->createElement('searchEngine_fieldAll');
		$searchSomething=(array_key_exists('none', $row)) ? 0 : 1;

		$form=$dom->createElement('form');
		$searchEngine_field->appendChild($form);

		$form->setAttribute('located', $searchSomething);

		if($reset)
			$form->setAttribute('reset', ($searchSomething == 0) ? '1' : '0');

		if(!empty($id_form))
			$form->setAttribute('id_form', $id_form);

		$dom->appendChild($searchEngine_field);
		foreach($row as $k => $v){
			$field=$dom->createElement('field');
			$field->setAttribute('id', $k);

			$v = is_bool($v) && $v===false ? 0 : $v;

			$text=$dom->createTextNode($v);
			$field->appendChild($text);
			$form->appendChild($field);
		}

		self::xmlOutput($dom);
	}

  /**
   * description      Converte um array em XML
   * access           public
   * version          1.0.0
   * return           void
   */
  private static function xmlOutput(DomDocument $dom)
  {
		if (ob_get_level() > 0) ob_clean();

		if(!headers_sent()){
			header("Content-type: application/xml; charset=utf-8");
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon 26 Dec 2011 01:00:00 GMT");
			header("Pragma: public");
			exit($dom->saveXML());
		}
	}
}