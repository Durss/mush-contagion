<?php
$stats = array();
require_once('c/mysql.php');
require_once('php/class/mysqlManager.php');
require_once('php/class/mushSQL.php');

$db = new mushSQL($mysql_vars,1);

$db->countRealUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	$stats[] = "Nombre de personnes suceptibles d'avoir approché le mush : <strong>{$row['countRealUsers']}</strong>";
}
else $stats[] = "Impossible d'établir le nombre de personnes ayant approché le mush.";
	
$db->countInfectedUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	$stats[] = "Nombre de personnes contaminées par le spore : <strong>{$row['countInfectedUsers']}</strong>";
}
else $stats[] = "Impossible d'établir le nombre de personnes touchées par le spore.";

$db->countUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	
	$stats[] = "Envergure probable de la pandémie : <strong>{$row['countUsers']}</strong>";
}
else $stats[] = "Détail statistique indisponible.";
	
$db->__destruct();
	
if(count($stats)) $stats = "<dl>\n\t<dd>".implode("</dd>\n\t<dd>", $stats)."</dd>\n</dl>";
else $stats = false;

if($stats) $page->c .= $stats;
?>