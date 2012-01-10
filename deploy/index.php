<?php
if(isset($_GET['act']) && $_GET['act'] == 'php') //Dev: nSun
{
	define('baseURL', NULL);
	include('php/start.php');
	die();
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mush Contagion</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/base.css"/>
		
		<script type="text/javascript" src="js/swfobject.js"></script>
		<script type="text/javascript" src="js/SWFAddress.js"></script>
		<script type="text/javascript" src="js/swfwheel.js"></script>
		<script type="text/javascript" src="js/swffit.js"></script>
    </head>
    <body>
		<div id="content1">
		<div id="content">
			<!-- p>In order to view this page you need JavaScript and Flash Player 10.2+ support!</p -->
			<p>Afin de visualiser cette page, vous devez activer JavaScript et Flash Player 10.2+</p>
			<a href="http://get.adobe.com/fr/flashplayer/">Installer flash</a>
		</div>
		</div>

		<script type="text/javascript">
<?php
	$version= "1";
?>
			var flashvars = {};
			flashvars["version"] = "<?php echo $version; ?>";
			flashvars["configXml"] = "./xml/config.xml?v=<?php echo $version; ?>";
			flashvars["lang"] = "fr";
			
			var attributes = {};
			attributes["id"] = "externalDynamicContent";
			attributes["name"] = "externalDynamicContent";
			
			var params = {};
			params['allowFullScreen'] = 'true';
			params['menu'] = 'false';
			params['wmode'] = 'direct';
			
			swfobject.embedSWF("swf/application.swf?v=<?php echo $version; ?>", "content", "100%", "100%", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);
			
			swffit.fit("externalDynamicContent", 800, 600, 2000, 2000, true, true);
		</script>

	</body>
</html>