<?php 
/**
 * <h1>ADMIN PANEL</h1>
 * <p>Menu de paramétrage de l'app. Accès exclusif, défini dans le fichier 'params.ini'</p>
 */

//Bloque l'accès direct
if(!defined('ADMIN_OK') || !ADMIN_OK)
{
	$path = dirname(__FILE__).'/../c/usualSuspect.php';
	require_once($path);
	usualSuspect('admin_panel');
	if(isset($page)) $page->stop = false;
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	$_GET['code'] = 404;
	include(dirname(__FILE__).'/../error.php');
	die();
}

//Liste les admins
$adminList = array_keys($ini['admins']);

//Paramètres de la page
require_once('php/class/nsunTpl.php');
if(!isset($page)) $page = new nsunTpl();
$page->title = "Admin : Mush Contagion";
$page->addStyleSheet('css/admin.css');

//MAJ des paramètres
if(isset($_POST['setMaintenance']) || isset($_POST['setParams']))
{
	$do = false;
	$update = Array();
	//maintenance
	//--switch on
	if(isset($_POST['maintenance']) && !$ini['status']['maintenance'])
	{
		$update['maintenance'] = 1;
		$do = true;
	}
	//--switch off
	elseif(!isset($_POST['maintenance']) && $ini['status']['maintenance'])
	{
		$update['maintenance'] = 0;
		$do = true;
	}
	else $update['maintenance'] = $ini['status']['maintenance'];

	//infectSelf'
	//--switch on
	if(isset($_POST['infectSelf']) && !$ini['params']['infectSelf'])
	{
		$update['infectSelf'] = 1;
		$do = true;
	}
	//--switch off
	elseif(!isset($_POST['infectSelf']) && $ini['params']['infectSelf'])
	{
		$update['infectSelf'] = 0;
		$do = true;
	}
	else $update['infectSelf'] = $ini['params']['infectSelf'];

	//infectCover
	//--switch on
	if(isset($_POST['infectCover']) && !$ini['params']['infectCover'])
	{
		$update['infectCover'] = 1;
		$do = true;
	}
	//--switch off
	elseif(!isset($_POST['infectCover']) && $ini['params']['infectCover'])
	{
		$update['infectCover'] = 0;
		$do = true;
	}
	else $update['infectCover'] = $ini['params']['infectCover'];

	//infectPerTurn
	if(is_numeric($_POST['infectPerTurn']) && $_POST['infectPerTurn'] != $ini['params']['infectPerTurn'])
	{
		$update['infectPerTurn'] = $_POST['infectPerTurn'];
		$do = true;
	}
	else $update['infectPerTurn'] = $ini['params']['infectPerTurn'];
	
	if($do)
	{
		$page->c .= "<pre>".print_r($_POST,1)."</pre>"; 
		
		$date = date('Y-m-d H\hi:s');
		$iniAdminList = null;
		foreach($ini['admins'] as $n => $id) $iniAdminList .= "{$n}={$id}\n";

//INI FILE
		$content = <<<EOINI
;update {$date}	by {$name}
[status]
maintenance={$update['maintenance']};		(bool)	Fermer/Ouvrir l'application au public

[params]
infectCover={$update['infectCover']};		(bool)	Infecter les personnes déjà contaminée s'il n'y a plus de personnes aines disponibles
infectSelf={$update['infectSelf']};		(bool)	Infection d'office de l'utilisateur
infectPerTurn={$update['infectPerTurn']};	(int)	Nombre d'infection par tour

[admins]; pseudo=uid
{$iniAdminList}
EOINI;

		//MAJ du fichier ini
		if(file_put_contents('params.ini', $content)) $page->c .= "<div class='msg'>Configuration mise à jour avec succes.</div>\n";
		else $page->c .= "<div class='msg'>Echec de la mise à jour.</div>\n";
		
		//refresh
		$ini = parse_ini_file('params.ini',1);
	}
}

//Configure le formulaire
$maintenance = ($ini['status']['maintenance'] == 1)
	? ' checked="checked"'
	: null;
$infectSelf = ($ini['params']['infectSelf'] == 1)
	? ' checked="checked"'
	: null;
$infectCover = ($ini['params']['infectCover'] == 1)
	? ' checked="checked"'
	: null;
$infectPerTurn = $ini['params']['infectPerTurn'];

//Liste les admins
$adminList = "<li>".implode("</li>\n\t\t\t\t\t<li>",$adminList)."</li>";

//Contenu
$page->c .= <<<EOHTML
		<h1>Admin</h1>
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<fieldset>
				<legend>Statut</legend>
				<label><input type="checkbox" name="maintenance"{$maintenance} /> Maintenance (cocher pour désactiver le site au public)</label>
			</fieldset>
			<fieldset>
				<input type="submit" name="setMaintenance" value="enregistrer" />
			</fieldset>
			<fieldset>
				<legend>Paramètres</legend>
					<label><input type="checkbox" name="infectSelf"{$infectSelf} /> Auto-infection (Le visiteur est infecté d'office)</label>
					<label><input type="checkbox" name="infectCover"{$infectCover} /> Surinfection (possibilité d'être contaminé plusieurs fois)</label>
					<label>Nombre d'infections par tours : <input type="text" name="infectPerTurn" value="{$infectPerTurn}" /></label>
			</fieldset>
			<fieldset>
				<input type="submit" name="setParams" value="enregistrer" />
			</fieldset>
			<fieldset>
				<legend>Liste Admins</legend>
				<ul>
					{$adminList}
					<li><button onclick="javascript:alert('ça fera 30€ payable d\'avance.\n:P'); return false;">Ajouter</button></li>
				</ul>
			</fieldset>
		</form>
EOHTML;
?>