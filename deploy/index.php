<?php
//Base
require('php/msg.php');
require('c/config.php');
require('c/usualSuspect.php');
require('php/func/pReturn.php');
require('c/mysql.php');

//mtlib et DTO (gestion de l'API Muxxu)
require('php/class/mtlib.php');
require('php/class/dto/base_dto.php');
require('php/class/dto/user.php');
require('php/class/dto/friends.php');

//Parametres
$ini = parse_ini_file('params.ini');

//Démarrage de mtlib
include('php/inc/runMtlib.php');

//Sommaire de l'app
//--init
if(!isset($_GET['act'])) $_GET['act'] = null;
//--menu
switch($_GET['act'])
{
	#?uid=3916&name=newSunshine&pubkey=dd455970
	#?id=3916&key=1986aa04
	case 'admin':
		include('c/admin.php');
		if(!adminOffice()) usualSuspect('admin_access');
	case 'php': //Dev: nSun
		include('php/start.php');
	default:
		include('php/flashFrame.php');
}
?>