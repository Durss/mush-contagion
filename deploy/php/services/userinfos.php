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

//Recherche le profil dans la base
$sql = "-- Look for user\n"
."SELECT `pubkey`, `name`, `lastvisit`, `avatar`, `infected`\n"
."FROM `{$mysql_vars['tbl']['user']}`\n"
."WHERE `uid` = ".UID."\n"
."LIMIT 0, 1;";
//En cas d'erreur SQL
if(!$result = mysql_query($sql))
{
	$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
	xmlError($userinfos, 'db', $e);
}

//Si UID ne correspond pas à un utilisateur connu de près ou de loin
if(!mysql_num_rows($result)) xmlError($userinfos, 'mush', MSG_USER_NOT_FOUND);

//Déploiement des données
$userData = mysql_fetch_assoc($result);

if(FULL_INFOS)
	{
	//init
	$parentData = $childData = array();
	
	//Recherche une infection parent
	if($userData['infected'])
	{
		$sql = "-- Look for parents\n"
		."SELECT L.`parent`, L.`date`, U.`name`, U.`avatar`\n"
		."FROM `{$mysql_vars['tbl']['link']}` L, `{$mysql_vars['tbl']['user']}` U\n"
		."WHERE L.`child` = ".UID."\n"
		."AND L.`parent` = U.`uid`\n"
		."ORDER BY `index` ASC;";
		
		//En cas d'erreur SQL
		if(!$result = mysql_query($sql))
		{
			$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
			xmlError($userinfos, 'db', $e);
		}
		
		if(mysql_num_rows($result))
		{
			//Déploiement des parents
			while($row = mysql_fetch_assoc($result)) $parentData[] = $row;
		}
	}
	//Recherche une infection enfant
	$sql = "-- Look for child\n"
	."SELECT L.`child`, L.`date`, U.`name`, U.`avatar`\n"
	."FROM `{$mysql_vars['tbl']['link']}` L, `{$mysql_vars['tbl']['user']}` U\n"
	."WHERE L.`parent` = ".UID."\n"
	."AND L.`child` = U.`uid`\n"
	."ORDER BY `index` ASC;";
		
	//En cas d'erreur SQL
	if(!$result = mysql_query($sql))
	{
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
		xmlError($userinfos, 'db', $e);
	}
		
	if(mysql_num_rows($result))
	{
		//Déploiement des childs
		while($row = mysql_fetch_assoc($result)) $childData[] = $row;
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