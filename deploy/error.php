<?php
$httpCodes = array(
//1xx Information
100 => array('Continue',	"Attente de la suite de la requête"),
101 => array('Switching Protocols',	"Acceptation du changement de protocole"),
102 => array('Processing',	"WebDAV : Traitement en cours."), //(évite que le client dépasse le temps d'attente limite)
118 => array('Connection timed out',	"Délai imparti à l'opération dépassé"),
//2xx Succès
200 => array('OK',	"Requête traitée avec succès"),
201 => array('Created',	"Requête traitée avec succès avec création d'un document"),
202 => array('Accepted',	"Requête traitée mais sans garantie de résultat"),
203 => array('Non-Authoritative Information',	"Information retournée mais générée par une source non certifiée"),
204 => array('No Content',	"Requête traitée avec succès mais pas d'information à renvoyer"),
205 => array('Reset Content',	"Requête traitée avec succès, la page courante peut être effacée"),
206 => array('Partial Content',	"Une partie seulement de la requête a été transmise"),
207 => array('Multi-Status',	"WebDAV : Réponse multiple."),
210 => array('Content Different',	"WebDAV : La copie de la ressource côté client diffère de celle du serveur."), //(contenu ou propriétés)
//3xx Redirection
300 => array('Multiple Choices',	"L'URI demandée se rapporte à plusieurs ressources"),
301 => array('Moved Permanently',	"Document déplacé de façon permanente"),
302 => array('Found',	"Document déplacé de façon temporaire"),
303 => array('See Other',	"La réponse à cette requête est ailleurs"),
304 => array('Not Modified',	"Document non modifié depuis la dernière requête"),
305 => array('Use Proxy',	"La requête doit être ré-adressée au proxy"),
307 => array('Temporary Redirect',	"La requête doit être redirigée temporairement vers l'URI spécifiée"),
310 => array('Too many Redirect',	"La requête doit être redirigée de trop nombreuses fois, ou est victime d'une boucle de redirection."),
324 => array('Empty response',	"Le serveur a mis fin à la connexion sans envoyer de données."),
//4xx Erreur du client
400 => array('Bad Request',	"La syntaxe de la requête est erronée"),
401 => array('Unauthorized',	"Une authentification est nécessaire pour accéder à la ressource"),
402 => array('Payment Required',	"Paiement requis pour accéder à la ressource"), //(non utilisé)
403 => array('Forbidden',	"L'authentification est refusée."), //Contrairement à l'erreur 401, aucune demande d'authentification ne sera faite
404 => array('Not Found',	"Ressource non trouvée"),
405 => array('Method Not Allowed',	"Méthode de requête non autorisée"),
406 => array('Not Acceptable',	"Toutes les réponses possibles seront refusées."),
407 => array('Proxy Authentication Required',	"Accès à la ressource autorisé par identification avec le proxy"),
408 => array('Request Time-out',	"Temps d'attente d'une réponse du serveur écoulé"),
409 => array('Conflict',	"La requête ne peut être traitée à l'état actuel"),
410 => array('Gone',	"La ressource est indisponible et aucune adresse de redirection n'est connue"),
411 => array('Length Required',	"La longueur de la requête n'a pas été précisée"),
412 => array('Precondition Failed',	"Préconditions envoyées par la requête non-vérifiées"),
413 => array('Request Entity Too Large',	"Traitement abandonné dû à une requête trop importante"),
414 => array('Request-URI Too Long',	"URI trop longue"),
415 => array('Unsupported Media Type',	"Format de requête non-supportée pour une méthode et une ressource données"),
416 => array('Requested range unsatisfiable',	"Champs d'en-tête de requête « range » incorrect."),
417 => array('Expectation failed',	"Comportement attendu et défini dans l'en-tête de la requête insatisfaisable"),
418 => array('I\'m a teapot',	"\"Je suis une théière\"."), //Ce code est défini dans la RFC 2324 datée du premier avril, Hyper Text Coffee Pot Control Protocol. Il n'y a pas d'implémentation de ce code.
422 => array('Unprocessable entity',	"WebDAV : L'entité fournie avec la requête est incompréhensible ou incomplète."),
423 => array('Locked',	"WebDAV : L'opération ne peut avoir lieu car la ressource est verrouillée."),
424 => array('Method failure',	"WebDAV : Une méthode de la transaction a échoué."),
425 => array('Unordered Collection',	""), //WebDAV (RFC 3648). Ce code est défini dans le brouillon WebDAV Advanced Collections Protocol, mais est absent de Web Distributed Authoring and Versioning (WebDAV) Ordered Collections Protocol
426 => array('Upgrade Required',	""), //(RFC 2817) Le client devrait changer de protocole, par exemple au profit de TLS/1.0
449 => array('Retry With',	""), //Code défini par Microsoft. La requête devrait être renvoyée après avoir effectué une action.
450 => array('Blocked by Windows Parental Controls',	""), //Code défini par Microsoft. Cette erreur est produite lorsque les outils de contrôle parental de Windows sont activés et bloquent l'accès à la page.
//5xx Erreur du serveur[modifier]
500 => array('Internal Server Error',	"Erreur interne du serveur"),
501 => array('Not Implemented',	"Fonctionnalité réclamée non supportée par le serveur"),
502 => array('Bad Gateway ou Proxy Error',	"Mauvaise réponse envoyée à un serveur intermédiaire par un autre serveur."),
503 => array('Service Unavailable',	"Service temporairement indisponible ou en maintenance"),
504 => array('Gateway Time-out',	"Temps d'attente d'une réponse d'un serveur à un serveur intermédiaire écoulé"),
505 => array('HTTP Version not supported',	"Version HTTP non gérée par le serveur"),
507 => array('Insufficient storage',	"WebDAV : Espace insuffisant pour modifier les propriétés ou construire la collection"),
509 => array('Bandwidth Limit Exceeded',	""), //Utilisé par de nombreux serveurs pour indiquer un dépassement de quota.
);

if(isset($_GET['code']) && isset($httpCodes[$_GET['code']]))
{
	$n = $_GET['code'];
	$subtitle = $httpCodes[$_GET['code']][0];
	$desc = $httpCodes[$_GET['code']][1];
}
else $n = $subtitle = $desc = null;

echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Erreur <?php echo $n; ?></title>
</head>
<body>
<h1>Erreur <?php echo $n; ?></h1>
<h2><?php echo $subtitle; ?></h2>
<p><?php echo $desc; ?></p>
</body>
</html>