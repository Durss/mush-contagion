<?php
function tableUserResults($result, $act, $selectMenu=null){
	global $ini, $toubibs;
	//init	
	$i=0;
	$range = array();
	$td = array();
	//traitement du résultat
	while($row = mysql_fetch_assoc($result)){
		//Préparation des contenus
		$lowName = strtolower($row['name']);
		//--est un toubib
		$isDoc = (bool) (isset($toubibs[$lowName]) && $toubibs[$lowName] == $row['uid']);
		$doc = $isDoc ? "<img src='gfx/mini-doc.png' width='16' height='16' alt='d'/>" : null;
		//--activité sur l'app
		$actif = empty($row['pubkey']) ? 'non' : 'oui';
		//--etat de santé
		$state = $row['infected'] < $ini['params']['infectCeil']
			? "<img class='state' src='gfx/toggleTwino.png' width='16' height='16' title='{$row['infected']}' alt='c'/> [{$row['infected']}]"
			: "<img class='state' src='gfx/toggleMush.png' width='16' height='16' title='{$row['infected']}' alt='m'/> [{$row['infected']}]";
		//--profil twinoid
		$twin = null;
		if(!empty($row['avatar'])){
			$twinID = getTwinID($row['avatar']);
			$twin = $twinID ? "<a href='http://twinoid.com/user/{$twinID}' target='twinoid'>link</a>" : null;
		}
		//--profil muxxu
		$muxxu = "<a href='http://muxxu.com/user/{$row['uid']}' target='muxxu'>link</a>";
		//--profil pandemie
		$pandemie = "<a href='http://muxxu.com/a/".appName."/?act=p/{$row['uid']}' target='muxxuApp'>link</a>";
		//--label (ligne)
		$check = 'check_'.++$i;
		$name = 'name_'.$i;
			
		//Ligne du tableau
		$range[]=$i;
		$td[] = <<<EOTD
		<tr>
			<td><input type="checkbox" id="{$check}" name="{$check}" value="{$row['uid']}"/></td>
			<td><label for="{$check}">{$row['name']}</label><input type="hidden" name="{$name}" value="{$lowName}"/></td>
			<td><label for="{$check}">{$doc}</label></td>
			<td><label for="{$check}">{$actif}</label></td>
			<td><label for="{$check}">{$state}</label></td>
			<td>{$twin}</td>
			<td>{$muxxu}</td>
			<td>{$pandemie}</td>
		</tr>
EOTD;
	}
	
	//Concaténation du contenu
	$tbody = implode("\n",$td);
	$range = implode(',',$range);
	
	//Concaténation du tableau
	return <<<EOTABLE
<table>
	<thead>
		<tr>
			<td><img src="gfx/fleche-UR.png" width="21" height="21" /></td>
			<td colspan="6">{$selectMenu}</td>
			<td><input type="submit" name="{$act}" value="EXE"/></td>
		</tr>
		<tr>
			<td><input type="checkbox" name="check_all" onclick="checkAll(this,'check_',{$i});" title="Cocher tout" /></td>
			<td>pseudo</td>
			<td>doc</td>
			<td>Actif</td>
			<td>Etat</td>
			<td>[Twinoid]</td>
			<td>[Muxxu]</td>
			<td>[Mycélium]</td>
		</tr>
	</thead>
	<tbody>
{$tbody}

	</tbody>
</table>
<input type="hidden" name="range" value="{$range}"/>
EOTABLE;
}
?>