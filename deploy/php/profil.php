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
$infected = (intval($userinfos->user['level']) >= intval($ini['params']['infectCeil'])) ? 'true' : 'false';
$testMush = (bool) (intval($userinfos->user['level']) >= intval($ini['params']['infectCeil']));
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
		var imgSwitch = document.getElementById('switch2');
		img.src = "data:image/png;base64,"+flash.getImage({$userVars}, false);
		if(imgSwitch){
			imgSwitch.src = "gfx/switch2Cube.png"+imgSwitch.getAttribute('tmp');
		}
		
		var dlBtn =document.getElementById('avatarDownload');
		img.style.cursor = "pointer";
		img.onclick = dlBtn.onclick = function() {
			window.open(document.getElementById('uAvatar').src,'_blank');
		}
	}
	
	function select_all(target) {
		target.focus();
		target.select();
	}
EOJS;

//Paramètres de la page
$page->addBodyClass('user profil');
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/avatarToggle.js?v=1');
$page->addScript($js);
/*
 * CONFECTION DES ELEMENTS
 */
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

$parentName = '--';
$dateInfection = '--';
if($testMush && isset($userinfos->user->parent, $userinfos->user->parent->spore)){
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
		if($dateInfection == '--') $dateInfection = dateRelative(intval($spore['ts']));
	}
	$parentName = null;
	foreach($parentList as $thisUid => $thisName){
		$thisCount = ($parentCount[$thisUid] > 1) ? "&nbsp;(x{$parentCount[$thisUid]})" : null;
		$parentName .= "<a href='?{$userLink}&act=p/{$thisUid}'>{$thisName}{$thisCount}</a> ";
	}
}

//quickMisc
$quickMisc = array();
//Switch-avatar
$switchData = "?uid={$id}&name={$pseudo}&infected={$infected}";
$switch = $testMush ? "<img id='switch2' tmp='{$switchData}'/>" : null;
$switchON = $testMush ? 'sw_on' : 'sw_off';

$altMainAvatar = <<<EOHTML
	<div id="flash">
		<p>Afin d'utiliser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
		<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
	</div>
	<img id="uAvatar" class="avatar ft120 {$switchON}" alt="{$pseudo}"/>
	{$switch}
	<p class='userName'>{$pseudoLink}</p>
	<p class='userStatus'>{$altMainStatus}</p>
	<p class='quickMisc'><button class="btn" id="avatarDownload"><img src="gfx/dl.png" width="16" height="16" alt="" /> Avatar HD</button></p>
EOHTML;


//Transcodeur mush
if($id == UID && $testMush){
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
			
			var lastCryptTime = -100;
			/*
			 * @param bool t (true: decrypt;false: encrypt;)
			 */
			function m_crypt(t) {
				var time = new Date().getTime();
				if(time - lastCryptTime > 1000) {
					if(t){ //encrypt
						document.getElementById("mushTranscryptor").value = document.getElementById("content").encrypt(document.getElementById("mushTranscryptor").value);
					}
					else{ //decrypt
						document.getElementById("mushTranscryptor").value = document.getElementById("content").decrypt(document.getElementById("mushTranscryptor").value);
					}
					lastCryptTime = time;
				}
			}
			
EOJS;
	$page->addScript($js);
	$mushTranscryptor = <<<EOHTML
		<tr>
			<td colspan="2" class="TM_menu">
				<span>Transcrypteur Mush :</span>
				<img src="gfx/fleche-UR.png" alt="" width="21" height="21" />
				<input type="submit" class="btn" value="Crypter" onclick="m_crypt(1)" />
				<input type="submit" class="btn" value="Décrypter" onclick="m_crypt(0)" />
				<img src="gfx/fleche-RD.png" alt="" width="21" height="21" />
			</td>
		</tr>
		<tr>
			<td colspan="2" class="TM">
				<div id="content">
					<!--p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p-->
					<!--a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a-->
				</div>
				<textarea class="text" id="mushTranscryptor" ondblclick="select_all(this);">Utilisez le transcrypteur pour communiquer entre Mushs à l'insu de ces horribles humains.</textarea>
				<!--textarea class="mushTranscryptor" id="result" readonly="readonly" onclick="select_all(this);"></textarea-->
			</td>
		</tr>
EOHTML;
}
else{
	function embedCC($txt,$n=0){//readonly='readonly'
		return "<textarea  onclick='select_all(this);'>{$txt}\n[link=http://muxxu.com/a/".appName."/?act=warning]Flash d'information sur la contagion[/link]</textarea>";
//				<embed src="http://twinoid.com/swf/copyButton.swf?v=1" quality="high" bgcolor="#1d2028" width="150" height="48" name="copy_{$n}" type="application/x-shockwave-flash" flashvars="text={$txt}&amp;color=#1d2028&amp;label=Collez cette affiche\nsur votre Nexus&amp;confirm=Faites CTRL&#43;V dans un\nmessage du Nexus" allowscriptaccess="always" menu="false" scale="noScale" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="opaque">
	}
	$cc = array(
		'wc' => embedCC('http://flic.kr/p/da9edy',1),
		'champi' => embedCC('http://flic.kr/p/da9e2z',2),
		'oeil' => embedCC('http://flic.kr/p/da9efh',3),
		'xoxo' => embedCC('http://flic.kr/p/da9eeq',4),
	);
	$mushTranscryptor = <<<EOHTML
		<tr class="recommandations">
			<td class="prudence champi">Ne caressez pas un champignon que vous ne connaissez pas. Surtout s'il a l'air gentil.</td>
			<td>{$cc['champi']}</td>
		</tr><tr class="recommandations">
			<td class="prudence xoxo">Préférez la bise à la mode chez les inuits, en frottant le bout de votre nez sur celui de votre partenaire.</td>
			<td>{$cc['xoxo']}</td>
		</tr><tr class="recommandations">
			<td class="prudence wc">Ne tirez la chasse qu'en cas d'extrême urgence.</td>
			<td>{$cc['wc']}</td>
		</tr><tr class="recommandations">
			<td class="prudence oeil">Autant éviter les contacts oculaires inutiles, fermez les yeux avant de vous endormir.</td>
			<td>{$cc['oeil']}</td>
		</tr>
EOHTML;
}
$fiche = <<<EOHTML
<div id='ficheProfil'>
	<table class="diagnostic">
		<tr>
			<td class="fCol">{$altMainAvatar}</td>
			<td class="dCol l2"><dl id="details">
				<dt>Origine présumée de l'infection</dt><dd class="parentList">{$parentName}</dd>
				<dt>Date probable de l'incubation</dt><dd>{$dateInfection}</dd>
			</dl></td>
		</tr>
		{$mushTranscryptor}
	</table>
</div>
EOHTML;

$page->c .= $fiche;
?>