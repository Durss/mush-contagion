<?php
/**
 * Enveloppe une chaîne dans une section CDATA
 * @param	string	$str	-Contenu de la section
 * @return	string
 */
function cdata($str)
{
	return "<![CDATA[{$str}]]>";
}
?>