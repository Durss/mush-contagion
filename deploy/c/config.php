<?php
/*
 * Configuration de la base de données
 * Fichier confidentiel
 */
define('LOCAL_MODE', (bool) ($_SERVER['REMOTE_ADDR'] == '127.0.0.1'));

/* MODE LOCAL */
if(LOCAL_MODE){
	define('appName','xxx');
	define('privKey','xxx'); //mushinfector
	define('website', 'http://127.0.0.1/mushcontagion/');
}
/* MODE ONLINE */
else{
	define('appName','xxx');
	define('privKey','xxx');
	define('website', 'http://fevermap.org/mushcontagion/');
#	define('appName','xxx');
#	define('privKey','xxx'); //mushinfector-dev
}
?>