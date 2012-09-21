<?php
$op_radio = parse_ini_file('radio.ini');

//identification
$flow = $api->flow('user', UID, PUBKEY);
//--Vérifie que le flux est valide
if($api->notice()) $user = false;
else $user = new user($flow);
		
if($user
&& isset($op_radio[strtolower($user->name)])
&& $op_radio[strtolower($user->name)] == UID)
{
	//OK
	require_once('php/class/radio.php');
	require_once('php/func/getTwinID.php');
	require_once('php/func/tableUserResults.php');
	require_once('php/func/radio_keyGen.php');
	require_once('php/func/swf_crypt.php');
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
$radio = new radio();

if(isset($_GET['DISPLAY_POST'])) $page->c .= "<textarea>".print_r($_POST,1)."</textarea>";

$page->c .= "<h1><a href='?{$_SERVER['QUERY_STRING']}'>Labo 42.0 FM <sup>radio</sup></a></h1>";


/**
 * /Ajouter un opérateur
 */
//--recherche de profils
if(isset($_POST['searchRadio'])){
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Gestion des effectifs : Rechercher les profils</legend>
				<p>Inscrivez leurs pseudos (1 par ligne)</p>
				<div class="adv">NB : N'inscrivez que des personnes de confiance. Eviter d'inscrire des gens extérieurs au 42e</div>
				<textarea name="searchList"></textarea>
				<input type="submit" name="findOp" value="Rechercher" />
			</fieldset>
		</form>
EOHTML;
	die();
}
//--validation des profils
elseif(isset($_POST['findOp'])){
	$searchList = explode("\n",$_POST['searchList']);
	$searchList = array_map('trim', $searchList);
	
	$db = new mushSQL($mysql_vars,1);
	$db->findUsersLot($searchList);
	if($db->result){
		//Constitution du menu
		$select = "<select name='action'><optgroup label='Actions'>"
		."<option value='1'>Promouvoir au titre d'opérateur radio</option>"
		."<option value='2'>Déchoir du titre d'opérateur radio</option>"
		."</optgroup></select>";
		//Constitution du from tableau
		$page->c .= "<form method='post' action='?{$_SERVER['QUERY_STRING']}'>"
		.tableUserResults($db->result, 'setRadio', $select, 'op_radio', 'gfx/microphone.png')
		."</form>";
	}
	else $page->c .= mysql_error();
	$db->__destruct();
	die();
}
//--confirmation
elseif(isset($_POST['setRadio'])){
	$radio->db = new mushSQL($mysql_vars,1);
	$test = $radio->confirm('setRadio');
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	$radio->db->__destruct();
	die();
}
//--execution
elseif(isset($_POST['confirm'], $_POST['confirm_setRadio'])) {
	$test = $radio->control('setRadio',$op_radio);
	$page->c .= $test ? $test : "<div class='adv'>Oups : un bug ?</div>"; 
	die();
}
//Edition Messages
elseif(isset($_POST['newMsg']) || isset($_POST['editMsg'])){
	$isNew = null;
	$baseURL = "http://muxxu.com/a/".appName."/?act=labo";
	if(isset($_POST['newMsg'])){
		$isNew = " nouveau";
		//check N°
		$dir = scandir('msg');
		$No = count($dir)-1;
		$key = radio_keyGen($No);
		$key_str = radio_keyGen($No,1);
		$contents = null;
	}
	elseif(isset($_POST['editMsg'])){
		$No = intval($_POST['editMsg']);
		$key = radio_keyGen($No);
		$key_str = radio_keyGen($No,1);
		$filename = "msg/{$No}_{$key}.txt";
		if(!is_file($filename)){
			$page->c .= "<div class='adv'>Oups : un bug ?</div>";
			die();
		}
		$contents = file_get_contents($filename);
	}	

//--rédaction
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Rédaction d'un{$isNew} message</legend>
				<p>Adresse : <code>{$baseURL}/{$No}</code><input type="hidden" name="No" value="{$No}"/></p>
				<p>Clé : <code>{$key_str}</code><input type="hidden" name="key" value="{$key_str}"/></p>
				<textarea name="msg">{$contents}</textarea>
				<input type="submit" name="checkMsg" value="Valider" />
			</fieldset>
		</form>
EOHTML;
	die();
}
//--traitement-confirmation
elseif(isset($_POST['checkMsg'])){
	if(isset($_POST['No'],$_POST['msg'],$_POST['key'])
	&& is_numeric($_POST['No']) && $_POST['key'] == radio_keyGen($_POST['No'],1)){
		//fichier
		$keyFile = radio_keyGen($_POST['No']);
		$filename = "msg/".intval($_POST['No'])."_{$keyFile}.txt";
		//Contenu
		$contents = strip_tags($_POST['msg']);
		$key = md5("Pép1t0 c4c4huEttE bAmb0UlA n0ug4t ch0c4p1c".$contents."S4UC1553-4L54C13NN3");
		//création / écrasement ?
		$ecrase = (bool) is_file($filename);
		$legend = $ecrase
			? "Modifier l'émission N°{$_POST['No']}"
			: "Sauvegarder l'émission ?";
		$details = "<input type='hidden' name='No' value='{$_POST['No']}'/>"
		."<textarea name='msg' readonly='readonly'>{$contents}</textarea>";
		if($ecrase){
			$contents2 = file_get_contents($filename);
			
			if($contents != $contents2){
				$details .= "</fieldset><fieldset><legend>Ancienne version</legend>"
				."<textarea name='old' readonly='readonly'>{$contents2}</textarea>";
			}
			else{
				$page->c .= "<div class='adv'>Il n'y a pas de modification.</div>";
				die();
			}
		}
		$btn = array(
			"cancel" => "Annuler",
			"confirm_MSG" => $ecrase ? "Modifier" : "Sauvegarder",
		);
		$page->c .= <<<EOHTML
			<form method="post" action="?{$_SERVER['QUERY_STRING']}">
				<input type="hidden" name="saveMsg" value="{$key}"/>
				<fieldset>
					<legend>{$legend}</legend>
					
EOHTML;
		foreach($btn as $name => $value){
			$page->c .= "<input type='submit' name='{$name}' value='{$value}' />";
		}
		$page->c .= "{$details}</fieldset></form>";
		die();
	}
	else{
		$contents = isset($_POST['msg'])
		? "<p>Récupération de votre message :</p>"
		."<textarea name='old' readonly='readonly'>".strip_tags($_POST['msg'])."</textarea>"
		: null;
		
		$page->c .= "<div class='adv'>Oups : un bug ?</div>".$contents;
	}
}
//--enregistrement
elseif(isset($_POST['confirm_MSG'])){
	$test = false;
	if(isset($_POST['saveMsg'],$_POST['No'],$_POST['msg'])
	&& is_numeric($_POST['No'])
	&& $_POST['saveMsg'] == md5("Pép1t0 c4c4huEttE bAmb0UlA n0ug4t ch0c4p1c{$_POST['msg']}S4UC1553-4L54C13NN3")){
		$keyFile = radio_keyGen($_POST['No']);
		$filename = "msg/".intval($_POST['No'])."_{$keyFile}.txt";
		$data = strip_tags($_POST['msg']);
		$test = file_put_contents($filename, $data);
	}
	if(!$test){
		$contents = isset($_POST['msg'])
		? "<p>Récupération de votre message :</p>"
		."<textarea name='old' readonly='readonly'>".strip_tags($_POST['msg'])."</textarea>"
		: null;
		
		$page->c .= "<div class='adv'>Oups : un bug ?</div>".$contents;
		die();
	}
	else $page->c .= "<div class='adv'>Le message N°{$_POST['No']} a bien été enregistré.</div>";
}
//Gestion des messages
elseif(isset($_POST['manager'])){
	require_once('php/func/tableFiles.php');
	$page->c .= tableFiles('msg');
	die();
}
//--suppr
elseif(isset($_POST['supprMsg'], $_POST['keySuppr'])){
	$test = false;
	$filename = $_POST['supprMsg'].'_'.radio_keyGen($_POST['supprMsg']).'.txt';
	if($_POST['keySuppr'] == md5('D3L3T3'.$filename.'poûet-pouêt !') && file_exists('msg/'.$filename)){
		$test = unlink('msg/'.$filename);
	}
	if(!$test) $page->c .= "<div class='adv'>Le message n'a pu être supprimé.</div>";
	else $page->c .= "<div class='adv'>Le message N°{$_POST['supprMsg']} a été supprimé.</div>";
}


$radioList = array_keys($op_radio);
sort($radioList);
$radioList = "<li>".implode("</li>\n<li>",$radioList)."</li>";

$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Actions</legend>
				<input type="submit" name="newMsg" value="Nouveau message"/>
				<input type="submit" name="manager" value="Gérer les messages"/>
				<input type="submit" name="searchRadio" value="Gestion des effectifs"/>
			</fieldset>
			<fieldset>
				<legend>Effectifs des opérations radio</legend>
				<ul>
					{$radioList}
				</ul>
			</fieldset>
		</form>
EOHTML;
?>