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

//Données d'accès au service
$id = UID ? UID : null;
$key = isset($user->key['friends']) ? $user->key['friends'] : null; // NOTE: Et OUI, il ne s'agit pas du pubkey ;)

$version= "2.0";

//Paramètres de la page
$page->addScriptFile('js/swfobject.js');
$page->addScriptFile('js/SWFAddress.js');
$page->addScriptFile('js/swfwheel.js');
$page->addScriptFile('js/swffit.js');

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

			flashvars['id'] = "{$id}";
			flashvars['key'] = "{$key}";
			flashvars['maintenance'] = "{$ini['status']['maintenance']}";
			flashvars['ceil'] = "{$ini['params']['infectceil']}";
			
			var attributes = {};
			attributes["id"] = "externalDynamicContent";
			attributes["name"] = "externalDynamicContent";
			
			var params = {};
			params['allowFullScreen'] = 'true';
			params['menu'] = 'false';
			params['wmode'] = 'opaque';
			
			swfobject.embedSWF("swf/contaminator.swf?v={$version}", "content", "100%", "100%", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);
			
			swffit.fit("externalDynamicContent", 800, 600, 2000, 2000, true, true);
		</script>
EOHTML;
?>