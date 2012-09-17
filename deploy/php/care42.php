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
	require_once('php/func/getTwinID.php');
	require_once('php/func/tableUserResults.php');
	$page->addScriptFile('js/misc.js');
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

if(isset($_GET['DISPLAY_POST'])) $page->c .= "<textarea>".print_r($_POST,1)."</textarea>";

$page->c .= "<h1><a href='?{$_SERVER['QUERY_STRING']}'>Fungi-Care 42<sup>mg</sup></a></h1>";

/**
 * Soigner tout le monde
 */
//--demande de confirmation
if(isset($_POST['healAllUsers'])) {
	$page->c .= $care->confirm('healAllUsers');
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirm_healAllUsers'])) {
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->control('healAllUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	$care->db->__destruct();
	die();
}
/**
 * /Ajouter un toubib
 */
//--recherche de profils
elseif(isset($_POST['searchToubib'])){
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Gestion des effectifs : Rechercher les profils</legend>
				<p>Inscrivez leurs pseudos (1 par ligne)</p>
				<div class="adv">NB : N'inscrivez que des personnes de confiance. Eviter d'inscrire des gens extérieurs au 42e</div>
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
		//Constitution du menu
		$select = "<select name='action'><optgroup label='Actions'>"
		."<option value='1'>Promouvoir au titre de Soigneur</option>"
		."<option value='2'>Déchoir du titre de Soigneur</option>"
		."</optgroup></select>";
		//Constitution du from tableau
		$page->c .= "<form method='post' action='?{$_SERVER['QUERY_STRING']}'>"
		.tableUserResults($db->result, 'setToubibs', $select)
		."</form>";
	}
	else $page->c .= mysql_error();
	$db->__destruct();
	die();
}
//--confirmation
elseif(isset($_POST['setToubibs'])){
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->confirm('setToubibs');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	$care->db->__destruct();
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirm_setToubibs'])) {
	$test = $care->control('setToubibs',$toubibs);
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	die();
}
/**
 * Soins ciblés
 */
//--recherche de profils
elseif(isset($_POST['healTargetUsers'])){
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Soins ciblés : Rechercher les profils</legend>
				<p>Inscrivez leurs pseudos (1 par ligne)</p>
				<textarea name="searchList"></textarea>
				<input type="submit" name="findTargetUsers" value="Rechercher" />
			</fieldset>
		</form>
EOHTML;
	die();
}
//--validation des profils + choix technique
elseif(isset($_POST['findTargetUsers'])){
	$searchList = explode("\n",$_POST['searchList']);
	$searchList = array_map('trim', $searchList);
	
	$db = new mushSQL($mysql_vars,1);
	$db->findUsersLot($searchList);
	if($db->result){
		//Constitution du menu
		$select = "<select name='action'><optgroup label='Actions'>"
		."<option value='1'>Soigner les profils sélectionnés</option>"
		."<option value='2'>Soigner les profils et leurs enfants</option>"
		."<option value='3'>Soigner uniquement  les enfants des profils sélectionnés</option>"
		."</optgroup></select>";
		//Constitution du from tableau
		$page->c .= "<form method='post' action='?{$_SERVER['QUERY_STRING']}'>"
		.tableUserResults($db->result, 'setTargetUsers', $select)
		."</form>";
	}
	else $page->c .= mysql_error();
	$db->__destruct();
	die();
}
//--confirmation
elseif(isset($_POST['setTargetUsers'])){
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->confirm('setTargetUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	$care->db->__destruct();
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirm_setTargetUsers'])) {
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->control('setTargetUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>";
	$care->db->__destruct();
	die();
}
/**
 * Soins au pifomètre
 */
//--paramétrage
elseif(isset($_POST['healSomeUsers'])){
	$init = array(
		'qty' => rand(floor($ini['params']['queryLimit']/4),$ini['params']['queryLimit']*4),
		'qtyMax' => $ini['params']['queryLimit']*4,
		'ceil' => rand(1,$ini['params']['infectCeil']+1),
		'ceilMax' => $ini['params']['infectCeil']+1,
		'compare' => array(
			"<option value='1'>égal à</option>",
			"<option value='2'>supérieur ou égal à</option>",
			"<option value='3'>inférieur ou égal à</option>",
		),
	);
	shuffle($init['compare']);
	$init['compare'] = implode($init['compare']);
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}" onsubmit="return pifometreConfirm()">
			<fieldset>
				<legend>Soigner au pifomètre : Critères</legend>
				<dl>
					<dt><label><input type="checkbox" name="osef"/> OSEF</label></dt>
					<dd>Opter pour des critère aléatoires</dd>
				
					<dt><label><input type="checkbox" name="setQty"/> Quantités :</label> <span id="rangeQty">{$init['qty']}</span></dt>
					<dd><input type="range" name="qty" value="{$init['qty']}" max="{$init['qtyMax']}" min="1" step="1" onchange="rangeReturn(this,'rangeQty');"/></dd>
					
					<dt><label><input type="checkbox" name="setFrame"/> Seuil d'infection</label></dt>
					<dd><select name="compare">{$init['compare']}</select> <span id="rangeCeil">{$init['ceil']}</span></dd>
					<dd><input type="range" name="ceil" value="{$init['ceil']}" max="{$init['ceilMax']}" min="1" step="1" onchange="rangeReturn(this,'rangeCeil');"/></dd>
					
					<dt><label><input type="checkbox" name="setActif"/> Comportement sur l'app :</label></dt>
					<dd><label><input type="radio" name="actif" value="yes"> Actif</label></dd>
					<dd><label><input type="radio" name="actif" value="no"> Passif</label></dd>
				</dl>
				<input type="submit" name="setHealSomeUsers" value="Valider" />
			</fieldset>
		</form>
EOHTML;
	die();
}
//--confirmation
elseif(isset($_POST['setHealSomeUsers'])){
	$test = $care->confirm('setHealSomeUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>";
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirm_setHealSomeUsers'])) {
	$care->db = new mushSQL($mysql_vars,1);
	$test = $care->control('setHealSomeUsers');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>";
	$care->db->__destruct();
	die();
}

$medicList = array_keys($toubibs);
sort($medicList);
$medicList = "<li>".implode("</li>\n<li>",$medicList)."</li>";

$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Actions</legend>
				<input type="submit" name="healAllUsers" value="Soigner tout le monde" />
				<input type="submit" name="healTargetUsers" value="Soins ciblés" />
				<input type="submit" name="healSomeUsers" value="Soins au pifomètre" />
				<input type="submit" name="searchToubib" value="Gestion des effectifs"/>
			</fieldset>
			<fieldset>
				<legend>Effectifs médicaux</legend>
				<ul>
					{$medicList}
				</ul>
			</fieldset>
		</form>
EOHTML;
?>