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

require('php/func/fr_strftime.php');
require('php/func/dateRelative.php');
require('php/func/getTwinID.php');

//Données d'accès au service
if($targetUID) $id = $targetUID;
else $id = UID;

//données du profil
$userService = website."php/services/userinfos.php?id={$id}&pandemie";
$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);
$twinID = (strval($userinfos->user->avatar) != null) ? getTwinID(strval($userinfos->user->avatar)) : false;
$pseudo = strval($userinfos->user->name);
$pseudoLink = $twinID ? "<a href='http://twinoid.com/user/{$twinID}' target='twinoid'>{$pseudo}</a>" : $pseudo;
$infected = (bool) intval($userinfos->user['level']) ? 'true' : 'false';
$version= "1";

/*
 * CONFECTION DES SCRIPTS JS
 */
//init
$i = $f = 0;
$pagination = $aPagi = $aUsers = $dlSpore = array();
$js = null;
$userLink = "uid=".UID."&pubkey=".PUBKEY;

//Params
$grid = array('x' => 6, 'y' => 2);
$limit = array_product($grid);

$js .= "var limit = {$limit};
var userLink = '{$userLink}';\n";

foreach($userinfos->user->child->spore as $spore)
{
	$ts = intval($spore['ts']);
	$date = dateRelative($ts);
	if(!count($aPagi))
	{
		//Fiche d'une personne de la liste des infectés (childs)
		$dlSpore[] = <<<EOTD
<dl class='fiche'>
	<dt><img class="avatar ft100" id="avatar_{$i}" alt="uid_{$i} : {$spore['uid']}" /></dt>
	<dd class='uid' id="uid_{$i}" style="display: none;">{$spore['uid']}</dd>
	<dd class='pseudo' id="pseudo_{$i}"><a href="?{$userLink}&act=u/{$spore['uid']}">{$spore->name}</a></dd>
	<dd class='date' id="date_{$i}">{$date}</dd>
</dl>
EOTD;
	}
	$aUsers[] = "[{$spore['uid']},'{$spore->name}',1,\"{$date}\"]";
	if(count($aUsers) >= $limit)
	{
		$aPagi[] = '['.implode(',',$aUsers).']';
		$pagination[] = "<option value='{$f}'>Génération N°".($f+1)."</option>";
		$f++;
		$aUsers = array();
	}
	$i++;
}
if(count($aUsers))
{
	$aPagi[] = '['.implode(',',$aUsers).']';
	$pagination[] = "<option value='{$f}'>Génération N°".($f+1)."</option>";
}
//Pagination
if(count($pagination) >= 1)
{
	//Liste de liens : Pagination 
	if(count($pagination) > 1) $pagination = "<select onChange='page(this.value)'>\n".implode("\n", $pagination)."\n</select>\n";
	else $pagination = null;
	//Concaténation finale tu tableau de données JS
	$js .= "var table = [".implode(',',$aPagi)."];\n";
	//Instruction pour initialiser l'affichage de la première page
	$jsPagInit = "page(0);";
}
else
{
	$jsPagInit = $pagination = null;
}

//Init de l'instance de flash
$js .= <<<EOJS
function flashReady() {
	console.log("ready");
	avatar('user', {$id}, '{$pseudo}', {$infected});
	{$jsPagInit}
}
EOJS;

//Paramètres de la page
$page->addBodyClass('user tablette');
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/avatar.b64.js');
$page->addScript($js);

/*
 * CONFECTION DES ELEMENTS
 */
//Contenu
$altMainStatus = $infected == 'true' ? '<span class="red">infecté</span>' : '<span class="green">sain</span>';
$altMainAvatar = <<<EOHTML
	<div id="flash">
		<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
		<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
	</div>
	<img id="avatar_user" class="avatar ft120" alt="{$pseudo}" />
	<p class='userName'>{$pseudoLink}</p>
	<p class='userStatus'>{$altMainStatus}</p>
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
	."<thead>\n<tr><td colspan='4'>{$altMainAvatar}</td>\n<td colspan='2'>{$pagination}</td></tr></thead>\n"
	."<tbody>\n{$tbody}\n</tbody>\n"
	."</table>\n";
}
else $tableSpores = "<p>Personne n'a été touché par les spores de {$pseudo}.</p>";

$page->c = $tableSpores;
?>