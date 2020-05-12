<?php
/**
 * Notifie une visite irrégulière
 * @param	string	$comment	-Commentaire additionel
 */
function usualSuspect($comment=null)
{
	file_put_contents(
		$path = dirname(__FILE__).'/../log/usualSuspects.txt',
		'['.date('Y-m-d H\hi:s')."]"
		."\t{$_SERVER['REMOTE_ADDR']}"
		."\t{$_SERVER['QUERY_STRING']}"
		."\t{$comment}\n",
		FILE_APPEND
	);
}
?>