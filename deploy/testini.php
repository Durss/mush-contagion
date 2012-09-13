<?php
die();
var_dump(
	'casual', parse_ini_file('params.ini'),
	'perSections', parse_ini_file('params.ini',1),
	'end'
);
?>