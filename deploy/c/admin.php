<?php
/*
function deadend()
{
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die();
}
*/

function adminOffice()
{
	global $api;
	$ini = parse_ini_file('params.ini',1);
	
	//Vérifie les coordonnées
	//--Checkpoint #1 : identifiant utilisateur
	if(!defined('UID') || !UID) return false;

	//--Checkpoint #2 : clé publique (utilisateur)
	if(!defined('PUBKEY') || !PUBKEY)  return false;

	//Vérifie l'identité
	$flow = $api->flow('user', UID, PUBKEY);
	
	//--Checkpoint #3 : Validité du flux
	if($api->notice()) return false;
	
	$user = new user($flow);
	$name = strtolower($user->name);
	
	//--Checkpoint #4 : identification dans la liste des admins
	if(!isset($ini['admins'][$name]) || intval($ini['admins'][$name]) != UID) return false;

	define('ADMIN_OK', true);
	require('php/adminPanel.php');
	die();
}
?>