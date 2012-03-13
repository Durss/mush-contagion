<?php
//Contantes de comparaison
define('DATE_RELATIVE_Y', (int) date('Y'));
define('DATE_RELATIVE_W', (int) date('YW'));
define('DATE_RELATIVE_Z', (int) date('Yz'));

/**
 * <p>Retourne une date formaté en français, identique à la fonction strftime(), et relative à la date actuelle</p>
 * @param	(int)	$timestamp	-timestamp de la date correspondante
 * @author	nSun
 * @see	fr_strftime.php
 */
function dateRelative($timestamp)
{
	//Heure
	$heure = date('H\hi', (int) $timestamp);

	//Avant cette année
	$date['Y'] = (int) date('Y', $timestamp);
	if($date['Y'] < DATE_RELATIVE_Y)
	{
		return fr_strftime('%e %B %Y',(int) $timestamp); #." à {$heure}";
	}
	//Cette Année, mais avant cette semaine
	elseif((int) date('YW', $timestamp) < DATE_RELATIVE_W)
	{
		return fr_strftime('%e %B',(int) $timestamp); #." à {$heure}";
	}
	//Cette semaine, mais pas ces derniers jours
	elseif((int) date('Yz', $timestamp) < (DATE_RELATIVE_Z-1))
	{
		return fr_strftime('%A %e',(int) $timestamp); #." à {$heure}";
	}
	//Hier
	elseif((int) date('Yz', $timestamp) == (DATE_RELATIVE_Z-1))
	{
		return "Hier à {$heure}";
	}
	//Aujourd'hui
	elseif((int) date('Yz', $timestamp) == DATE_RELATIVE_Z)
	{
		return "Aujourd'hui à {$heure}";
	}
	//???
	else
	{
		return fr_strftime('%e %B %Y',(int) $timestamp)." à {$heure}";
	}
}
?>