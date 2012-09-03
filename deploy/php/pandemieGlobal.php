<?php
$stats = array();
require_once('c/mysql.php');
require_once('php/class/mysqlManager.php');
require_once('php/class/mushSQL.php');

$db = new mushSQL($mysql_vars,1);

$page->addBodyClass('warning');

$db->countRealUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	$pl1 = $row['countRealUsers'] > 1 ? 's ont' : ' a';
	
	$countRealUsers= "<strong>{$row['countRealUsers']}</strong>&nbsp;personne{$pl1}";
}
else $countRealUsers = "certaines personnes ont";
	
$db->countInfectedUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	$pl2 = $row['countInfectedUsers'] > 1 ? 's' : null;
	$countInfectedUsers = "déplore déjà <strong>{$row['countInfectedUsers']}</strong>&nbsp;victime{$pl2}, et ce nombre";
}
else $countInfectedUsers= "ignore combien le mush a pu faire de victimes, mais cela ";

$db->countUsers();
if($db->result)
{
	$row = mysql_fetch_assoc($db->result);
	$pl3 = $row['countUsers'] > 1 ? 's' : null;
	$countUsers = "passer à <strong>{$row['countUsers']}</strong>";
}
else $countUsers = "devenir ingérable";
	
$db->__destruct();

$page->c .= <<<EOTXT
<div id="affiche">
<h1>PRUDENCE !</h1>
<p>Par inconscience, {$countRealUsers} approché un spécimen mush extrèmement
volatile et contagieux. On {$countInfectedUsers} pourrait vite {$countUsers}&nbsp;!</p>
</div>
EOTXT;
?>