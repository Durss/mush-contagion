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

if(DEVMODE) $page->c .= "<textarea>".print_r($_POST,1)."</textarea>";

$page->c .= "<h1><a href='?{$_SERVER['QUERY_STRING']}'>Care 42<sup>e</sup></a></h1>";

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
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Rechercher les profils</legend>
				<p>Inscrivez leurs pseudos (1 par ligne)</p>
				<textarea name="searchList"></textarea>
				<input type="submit" name="findToubibs" value="Rechercher" />
			</fieldset>
		</form>
EOHTML;
	die();
}
//--validation des profils
elseif(isset($_POST['findToubibs'])){
	$searchList = explode("\n",$_POST['searchList']);
	$searchList = array_map('trim', $searchList);
	
	$db = new mushSQL($mysql_vars,1);
	$db->findUsersLot($searchList);
	if($db->result){
		$page->c .= "<table border='1'>"
		."<tr><td>uid</td><td>pseudo</td><td>Actif</td><td>Etat</td><td>[Twinoid]</td><td>[Muxxu]</td></tr>";
		
		while($row = mysql_fetch_assoc($db->result)){
			if(!empty($row['avatar'])){
				require_once('php/func/getTwinID.php');
				$twinID = getTwinID($row['avatar']);
				$twin = $twinID ? "<a href='http://twinoid.com/user/{$twinID}' target='twinoid'>link</a>" : null;
			}
			else $twin = null;
			$actif = empty($row['pubkey']) ? 'non' : 'oui';
			$state = $row['infected'] < $ini['params']['infectCeil']
				? '<img src="gfx/toggleTwino.png" width="16" height="16" alt="c"/>'
				: '<img src="gfx/toggleMush.png" width="16" height="16" alt="m"/>';
			$page->c .= "<tr>"
			."<td>{$row['uid']}</td>"
			."<td>{$row['name']}</td>"
			."<td>{$actif}</td>"
			."<td>{$state}</td>"
			."<td>{$twin}</td>"
			."<td><a href='http://muxxu.com/user/{$row['uid']}' target='muxxu'>link</a></td>"
			."</tr>";
		}
		$page->c .= "</table>";
	}
	else $page->c .= mysql_error();
	$db->__destruct();
	die();
}
//--confirmation
//--execution

$medicList = array_keys($toubibs);
$medicList = "<li>".implode("</li>\n<li>",$medicList)."</li>";

$page->c .= <<<EOHTML
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