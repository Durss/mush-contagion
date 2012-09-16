<?php

/*
 * USERINFOS : Service d'information sur un utilisateur, ses parents et enfants directs
 */

define('baseURL','../../');

//Compléments de base
require(baseURL.'php/msg.php');
require(baseURL.'c/config.php');
require(baseURL.'php/func/pReturn.php');
require(baseURL.'c/mysql.php');

//Gestion DB
require(baseURL.'php/class/mysqlManager.php');
require(baseURL.'php/class/mushSQL.php');

//Fonctions utiles XML
require(baseURL.'php/func/xmlError.php');
require(baseURL.'php/func/xmlFinish.php');

//mtlib et DTO (gestion de l'API Muxxu)
require(baseURL.'php/class/mtlib.php');
require(baseURL.'php/class/dto/base_dto.php');
require(baseURL.'php/class/dto/user.php');


/*
 * Initialisation
 */
$ini = parse_ini_file(baseURL.'params.ini');

//Fichier XML de référence
$base = website.'/xml/userinfos.xml';
//Intanciation de l'objet XML
$userinfos = new SimpleXMLElement($base, 0, 1);

//Initialise la connexion à l'API
$api = new mtlib(appName, privKey);

//Vérifie les coordonnées
//--identifiant utilisateur
if(isset($_GET['id']) && $api->is_id($_GET['id']))
{
	define('UID', intval($_GET['id']));
}
//--identifiant erroné
else
{
	define('UID', false);
	xmlError($userinfos, 'GET_INVALID_UID');
}

//--Option de pandémie
define('PARENT_INFOS', (bool) isset($_GET['parent']));
define('FULL_INFOS', (bool) isset($_GET['pandemie']));

//Initialisation du gestionnaire DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//Recherche le profil dans la base
if(! $db->selectUsers(1, array(UID), '1')) //En cas d'erreur SQL
{
	xmlError($userinfos, 'MYSQL_QUERY_FAIL_7', mysql_error());
	xmlFinish($userinfos);
}

//Si UID ne correspond pas à un utilisateur connu de près ou de loin
if(! mysql_num_rows($db->result))
{
	xmlError($userinfos, 'APP_USER_NOT_FOUND');
	xmlFinish($userinfos);
}

//Déploiement des données
$userData = mysql_fetch_assoc($db->result);

//dernière infection effectuée
if(! $db->selectLastInfection(UID)) //En cas d'erreur SQL
{
	xmlError($userinfos, 'MYSQL_QUERY_FAIL_10', mysql_error());
	xmlFinish($userinfos);
}

//Si UID n'a pas encore effectué d'infection
if(! mysql_num_rows($db->result)) $lastInfection = false;
else
{
	$row = mysql_fetch_assoc($db->result);
	//Convertir la date en timestamp..
	date_default_timezone_set('Europe/Paris');
	$lastInfection = strtotime($row['date']);
}

//Si le détail du parent est demandé
if(PARENT_INFOS || FULL_INFOS)
{
	//init
	$parentData = array();
	
	//Recherche une infection parent
	if($userData['infected'])
	{
		if(! $db->selectParents(UID)) //En cas d'erreur SQL
		{
			xmlError($userinfos, 'MYSQL_QUERY_FAIL_8');
		}
		elseif(mysql_num_rows($db->result))
		{
			//Déploiement des parents
			while($row = mysql_fetch_assoc($db->result)) $parentData[] = $row;
		}
	}
}

//Si une information complète est demandée
if(FULL_INFOS)
{
	//init
	$childData = array();
	
	//Recherche une infection enfant
	if(! $db->selectChilds(UID)) //En cas d'erreur SQL
	{
		xmlError($userinfos, 'MYSQL_QUERY_FAIL_9');
	}
	elseif(mysql_num_rows($db->result))
	{
		//Déploiement des childs
		while($row = mysql_fetch_assoc($db->result)) $childData[] = $row;
	}
}

/*
 * Finitions du XML
 */

//Elément <userinfos><user>
$user = $userinfos->addChild('user');
$user->addAttribute('uid', UID);
$user->addAttribute('level', $userData['infected']);
$user->addAttribute('genre', $userData['genre']);

$user->addChild('name', $userData['name']);
$user->addChild('avatar', $userData['avatar']);


//Controle du délai
if($lastInfection)
{
	//Compte le nombre d'infections
	$countChilds = 0;
	if($db->countChilds(UID)) //En cas d'erreur SQL
	{
		if($row = mysql_fetch_assoc($db->result)){
			$countChilds = ceil(intval($row['countChilds'])/intval($ini['infectPerTurn']));
			//var_dump('$row',$row,'$countChilds',$countChilds,"\$row['countChilds']",$row['countChilds'],"intval(\$row['countChilds'])",intval($row['countChilds']),"intval(\$ini['infectPerTurn'])",intval($ini['infectPerTurn']));
			//die();
		}
	}
	
	//delay = pow($ini['coefMaxDelay'],$countChilds-$ini['ceilDelay']) * $delayBase
	//définition du délai : delay = coef^x * base
	$delayBase = intval($ini['infectDelay']);

	if($countChilds <= $ini['ceilDelay']) $laps = $delayBase;
	elseif($countChilds >= $ini['topDelay']){
		$x = intval($ini['topDelay']);
		$coef = floatval($ini['coefMaxDelay']);
		$laps = round(pow($coef,$x) * $delayBase);
	}
	else{
		$x = $countChilds-intval($ini['ceilDelay']);
		$coef = floatval($ini['coefMaxDelay']);
		$laps = round(pow($coef,$x) * $delayBase);
	}
	
	//respect du délai
	if(time() - $laps >= $lastInfection)
	{
		//Délai respecté
		$delay = $user->addChild('delay');
		$delay->addAttribute('wait', '0');
		$delay->addAttribute('lastInfection', $lastInfection);
		$delay->addAttribute('ctrl', $ini['infectDelay'].'|'.$ini['ceilDelay'].'|'.$ini['topDelay'].'|'.$ini['coefMaxDelay']);
		
	}
	else
	{
		//Veuillez patienter
		$wait = $lastInfection + $laps - time();
		$delay = $user->addChild('delay');
		$delay->addAttribute('wait', $wait);
		$delay->addAttribute('lastInfection', $lastInfection);
		$delay->addAttribute('ctrl', $ini['infectDelay'].'|'.$ini['ceilDelay'].'|'.$ini['topDelay'].'|'.$ini['coefMaxDelay']);
	//	xmlFinish($root);
	}
}
else {
	$delay = $user->addChild('delay');
	$delay->addAttribute('wait', '0');
	$delay->addAttribute('ctrl', $ini['infectDelay'].'|'.$ini['ceilDelay'].'|'.$ini['topDelay'].'|'.$ini['coefMaxDelay']);
}

//Déconnexion de la base.
$db->__destruct();

//Information sur le parent
if(PARENT_INFOS || FULL_INFOS)
{
	$parent = $user->addChild('parent');
	if(count($parentData))
	{
		foreach($parentData as $s)
		{
			$spore = $parent->addChild('spore');
			$spore->addAttribute('uid', $s['parent']);
			$spore->addAttribute('ts', strtotime($s['date']));
			$spore->addChild('name', $s['name']);
			if(strlen($s['avatar'])) $spore->addChild('avatar', $s['avatar']);
			else $spore->addChild('avatar');
		}
	}
}
//Information sur les enfants
if(FULL_INFOS)
{	
	$child = $user->addChild('child');
	if(count($childData))
	{
		foreach($childData as $s)
		{
			$spore = $child->addChild('spore');
			$spore->addAttribute('uid', $s['child']);
			$spore->addAttribute('ts', strtotime($s['date']));
			$spore->addAttribute('level', $s['infected']);
			$spore->addChild('name', $s['name']);
			if(strlen($s['avatar'])) $spore->addChild('avatar', $s['avatar']);
			else $spore->addChild('avatar');
		}
	}
}

//Finalise
#echo $userinfos->asXML();
xmlFinish($userinfos);
?>