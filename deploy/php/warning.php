<?php
$stats = array();
require_once('c/mysql.php');
require_once('php/class/mysqlManager.php');
require_once('php/class/mushSQL.php');

$db = new mushSQL($mysql_vars,1);

$page->addBodyClass('warning');

$db->selectStats(0,1);
if($db->result){
	$row = mysql_fetch_assoc($db->result);

	if($row['realUsers'] > 0){
		$pl1 = $row['realUsers'] > 1 ? 's ont' : ' a';
		$countRealUsers= "<strong>{$row['realUsers']}</strong>&nbsp;personne{$pl1}";
	}
	else $countRealUsers = "certaines personnes ont";
	
	
	if($row['infect'] > 0){
		$pl2 = $row['infect'] > 1 ? 's' : null;
		$countInfectedUsers = "déplore déjà <strong>{$row['infect']}</strong>&nbsp;victime{$pl2}, et ce nombre";
	}
	else $countInfectedUsers= "ignore combien le mush a pu faire de victimes, mais cela ";
	
	if($row['infect'] >= $row['users']){
		$countInfectedUsers = "déplore déjà <strong>{$row['infect']}</strong>&nbsp;victime{$pl2}";
		$countUsers = null;
	}
	elseif($row['users'] > 0){
		$pl3 = $row['users'] > 1 ? 's' : null;
		$countUsers = " pourrait vite passer à <strong>{$row['users']}</strong>";
	}
	else $countUsers = " pourrait vite devenir ingérable";
}
else{
	$countRealUsers = "certaines personnes ont";
	$countInfectedUsers= "ignore combien le mush a pu faire de victimes, mais cela ";
	$countUsers = " pourrait vite devenir ingérable";
}	
$db->__destruct();

$page->c .= <<<EOTXT
<div id="affiche">
<h1>PRUDENCE !</h1>
<p>Par inconscience, {$countRealUsers} approché un spécimen mush extrèmement
volatile et contagieux. On {$countInfectedUsers}{$countUsers}&nbsp;!</p>
</div>
EOTXT;
?>