<?php
/**
 * Achève le XML
 * @param	SimpleXMLElement	$sxe	-Passé par référence
 */
function xmlFinish(&$sxe)
{
	if(!headers_sent()) header('Content-Type: text/xml; charset="UTF-8"');
	#if(!headers_sent()) header('Content-Type: text/html; charset="UTF-8"');
	echo $sxe->asXML();
	die();
}
?>