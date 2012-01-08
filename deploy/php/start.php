<?php
/**
 * <h1>START</h1>
 * <p>Reception du visiteur, contrôle de l'indentité etc.</p>
 */
#DEVMODE
define('baseURL','../');

//Bloque l'accès direct
/* const	baseURL	string	-URL relatif
 * @todo	à définir dans index.php à la racine du site
 */
if(!defined('baseURL')) die();

require(baseURL.'c/config.php');
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

if(!UID || !PUBKEY )
{
	//todo: redirect $appURL = 'http://muxxu.com/a/'.appName;
}
?>