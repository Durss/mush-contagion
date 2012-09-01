<?php

/*
 * USERINFOS : Service d'information sur un utilisateur, ses parents et enfants directs
 */

define('baseURL','../../');

//ComplÃ©ments de base
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

//mtlib et DTO (gestion de l'API Muxxu)
require(baseURL.'php/class/mtlib.php');
require(baseURL.'php/class/dto/base_dto.php');
require(baseURL.'php/class/dto/user.php');


/*
 * Initialisation
 */
$ini = parse_ini_file(baseURL.'params.ini');

//Fichier XML de rÃ©fÃ©rence
$base = website.'/xml/userinfos.xml';
//Intanciation de l'objet XML
$userinfos = new SimpleXMLElement($base, 0, 1);

//Initialise la connexion Ã  l'API
$api = new mtlib(appName, privKey);

//VÃ©rifie les coordonnÃ©es
//--identifiant utilisateur
if(isset($_GET['id']) && $api->is_id($_GET['id']))
{
	define('UID', intval($_GET['id']));
}
//--identifiant erronÃ©
else
{
	define('UID', false);
	xmlError($userinfos, 'GET_INVALID_UID');
}

//--Option de pandÃ©mie
define('PARENT_INFOS', (bool) isset($_GET['parent']));
define('FULL_INFOS', (bool) isset($_GET['pandemie']));

//Initialisation du gestionnaire DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//Recherche le profil dans la base
if(! $db->selectUsers(1, array(UID), '1')) //En cas d'erreur SQL
{
	xmlError($userinfos, 'MYSQL_QUERY_FAIL_7', mysql_error());
	xmlFinish($userinfos);
}

//Si UID ne correspond pas Ã  un utilisateur connu de prÃ¨s ou de loin
if(! mysql_num_rows($db->result))
{
	xmlError($userinfos, 'APP_USER_NOT_FOUND');
	xmlFinish($userinfos);
}

//DÃ©ploiement des donnÃ©es
$userData = mysql_fetch_assoc($db->result);

//derniÃ¨re infection effectuÃ©e
if(! $db->selectLastInfection(UID)) //En cas d'erreur SQL
{
	xmlError($userinfos, 'MYSQL_QUERY_FAIL_10', mysql_error());
	xmlFinish($userinfos);
}

//Si UID n'a pas encore effectuÃ© d'infection
if(! mysql_num_rows($db->result)) $lastInfection = false;
else
{
	$row = mysql_fetch_assoc($db->result);
	//Convertir la date en timestamp..
	date_default_timezone_set('Europe/Paris');
	$lastInfection = strtotime($row['date']);
}

//Si le dÃ©tail du parent est demandÃ©
if(PARENT_INFOS || FULL_INFOS)
{
	//init
	$parentData = array();
	
	//Recherche une infection parent
	if($userData['infected'])
	{
		if(! $db->selectParents(UID)) //En cas d'erreur SQL
		{
			xmlError($userinfos, 'MYSQL_QUERY_FAIL_8');
		}
		elseif(mysql_num_rows($db->result))
		{
			//DÃ©ploiement des parents
			while($row = mysql_fetch_assoc($db->result)) $parentData[] = $row;
		}
	}
}

//Si une information complÃ¨te est demandÃ©e
if(FULL_INFOS)
{
	//init
	$childData = array();
	
	//Recherche une infection enfant
	if(! $db->selectChilds(UID)) //En cas d'erreur SQL
	{
		xmlError($userinfos, 'MYSQL_QUERY_FAIL_9');
	}
	elseif(mysql_num_rows($db->result))
	{
		//DÃ©ploiement des childs
		while($row = mysql_fetch_assoc($db->result)) $childData[] = $row;
	}
}

//DÃ©connexion de la base.
$db->__destruct();

/*
 * Finitions du XML
 */

//ElÃ©ment <userinfos><user>
$user = $userinfos->addChild('user');
$user->addAttribute('uid', UID);
$user->addAttribute('level', $userData['infected']);

$user->addChild('name', $userData['name']);
$user->addChild('avatar', $userData['avatar']);

if($lastInfection)
{
	//respect du dÃ©lai
	if(time() - intval($ini['infectDelay']) > $lastInfection)
	{
		//DÃ©lai respectÃ©
		$wait = intval(0);
	}
	else
	{
		//Veuillez patienter
		$wait = $lastInfection + intval($ini['infectDelay']) - time();
	}
	$delay = $user->addChild('delay');
	$delay->addAttribute('wait', $wait);
	$delay->addAttribute('lastInfection', $lastInfection);
}
else {
	$delay = $user->addChild('delay');
	$delay->addAttribute('wait', '0');
}

//Information sur le parent
if(PARENT_INFOS || FULL_INFOS)
{
	$parent = $user->addChild('parent');
	if(count($parentData))
	{
		foreach($parentData as $s)
		{
			$spore = $parent->addChild('spore');
			$spore->addAttribute('uid', $s['parent']);
			$spore->addAttribute('ts', strtotime($s['date']));
			$spore->addChild('name', $s['name']);
			if(strlen($s['avatar'])) $spore->addChild('avatar', $s['avatar']);
			else $spore->addChild('avatar');
		}
	}
}
//Information sur les enfants
if(FULL_INFOS)
{	
	$child = $user->addChild('child');
	if(count($childData))
	{
		foreach($childData as $s)
		{
			$spore = $child->addChild('spore');
			$spore->addAttribute('uid', $s['child']);
			$spore->addAttribute('ts', strtotime($s['date']));
			$spore->addChild('name', $s['name']);
			if(strlen($s['avatar'])) $spore->addChild('avatar', $s['avatar']);
			else $spore->addChild('avatar');
		}
	}
}

//Finalise
#echo $userinfos->asXML();
xmlFinish($userinfos);
?>