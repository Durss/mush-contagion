<?php
/**
 * <h1>START</h1>
 * <p>Reception du visiteur, contrôle de l'indentité etc.</p>
 */

/*
 * Bloque l'accès direct
 * const	baseURL	string	-URL relatif
 * @todo	à définir dans index.php à la racine du site
 */
if(!defined('baseURL'))
{
	#die();
	#DEVMODE
	define('baseURL','../');
}

require(baseURL.'msg.php');
require(baseURL.'c/config.php');
require(baseURL.'php/func/pReturn.php');
require(baseURL.'c/mysql.php');
require(baseURL.'php/class/mtlib.php');
require(baseURL.'php/class/dto/base_dto.php');
require(baseURL.'php/class/dto/user.php');
require(baseURL.'php/class/dto/friends.php');

//Instance de mtlib
$api = new mtlib(appName, privKey);

//Vérifie les coordonnées
//--identifiant utilisateur
if(isset($_GET['uid']) && $api->is_id($_GET['uid']))
{
	define('UID', intval($_GET['uid']));
}
else define('UID', false);
//--clé publique (utilisateur)
if(isset($_GET['pubkey']) && $api->is_key($_GET['pubkey']))
{
	define('PUBKEY', strval($_GET['pubkey']));
}
else define('PUBKEY', false);

//Paire invalide id/key
if(!UID || !PUBKEY)
{
	//todo: redirect $appURL = 'http://muxxu.com/a/'.appName;
	die(403);
}

//Identification du visiteur
$flow = $api->flow('user', UID, PUBKEY);
//--Vérifie que le flux est valide
if($api->notice())
{
	//todo: evaluer chaque type d'erreur.
	foreach($api->notice() as $error) echo "<div class='adv'>{$error['type']}</div>\n";
	die();
}

$user = new user($flow);

//Insert, complète ou met à jour les infos sur l'utilisateur
$sql = "-- Nouvel user ou MAJ\n"
."INSERT INTO `{$mysql_vars['db']}`.`{$mysql_vars['tbl']['user']}`\n"
."(`uid`, `pubkey`, `name`, `lastvisit`, `avatar`) VALUES\n"
."('".UID."', '".PUBKEY."', '{$user->name}', '".time()."', '{$user->avatar}')\n"
//--Si l'utilisateur est déjà enregistré, mon met à jour toutes les données
."ON DUPLICATE KEY UPDATE\n"
."`pubkey` = '".PUBKEY."', `name` = '{$user->name}', `lastvisit` = '".time()."', `avatar` = '{$user->avatar}';";
mysql_query($sql) or die(pReturn(mysql_error(), MSG_QueryFail));

//liste d'amis
$flow = $api->flow('friends', UID, $user->key['friends']);

if(!$user->friends = new friends($flow))
{
	//todo: evaluer chaque type d'erreur.
	foreach($api->notice() as $error) echo "<div class='adv'>{$error['type']}</div>\n";
	die();
} 
elseif(!count($user->friends->list)) {} //Pauvre chou (pas d'amis)
else
{
	//init
	$update = Array();
	
	foreach($user->friends->list as $id => $name) $update[] = "('{$id}', '{$name}')";
	
	$sql = "-- Ajout des amis\n"
	."INSERT INTO `{$mysql_vars['db']}`.`{$mysql_vars['tbl']['user']}`\n"
	."(`uid`, `name`) VALUES\n"
	.implode(",\n", $update)."\n"
	//--Si l'utilisateur est déjà enregistré, on ne met à jour que son pseudo
	."ON DUPLICATE KEY UPDATE\n"
	."`name` = VALUES(`name`);";
	mysql_query($sql) or die(pReturn(mysql_error(), MSG_QueryFail));
}

var_dump($user);
?>