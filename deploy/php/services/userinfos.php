<?php
/*
 * USERINFOS : Service d'information sur un utilisateur, ses parents et enfants directs
 */

define('baseURL','../../');

//Compléments de base
require(baseURL.'php/msg.php');
require(baseURL.'c/config.php');
require(baseURL.'php/func/pReturn.php');
require(baseURL.'c/mysql.php');

//Gestion DB
require(baseURL.'php/class/mysqlManager.php');
require(baseURL.'php/class/mushSQL.php');

//Fonctions utiles XML
require(baseURL.'php/func/xmlError.php');
require(baseURL.'php/func/xmlFinish.php');
require(baseURL.'php/func/cdata.php');

//mtlib et DTO (gestion de l'API Muxxu)
require(baseURL.'php/class/mtlib.php');
require(baseURL.'php/class/dto/base_dto.php');
require(baseURL.'php/class/dto/user.php');


/*
 * Initialisation
 */
//Fichier XML de référence
$base = website.'/xml/userinfos.xml';
//Intanciation de l'objet XML
$userinfos = new SimpleXMLElement($base, 0, 1);

//Initialise la connexion à l'API
$api = new mtlib(appName, privKey);

//Vérifie les coordonnées
//--identifiant utilisateur
if(isset($_GET['id']) && $api->is_id($_GET['id']))
{
	define('UID', intval($_GET['id']));
}
//--identifiant erroné
else
{
	define('UID', false);
	xmlError($userinfos, 'get', MSG_GET_INVALID_UID);
}

//--Option de pandémie
define('FULL_INFOS', (bool) isset($_GET['pandemie']));

//Initialisation du gestionnaire DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//Recherche le profil dans la base
if(! $db->selectUsers(1, array(UID), '0,1')) //En cas d'erreur SQL
{
	$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
	xmlError($userinfos, 'db', $e);
}

//Si UID ne correspond pas à un utilisateur connu de près ou de loin
if(!mysql_num_rows($db->result)) xmlError($userinfos, 'mush', MSG_USER_NOT_FOUND);

//Déploiement des données
$userData = mysql_fetch_assoc($db->result);

if(FULL_INFOS)
	{
	//init
	$parentData = $childData = array();
	
	//Recherche une infection parent
	if($userData['infected'])
	{
		if(! $db->selectParents(UID)) //En cas d'erreur SQL
		{
			$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
			xmlError($userinfos, 'db', $e);
		}
		
		if(mysql_num_rows($db->result))
		{
			//Déploiement des parents
			while($row = mysql_fetch_assoc($db->result)) $parentData[] = $row;
		}
	}
	//Recherche une infection enfant
	if(! $db->selectChilds(UID)) //En cas d'erreur SQL
	{
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
		xmlError($userinfos, 'db', $e);
	}
		
	if(mysql_num_rows($db->result))
	{
		//Déploiement des childs
		while($row = mysql_fetch_assoc($db->result)) $childData[] = $row;
	}
}
/*
 * Finitions du XML
 */

//Elément <userinfos><user>
$user = $userinfos->addChild('user');
$user->addAttribute('uid', UID);
$user->addAttribute('level', $userData['infected']);

$user->addChild('name', cdata($userData['name']));
$user->addChild('avatar', cdata($userData['avatar']));

if(FULL_INFOS)
{
	$parent = $user->addChild('parent');
	if(count($parentData))
	{
		foreach($parentData as $s)
		{
			$spore = $parent->addChild('spore');
			$spore->addAttribute('uid', $s['parent']);
			$spore->addAttribute('ts', $s['date']);
			$spore->addChild('name', cdata($s['name']));
			if(strlen($s['avatar'])) $spore->addChild('avatar', cdata($s['avatar']));
			else $spore->addChild('avatar', null);
		}
	}
	
	$child = $user->addChild('child');
	if(count($childData))
	{
		foreach($childData as $s)
		{
			$spore = $child->addChild('spore');
			$spore->addAttribute('uid', $s['child']);
			$spore->addAttribute('ts', $s['date']);
			$spore->addChild('name', cdata($s['name']));
			if(strlen($s['avatar'])) $spore->addChild('avatar', cdata($s['avatar']));
			else $spore->addChild('avatar', null);
		}
	}
}

//Finalise
#echo $userinfos->asXML();
xmlFinish($userinfos);
?>