<?php
/**
 * <h1>Test des statuts</h1>
 */
//Base
require('php/msg.php');
require('c/config.php');
require('c/mysql.php');

//Template
require('php/class/nsunTpl.php');
$page = new nsunTpl();
$page->title = null;
$page->addMetaTag("ROBOTS", "NOINDEX, NOFOLLOW");

//utils
require('php/func/strip_cdata.php');

//Tire au sort 3 noms

//Textes
$bank = simplexml_load_file(website."xml/status.xml");

//URL du lien
$url = strval($bank->header['link']);

//menu
$options = null;
for($i=1; $i <= count($bank->status->s); $i++) $options .= "\n<option value='{$i}'>{$i}</option>";
$menu = <<<EOM
<form method="get">
<select name="id">
<option value="false">Autres textes</option>{$options}
</select>
<input type="submit" value="Charger"/>
</form>
EOM;

if(isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] <= count($bank->status->s))
{
	$choose = intval($_GET['id'])-1;
	if($_GET['id'] > 1) $menu .= "<a href='?id=".($_GET['id']-1)."'>Précédent</a>\n";
	if($_GET['id'] < count($bank->status->s)) $menu .= "<a href='?id=".($_GET['id']+1)."'>Suivant</a>\n";
}
//Random--texte
else $choose = rand(1, count($bank->status->s))-1;
//--noms
$sql = "-- Tire 3 noms au hasard\n"
."SELECT `name`\n"
."FROM `{$mysql_vars['tbl']['user']}`\n"
."ORDER BY rand() ASC\n"
."LIMIT 3";

mysql_connect($mysql_vars['host'],$mysql_vars['user'],$mysql_vars['pass']) or die(pReturn(mysql_error(), MSG_DBConnectFail));
mysql_selectdb($mysql_vars['db']) or die(pReturn(mysql_error(), MSG_DBSelectFail));

$result = mysql_query($sql) or die(mysql_error());

//Préparation du texte
$subject = strip_cdata($bank->status->s[$choose]);

$pattern[] = '#<link/>#';	$replacement[] = $url;
$pattern[] = '#<xxx/>#';
$pattern[] = '#<yyy/>#';
$pattern[] = '#<zzz/>#';

while($row = mysql_fetch_assoc($result)) $replacement[] = "[i]{$row['name']}[/i]";

mysql_close();

$txt = preg_replace($pattern, $replacement, $subject);

$choose++; //
#$txt = nl2br($txt);

$page->addStyleSheet('css/admin.css');

$page->c = <<<EOC
<fieldset>
<legend>{$choose}</legend>
<textarea style="width: 600px; height: 150px;">{$txt}</textarea>
</pre>
</fieldset>
{$menu}
EOC;
?>