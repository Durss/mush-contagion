<?php 
/**
 * Mapping du flux user_priv
 * @author	nsun
 * @link	nsun at tac-tac dot org
 * @version	0.1
 */
class user_priv extends base_dto
{
	/**
	 * last login date
	 * @var string
	 * @example	<code>2011-12-23 03:43:43</code>
	 */
	public $login;
	/**
	 * Monnaie Muxxu en possetion de l'utilisateur
	 * @var int
	 */
	public $tokens;
	
	/**
	 * Vérifie l'argument et attribue une methode de mapping
	 * @param object	$flow	- Flux XML
	 */
	public function user_priv($flow)
	{
		$this->init_dto($flow);
	}

	/**
	 * Mapping du flux XML à partir d'un objet de type SimpleXMLElement
	 */
	public function initSimpleXML()
	{
		//On récupère l'objet SimpleXMLElement correspondant au flux
		if(!$xmlAsObject = $this->getXmlAsObject()) return FALSE;
		
		//Assignation des données
		$this->login = strval($xmlAsObject['login']);
		$this->tokens = intval($xmlAsObject['tokens']);
		
		return TRUE;
	}
}
?>