<?php
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
?>