<?php
function cdata($str) { return "<![CDATA[{$str}]]>"; }

$root = new SimpleXMLElement('http://localhost/mushcontagion/xml/bachibousouk.xml',0,1);

$root->addChild('error', 'shit');

$infectedUsers = $root->infectedUsers;

$user = $infectedUsers->addChild('user');
$user->addAttribute('uid', $_GET['uid']);

$user->addChild('name', cdata('champion'));
$user->addChild('avatar', cdata('tête de babar'));

//Finalise
header('Content-Type: text/xml; charset="UTF-8"');
echo $root->asXML();
?>