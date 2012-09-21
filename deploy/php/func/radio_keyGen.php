<?php
/**
 * Générateur de clé d'un canal radio
 * @param	int		$No
 * @param	bool	$screen	-affichage écran (scindé en 4)
 * @return	string	$key
 */
function radio_keyGen($No,$screen=false){
	$md5 = substr(md5(":calim: coin coin !".$No." èçé* WH1T3-H4T:5UX-MY-C0CK!"),$No%16,16);
	
	//Clés possibles
	$p_keys = array(
		'0000','00f3','02d8','04be','06a3','0889','0a6e','0c53','0e39',
		'101e','1204','13e9','15cf','17b4','199a','1b7f','1d64','1f4a',
		'212f','2315','24fa','26e0','28c5','2aab','2c90','2e75',
		'305b','3240','3426','360b','37f1','39d6','3bbc','3da1','3f86',
		'416c','4351','4537','471c','4902','4ae7','4ccd', '4eb2',
		'5097','527d','5462','5648','582d','5a13','5bf8','5dde','5fc3',
		'61a8','638e','6573','6759','693e','6b24','6d09','6eef',
		'70d4','72b9','749f','7684','786a','7a4f','7c35','7e1a',
		'8000','81e5','83ca','85b0','8795','897b','8b60','8d46','8f2b',
		'9111','92f6','94db','96c1','98a6','9a8c','9c71','9e57',
		'a03c','a222','a407','a5ec','a7d2','a9b7','ab9d','ad82','af68',
		'b14d','b333','b518','b6fd','b8e3','bac8','bcae','be93',
		'c079','c25e','c444','c629','c80e','c9f4','cbd9','cdbf','cfa4',
		'd18a','d36f','d555','d73a','d91f','db05','dcea','ded0',
		'e0b5','e29b','e480', 'e666', 'e84b','ea30','ec16','edfb','efe1',
		'f1c6','f3ac','f591','f777','f95c','fb41','fd27','ff0c','ffff');
	$key = array();
	
	foreach(str_split($md5,4) as $i => $v){
		$dec = hexdec($v);
		$laps = count($p_keys);
		$m = $dec%$laps;
		
		$key[$i] = $p_keys[$m] ;
	}
	return $screen ? implode(' ',$key) : implode($key);
}
?>