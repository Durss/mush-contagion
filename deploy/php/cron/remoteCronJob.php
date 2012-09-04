<?php
/*
 * Relevé des statistiques
 */
define('DEVMODE', true);
define('URL', 'http://fevermap.org/mushcontagion/php/cron/stat.php');

//Filtre CRON-JOB
if(!DEVMODE && !isset($_SERVER['CRON_ID']))
{
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden"); 
	die("403 Forbidden");
}

$rawData = file_get_contents(URL);
$data = simplexml_load_string($rawData);

var_dump($rawData, $data);
?>