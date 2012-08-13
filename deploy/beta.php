<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mush Contagion</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/base.css"/>
    </head>
    <body class="beta" style="overflow: scroll !important; background-color: #226;">
<?php
	$score = false;	
			
	if($_GET['act'] == 'infecter' && isset($_GET['id'], $_GET['key']))
	{
		$atchoum = website."php/services/atchoum.php?id={$_GET['id']}&key={$_GET['key']}";
		$action = simplexml_load_file($atchoum, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$score = null;
	
		if(isset($action->result))
		{
			$score .= "<h3>Résultat = {$action->result}</h3>\n";
		}
		
		if(isset($action->infectedUsers) && isset($action->infectedUsers->user))
		{	
			$score .= "<dl>\n<dt>infectés</dt>\n";
			foreach($action->infectedUsers->user as $victime)
			{
				$ami = ($victime['isFriend'] == '1') ? '(un ami)' : "(quelqu'un par hasard)";
				
				$score .= "<dd>"
				."<img src='{$victime->avatar}' class='avatar40' /> "
				."<strong>{$victime->name}</strong> {$ami}"
				."</dd>\n";
			}
			$score .= "</dl>\n";
		}
		
		if(isset($action->error))
		{
			$score .= "<dl>\n<dt>BUG</dt>\n";
			foreach($action->error as $e)
			{
				$score .= "<dd>"
				."<code>{$e}</code>"
				."</dd>\n";
			}
			$score .= "</dl>\n";
		}
		
		#$score .= "<textarea>".$action->asXML()."</textarea>";
	}
	
	if($_GET['act'] == 'sante' && isset($_GET['id']))
	{
		$userService = website."php/services/userinfos.php?id={$_GET['id']}";
		$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		if(isset($userinfos->user['level'])) $score = "<dl><dt>Taux d'infection</dt><dd>{$userinfos->user['level']}</dd></dl>";
	}
	
	
	if($_GET['act'] == 'pandemie' && isset($_GET['id']))
	{
	#	$_GET['id'] = 2;
		$userService = website."php/services/userinfos.php?id={$_GET['id']}&pandemie";
		$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$score = null;
		
		if(count($userinfos->user->parent->spore))
		{
			$score .= "<dt>Parents</dt>\n";
			foreach($userinfos->user->parent->spore as $parent)
			{
				$score .= ""
				."<dd>".date('Y-m-d H\hi:s',intval($parent['ts']))."</dd>\n" 
				."<dd>"
				."<img src='{$parent->avatar}' class='avatar40' /> "
				."<strong>{$parent->name}</strong>"
				."</dd>\n";
			}
		}
		if(count($userinfos->user->child->spore))
		{
			$score .= "<dt>Childs</dt>\n";
			foreach($userinfos->user->child->spore as $child)
			{
				$score .= ""
				."<dd>".date('Y-m-d H\hi:s',intval($child['ts']))."</dd>\n" 
				."<dd>"
				."<img src='{$child->avatar}' class='avatar40' /> "
				."<strong>{$child->name}</strong>"
				."</dd>\n";
			}
		}
		if($score != null) $score = "<dl>\n{$score}</dl>\n";
		
	}

	# Données d'accès au service
	$id = UID ? UID : null;
	$key = isset($user->key['friends']) ? $user->key['friends'] : null; // NOTE: Et OUI, il ne s'agit pas du pubkey ;)
	
	$baseGet = "?uid=".UID."&pubkey=".PUBKEY;
	$actionHealth = $baseGet."&id=".UID."&act=sante";
	$actionInfecter = $baseGet."&id=".UID."&key={$key}&act=infecter";
	$actionPandemie = $baseGet."&id=".UID."&act=pandemie";
?>
		<div>
			<h1>Infos persos</h1>
			<p>
				<img src="<?php echo $user->avatar?>" class="avatar40 floatLeft" />
				<strong><?php echo $user->name; ?></strong>
			</p>
			<code>Flashvars : id(<?php echo $id; ?>); key(<?php echo $key; ?>);</code>
		</div>
		
		<div>
			<h1>Actions</h1>
			<ul>
				<li><a href="<?php echo $actionHealth; ?>">Votre état de santé</a></li>
				<li><a href="<?php echo $actionInfecter; ?>">Infecter des gens</a></li>
				<li><a href="<?php echo $actionPandemie; ?>">Aperçu de la pandémie</a></li>
			</ul>
		</div>
<?php 
	if($score)
	{
		echo "<div>\n"
		.$score
		."</div>";
	}
?>
	</body>
</html>