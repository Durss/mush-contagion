<?php
/*
 * ATCHOUM ! Service d'infection express !
 */

define('baseURL','../../');

require(baseURL.'msg.php');
require(baseURL.'c/config.php');
require(baseURL.'php/func/pReturn.php');
require(baseURL.'c/mysql.php');

require(baseURL.'php/func/xmlError.php');
require(baseURL.'php/func/cdata.php');

require(baseURL.'php/class/mtlib.php');
require(baseURL.'php/class/dto/base_dto.php');
require(baseURL.'php/class/dto/user.php');
require(baseURL.'php/class/dto/friends.php');

//Fichier XML de référence
$base = 'http://localhost/mushcontagion/xml/bachibousouk.xml';
//Intanciation de l'objet XML
$root = new SimpleXMLElement($base, 0, 1);

//Initialise la connexion à l'API
$api = new mtlib(appName, privKey);

//Vérifie les coordonnées
//--identifiant utilisateur
if(isset($_GET['uid']) && $api->is_id($_GET['uid']))
{
	define('UID', intval($_GET['uid']));
}
//--identifiant erroné
else
{
	define('UID', false);
	xmlError($root, MSG_GET_INVALID_UID);
}
//--clé friends (utilisateur)
if(isset($_GET['key']) && $api->is_key($_GET['key']))
{
	define('FRIENDS_KEY', strval($_GET['FRIENDS_KEY']));
}
//--clé friends erronée
else
{
	define('FRIENDS_KEY', false);
	xmlError($root, MSG_GET_INVALID_KEY);
}

//Récupère la liste d'amis
$flowOK = false;
if(UID && FRIENDS_KEY)
{
	$flow = $api->flow('friends', UID, FRIENDS_KEY);
	//En cas d'erreur avec l'API ou le flux
	if(!$friends = new friends($flow)) foreach($api->notice() as $error) xmlError($root, cdata($error['type']));
	//Pas d'erreur c'est lessieur
	else $flowOK = true;
}

//Paire invalide id/key ou flux friend indisponible
if(!UID || !FRIENDS_KEY || !$flowOK)
{
	header('Content-Type: text/xml; charset="UTF-8"');
	echo $root->asXML();
	die();
}

//Dresse la liste des amis pour une requête SQL
$f = array_flip($friends->list);
//Sélectionne tous les amis qui ne sont pas encore infectés.
$sql = "-- Amis non-infectés\n"
."SELECT `name`,`avatar`\n"
."FROM `{$mysql_vars['tbl']['user']}`\n"
."WHERE `uid`\n"
."IN (".implode(', ',$f).")\n"
."AND `infected` = 0;";

//Elément <root><result>
$root->result = 1;

//Elément <root><infectedUsers>
$infectedUsers = $root->addChild('infectedUsers');

//Elément <root><infectedUsers><user>
$user = $infectedUsers->addChild('user');
$user->addAttribute('uid', $_GET['uid']);

$user->addChild('name', cdata('champion'));
$user->addChild('avatar', cdata('tête de babar'));

//Finalise
header('Content-Type: text/xml; charset="UTF-8"');
echo $root->asXML();
?>