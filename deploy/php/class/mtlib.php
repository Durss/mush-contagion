<?php

/**
 * <p>Interraction entre API-Muxxu et Application PHP</p>
 * @author	nsun
 * @link	nsun at tac-tac dot org
 * @version	0.91
 */
class mtlib
{
	/**
	 * Etat "public" d'un flux
	 * @var int
	 */
	const PUBLIC_FLOW = 1;
	
	/**
	 * Etat "Privé" d'un flux
	 * @var int
	 */
	const PRIVATE_FLOW = 2;
	
	/**
	 * Nom de fichier du document de définition des flux
	 * @var	string
	 * @example	muxxu.general.API.Request.xml
	 */
	const XMLFILE_FLEW_LIST = 'muxxu.general.API.Request.xml';
	
	/**
	 * Etat du Dev-Mode
	 * @var boolean
	 */
	private $_devMode = FALSE;
	
	/**
	 * Mode local actif
	 * @var boolean
	 */
	private $_localMode;
	
	/**
	 * Slash (/) ou antislash (\) défini selon OS
	 * @var string
	 */
	private $_slash = NULL;
	
	//Données de l'application
	/**
	 * Nom de l'application
	 * @var	string
	 * @example	<pre>_appName = 'muxxurank'</pre>
	 */
	private $_appName = NULL;
	
	/**
	 * Clé privée de l'application
	 * @var	string
	 * @example	<pre>_privKey = '1a2b3c4d5e6f7e8d9c8b7a6b5c4d3e2f'</pre>
	 */
	private $_privKey = NULL;
	
	/**
	 * Etat du Refresh-Mode
	 * @var	boolean
	 */
	private $_refresh = FALSE;
	
	/**
	 * Cache des données au format XML
	 * @var	Array
	 * @example	<pre>_xmlAsObject [ <i>(int)</i> <b>id</b> ] [ <i>(string)</i> <b>flowName</b> ]<hr/>_xmlAsObject = Array(<br/>	3916 => Array(<br/>		'user' => '<xml><user id="3916" />(...)</xml>',<br/>		'user_priv' =>'<xml><user_priv id="3916" />(...)</xml>',<br/>	),<br/>	5625 => Array(<br/>		'user' => '<xml><user id="5625" />(...)</xml>',<br/>	),<br/>)</pre>
	 */
	private $_xmlDatas = Array();
	
	/**
	 * Log (effectif si devmode est activé)
	 * Journal des requêtes effectuées vers l'API
	 * @var Array
	 */
	private $_log = Array();
	/*
	 * Cache des données sous forme d'objet SimpleXMLElement
	 * <br />Doc : http://php.net/manual/fr/book.simplexml.php
	 * @var	Array
	 * @example	<pre>_xmlAsObject [ <i>(int)</i> <b>id</b> ] [ <i>(string)</i> <b>flowName</b> ]<hr/>_xmlAsObject = Array(<br/>	3916 => Array(<br/>		'user' => SimpleXMLElement Object(...),<br/>		'user_priv' => SimpleXMLElement Object(...),<br/>	),<br/>	5625 => Array(<br/>		'user' => SimpleXMLElement Object(...),<br/>	),<br/>)</pre>
	 
	private $_xmlAsObject = Array();
	*/
	/**
	 * Messages
	 * @var	Array
	 * @example	<pre>_notice = Array(<br/>	'mtlib:API_NEEDAUTH',<br/>	'mtlib:INVALID_XML',<br/>)</pre>
	 */
	private $_notice = Array();
	
	/**
	 * <p>Liste des flux supportés</p>
	 * @var	Array
	 * @example	<pre>_flowAvaliable = Array(<br/>	'user' => PUBLIC_FLOW,<br/>	'user_priv' => PRIVATE_FLOW,<br/>...<br/>)</pre>
	 */
	private $_flowAvaliable = Array();
	/*
	 * Détail des flux
	 * @var Array
	 * @example	<pre>_flowDetails = Array(<br/>	'user' => SimpleXMLElement Object<br/>	(<br/>		[@attributes] => Array<br/>		(<br/>			[name] => user<br/>			[access] => public<br/>		)<br/><br/>		[id] => uid<br/>		[key] => pubkey<br/>		[title] => Donn�es publiques Muxxu<br/>		[desc] => Rassemble les donn�es publiques du profil (ASV, etc) et donne des acc�s aux donn�es publiques des jeux et des amis<br/>	)<br/>	'user_priv' => SimpleXMLElement Object<br/>...<br/>)</pre>
	 */
	#private $_flowDetails = Array();
	
	/**
	 * <p>Initialisation de mtlib</p>
	 * @param	string	$appName	- Nom de l'application
	 * @param	string	$privKey	- Clé privée de l'application
	 */
	public function mtlib($appName, $privKey)
	{
		//Système
		$this->_slash = (strtoupper(substr(PHP_OS,0,3)) == 'WIN') ? '\\' : '/';
		$this->_localMode =  (bool) ($_SERVER['REMOTE_ADDR'] == '127.0.0.1');
		
		//Application
		$this->_appName = $appName;
		$this->_privKey = $privKey;

		//Définition des flux
		$this->_initFlew(dirname(__FILE__).$this->_slash.self::XMLFILE_FLEW_LIST);
	}
	
	/*	+++++++++++++
		+ GET / SET +
		+++++++++++++	*/

	/**
	 * <p>Activation du DevMode</p>
	 * @param	boolean	$set	- statut devMode
	 */
	public function setDevMode($set=TRUE)
	{
		$this->_devMode = (bool) $set;
	}
	/**
	 * <p>Retourne le nom de l'application</p>
	 * @return	string	$appName	- Nom de l'application
	 */
	public function getAppName()
	{
		return $this->_appName;
	}
	/**
	 * <p>Retourne la clé privée de l'application</p>
	 * @return	string	$privKey	- Clé privée de l'application
	 */
	public function getPrivKey()
	{
		return $this->_privKey;
	}
	/**
	 * <p>Active la ré-émission des requètes vers l'API</p>
	 */
	public function setRefreshOn()
	{
		return $this->_refresh = TRUE;
	}
	/**
	 * <p>Evite l'émission de requètes déjà effectuées vers l'API</p>
	 */
	public function setRefreshOff()
	{
		return $this->_refresh = FALSE;
	}
	
	/*	+++++++++++++++++++++
		+ METHODES INTERNES +
		+++++++++++++++++++++	*/
	/**
	 * <p>Initialise la liste des flux disponibles.</p>
	 * @param	string	$xmlFile	- URL du fichier muxxu.general.API.Request.xml
	 */
	private function _initFlew($xmlFile)
	{
		//Emission de la requête
		if(! $rawData = file_get_contents($xmlFile))
		{
			if($this->_devMode) $msg =	"mtlib:FILE_NOT_FOUND '{$xmlFile}'";
			else $msg =	"mtlib:FILE_NOT_FOUND '".self::XMLFILE_FLEW_LIST."'";

			die($msg);
		}
		//Traitement des données
		$xmlData = simplexml_load_string($rawData);
		
		foreach($xmlData->api->flow as $flow)
		{
			//Intitulé du flux
			$name = strval($flow['name']);
			
			//Public/privé
			switch($flow['access'])
			{
				case 'public':
				$type = self::PUBLIC_FLOW;
				break;
				case 'private':
				$type = self::PRIVATE_FLOW;
				break;
				default:
				$type = FALSE;
			}
			
			//Disponibilité du flux
			$this->_flowAvaliable[$name] = $type;
			
			//Détails du flux
			#$this->_flowDetails[$name] = $flow;
			
		}
	}
	
	/*
	 * <p>Recherche les données du flux qui seraient déjà en mémoire.</p>
	 * @param	int	$id	- Identifiant du flux demandé
	 * @param	string	$name	- Nom du flux demandé
	 * @return	<li>object	$simpleXML	- Objet SimpleXML</li><li>boolean	FALSE	- Les données ne sont pas en mémoire</li>
	 
	private function _xmlAsObject($id, $name)
	{
		if(isset($this->_xmlAsObject[$name])
		&& isset($this->_xmlAsObject[$name][$id])
		)	 return $this->_xmlAsObject[$name][$id];
		else return FALSE;
	}*/
		
	/**
	 * <p>Notifie les erreurs rencontrées</p>
	 * <p>Imprime les retours si le DevMode est actif</p>
	 * @param	object	$simpleXML	- Données XML
	 * @param	string	$rawData	- Contenu du flux
	 */
	private function _flowNotice($simpleXML, $rawData)
	{
		//init
		$msg = FALSE;
				
		//XML invalide
		if($simpleXML === FALSE)
		{
			$msg = "mtlib:INVALID_XML";
		}
		//Erreur de l'API
		elseif($simpleXML->xpath("/error"))
		{
			$msg = "mtlib:API_ERROR : ".$simpleXML;
		}
		//Autorisation d'acces exigée
		elseif($simpleXML->xpath('/needauth'))
		{
			$msg = "mtlib:API_NEEDAUTH";
		}
		//Si le flux est vide
		elseif(trim((string) $rawData) == NULL)
		{
			$msg = "mtlib:API_NULL";
		}
		
		if($msg)
		{
			$this->_notice[] = Array
			(
				'type' => $msg,
				'rawData' => $rawData,
			);
			if($this->_devMode)
			{
				echo "\n<div class='mtlibNotice'>{$msg} : <code>{$rawData}</code></div>\n";
			}
		}
	}
	
	/*	+++++++++++++++++++++
		+ METHODES USUELLES +
		+++++++++++++++++++++	*/
	/**
	 * <p>émet une requête pour un flux public.</p>
	 * <p>Si $refresh vaut FALSE, retourne des dernières données connues lorsquelles sont déjà en mémoire.</p>
	 * @param	string	$flowName	- Nom du flux demandé
	 * @param	int	$flowId	- Identifiant du flux demandé
	 * @param	string	$flowKey	- Clé du flux demandé
	 * @return	String	Le flux demandé au format XML
	 */
	public function flow($flowName, $flowId, $flowKey)
	{
		//Données déjà perçues
		if(!$this->_refresh && isset($this->_xmlDatas[$flowName]) && isset($this->_xmlDatas[$flowName][$flowId]))
		{	
			return $this->_xmlDatas[$flowName][$flowId];
		}
		
		//Concaténation de la clé
		$key = md5($this->_privKey . $flowKey);
		//URL : requête API
		$url = "http://muxxu.com/app/xml?app={$this->_appName}&xml={$flowName}&id={$flowId}&key={$key}";
		
		//Devmode
		if($this->_devMode) $this->_log[] = $url;
		
		//Emission de la requête
		$this->_xmlDatas[$flowName][$flowId] = file_get_contents($url);
		//Traitement des données
		$xmlAsObject = simplexml_load_string($this->_xmlDatas[$flowName][$flowId]);
		
		//Analyse le résultat
		$this->_flowNotice($xmlAsObject, $this->_xmlDatas[$flowName][$flowId]);
		
		return  $this->_xmlDatas[$flowName][$flowId];
	}
	
	/**
	 * <p>émet une requête pour un flux privé.</p>
	 * <p>Si $refresh vaut FALSE, retourne des dernières données connues lorsquelles sont déjà en mémoire.</p>
	 * @param	string	$flowName	- Nom du flux demandé
	 * @param	int	$flowId	- Identifiant du flux demandé
	 * @param	string	&$askAuth	- FALSE si le flux est disponible, sinon l'URL de demande d'accès est attribué à cette variable
	 * @return	String	Le flux demandé au format XML
	 */
	public function flowPriv($flowName, $flowId, &$askAuth)
	{
		//Données déjà perçues
		if(!$this->_refresh && isset($this->_xmlDatas[$flowName]) && isset($this->_xmlDatas[$flowName][$flowId]))
		{	
			return $this->_xmlDatas[$flowName][$flowId];
		}
		
		//Concaténation des clés
		$base = $this->_privKey . $flowName . $flowId;
		$key = md5($base . 'GET');
		
		//URL : requête API
		$url = "http://muxxu.com/app/xml?app={$this->_appName}&xml={$flowName}&id={$flowId}&key={$key}";
		
		//Devmode
		if($this->_devMode) $this->_log[] = $url;
		
		//Emission de la requête
		$this->_xmlDatas[$flowName][$flowId] = file_get_contents($url);

		//Traitement des données
		$xmlAsObject = simplexml_load_string($this->_xmlDatas[$flowName][$flowId]);
		
		//Vérifie l'autorisation d'accès
		if(is_object($xmlAsObject) && $xmlAsObject->xpath('/needauth'))
		{
			$keyAuth = md5($base . 'AUTH');
			//URL de la demande d'autorisation d'acces aux données privées
			$askAuth = "http://muxxu.com/app/auth?app={$this->_appName}&xml={$flowName}&id={$flowId}&key={$keyAuth}";
		}
		else $askAuth = FALSE;
		
		//Analyse le résultat
		$this->_flowNotice($xmlAsObject, $this->_xmlDatas[$flowName][$flowId]);
		
		return  $this->_xmlDatas[$flowName][$flowId];
	}
	
	/*	+++++++++++++++
		+ UTILITAIRES +
		+++++++++++++++	*/
	/**
	 * <p>Vérifie si $id est un numéro identifiant</p>
	 * @param	int	$id	- Identifiant supposé
	 * @return	boolean	- Conclusion de la vérification
	 */
	public function is_id($id)
	{
		return (is_numeric($id) && $id > 0);
	}
	
	/**
	 * <p>Vérifie si $key correspond au format Clé (hexadecimal)</p>
	 * @param	string	$key	- Clé supposée
	 * @param	int	$lenght	- Longueur exigée (8 par défaut)
	 * @return	boolean	- Conclusion de la vérification
	 */
	public function is_key($key, $lenght=8)
	{
		return (is_string($key) && strlen($key)==$lenght && preg_match('#^[0-9a-f]{'.$lenght.'}$#i', $key));
	}
	
	
	/*
	 * Retourne les données brutes du cache XML
	 * @param	string	$flowName - Intitulé du flux
	 * @param	int	$id	- N°identifiant du flux
	 * @return	<ul><li>string - Le contenu XML du flux spécifié par les paramètres</li><li>FALSE	- Le flux spécifié par les paramètres ne figure pas dans le cache</li><li>Array - Si l'appel n'est pas argumenté, retourne l'intégralité du cache</li></ul>
	 */
	/*
	public function xmlDatas($flowName=false, $id=false)
	{
		if($flowName && $id)
		{
			//Types
			$flowName = strval($flowName);
			$id = intval($id);
			
			if(isset($this->_xmlDatas[$flowName]) && isset($this->_xmlDatas[$flowName][$id])) return $this->_xmlDatas[$flowName][$id];
			else return FALSE;
		}
		else return $this->_xmlDatas;
	}
	*/
	
	/**
	 * Information sur le résultat des requêtes émises
	 * @return	<ul><li><b>Array :</b> Liste des messages enregistrés</li><li><b>FALSE :</b> Rien à signaler.</li></ul>
	 */
	public function notice()
	{
		if(count($this->_notice)) return $this->_notice;
		else return FALSE;
	}
	
	/*
	 * Retourne les données parsées du cache XML sous forme d'objet SimpleXMLElement
	 * @param	string	$flowName - Intitulé du flux
	 * @param	int	$id	- N°identifiant du flux
	 * @return	<ul><li>string - L'objet SimpleXML du flux spécifié par les paramètres</li><li>FALSE	- Le flux spécifié par les paramètres ne figure pas dans le cache</li><li>Array - Si l'appel n'est pas argumenté, retourne l'intégralité du cache</li></ul>	 */
	/*
	public function xmlAsObject($flowName=false, $id=false)
	{
		if($flowName && $id)
		{
			//Types
			$flowName = strval($flowName);
			$id = intval($id);
			
			if(isset($this->_xmlAsObject[$flowName]) && isset($this->_xmlAsObject[$flowName][$id])) return $this->_xmlAsObject[$flowName][$id];
			else return FALSE;
		}
		else return $this->_xmlAsObject;
	}*/
	
	/**
	 * <p>Effectue plusieurs tests sur le serveur pour estimer ses capacités envers l'API</p>
	 * @param	boolean	$print	- Afficher un rapport sur la page
	 * @param	string	&$str	- Si <b>str</b> est fourni, la variable prendra comme valeur le rapport formaté HTML
	 * @return	boolean	$ok	- Conclusion du rapport (si $print == false)
	 */
	public function test($print=false, &$str=NULL)
	{	
		//Version de PHP
		$phpVersion = (string) phpversion();
		$eval['phpVersion'] = (bool) (((int) substr($phpVersion , 0,1)) >= 5);
		
		//accès aux fichiers distants
		$eval['allow_url_fopen'] = (bool) ini_get ( 'allow_url_fopen' );	//get_cfg_var('allow_url_fopen')
		
		//module SimpleXML
		$eval['simpleXML'] = (bool) in_array('SimpleXML',get_loaded_extensions());
		
		//accès à l'API
		$time = microtime(1);
		$flow = @ file_get_contents('http://muxxu.com/app/xml?app=api-&xml=kube_photo&id=62942&key=8fdd790fade69f2313251cf8ed43b2de');
		$time -= microtime(1);
		$eval['connectAPI'] = (bool) $flow;
		$eval['connectDelay'] = round(abs($time),3)." sec";
		
		//validité
		$data = @ simplexml_load_string($flow);
		$eval['validReturn'] = (bool) is_object($data);
		
		//Seulement si un retour par référence ou print est demandé
		//càd->Si l'appel à la fonction est argumentée
		
		if(func_num_args() > 1 || (func_num_args() == 1 && $print))		
		{
			$str = "<style type='text/css'>dl#mtlibTest{clear: both; display: block; width: 650px; margin: 10px auto; padding: 10px; font: 12px verdana, sans-serif; border: solid 1px #CCC; background-color: #EEF;} dl#mtlibTest dt{font-size: 14px; font-weight: bold;} dl#mtlibTest dd + dt{margin-top: 5px; border-top: dashed 1px #CCC; padding-top: 5px;} dl#mtlibTest code{display: block; width: 600px; min-height: 100px; max-height: 400px; overflow: scroll; white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;} dl#mtlibTest dl dt{font-size: 12px; margin-top: 16px; border-top: dashed 1px #CCC; padding-top: 4px;}</style>\n"	
				."<dl id='mtlibTest'>\n"
			
				."<dt>Mode local</dt>\n";
			$str .= $this->_localMode	? "<dd>Oui</dd>\n" : "<dd>Non</dd>\n";
			$str .= "<dd>{$_SERVER['REMOTE_ADDR']}</dd>\n"
			
				."<dt>Dev-Mode</dt>\n";
			$str .= $this->_devMode	? "<dd>Oui</dd>\n" : "<dd>Non</dd>\n";
			$str .= "<dd>{$this->_devMode}</dd>\n"
			
				."<dt>PHP version</dt>\n"
				."<dd>{$phpVersion}</dd>\n";
			$str .= $eval['phpVersion'] ? "<dd>OK</dd>\n" : "<dd>Minimum requis : PHP 5</dd>\n";
			
			$str .= "<dt>allow_url_fopen</dt>\n"
				."<dd>".get_cfg_var('allow_url_fopen')."</dd>\n";
			$str .=  $eval['allow_url_fopen'] ? "<dd>OK</dd>\n" : "<dd>Doit être : '1'</dd>\n";
			
			$str .= "<dt>Module SimpleXML</dt>\n";
			$str .= $eval['simpleXML'] ? "<dd>OK</dd>\n" : "<dd>Le module SimpleXML doit être installé.</dd>\n";
			
			$str .= "<dt>Accès à l'API <small>(test)</small></dt>\n";
			$str .= $eval['connectAPI'] ? "<dd>OK</dd>\n" : "<dd>L'API n'a retourné aucun flux.</dd>\n";
			
			$str .= "<dt>Délais de l'API <small>(test)</small></dt>\n"
				."<dd>{$eval['connectDelay']}</dd>\n"
			
				."<dt>Validité du flux <small>(test)</small></dt>\n";
			$str .= ($eval['validReturn']) ? "<dd>OK</dd>\n" : "<dd>Erreur</dd>\n";
			
			$str .= "<dt>Contenu du flux <small>(test)</small></dt>\n";
			if($eval['connectAPI'])
			{
				if($eval['validReturn'])
				{
					$str .= "<dd>OK</dd>\n"
						."<dd><code>"
						.htmlspecialchars($flow)
						."</code></dd>\n";
				}
				else
				{
					$str .= "<dd>Contenu XML invalide</dd>\n"
						."<dd><code>{$flow}</code></dd>\n";
				}
			}
			else $str .= "<dd>Aucun contenu n'a été retourné.</dd>\n";
			
			$str .= "</dl>";
			
			if($print) echo $str;
		}
		
		if(	$eval['phpVersion']
		&&	$eval['allow_url_fopen']
		&&	$eval['simpleXML']
		&&	$eval['connectAPI']
		&&	$eval['validReturn'])
		{
			return TRUE;
		}
		else return FALSE;
	}
	
	/*
	private function _check(&$var)
	{
		if(is_array($var)) $var = current($var);
	}
	*/
	/*
	 * Retourne les informations du flux spécifié
	 * @param	string	$flowName	- Intitulé du flux
	 * @return	<ul><li>Array	- infos</li><li>FALSE	- Flux non référencé</li></ul>
	 * @example	<pre>Array(<br/>	'access'	=> (string) 'public|private|undefined',<br/>	'title'	=> (string) 'Short desc.',<br/>	[<i>'desc'	=> (string) 'Long desc...',</i>]<br/>);
	 
	public function flowInfo($flowName)
	{
		//Vérifie si le flux est référencé
		if(! isset($this->_flowAvaliable[$flowName]))
		{
			return FALSE;
		}
		
		//--init
		$details = $this->_flowDetails[$flowName];
		
		//Préparation du rapport
		$report = Array();
		//--access
		$report['access'] = strval( $details['access'] );
		//--title
		$report['title'] = strval( $details->title );
		//--description
		if(isset($details->desc)) $report['desc'] = strval($details->desc);
		//--Eléments clés (login)
		$report['login'] = Array();
		//--/--id/key
		for($var='id'; $var != 'end'; $var = $var=='id' ? 'key' : 'end')
		{
			foreach($details->$var as $ref)
			{
				if(!isset($ref['type']) || $ref['type'] == 'undefined') continue;
								
				$report['login'][strval($ref['type'])][$var] = strval($ref);
				
				if(isset($ref['src'])) $report['require'][strval($ref['src'])] = true;
			}
		}
		
		return $report;
	}
	*/
	/*
	public function __destruct()
	{
		$this->_xmlAsObject = Array();
		unset($this->_xmlAsObject);
	}
	*/
	public function log($return=false)
	{
		if(! $this->_devMode) return false;
		if($return) return $this->_log;
		else echo "<ol>\n<li>".implode("</li>\n<li>",$this->_log)."</li>\n</ol>\n";
		return true;
	}
}
?>