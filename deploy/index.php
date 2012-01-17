<?php
/**
 * <h1>INDEX</h1>
 * <p>Point névralgique de l'app.</p>
 */
define('DEVMODE',true);
define('BETA',false);
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

//Template
require('php/class/nsunTpl.php');
$page = new nsunTpl();
$page->title = "Mush Contagion";
$page->addMetaTag("ROBOTS", "NOINDEX, NOFOLLOW");

//Parametres
$ini = parse_ini_file('params.ini');

//Démarrage de mtlib
include('php/inc/runMtlib.php');

//Sommaire de l'app
//--init
if(!isset($_GET['act'])) $_GET['act'] = null;
//--admin
if($_GET['act'] == 'admin')
{
	include('c/admin.php');
	if(!adminOffice()) usualSuspect('admin_access');
}
//--Page de maintenance
if($ini['maintenance'] != '0') die(MSG_MAINTENANCE);
//--menu
switch($_GET['act'])
{	
	case 'php': //Dev: nSun
		if(DEVMODE)
		{
			if(!BETA) include('php/start.php');
			die();
		}
	default:
		if(BETA)
		{
			include('php/start.php');
			include('beta.php');
		}
		else
		{
			include('php/start.php');
			include('php/flashFrame.php');
		}
}
?>