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

//Gestion DB
require('php/class/mysqlManager.php');
require('php/class/mushSQL.php');

//mtlib et DTO (gestion de l'API Muxxu)
require('php/class/mtlib.php');
require('php/class/dto/base_dto.php');
require('php/class/dto/user.php');
require('php/class/dto/friends.php');

//Template
require('php/class/nsunTpl.php');
$page = new nsunTpl();
$page->title = "Mush  Contagion ";
$page->addMetaTag("ROBOTS", "NOINDEX, NOFOLLOW");
$page->addStyleSheet('css/base.css?v=2');

//Parametres
$ini = parse_ini_file('params.ini',1);

//Démarrage de mtlib
require('php/inc/runMtlib.php');

//Sommaire de l'app
//--init
if(!isset($_GET['act'])) $_GET['act'] = null;

//--Page de maintenance
if($ini['status']['maintenance'] != '0')
{
	//identification (si admin)
	$flow = $api->flow('user', UID, PUBKEY);
	//--Vérifie que le flux est valide
	if($api->notice()) $user = false;
	else $user = new user($flow);
		
	if($user
	&& isset($ini['admins'][strtolower($user->name)])
	&& $ini['admins'][strtolower($user->name)] == UID)
	{
		//@todo : marquer que le site est bloqué pour toute autre personne.
		$page->c .= "<div class='adv'>Maintenance en cours</div>";
	}
	//503	Service Unavailable
	else
	{
		if(isset($page)) $page->stop = true;
		header($_SERVER["SERVER_PROTOCOL"]." 503 Service Unavailable");
		$_GET['code'] = 503;
		include('error.php');
		die();
	}
}
//--admin
if($_GET['act'] == 'admin')
{
	include('c/admin.php');
	$page->stop = true;
	if(!adminOffice())
	{
		usualSuspect('admin_access');
		$page->stop = false;
	}
	
}
//--menu
$sections = array(
		'u' => 'user',
	//	'd' => 'diagnostic',
		'p' => 'pandemie',
		'labo' => 'labo',
);
$pattern = '#^('.implode('|',array_keys($sections)).')/([1-9][0-9]*)$#';
$targetUID = false;
$msgID = false;
if(preg_match($pattern, $_GET['act'], $matches)){
	$_GET['act'] = $sections[$matches[1]];
	
	if($matches[1] == 'p') $targetUID = intval($matches[2]);
	elseif($matches[1] == 'labo') $msgID = intval($matches[2]);
	elseif($matches[1] == 'u'){
		//identification (si admin)
		$flow = $api->flow('user', UID, PUBKEY);
		//--Vérifie que le flux est valide
		if($api->notice()) $user = false;
		else $user = new user($flow);
		if($user
		&& isset($ini['admins'][strtolower($user->name)])
		&& $ini['admins'][strtolower($user->name)] == UID) {
			$targetUID = intval($matches[2]);
		}
	}
} 

require_once('php/start.php');

switch($_GET['act'])
{	
	case 'user':
		include('php/profil.php');
		break;	
	case 'pandemie':
		include(('php/pandemie.php'));
		break;
	case 'warning':
		include('php/warning.php');
		break;
	case 'labo':
		include('php/labo.php');
		break;
	case 'care42':
		$page->stop = true;
		$page->__destruct();
		unset($page);
		$page = new nsunTpl();
		$page->title = "Mush Contagion";
		$page->addMetaTag("ROBOTS", "NOINDEX, NOFOLLOW");
		$page->addStyleSheet('css/care42.css');
		include('php/care42.php');
		break;
	case 'radio42':
		$page->stop = true;
		$page->__destruct();
		unset($page);
		$page = new nsunTpl();
		$page->title = "Mush Contagion";
		$page->addMetaTag("ROBOTS", "NOINDEX, NOFOLLOW");
		$page->addStyleSheet('css/radio42.css');
		include('php/radio42.php');
		break;
	case 'php': //Dev: nSun
		if(DEVMODE)
		{
			if(!BETA) die();
		}
	default:
		if(BETA)
		{
			#require_once('php/start.php');
			include('beta.php');
		}
		else
		{
			#require_once('php/start.php');
			include('php/flashFrame.php');
		}
}
?>