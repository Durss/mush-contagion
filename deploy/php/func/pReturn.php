<?php
/**
 * <h1>Public or Private Return</h1>
 * <p>Si la constante LOCAL_MODE est définie à true, l'argument $private est retourné, sinon c'est l'argument $public</p>
 * @param string $private
 * @param string $public
 */
function pReturn($private, $public=null)
{
	$str = $public;
	if(defined('LOCAL_MODE') && LOCAL_MODE)
	{
		if($public != null) $str .= " :\n";
		$str .= $private;
	}
	return $str;
}
?>