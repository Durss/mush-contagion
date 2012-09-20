<?php
/**
 * Générateur de clé d'un canal radio
 * @param	int		$No
 * @param	bool	$screen	-affichage écran (scindé en 4)
 * @return	string	$key
 */
function radio_keyGen($No,$screen=false){
	$key = substr(md5("coin coin !".$No." èçé* WH1T3-H4T:5UX-MY-C0CK!"),$No%16,16);
	return $screen ? implode(' ',str_split($key,4)) : $key;
}
?>