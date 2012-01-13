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

//Insert, complète ou met à jour les infos sur l'utilisateur
$sql = "-- Nouvel user ou MAJ\n"
."INSERT INTO `{$mysql_vars['db']}`.`{$mysql_vars['tbl']['user']}`\n"
."(`uid`, `pubkey`, `friends`, `name`, `lastvisit`, `avatar`) VALUES\n"
."('".UID."', '".PUBKEY."', '{$user->key['friends']}', '{$user->name}', '".time()."', '{$user->avatar}')\n"
//--Si l'utilisateur est déjà enregistré, mon met à jour toutes les données
."ON DUPLICATE KEY UPDATE\n"
."`pubkey` = '".PUBKEY."', `friends` = '{$user->key['friends']}', `name` = '{$user->name}', `lastvisit` = '".time()."', `avatar` = '{$user->avatar}';";
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
?>