<?php
/*
 * Configuration de la base de donn�es
 * Fichier confidentiel
 */

/* MODE LOCAL */
if(LOCAL_MODE){
	//Paramètres DB
	$mysql_vars = array(
		'host' => 'localhost',
		'user' => 'root',
		'pass' => '',
		'db' => 'mushTeasing',
		'tbl' => array(
			'user' => 'mushteasing_user',
			'link' => 'mushteasing_link',
			'stat' => 'mushteasing_stats',
		),
	);
}
/* MODE ONLINE */
else{
	//Paramètres DB
	$mysql_vars = array(
		'host' => 'xxx',
		'user' => 'xxx',
		'pass' => 'xxx',
		'db' => 'xxx',
		'tbl' => array(
			'user' => 'mushteasing_user',
			'link' => 'mushteasing_link',
			'stat' => 'mushteasing_stats',
		),
	);
}
/*
mysql_connect($mysql_vars['host'],$mysql_vars['user'],$mysql_vars['pass']) or die(pReturn(mysql_error(), MSG_DBConnectFail));
mysql_selectdb($mysql_vars['db']) or die(pReturn(mysql_error(), MSG_DBSelectFail));
*/
?>