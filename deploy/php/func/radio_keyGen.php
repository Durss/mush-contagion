<?php
/**
 * Générateur de clé d'un canal radio
 * @param	int		$No
 * @param	bool	$screen	-affichage écran (scindé en 4)
 * @return	string	$key
 */
function radio_keyGen($No,$screen=false){
	$md5 = substr(md5("coin coin !".$No." èçé* WH1T3-H4T:5UX-MY-C0CK!"),$No%16,16);
	$parts = str_split($md5,4);
	
	//Clés possibles
	$p_keys = array(
		'0000','01e5','03cb','05b0','0796','097b','0b61','0d46','0f2c','1111','12f6','14dc','16c1','18a7','1a8c','1c72','1e57','203d','2222','2407','25ed','27d2','29b8','2b9d','2d83','2f68','314e','3333','3518','36fe','38e3','3ac9','3cae','3e94','4079','425f','4444','4629','480f','49f4','4bda','4dbf','4fa5','518a','5370','5555','573a','5920','5b05','5ceb','5ed0','60b6','629b','6481','6666','684b','6a31','6c16','6dfc','6fe1','71c7','73ac','7592','7777','795c','7b42','7d27','7f0d','80f2','82d8','84bd','86a3','8888','8a6d','8c53','8e38','901e','9203','93e9','95ce','97b4','9999','9b7e','9d64','9f49','a12f','a314','a4fa','a6df','a8c5','aaaa','ac8f','ae75','b05a','b240','b425','b60b','b7f0','b9d6','bbbb','bda0','bf86','c16b','c351','c536','c71c','c901','cae7','cccc','ceb1','d097','d27c','d462','d647','d82d','da12','dbf8','dddd','dfc2','e1a8','e38d','e573','e758','e93e','eb23','ed09','eeee','f0d3','f2b9','f49e','f684','f869','fa4f','fc34','fe1a','ffff'
	);
	$key = array();
	
	foreach($parts as $i => $v){
		$dec = hexdec($v);
		$laps = count($p_keys);
		$m = $dec%$laps;
		
		$key[$i] = $p_keys[$m] ;
	}
	return $screen ? implode(' ',$key) : implode($key);
}

$len = 270;
$inc = hexdec('ffff') / $len;
var_dump($inc);
$step = 2;
$stock = array();
for($i = 0; $i <= $len; $i+=$step) {
    $stock[] = str_pad(dechex(round($inc*$i)),4,'0',STR_PAD_LEFT);
}
?>