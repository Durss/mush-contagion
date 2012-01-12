<?php
/**
 * Insertion d'une erreur
 * @param	SimpleXMLElement	&$sxe	-Elément parent
 * @param	string	$str	-Message d'erreur
 */
function xmlError(&$sxe, $str)
{
	$sxe->addChild('error', $str);
}
?>