<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mush Contagion</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/base.css"/>
    </head>
    <body class="beta">
<?php
	if($_GET['act'] == 'infecter' && isset($_GET['id']) && isset($_GET['key']))
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
			function strip_cdata($string) 
			{ 
			    preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches); 
			    return str_replace($matches[0], $matches[1], $string); 
			} 
			
			$score .= "<dl>\n<dt>infectés</dt>\n";
			foreach($action->infectedUsers->user as $victime)
			{
				$ami = ($victime['isFriend'] == '1') ? '(un ami)' : "(quelqu'un par hasard)";
				
				$score .= "<dd>"
				."<img src='".strip_cdata($victime->avatar)."' class='avatar40' /> "
				."<strong>".strip_cdata($victime->name)."</strong> {$ami}"
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
		
		$score .= "<textarea>".$action->asXML()."</textarea>";
	}
	else $score = false;


	# Données d'accès au service
	$id = UID ? UID : null;
	$key = isset($user->key['friends']) ? $user->key['friends'] : null; // NOTE: Et OUI, il ne s'agit pas du pubkey ;)
	
	$baseGet = "?uid=".UID."&pubkey=".PUBKEY;
	$actionInfecter = $baseGet."&id=".UID."&key={$key}&act=infecter";
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
				<li>Votre état de santé *</li>
				<li><a href="<?php echo $actionInfecter; ?>">Infecter des gens</a></li>
				<li>Apperçu de la pandémie *</li>
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
		<div>* Pas fait... :P</div>
	</body>
</html>