<?php
/**
 * Crypte une clé, ou vérifie sa validité
 * @param	string	$str2crypt	-Eléments constitutifs de la clé
 * @param	string	$compare	-Clé à comparer
 * @return	<p>La clé constituée.</p><p>Si $compare est fourni, retourne true ou false, selon la validité</p>
 */
function salt($str2crypt, $compare=false)
{
	$crypt = md5(privKey.$str2crypt.'CONFIDENTIEL');
	
	if($compare) return (bool) ($crypt === $compare);
	else return $crypt;
}
?>