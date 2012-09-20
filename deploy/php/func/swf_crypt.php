<?php
define('crypt_privKey', "1967BC1DD4F49845D9F711D3CC369F787133943AB1EAC6466CD8F9BD4D37BBA8D5BFA21487787F5132AC5C22CC8FC518F211AEDE453F45D2A9B9673");

//On prend le code ascii du caractère courant sur lequel on applique l'opérateur XOR
//avec de l'autre côté de l'opérateur le code ascii du caractère d'index i (modulo sa longueur pour boucler)
//de la clé privée.
//Pour s'y retrouver on sépare par des underscores

//test header('Content-Type: text/html; charset=utf-8');

/**
 * Crypte une chaine
 * @param	string	$source
 * @param	string	$key
 * @return	string
 */
function swf_crypt($src, $key=crypt_privKey) {
	$res = array();
	$lenKey = strlen($key);
	//$src= utf8_decode($src);	// Contrôle UTF-8
	for($i=0; $i < strlen($src); ++$i) $res[] = dechex(intval(ord($src[$i])) ^ ord($key[$i%$lenKey]));
	return implode('_',$res);
}

/**
 * Décrypte une chaine
 * @param	string	$src
 * @param	string	$key
 * @return	string
 */
function swf_decrypt($src, $key=crypt_privKey) {
	$res = null;
	$lenKey = strlen($key);
	$chars = explode('_',$src);
	foreach($chars as $i => $c) $res .= chr( hexdec($c)^ ord($key[$i%$lenKey]) );
	return $res;
}
?>