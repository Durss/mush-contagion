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
$userLink = "uid=".UID."&pubkey=".PUBKEY;
$userService = website."php/services/userinfos.php?id={$id}&parent&pandemie";
$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);

//Le service retourne une erreur
if(isset($userinfos->error))
{
	if(isset($page)) $page->stop = false;
	header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
	$_GET['code'] = 400;
	include(dirname(__FILE__).'/../error.php');
	die("<p>{$userinfos->error['code']}</p>");
}

$twinID = (strval($userinfos->user->avatar) != null) ? getTwinID(strval($userinfos->user->avatar)) : false;
$pseudo = strval($userinfos->user->name);
$pseudoLink = $twinID ? "<a href='http://twinoid.com/user/{$twinID}' target='twinoid'>{$pseudo}</a>" : "<a href='http://muxxu.com/user/{$id}' target='twinoid'>{$pseudo}</a>";
$testMush = (bool) (intval($userinfos->user['level']) >= intval($ini['params']['infectCeil']));
$infected = $testMush ? 'true' : 'false';

$version= "2.0";

if($testMush && isset($userinfos->user->parent->spore)){
	$parentList =  array();
	$parentCount = array();
	foreach($userinfos->user->parent->spore as $spore){
		$thisUid = intval($spore['uid']);
		if(isset($parentCount[$thisUid])){
			$parentCount[$thisUid]++;
			continue;
		}
		$parentCount[$thisUid] = 1;
		$parentList[$thisUid] = "{$spore->name}";
	}
	$infectedBy = "<p class='userParent'>";
	foreach($parentList as $thisUid => $thisName){
		$thisCount = ($parentCount[$thisUid] > 1) ? "&nbsp;(x{$parentCount[$thisUid]})" : null;
		$infectedBy .= "<a href='?{$userLink}&act=p/{$thisUid}'>{$thisName}{$thisCount}</a> ";
	}
	$infectedBy .= "</p>";
}
else $infectedBy = null;
/*---------
if($testMush && isset($userinfos->user->parent, $userinfos->user->parent->spore)){
	$pName = strval($userinfos->user->parent->spore->name);
	$pUid = intval($userinfos->user->parent->spore['uid']);
	$infectedBy = "<p class='userParent'><a href='?{$userLink}&act=p/{$pUid}'>{$pName}</a></p>";
}
else $infectedBy = null;
------------*/

/*
 * CONFECTION DES SCRIPTS JS
 */
//init
$i = $f = 0;
$pagination = $aPagi = $aUsers = $dlSpore = array();
$js = null;

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
	<dt><img class="avatar ft100" id="avatar_{$i}" alt="{$spore->name}" /></dt>
	<dd class='uid' id="uid_{$i}" style="display: none;">{$spore['uid']}</dd>
	<dd class='pseudo' id="pseudo_{$i}"><a href="?{$userLink}&act=p/{$spore['uid']}">{$spore->name}</a></dd>
	<dd class='date' id="date_{$i}">{$date}</dd>
</dl>
EOTD;
	}
	$thisInfected = (bool) (intval($spore['level']) >= intval($ini['params']['infectCeil'])) ? '1' : '0'; 
	$aUsers[] = "[{$spore['uid']},'{$spore->name}',{$thisInfected},\"{$date}\"]";
	if(count($aUsers) >= $limit)
	{
		$aPagi[] = '['.implode(',',$aUsers).']';
		$pagination[] = "<option value='{$f}'>Génération N°".($f+1)."</option>";
		$f++;
		$aUsers = array();
	}
	$i++;
}

//Ajout de cases vides pour que le design soit pas pété
while(count($dlSpore) < $limit) {
		$dlSpore[] = <<<EOTD
<dl class='fiche'>
	<dt><img class="avatar ftEmpty" id="avatar_{$i}" alt="" /></dt>
	<dd class='uid' id="uid_{$i}" style="display: none;"></dd>
	<dd class='pseudo' id="pseudo_{$i}"></dd>
	<dd class='date' id="date_{$i}"></dd>
</dl>
EOTD;
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
	avatar('user', {$id}, '{$pseudo}', {$infected}, true, false);
	{$jsPagInit}
}
function select_all(target) {
	target.focus();
	target.select();
}
EOJS;

//Paramètres de la page
$page->addBodyClass('user tablette');
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/avatar.b64.js?v=1.4');
$page->addScriptFile('js/avatarToggle.js?v=1');
$page->addScript($js);

/*
 * CONFECTION DES ELEMENTS
 */
//Genre
switch($userinfos->user['genre']){
	case 'm':
		$w_infecte = 'infecté';
		$w_sain = 'sain';
		break;
	case 'f':
		$w_infecte = 'infectée';
		$w_sain = 'saine';
		break;
	case 'u': default:
		$w_infecte = 'infecté(e)';
		$w_sain = 'sain(e)';
		break;
}

//Contenu
$altMainStatus = $testMush ? "<span class='red'>{$w_infecte}</span>" : "<span class='green'>{$w_sain}</span>";
/*
$switch = ($infected == 'true')
	? "<img id='toggleAvatar' src='gfx/toggleTwino.png?uid={$id}&name={$pseudo}&infected={$infected}' alt=\"Basculer l'avatar\" />"
	: null;
EOHTML;
}
*/
$altMainAvatar = <<<EOHTML
	<div id="flash">
		<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
		<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
	</div>
	<img id="avatar_user" class="avatar ft120" alt="{$pseudo}" />
	<p class='userName'>{$pseudoLink}</p>
	<p class='userStatus'>{$altMainStatus}</p>
	{$infectedBy}
EOHTML;

//Tableau des spores
if(count($dlSpore) > 0)
{
	$tr = array_chunk($dlSpore, $grid['x']);
	$tbody = null;
	foreach($tr as $line)
	{
		$tbody .= "<tr>\n<td>\n".implode("</td>\n<td>",$line)."</td>\n</tr>\n";
	}
	$tableSpores = "<table id='spores'>\n"
	."<thead>\n<tr><td colspan='4'>{$altMainAvatar}</td>\n"
	."<td id='details' colspan='2'>Référence du dossier :\n<input readonly='readonly' onclick='select_all(this)' value='http://muxxu.com/a/".appName."/?act=p/{$id}'/>\n{$pagination}</td></tr></thead>\n"
	."<tbody>\n{$tbody}\n</tbody>\n"
	."</table>\n";
}
else{
	$tableSpores = "<table id='spores'>\n"
	."<thead>\n<tr><td colspan='4'>{$altMainAvatar}</td>\n<td colspan='2'>{$pagination}</td></tr></thead>\n"
	."</table>\n";
	//$tableSpores = "<p>Personne n'a été touché par les spores de {$pseudo}.</p>";
}

$page->c .= $tableSpores;
?>