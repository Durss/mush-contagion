/*
 * Init
 */
var iterator = 1;
var laps = 125;
var width = 100;
var height = 100;
var avatarAttributes = {};
var avatarParams = {};
avatarParams['allowFullScreen'] = 'false';
avatarParams['allowScriptAccess'] = 'always';
avatarParams['menu'] = 'true';
avatarParams['scale'] = 'default';
avatarParams['wmode'] = 'transparent';
/**
 * Insertion d'un avatar
 * @param	id			(string) attribut id de l'élément de substitution
 * @param	uid			(int) identifiant du joueur
 * @param	pseudo		(string) nom du joueur
 * @param	infected	(bool) level du joueur (true == infecté)
 * @param	dl			(bool) option de téléchargement
 */
function avatar(id, uid, pseudo, infected, dl)
{
	if(dl)
	{
		//width = 80;
		height = 120;
	}
	else
	{
		//width = 80;
		height = 100;
	}
	
	var avatarFlashvars = {};
	avatarFlashvars["uid"] = uid;
	avatarFlashvars["pseudo"] = pseudo;
	avatarFlashvars["infected"] = infected;
	avatarFlashvars["buildDelay"] = iterator++*laps;
	avatarFlashvars["canDownload"] = dl;
	swfobject.embedSWF("swf/avatar.swf?v=1", "avatar_"+id, width, height, "10.2", "swf/expressinstall.swf", avatarFlashvars, avatarParams, avatarAttributes);
}

/**
 * Mise à jour d'une ligne du tableau
 * @param	id			(string) attribut id de l'élément de substitution
 * @param	uid			(int) identifiant du joueur
 * @param	pseudo		(string) nom du joueur
 * @param	infected	(bool) level du joueur (true == infecté)
 * @param	dl			(bool) option de téléchargement
 */
function tdUpdate(id, uid, pseudo, infected, date)
{
	if(uid == 0)
	{
		document.getElementById('avatar_'+id).style.visibility = 'hidden';
		document.getElementById('avatar_'+id).update(0,0,0);
		document.getElementById('uid_'+id).innerHTML = "";
		document.getElementById('pseudo_'+id).innerHTML = "";
		document.getElementById('date_'+id).innerHTML = "";
	}
	else
	{
		document.getElementById('avatar_'+id).style.visibility = 'visible';
		document.getElementById('avatar_'+id).update(uid, pseudo, infected);
		document.getElementById('uid_'+id).innerHTML = uid;
		document.getElementById('date_'+id).innerHTML = date;
	}
}

var limit = 5;
/**
 * Pagination
 * @param	f	(int) offset de référence du tableau
 */
function page(f)
{
	for(var i= 0; i < table[f].length; i++)
	{
		tdUpdate(i, table[f][i][0], table[f][i][1], table[f][i][2], table[f][i][3]);
	}
	if(table[f].length < limit)
	{
		for(i; i < limit; i++) tdUpdate(i, 0, "");
	}
}