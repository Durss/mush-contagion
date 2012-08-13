<?php
/**
 * Trouve l'identifiant twinoid dans le nom de fichier de l'avatar
 * @param	string	$urlAvatar
 * @return	int	-false en cas d'échec
 */
function getTwinID($urlAvatar){
	$pattern = "#/(twinoid|muxxu|hordes)/(?:[0-9a-f]/[0-9a-f]/[0-9a-f]+_)([1-9][0-9]*)\.jpg$#i";
	if(preg_match($pattern, $urlAvatar, $matches)) {
		switch($matches[1]) {
			case 'twinoid':
				return  intval($matches[2]);
				case 'muxxu':
					break;
				default:
					break;
		}
	}
	return false;
}
?>