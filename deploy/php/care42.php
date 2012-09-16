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
	require_once('php/class/care.php');
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

//Instanciation du module
$care = new care();

$interface = <<<EOHTML
<dl>
<dt>Intégrer un nouveau médecin</dt>
<dd></dd>

<dt>Soigner tout le monde</dt>
<dd></dd>

</dl>
EOHTML;

if(DEVMODE) $page->c .= "<textarea>".print_r($_POST,1)."</textarea>";

//Soigner tout le monde
//--demande de confirmation
if(isset($_POST['healAllUsers'])) {
	$page->c .= $care->confirm('healAllUsers');
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirmhealAllUsers'])) {
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->control('healAllUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	$care->db->__destruct();
	die();
}
//Ajouter un toubib
//--recherche de profils
elseif(isset($_POST['searchToubib'])){
$page->c .= <<<EOHTML
		<h1>Care 42<sup>e</sup></h1>
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Rechercher les profils</legend>
				<p>Inscrivez leurs pseudos (1 par ligne)</p>
				<textarea name="searchList"></textarea>
				<input type="submit" name="findToubibs" value="Rechercher" />
			</fieldset>
		</form>
EOHTML;
}
//--validation des profils
//--confirmation
//--execution

$medicList = array_keys($toubibs);
$medicList = "<li>".implode("</li>\n<li>",$medicList)."</li>";

$page->c .= <<<EOHTML
		<h1>Care 42<sup>e</sup></h1>
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Actions</legend>
				<input type="submit" name="healAllUsers" value="Soigner tout le monde" />
			</fieldset>
			<fieldset>
				<legend>Effectifs médicaux</legend>
				<ul>
					{$medicList}
					<li><input type="submit" name="searchToubib" value="Intégrer de nouveaus toubibs"/></li>
				</ul>
			</fieldset>
		</form>
EOHTML;
?>