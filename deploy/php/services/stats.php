<?php
define('baseURL','../../');
//Parametres
$ini = parse_ini_file(baseURL.'params.ini');

$file = "p{$ini['queryLimit']}.records.txt";

$values = file($file) or die("File '{$file}' not founded");
$count = count($values);
$sum = array_sum($values);
$moy = round($sum/count($values),4);

var_dump(
	"count : ".$count,
	"sum : ".$sum,
	"moy : ".$moy,
	'end'
);

//save
$test = file_put_contents(
	basename($file,'.txt').'.stats.txt',//filename
	"sum/count\n{$sum} / {$count} = {$moy} s\n----------------\n" //stats
	.implode($values)//datas
);

var_dump("file",$test);
?>