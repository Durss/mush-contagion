<?php 
//Bloque l'accès direct
if(!defined('ADMIN_OK') || !ADMIN_OK)
{
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	require('c/usualSuspect.php');
	usualSuspect('admin_panel');
	die();
}
$adminList = array_keys($ini['admins']);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Admin : Mush Contagion</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/admin.css" />
    </head>
    <body>
<?php
//MAJ


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
		echo "<pre>".print_r($_POST,1)."</pre>"; 
		$content = ";update ".date('Y-m-d H\hi:s')."\tby {$name}\n"
		."[status]\n"
		."maintenance={$update['maintenance']};		(bool)	Fermer/Ouvrir l'application au public\n"
		."\n"
		."[params]\n"
		."infectCover={$update['infectCover']};		(bool)	Infecter les personnes déjà contaminée s'il n'y a plus de personnes aines disponibles\n"
		."infectSelf={$update['infectSelf']};		(bool)	Infection d'office de l'utilisateur\n"
		."infectPerTurn={$update['infectPerTurn']};	(int)	Nombre d'infection par tour\n"
		."\n"
		."[admins]; pseudo=uid\n";
		foreach($ini['admins'] as $n => $id) $content .= "{$n}={$id}\n";
		
		if(file_put_contents('params.ini', $content)) echo "<div class='msg'>Configuration mise à jour avec succes.</div>\n";
		else echo "<div class='msg'>Echec de la mise à jour.</div>\n";
		
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

?>
		<h1>Admin</h1>
		<form method="post" action="?<?php echo $_SERVER['QUERY_STRING']; ?>">
			<fieldset>
				<legend>Statut</legend>
				<label><input type="checkbox" name="maintenance"<?php echo $maintenance; ?> /> Maintenance (cocher pour désactiver le site au public)</label>
			</fieldset>
			<fieldset>
				<input type="submit" name="setMaintenance" value="enregistrer" />
			</fieldset>
			<fieldset>
				<legend>Paramètres</legend>
					<label><input type="checkbox" name="infectSelf"<?php echo $infectSelf; ?> /> Auto-infection (Le visiteur est infecté d'office)</label>
					<label><input type="checkbox" name="infectCover"<?php echo $infectCover; ?> /> Surinfection (possibilité d'être contaminé plusieurs fois)</label>
					<label>Nombre d'infections par tours : <input type="text" name="infectPerTurn" value="<?php echo $infectPerTurn; ?>" /></label>
			</fieldset>
			<fieldset>
				<input type="submit" name="setParams" value="enregistrer" />
			</fieldset>
			<fieldset>
				<legend>Liste Admins</legend>
				<ul>
					<?php echo "<li>".implode("</li>\n\t\t\t\t\t<li>",$adminList)."</li>"; ?>
					<li><button onclick="javascript:alert('ça fera 30€ payable d\'avance.\n:P'); return false;">Ajouter</button></li>
				</ul>
			</fieldset>
		</form>
	</body>
</html>