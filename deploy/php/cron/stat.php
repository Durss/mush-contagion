<?php
/*
 * Relevé des statistiques
 */
define('DEVMODE', true);

//Filtre CRON-JOB
if(!DEVMODE && !isset($_SERVER['CRON_ID']))
{
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
	die("404 Forbidden");
}

//init
define('STAT_DELAY', 60*60); //secondes
$stat = $print = array();

require_once('../../c/config.php');
require_once('../../c/mysql.php');
require_once('../class/mysqlManager.php');
require_once('../class/mushSQL.php');

$db = new mushSQL($mysql_vars, DEVMODE);

//Date de la dernière stat enregistrée
$db->selectLastStatDate();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	
	//Vérifier le délai : Comparer la date
	if($row)
	{
		$lastDate = intval($row['date']);
		$print[] = "lastDate : <strong>{$row['date']}</strong>";
	}
	else
	{
		$lastDate = 0;
		$print[] = "lastDate : <i>no-record</i>";
	}
	
	//Délai respecté
	if(time()-STAT_DELAY >= $lastDate)
	{
		$db->countRealUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$realUsers = $row['countRealUsers'];
			$print[] = "realUsers : <strong>{$row['countRealUsers']}</strong>";
		}
		else $print[] = "/!\\ realUsers";
			
		$db->countInfectedUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$infect = $row['countInfectedUsers'];
			$print[] = "infect : <strong>{$row['countInfectedUsers']}</strong>";
		}
		else $print[] = "/!\\ infect";
		
		$db->countUsers();
		if($db->result)
		{
			$row = mysql_fetch_assoc($db->result);
			$users = $row['countUsers'];
			$print[] = "users : <strong>{$row['countUsers']}</strong>";
		}
		else $print[] = "/!\\ users";
		
		//MAJ des données
		$db->insertStat($realUsers, $users, $infect);
	}
}
else $print[] = "/!\\ date";

$db->__destruct();
	
if(count($print)) $print = "<dl>\n\t<dd>".implode("</dd>\n\t<dd>", $print)."</dd>\n</dl>";
else $print = false;

if(DEVMODE) echo $print;
?>