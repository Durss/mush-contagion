<?php
/**
 * Sauvegarde les logs
 * @param	mixed	$data
 * @return	bool
 */
function setLog($data){
	
	if(is_string($data)) $line = $data;
	elseif(is_array($data)) $line = implode("\t",$data);
	else $line = preg_replace('#[\r|\n]+#m',"\t", print_r($data,1));
	
	$date = date('Y-m-d H:i:s');
	
	return file_put_contents(logFile, $date."\tu/".UID."\t{$line}\n", FILE_APPEND);
}
?>