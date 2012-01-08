<?php 
/**
 * Base commune à tous les objets représentant un flux MT
 * @author	nsun
 * @link	nsun at tac-tac dot org
 * @version	0.1
 */
class base_dto
{

	/**
	 * Le flux XML correspondant à l'objet
	 * @var	String
	 */
	private $_xmlDatas = NULL;
	
	/**
	 * Le flux XML de l'objet sous forme d'objet SimpleXMLElement
	 * <br />Doc : http://php.net/manual/fr/book.simplexml.php
	 * @var	Object
	 */
	// private $_xmlAsObject = null;
	
	/**
	 * Vérifie l'argument et attribue une methode de mapping
	 * @param object	$flow	- Flux XML
	 * @return	<li>FALSE	- L'argument indiqué comme flux ne peut subir un mapping</li><li>void</li>
	 */
	public function init_dto($flow)
	{
		if(! is_string($flow)) return FALSE;
		$this->_xmlDatas = $flow;
		return $this->initSimpleXML();
	}
	
	/**
	 * Accès au flux XML
	 * @return	String le flux XML correspondant à l'objet courant
	 */
	public function getXmlData()
	{
		return $this->_xmlDatas;
	}
	
	/**
	 * Accès à l'objet SimpleXMLElement correspondant au flux d'entrée
	 * @return	SimpleXMLElement objet correspondant au flux xml d'entrée
	 */
	public function getXmlAsObject()
	{
		if ($this->_xmlDatas != NULL) {
			return simplexml_load_string($this->_xmlDatas);
		} 
	}
	
	/**
	 * prototype
	 */
	public function initSimpleXML()	{}
}
?>