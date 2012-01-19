<?php
/**
 * Supprime le balisage CDATA
 * @param	string	$string	-Chaine à décoffrer
 * @return	string
 */
function strip_cdata($string) 
{ 
    preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches); 
    return str_replace($matches[0], $matches[1], $string); 
}
?>