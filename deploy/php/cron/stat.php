<?php
/*
 * Relevé des statistiques
 */
define('DEVMODE', false);
$print = null;

//Filtre CRON-JOB
if(!DEVMODE && !isset($_SERVER['CRON_ID']))
{
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
	die("404 Forbidden");
}

//init
define('STAT_DELAY', 60*60-1); //secondes

require_once('../../c/config.php');
require_once('../../c/mysql.php');
require_once('../class/mysqlManager.php');
require_once('../class/mushSQL.php');

require('../func/xmlError.php');
require('../func/xmlFinish.php');

//Fichier XML de référence
$base = website.'xml/stats.xml';
//Intanciation de l'objet XML
$xml = new SimpleXMLElement($base, 0, 1);

$db = new mushSQL($mysql_vars, DEVMODE);

//Date de la dernière stat enregistrée
$db->selectLastStatDate();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	
	//Vérifier le délai : Comparer la date
	if($row)
	{
		date_default_timezone_set('Europe/Paris');
		$lastDate = strtotime($row['date']);
		$xml_lastDate = $xml->addChild('d',$lastDate);
	}
	else $xml_lastDate = $xml->addChild('d', 'no-record');
	
	$xml_lastDate->addAttribute('label', 'selectLastStatDate');
	
	//Délai respecté
	if(time()-STAT_DELAY >= $lastDate)
	{
		$db->countRealUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$realUsers = $row['countRealUsers'];
			$xml_realUsers = $xml->addChild('d',$row['countRealUsers']);
		}
		else $xml_realUsers = $xml->addChild('d', 'no-result');
		
		$xml_realUsers->addAttribute('label', 'countRealUsers');
			
		$db->countInfectedUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$infect = $row['countInfectedUsers'];
			$xml_infect = $xml->addChild('d',$row['countInfectedUsers']);
		}
		else $xml_infect = $xml->addChild('d', 'no-result');
		
		$xml_infect->addAttribute('label', 'countInfectedUsers');
		
		$db->countUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$nbUsers = $row['countUsers'];
			$xml_users = $xml->addChild('d',$row['countUsers']);
		}
		else $xml_users = $xml->addChild('d', 'no-result');
		
		$xml_users->addAttribute('label', 'countUsers');
		
		//MAJ des données
		$db->insertStat($realUsers, $nbUsers, $infect);
		if(DEVMODE) $print = "\$db->insertStat({$realUsers}, {$nbUsers}, {$infect});";
	}
}
else {
	$xml_lastDate = $xml->addChild('d', 'no-result');
	$xml_lastDate->addAttribute('label', 'selectLastStatDate');
}

$db->__destruct();

xmlFinish($xml);

//if(DEVMODE) echo $print;
?>