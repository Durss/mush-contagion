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

if(isset($_POST['stats']))
{
	$stats = array();
	require('c/mysql.php');
	require_once('php/class/mysqlManager.php');
	require_once('php/class/mushSQL.php');
		
	$db = new mushSQL($mysql_vars,1);
	
	$db->countRealUsers();
	if($db->result)
	{
		$row = mysql_fetch_assoc($db->result);
		$stats[] = "Nombre de visiteurs : <strong>{$row['countRealUsers']}</strong>";
	}
	else $page->c .= "<div class='adv'>Le décompte des visiteurs a échoué.</div>\n"
	."<div class='adv'>".mysql_error()."</div>\n";
	
	$db->countInfectedUsers();
	if($db->result)
	{
		$row = mysql_fetch_assoc($db->result);
		$stats[] = "Nombre de personnes infectées : <strong>{$row['countInfectedUsers']}</strong>";
	}
	else $page->c .= "<div class='adv'>Le décompte des personnes infectées a échoué.</div>\n"
	."<div class='adv'>".mysql_error()."</div>\n";
	
	$db->tableStatus();
	if($db->result)
	{
		while($row = mysql_fetch_assoc($db->result))
		{
			switch($row['Name'])
			{
				case $db->tbl['user']:
					$stats[] = "Nombre d'enregistements dans la table 'Users' : <strong>{$row['Rows']}</strong>";
					break;
				case $db->tbl['link']:
					$stats[] = "Nombre d'enregistements dans la table 'Links' : <strong>{$row['Rows']}</strong>";
					break;
			}
		}
	}
	else $page->c .= "<div class='adv'>Statistiques indisponibles.</div>\n"
	."<div class='adv'>".mysql_error()."</div>\n";
	
	$db->__destruct();
	
	if(count($stats)) $stats = "<dl>\n\t<dd>".implode("</dd>\n\t<dd>", $stats)."</dd>\n</dl>";
	else $stats = false;
}
else $stats = false;

//Soigner tout le monde
if(isset($_POST['healUsers']))
{
	$key = md5('confirmHealUsers'.date('YmdH').floor(date('i')/12).'zobiiii la mouche');
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<input type="hidden" name="confirmHealUsers" value="{$key}"/>
			<fieldset>
				<legend>Soigner tous les 'users'</legend>
				<p>Attribuer à tous les profil un niveau d'infection = 0</p>
				<input type="submit" name="cancel" value="Annuler" />
				<input type="submit" name="confirm" value="Soigner" />
			</fieldset>
		</form>
EOHTML;
	die();
}
elseif(isset($_POST['confirm'])
&&	isset($_POST['confirmHealUsers']))
{
	$key = md5('confirmHealUsers'.date('YmdH').floor(date('i')/12).'zobiiii la mouche');
	if($_POST['confirmHealUsers'] != $key)
	{
		$page->c .= "<div class='adv'>Délai de confirmation dépassé, Les 'users' <strong>n'ont pas été</strong> soignés</div>\n";
	}
	else
	{
		require('c/mysql.php');
		require_once('php/class/mysqlManager.php');
		require_once('php/class/mushSQL.php');
		
		$db = new mushSQL($mysql_vars,1);
		
		$db->healEveryone();
		
		if($db->result) $page->c .= "<div class='adv'>Tous les 'users' ont été soignés.</div>\n";
		else $page->c .= "<div class='adv'>Les 'users' <strong>n'ont pas été</strong> soignés.</div>\n"
		."<div class='adv'>".mysql_error()."</div>\n";
		
		$db->__destruct();
	}
}
//RAZ des tables
if(isset($_POST['truncateLinks']))
{
	$key = md5('confirmTruncateLinks'.date('YmdH').floor(date('i')/12).'zobiii la mouche');
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<input type="hidden" name="confirmTruncateLinks" value="{$key}"/>
			<fieldset>
				<legend>Vider la table 'links'</legend>
				<input type="submit" name="cancel" value="Annuler" />
				<input type="submit" name="confirm" value="Vider" />
			</fieldset>
		</form>
EOHTML;
	die();
}
elseif(isset($_POST['confirm'])
&&	isset($_POST['confirmTruncateLinks']))
{
	$key = md5('confirmTruncateLinks'.date('YmdH').floor(date('i')/12).'zobiii la mouche');
	if($_POST['confirmTruncateLinks'] != $key)
	{
		$page->c .= "<div class='adv'>Délai de confirmation dépassé, la table 'links' <strong>n'a pas été</strong> vidée</div>\n";
	}
	else
	{
		require('c/mysql.php');
		require_once('php/class/mysqlManager.php');
		require_once('php/class/mushSQL.php');
		
		$db = new mushSQL($mysql_vars,1);
		
		$db->truncateLinks();
		
		if($db->result) $page->c .= "<div class='adv'>La table 'links' a été vidée.</div>\n";
		else $page->c .= "<div class='adv'>La table 'links' <strong>n'a pas été</strong> vidée.</div>\n"
		."<div class='adv'>".mysql_error()."</div>\n";
		
		$db->__destruct();
	}
}
//RAZ des tables
elseif(isset($_POST['truncateUsers']))
{
	$key = md5('confirmTruncateUsers'.date('YmdH').floor(date('i')/12).'zobi la mouche');
	$page->c .= <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<input type="hidden" name="confirmTruncateUsers" value="{$key}"/>
			<fieldset>
				<legend>Vider la table 'users'</legend>
				<input type="submit" name="cancel" value="Annuler" />
				<input type="submit" name="confirm" value="Vider" />
			</fieldset>
		</form>
EOHTML;
	die();
}
elseif(isset($_POST['confirm'])
&&	isset($_POST['confirmTruncateUsers']))
{
	$key = md5('confirmTruncateUsers'.date('YmdH').floor(date('i')/12).'zobi la mouche');
	if($_POST['confirmTruncateUsers'] != $key)
	{
		$page->c .= "<div class='adv'>Délai de confirmation dépassé, la table 'users' <strong>n'a pas été</strong> vidée</div>\n";
	}
	else
	{
		require('c/mysql.php');
		require_once('php/class/mysqlManager.php');
		require_once('php/class/mushSQL.php');
		
		$db = new mushSQL($mysql_vars,1);
		
		$db->truncateUsers();
		
		if($db->result) $page->c .= "<div class='adv'>La table 'users' a été vidée.</div>\n";
		else $page->c .= "<div class='adv'>La table 'users' <strong>n'a pas été</strong> vidée.</div>\n"
		."<div class='adv'>".mysql_error()."</div>\n";
		
		$db->__destruct();
	}
}

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

	//infectCeil
	if(is_numeric($_POST['infectCeil']) && $_POST['infectCeil'] != $ini['params']['infectCeil'])
	{
		$update['infectCeil'] = $_POST['infectCeil'];
		$do = true;
	}
	else $update['infectCeil'] = $ini['params']['infectCeil'];

	//infectPerTurn
	if(is_numeric($_POST['infectPerTurn']) && $_POST['infectPerTurn'] != $ini['params']['infectPerTurn'])
	{
		$update['infectPerTurn'] = $_POST['infectPerTurn'];
		$do = true;
	}
	else $update['infectPerTurn'] = $ini['params']['infectPerTurn'];
	
	//queryLimit
	if(is_numeric($_POST['queryLimit']) && $_POST['queryLimit'] != $ini['params']['queryLimit'])
	{
		$update['queryLimit'] = $_POST['queryLimit'];
		$do = true;
	}
	else $update['queryLimit'] = $ini['params']['queryLimit'];
	
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
infectCeil={$update['infectCeil']};			(int)	Seuil d'infection
infectPerTurn={$update['infectPerTurn']};	(int)	Nombre d'infection par tour
queryLimit={$update['queryLimit']};		(int)	Limitation du nombre de profils évalués dans les requêtes de tirage au sort

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
$infectCeil = $ini['params']['infectCeil'];
$infectPerTurn = $ini['params']['infectPerTurn'];
$queryLimit = $ini['params']['queryLimit'];

//Liste les admins
$adminList = "<li>".implode("</li>\n\t\t\t\t\t<li>",$adminList)."</li>";

//Statistiques
if(!$stats) $stats = '<input type="submit" name="stats" value="Statistiques générales" />';

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
					<label>Seuil d'infection : <input type="text" name="infectCeil" value="{$infectCeil}" /></label>
					<label>Nombre d'infections par tours : <input type="text" name="infectPerTurn" value="{$infectPerTurn}" /></label>
					<label>Nombre d'ID par requètes : <input type="text" name="queryLimit" value="{$queryLimit}" /></label>
			</fieldset>
			<fieldset>
				<input type="submit" name="setParams" value="enregistrer" />
			</fieldset>
			<fieldset>
				<legend>Stats</legend>
				{$stats}
			</fieldset>
			<fieldset>
				<legend>RAZ</legend>
				<input type="submit" name="healUsers" value="Soigner tous les 'users'" />
				<input type="submit" name="truncateUsers" value="Vider table 'users'" />
				<input type="submit" name="truncateLinks" value="Vider table 'links'" />
			</fieldset>
			<fieldset>
				<legend>Liste Admins</legend>
				<ul>
					{$adminList}
					<li><button onclick="javascript:alert('ça fera 30€ payable d\'avance.\\n:P'); return false;">Ajouter</button></li>
				</ul>
			</fieldset>
		</form>
EOHTML;
?>