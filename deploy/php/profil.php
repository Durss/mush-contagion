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

$userLink = "uid=".UID."&pubkey=".PUBKEY;

//Données d'accès au service
if($targetUID) $id = $targetUID;
else $id = UID;

//données du profil
$userService = website."php/services/userinfos.php?id={$id}&parent";
$userinfos = simplexml_load_file($userService, 'SimpleXMLElement', LIBXML_NOCDATA);
$twinID = (strval($userinfos->user->avatar) != null) ? getTwinID(strval($userinfos->user->avatar)) : false;
$pseudo = strval($userinfos->user->name);
$pseudoLink = $twinID ? "<a href='http://twinoid.com/user/{$twinID}' target='twinoid'>{$pseudo}</a>" : $pseudo;
$infected = (bool) intval($userinfos->user['level']) ? 'true' : 'false';
$version= "1";

$userVars = "'{$id}', '{$pseudo}', {$infected}, true";

$js = <<<EOJS
	//Créé le flash invisible
	var userLink = "";
	var attributes = {};
	var params = {};
	params['allowScriptAccess'] = 'always';
	params['menu'] = 'false';
	var flashvars = {};
	flashvars["canDownload"] = "false";
	swfobject.embedSWF("swf/avatar.swf?v=1.1", "flash", "0", "0", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);
	
	function flashReady() {
		var flash = document.getElementById('flash');
		var img = document.getElementById('uAvatar');
		img.style.cursor = "pointer";
		img.onclick = function() {
			window.open(this.src,'_avatar');
		}
		img.src = "data:image/png;base64,"+flash.getImage({$userVars});
	}
EOJS;

//Paramètres de la page
$page->addBodyClass('user profil');
$page->addScriptFile('js/swfobject.js');
$page->addScript($js);
/*
 * CONFECTION DES ELEMENTS
 */
//Contenu
$altMainStatus = $infected == 'true' ? '<span class="red">infecté</span>' : '<span class="green">sain</span>';

$parentName = '--';
$dateInfection = '--';
if(isset($userinfos->user->parent->spore)){
	$parentName = "<a href='?{$userLink}&act=p/{$userinfos->user->parent->spore['uid']}'>{$userinfos->user->parent->spore->name}</a>";
	$dateInfection = dateRelative(intval($userinfos->user->parent->spore['ts']));
}

$altMainAvatar = <<<EOHTML
	<div id="flash">
		<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
		<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
	</div>
	<img id="uAvatar" class="avatar ft120" alt="{$pseudo}"/>
	<p class='userName'>{$pseudoLink}</p>
	<p class='userStatus'>{$altMainStatus}</p>
EOHTML;

//Transcodeur mush
if($id == UID && $infected == 'true'){
	$js = <<<EOJS
			var attributesTM = {};
			var flashvarsTM = {};
			var paramsTM = {};
			flashvarsTM["version"] = "1";
			flashvarsTM["setSecureOff"] = "yes";
			paramsTM['allowScriptAccess'] = 'always';
			paramsTM['allowFullScreen'] = 'true';
			paramsTM['menu'] = 'false';
			
			swfobject.embedSWF("swf/language.swf?v=1", "content", "0", "0", "10.2", "swf/expressinstall.swf", flashvarsTM, paramsTM, attributesTM);
			
			function encrypt() {
				document.getElementById("result").value = document.getElementById("content").encrypt(document.getElementById("text").value);
			}
			
			function decrypt() {
				document.getElementById("result").value = document.getElementById("content").decrypt(document.getElementById("text").value);
			}
			function select_all(target) {
				target.focus();
				target.select();
			}
EOJS;
	$page->addScript($js);
	$mushTranscryptor = <<<EOHTML
		<tr>
			<td colspan="2" class="TM_menu">
				<span>Transcrypteur Mush :</span>
				<img src="gfx/fleche-UR.png" alt="" width="21" height="21" />
				<input type="submit" class="btn" value="Crypter" onClick="encrypt()" />
				<input type="submit" class="btn" value="Décrypter" onClick="decrypt()" />
				<img src="gfx/fleche-RD.png" alt="" width="21" height="21" />
			</td>
		</tr>
		<tr>
			<td colspan="2" class="TM">
				<div id="content">
					<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
					<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
				</div>
				<textarea class="mushTranscryptor" id="text" onClick="select_all(this);">Utilisez le transcrypteur pour communiquer entre Mushs à l'insu de ces horribles humains.</textarea>
				<textarea class="mushTranscryptor" id="result" readonly="readonly" onClick="select_all(this);"></textarea>
			</td>
		</tr>
EOHTML;
}
else $mushTranscryptor = null;


$fiche = <<<EOHTML
<div id='ficheProfil'>
	<table class="diagnostic">
		<tr>
			<td class="fCol">{$altMainAvatar}</td>
			<td><dl>
				<dt>Origine présumée de l'infection</dt><dd>{$parentName}</dd>
				<dt>Date probable de l'incubation</dt><dd>{$dateInfection}</dd>
			</dl></td>
		</tr>
		{$mushTranscryptor}
	</table>
</div>
EOHTML;

$page->c .= $fiche;
?>