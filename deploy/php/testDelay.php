<table border="1">
<tr>
<td>childs</td><td>delay</td>
</tr>
<?php
$base = 60;
$max = $base*10;
$ceil = 4;
$top = 50;

for($i=0; $i < 60; $i++){
	if($i <= $ceil) $d = $base;
	elseif($i >= $top) $d = $max;
	else{
		$x = $i-$ceil;
		$d = $base+round($x*$max/$top);
	}
	
	echo "<tr><td>{$i}</td><td>{$d}</td></tr>\n";
}
?>
</table>