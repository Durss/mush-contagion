<?php
/**
 * <h1>START</h1>
 * <p>Reception du visiteur, contrôle de l'indentité etc.</p>
 */
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

//Initialisation du gestionnaire DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//Insert, complète ou met à jour les infos sur l'utilisateur
//--Si l'utilisateur est déjà enregistré, mon met à jour toutes les données
$sex = 'u';
if(isset($user->profil['male'])) $sex = $user->profil['male'] ? 'm' : 'f';
$db->insertUser(
	UID,
	PUBKEY,
	strval($user->key['friends']),
	strval($user->name),
	$sex,
	strval($user->avatar)
	);


//liste d'amis
$flow = $api->flow('friends', UID, $user->key['friends']);

if(!$user->friends = new friends($flow))
{
	//todo: evaluer chaque type d'erreur.
	foreach($api->notice() as $error) echo "<div class='adv'>{$error['type']}</div>\n";
	die();
} 
elseif(!count($user->friends->list)) {} //Pauvre chou (pas d'amis)
//Insertion des amis dans la table 'user' / MAJ du pseudo s'ils sont déjà présents dans la table.
else
{
	$friendsList = array_chunk($user->friends->list, $ini['params']['queryLimit'],1);
	foreach($friendsList as $list) $db->insertFriends($list);
}

//Déconnexion de la base.
$db->__destruct();
?>