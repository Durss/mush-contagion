<?php
$toubibs = parse_ini_file('soigneurs.ini');

//identification
$flow = $api->flow('user', UID, PUBKEY);
//--Vérifie que le flux est valide
if($api->notice()) $user = false;
else $user = new user($flow);
		
if($user
&& isset($toubibs[strtolower($user->name)])
&& $toubibs[strtolower($user->name)] == UID)
{
	//OK
	$page->c .= "toubib logged  in";
}
//503	Service Unavailable
else
{
	if(isset($page)) $page->stop = true;
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
	$_GET['code'] = 403;
	include('error.php');
	die();
}



?>