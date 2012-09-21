<?php
/**
 * Contaténation en remplaçant les retours par le caractère $end
 * @param	string	$src
 * @param	string	$end
 * @return	string
 */
function swf_concat($src, $end='|||'){
	return utf8_decode(preg_replace('#([\n|\r]+)#m', $end, $src));
}
?>