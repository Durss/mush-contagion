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

require_once('func/radio_keyGen.php');
require_once('func/swf_crypt.php');
require_once('func/swf_concat.php');

//Recherche le message
$msg = false;
if(is_int($msgID)){
	$key = radio_keyGen($msgID);
	$filename = "msg/{$msgID}_{$key}.txt";
	if(file_exists($filename)){
		$msg = swf_concat(file_get_contents($filename));
	}
}

//Introuvable : Premier message qui traine
if(!$msg){
	foreach(scandir('msg/') as $filename){
		//filtres
		if(!is_file('msg/'.$filename)) continue;
		$pattern = '#^(?<No>[1-9][0-9]*)_(?<key>[0-9a-f]{16})\.txt$#';
		if(!preg_match($pattern, $filename, $matches)) continue;
		$key = $matches['key'];
		$msg = swf_concat(file_get_contents('msg/'.$filename));
	}
}

//Toujours pas de message
if(!$msg){
	$key = '000080008000ffff';
	$msg = utf8_decode("Vous me recevez ?|||Bien, désormais nous savons que la radio fonctionne.");
}

//cryptage

$clean = "key==={$key}&&&diags==={$msg}";

$data = swf_crypt($clean);

$version= "1.4";

//Paramètres de la page
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/SWFAddress.js');
$page->addScriptFile('js/swfwheel.js');
$page->addScriptFile('js/swffit.js');
$page->addBodyClass('labo');

//Contenu
$page->c .= <<<EOHTML
		<div id="content1">
		<div id="content">
			<div id="warn">
			<!-- p>In order to view this page you need JavaScript and Flash Player 10.2+ support!</p -->
			<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
			<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
			</div>
		</div>
		</div>

		<script type="text/javascript">
			var flashvars = {};
			flashvars["version"] = "{$version}";
			flashvars["configXml"] = "./xml/config.xml?v={$version}";
			flashvars["lang"] = "fr";

			flashvars['data'] = "{$data}";
			flashvars['maintenance'] = "{$ini['status']['maintenance']}";
			
			var attributes = {};
			attributes["id"] = "externalDynamicContent";
			attributes["name"] = "externalDynamicContent";
			
			var params = {};
			params['allowFullScreen'] = 'true';
			params['menu'] = 'false';
			params['wmode'] = 'transparent';
			
			swfobject.embedSWF("swf/labo.swf?v={$version}", "content", "671px", "377px", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);
			
			//swffit.fit("externalDynamicContent", 800, 600, 2000, 2000, true, true);
		</script>
EOHTML;
?>