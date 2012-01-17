<?php
/*
 * ATCHOUM ! Service d'infection express !
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

//init
$parentData = $childData = array();

//Recherche une infection parent
if($userData['infected'])
{
	$sql = "-- Look for parents\n"
	."SELECT `parent`, `date`\n"
	."FROM `{$mysql_vars['tbl']['link']}`\n"
	."WHERE `child` = ".UID."\n"
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
if($userData['infected'])
{
	$sql = "-- Look for child\n"
	."SELECT `child`, `date`\n"
	."FROM `{$mysql_vars['tbl']['link']}`\n"
	."WHERE `parent` = ".UID."\n"
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

if(count($parentData))
{
	$parent = $user->addChild('parent');
	foreach($parentData as $s)
	{
		$spore = $parent->addChild('spore');
		$spore->addAttribute('uid', $s['parent']);
		$spore->addAttribute('ts', $s['date']);
	}
}
if(count($childData))
{
	$child = $user->addChild('child');
	foreach($childData as $s)
	{
		$spore = $child->addChild('spore');
		$spore->addAttribute('uid', $s['child']);
		$spore->addAttribute('ts', $s['date']);
	}
}

//Finalise
#echo $userinfos->asXML();
xmlFinish($userinfos);
?>