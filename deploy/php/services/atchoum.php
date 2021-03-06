<?php
/*
 * ATCHOUM ! Service d'infection express !
 */
//DEBUG
#header('Content-Type: text/html; charset="UTF-8"');

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
require(baseURL.'php/class/dto/friends.php');

//Parametres
$ini = parse_ini_file(baseURL.'params.ini');
//--Nombre de personnes à infecter
$max = intval($ini['infectPerTurn']);

/*
 * Initialisation
 */
//Fichier XML de référence
$base = website.'xml/atchoum.xml';
//Intanciation de l'objet XML
$root = new SimpleXMLElement($base, 0, 1);

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
	xmlError($root, 'GET_INVALID_UID');
}
//--clé friends (utilisateur)
if(isset($_GET['key']) && $api->is_key($_GET['key']))
{
	define('FRIENDS_KEY', strval($_GET['key']));
}
//--clé friends erronée
else
{
	define('FRIENDS_KEY', false);
	xmlError($root, 'GET_INVALID_KEY');
}

/*
 * Récupère la liste d'amis
 */
$flowOK = false;
if(UID && FRIENDS_KEY)
{
	$flow = $api->flow('friends', UID, FRIENDS_KEY);
	//En cas d'erreur avec l'API ou le flux
	if($api->notice()) foreach($api->notice() as $error) xmlError($root, str_replace('mtlib:','MUXXU_',$error['type']), $error['rawData']);
	//Pas d'erreur c'est lessieur
	else
	{
		$friends = new friends($flow);
		$flowOK = true;
	}
}

//Paire invalide id/key ou flux friend indisponible
if(!UID || !FRIENDS_KEY || !$flowOK) xmlFinish($root);

//Ouverture d'une connexion avec la DB
$db = new mushSQL($mysql_vars, isset($_GET['debug']));

//dernière infection effectuée
if(! $db->selectLastInfection(UID)) //En cas d'erreur SQL
{
	xmlError($root, 'MYSQL_QUERY_FAIL_10', mysql_error());
	xmlFinish($root);
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
//Controle du délai
if($lastInfection)
{
	//Compte le nombre d'infections
	$countChilds = 0; //
	if($db->countChildsByWave(UID, $ini['RAZprogress'])) //En cas d'erreur SQL
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
		$wait = intval(0);
	}
	else
	{
		//Veuillez patienter
		$wait = $lastInfection + $laps - time();
		$delay = $root->addChild('delay');
		$delay->addAttribute('wait', $wait);
		$delay->addAttribute('lastInfection', $lastInfection);
		$delay->addAttribute('ctrl', $ini['infectDelay'].'|'.$ini['ceilDelay'].'|'.$ini['topDelay'].'|'.$ini['coefMaxDelay']);
		xmlFinish($root);
	}
}

//Dresse la liste des amis pour une requête SQL
//--Initialisation du gestionnaire DB
$list = Array();
if(is_array($friends->list) && count($friends->list)){
	$friendsID = array_keys($friends->list);
	//--mélange
	shuffle($friendsID);
	$chunkFriendsID = array_chunk($friendsID, $ini['queryLimit']);

	#$i=0;
	#$temps_debut = microtime(true);
	
	foreach($chunkFriendsID as $f)
	{
		$select = false;
		#echo ++$i;
		//Sélectionne tous les amis qui ne sont pas encore infectés.
		if(! $db->selectUsers(1, $f, $max - count($list), $ini['infectCeil'],1))
		{
			//En cas d'erreur SQL
			xmlError($root, 'MYSQL_QUERY_FAIL_1');
			xmlFinish($root);
		}
		else $select = $db->result;
		
		//Déplie la liste
		if($select && mysql_num_rows($select))
		{
			while($row = mysql_fetch_assoc($select))
			{
				//MAJ de l'infection au plus vite
				if(!$db->updateInfection(array($row['uid'])))
				{
					//En cas d'erreur SQL
					xmlError($root, 'MYSQL_QUERY_FAIL_6');
					xmlFinish($root);
				}
				$list[intval($row['uid'])] = $row;
			}
		}
		
		//Soupape
		if(count($list) >= $max) break;
	}
}

//Pour les gens qui n'ont pas ou peu d'amis non-infectés
if(count($list) < $max)
{
	if(count($list))
	{
		$exclude = array_keys($list);
		$exclude[] = UID;
	}
	else $exclude = array(UID);
	
	$select = false;
	if(! $db->selectUsers(0, $exclude, $max - count($list), $ini['infectCeil'], 1))
	{
		//En cas d'erreur SQL
		xmlError($root, 'MYSQL_QUERY_FAIL_2');
		xmlFinish($root);	
	}
	else $select = $db->result; 
	
	if($select && mysql_num_rows($select))
	{
		while($row = mysql_fetch_assoc($select))
		{
			//MAJ de l'infection au plus vite
			if(!$db->updateInfection(array($row['uid'])))
			{
				//En cas d'erreur SQL
				xmlError($root, 'MYSQL_QUERY_FAIL_6');
				xmlFinish($root);
			}
			$list[intval($row['uid'])] = $row;
		}
	}
}

/*
 * Processus d'infection
 */
//Auto-infection
if($ini['infectSelf'])
{
	if(!$db->insertLink(array(UID), 0))
	{
		//En cas d'erreur SQL
		xmlError($root, 'MYSQL_QUERY_FAIL_3');
		xmlFinish($root);
	}
	
	if(!$db->updateInfection(array(UID)))
	{
		//En cas d'erreur SQL
		xmlError($root, 'MYSQL_QUERY_FAIL_4');
		xmlFinish($root);
	}
}

if(count($list))
{
	if(!$db->insertLink(array_keys($list), UID))
	{
		//En cas d'erreur SQL
		xmlError($root, 'MYSQL_QUERY_FAIL_5');
		xmlFinish($root);
	}

	/*
	 * note : Intert rapproché de la sélection
	if(!$db->updateInfection(array_keys($list)))
	{
		//En cas d'erreur SQL
		xmlError($root, 'MYSQL_QUERY_FAIL_6');
		xmlFinish($root);
	}
	*/
}
//Tout le monde est infecté
else
{
	xmlError($root, 'APP_FULL_INFECTED');
	xmlFinish($root);
}

//Déconnexion de la base.
$db->__destruct();

#$temps_fin = microtime(true);
#echo '<div>Temps d\'execution : '.round($temps_fin - $temps_debut, 4).'</div>';

#file_put_contents("p{$ini['queryLimit']}.records.txt",round($temps_fin - $temps_debut, 4)."\n", FILE_APPEND);
/*
 * Finitions du XML
 */
//Elément <root><result>
$root->result = count($list);

//Elément <root><infectedUsers>
$infectedUsers = $root->addChild('infectedUsers');

foreach($list as $target)
{	
	//Elément <root><infectedUsers><user>
	$user = $infectedUsers->addChild('user');
	$user->addAttribute('uid', $target['uid']);
	$user->addAttribute('level', $target['infected']);
	$isFriend = (is_array($friends->list) && isset($friends->list[$target['uid']])) ? 1 : 0;
	$user->addAttribute('isFriend', $isFriend);

	$user->addChild('name', $target['name']);
	if(strlen($target['avatar'])) $user->addChild('avatar', $target['avatar']);
	else $user->addChild('avatar');
}

//Finalise
#echo $root->asXML();
xmlFinish($root);
?>