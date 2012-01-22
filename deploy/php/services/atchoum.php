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
require(baseURL.'php/class/dto/friends.php');

//Parametres
$ini = parse_ini_file(baseURL.'params.ini');
//--Nombre de personnes à infecter
$max = intval($ini['infectPerTurn']);

/*
 * Initialisation
 */
//Fichier XML de référence
$base = website.'xml/atchoum.xml';
//Intanciation de l'objet XML
$root = new SimpleXMLElement($base, 0, 1);

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
	xmlError($root, 'get', MSG_GET_INVALID_UID);
}
//--clé friends (utilisateur)
if(isset($_GET['key']) && $api->is_key($_GET['key']))
{
	define('FRIENDS_KEY', strval($_GET['key']));
}
//--clé friends erronée
else
{
	define('FRIENDS_KEY', false);
	xmlError($root, 'get', MSG_GET_INVALID_KEY);
}

/*
 * Récupère la liste d'amis
 */
$flowOK = false;
if(UID && FRIENDS_KEY)
{
	$flow = $api->flow('friends', UID, FRIENDS_KEY);
	//En cas d'erreur avec l'API ou le flux
	if($api->notice()) foreach($api->notice() as $error) xmlError($root, 'api', cdata($error['type']));
	//Pas d'erreur c'est lessieur
	else
	{
		$friends = new friends($flow);
		$flowOK = true;
	}
}

//Paire invalide id/key ou flux friend indisponible
if(!UID || !FRIENDS_KEY || !$flowOK) xmlFinish($root);

//Initialisation du gestionnaire DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//Params
#$addInfectedUsers = ($ini['infectCover'] == '1') ? null : "AND `infected` = 0\n";

//Dresse la liste des amis pour une requête SQL
$f = array_keys($friends->list);

//Sélectionne tous les amis qui ne sont pas encore infectés.
if(! $db->selectUsers(1, $f, $max, $ini['infectCover'], 1))
{
	//En cas d'erreur SQL
	$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
	xmlError($root, 'db', $e);
	xmlFinish($root);
}

//Déplie la liste
$list = Array();
if(mysql_num_rows($db->result))
{
	while($row = mysql_fetch_assoc($db->result)) $list[intval($row['uid'])] = $row;
}

//Pour les gens qui n'ont pas ou peu d'amis non-infectés
if(count($list) < $max)
{
	if(! $db->selectUsers(0, $f, $max - count($list), $ini['infectCover'], 1))
	{
		//En cas d'erreur SQL
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
		xmlError($root, 'db', $e);
		xmlFinish($root);
	}
	if(mysql_num_rows($db->result))
	{
		while($row = mysql_fetch_assoc($db->result)) $list[intval($row['uid'])] = $row;
	}
}

/*
 * Processus d'infection
 */
//Auto-infection
if($ini['infectSelf'])
{
	if(!$db->insertLink(array(UID), 0))
	{
		//En cas d'erreur SQL
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
		xmlError($root, 'db', $e);
		xmlFinish($root);
	}
	
	if(!$db->updateInfection(array(UID)))
	{
		//En cas d'erreur SQL
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error().$sql) );
		xmlError($root, 'db', $e);
		xmlFinish($root);
	}
}

if(count($list))
{
	if(!$db->insertLink(array_keys($list), UID))
	{
		//En cas d'erreur SQL
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error()) );
		xmlError($root, 'db', $e);
		xmlFinish($root);
	}

	if(!$db->updateInfection(array_keys($list)))
	{
		//En cas d'erreur SQL
		$e = cdata( pReturn(MSG_QueryFail.' : '.mysql_error().$sql) );
		xmlError($root, 'db', $e);
		xmlFinish($root);
	}
}
//Tout le monde est infecté
else
{
	$e = cdata(MSG_FULL_INFECTED);
	xmlError($root, 'mush', $e);
	xmlFinish($root);
}

/*
 * Finitions du XML
 */
//Elément <root><result>
$root->result = count($list);

//Elément <root><infectedUsers>
$infectedUsers = $root->addChild('infectedUsers');

foreach($list as $target)
{	
	//Elément <root><infectedUsers><user>
	$user = $infectedUsers->addChild('user');
	$user->addAttribute('uid', $target['uid']);
	$user->addAttribute('isFriend', (bool) isset($friends->list[$target['uid']]));

	$user->addChild('name', cdata($target['name']));
	$user->addChild('avatar', cdata($target['avatar']));
}

//Finalise
#echo $root->asXML();
xmlFinish($root);
?>