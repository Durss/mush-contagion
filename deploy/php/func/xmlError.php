<?php
/**
 * Insertion d'une erreur
 * @param	SimpleXMLElement	&$sxe	-Elément parent
 * @param	string	$code	-Code de l'erreur (api|db|xml...)
 * @param	string	$str	-Message d'erreur
 */
function xmlError(&$sxe, $code, $str=null)
{
	$error = $sxe->addChild('error', $str);
	$error->addAttribute('code', $code);
}
?>