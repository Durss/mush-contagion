<?php
function tableFiles($dir){
	//init
	$td = array();
	//traitement du résultat
	foreach(scandir($dir) as $filename) {
		//filtres
		if(!is_file($dir.'/'.$filename)) continue;
		$pattern = '#^(?<No>[1-9][0-9]*)_(?<key>[0-9a-f]{16})\.txt$#';
		if(!preg_match($pattern, $filename, $matches)) continue;
		//Préparation des contenus
		$No = $matches['No'];
		$key = $matches['key'];
		$key_str = implode(' ',str_split($key,4));
		$key_suppr = md5('D3L3T3'.$filename.'poûet-pouêt !');
		//Ligne du tableau
		$td[$No] = <<<EOTD
		<tr>
			<td>N°{$No}</label></td>
			<td>{$key_str}</td>
			<td>
				<form method="post" action="?{$_SERVER['QUERY_STRING']}">
					<input type="hidden" name="editMsg" value="{$No}"/>
					<input type="submit" value="éditer"/>
				</form>
			</td>
			<td>
				<form method="post" action="?{$_SERVER['QUERY_STRING']}" onsubmit="return deleteConfirm('{$No}');">
					<input type="hidden" name="supprMsg" value="{$No}"/>
					<input type="hidden" name="keySuppr" value="{$key_suppr}"/>
					<input type="submit" value="supprimer"/>
				</form>
			</td>
		</tr>
EOTD;
	}
	
	//Concaténation du contenu
	ksort($td);
	$tbody = implode("\n",$td);
	
	//Concaténation du tableau
	return <<<EOTABLE
<table>
	<thead>
		<tr>
			<td>#</td>
			<td>Clé</td>
			<td colspan="2">Actions</td>
		</tr>
	</thead>
	<tbody>
{$tbody}
	</tbody>
</table>
EOTABLE;
}
?>