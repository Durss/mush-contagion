<?php
//Bloque l'accès direct
if(!isset($page))
{
	if(isset($page)) $page->stop = false;
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	$_GET['code'] = 404;
	include(dirname(__FILE__).'/../error.php');
	die();
}

//Params
$grid = array('x' => 6, 'y' => 2);
$limit = array_product($grid);

//Données d'accès au service
if($targetUID) $id = $targetUID;
else $id = UID;
#$key = isset($user->key['friends']) ? $user->key['friends'] : null; // NOTE: Et OUI, il ne s'agit pas du pubkey ;)

//données du profil
$userService = website."php/services/userinfos.php?id={$id}&pandemie";
$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);

$pseudo = strval($userinfos->user->name);
$infected = (bool) intval($userinfos->user['level']) ? 'true' : 'false';
$version= "1";

/*
 * CONFECTION DES SCRIPTS JS
 */
//init
$i = $f = 0;
$pagination = $aPagi = $aUsers = $dlSpore = array();

$js = "avatar('main', {$id}, '{$pseudo}', {$infected}, true);\n";
foreach($userinfos->user->child->spore as $spore)
{
	if(!count($aPagi))
	{
		$js .= "avatar({$i},{$spore['uid']},'{$spore->name}',1,false);\n";
		$dlSpore[] = <<<EOTD
<dl class='fiche'>
	<dt><div id="avatar_{$i}"></div></dt>
	<dd class='uid' id="uid_{$i}">{$spore['uid']}</dd>
	<dd class='pseudo' id="pseudo_{$i}">{$spore->name}</dd>
</dl>
EOTD;
	}
	$aUsers[] = "[{$spore['uid']},'{$spore->name}',1]";
	if(count($aUsers) >= $limit)
	{
		$aPagi[] = '['.implode(',',$aUsers).']';
		$pagination[] = "<li><a href=\"javascript:page({$f});\">page ".$f++."</a></li>";
		$aUsers = array();
	}
	$i++;
}
if(count($aUsers))
{
	$aPagi[] = '['.implode(',',$aUsers).']';
	$pagination[] = "<li><a href=\"javascript:page({$f});\">page {$f}</a></li>";
}
//Pagination
if(count($pagination) > 1)
{
	$js .= "var table = [".implode(',',$aPagi)."];\n";
	$pagination = "<tr>\n<td colspan='3'>\n<ul>\n".implode("\n", $pagination)."\n</ul>\n</td>\n</tr>\n";
}
else $pagination = null;

//Paramètres de la page
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/avatar.js');
$page->addScript($js);

/*
 * CONFECTION DES ELEMENTS
 */
//Contenu
$altMainAvatar = <<<EOHTML
		<div id="avatar_main">	
			<!-- p>In order to view this page you need JavaScript and Flash Player 10.2+ support!</p -->
			<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
			<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
		</div>
EOHTML;

//Tableau des spores
if(count($dlSpore))
{
	$tr = array_chunk($dlSpore, $grid['x']);
	$tbody = null;
	foreach($tr as $line)
	{
		$tbody .= "<tr>\n<td>\n".implode("</td>\n<td>",$line)."</td>\n</tr>\n";
	}
	$tableSpores = "<table id='spores'>\n"
	."<thead>\n{$pagination}</thead>\n"
	."<tbody>\n{$tbody}\n</tbody>\n"
	."<tfoot>\n{$pagination}</tfoot>\n"
	."</table>\n";
}
else $tableSpores = "<p>Personne n'a été touché par les spores de {$pseudo}.</p>";

$page->c = $altMainAvatar."\n".$tableSpores;
#$page->c .= "<script type='text/javascript'>\n".$swfAvatar->embedSWF()."\n</script>";

/*
if(!$targetUID)
{
	$language = new swfObject();
	$language->id = "mushLanguage";
	$language->width = $language->height = "0";
	$language->version = "10.2";
	$language->expressInstallSwfurl = "swf/expressinstall.swf";
	
	$language->flashvars = array(
		"version" => $version,
	);
	if(DEVMODE) $language->flashvars["setSecureOff"] = "yes";
	
	$language->attributes = array(
		"id" => "mushLanguage",
		"class" => "flashFrame",
		"name" => "mushLanguage",
	);
	$language->params = array(
		"allowScriptAccess" => "always",
		"allowFullScreen" => "false",
		"menu" => "false",
	);
	
/*	$page->c .= $swfAvatar->alt();
	$script = $swfAvatar->embedSWF();
	$page->c .= <<<EOSCRIPT
<script type="text/javascript">
function encrypt() {
	document.getElementById("result").value = document.getElementById("mushLanguage").encrypt(document.getElementById("text").value);
}
			
function decrypt() {
	document.getElementById("result").value = document.getElementById("mushLanguage").decrypt(document.getElementById("text").value);
}
</script>";
EOSCRIPT;
	$page->c .= <<<EOFORM
<input type="text" id="text" value="" />
<input type="submit" value="Encrypt" onClick="encrypt()" />
<input type="submit" value="Decrypt" onClick="decrypt()" />
<br />
<textarea id="result" cols="60" rows="10"></textarea>
EOFORM;

}
else $language = null;
*/
#$page->c .= $adv.$avatar;
#var_dump($userinfos);
?>