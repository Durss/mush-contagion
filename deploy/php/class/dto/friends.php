<?php 
/**
 * Mapping du flux friends
 * @author	nsun
 * @link	nsun at tac-tac dot org
 * @version	0.1
 */
class friends extends base_dto
{
	/**
	 * Liste d'amis
	 * @var array
	 * @example	<code>$this->frineds[</code> (int) <i>id</i> <code>] = </code> (string) <i>userName</i>
	 */
	public $list;
	
	/**
	 * Vérifie l'argument et attribue une methode de mapping
	 * @param object	$flow	- Flux XML
	 */
	public function friends($flow)
	{
		return $this->init_dto($flow);
	}

	/**
	 * Mapping du flux XML à partir d'un objet de type SimpleXMLElement
	 */
	public function initSimpleXML()
	{
		//On récupère l'objet SimpleXMLElement correspondant au flux
		if(!$xmlAsObject = $this->getXmlAsObject()) return FALSE;
		
		//Assignation des données
		foreach($xmlAsObject as $friend)
		{
			$this->list[ intval($friend['id']) ] = strval($friend['name']);
		}
		
		return TRUE;
	}
}
?>