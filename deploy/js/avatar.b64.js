/*
 * Init
 */
/*
var avatarAttributes = {};
avatarAttributes['id'] = 'avatar';
var avatarParams = {};
avatarParams['allowFullScreen'] = 'false';
avatarParams['allowScriptAccess'] = 'always';
avatarParams['menu'] = 'true';
avatarParams['scale'] = 'default';
avatarParams['wmode'] = 'transparent';
var avatarFlashvars = {};
avatarFlashvars["uid"] = 3916;
avatarFlashvars["pseudo"] = 'newSunshine';
avatarFlashvars["infected"] = false;
avatarFlashvars["canDownload"] = true;
swfobject.embedSWF("swf/avatar.swf?v=1", "avatar", "80", "80", "10.2", "swf/expressinstall.swf", avatarFlashvars, avatarParams, avatarAttributes);
*/
//Créé le flash invisible
var attributes = {};
attributes['id'] = 'flash';
var params = {};
params['allowScriptAccess'] = 'always';
params['menu'] = 'false';
var flashvars = {};
flashvars["canDownload"] = "false";//masque le bouton de DL
swfobject.embedSWF("swf/avatar.swf?v=1", "flash", "0", "0", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);

/**
 * Insertion d'un avatar
 * @param	id			(string) attribut id de l'élément de substitution
 * @param	uid			(int) identifiant du joueur
 * @param	pseudo		(string) nom du joueur
 * @param	infected	(bool) level du joueur (true == infecté)
 */
function avatar(id, uid, pseudo, infected)
{
	var flash = document.getElementById('flash');
	var img = document.getElementById('avatar_'+id);
	img.src = "data:image/png;base64,"+flash.getImage(uid, pseudo, infected);
	/* document.body.appendChild(img);
	
	setTimeout(addImage, 30); */
}

var limit = 12;
/**
 * Pagination
 * @param	f	(int) offset de référence du tableau
 */
function page(f)
{
	for(var i= 0; i < table[f].length; i++)
	{
		tdUpdate(i, table[f][i][0], table[f][i][1], table[f][i][2]);
	}
	if(table[f].length < limit)
	{
		for(i; i < limit; i++) tdUpdate(i, 0, "", 0);
	}
}

/**
 * Mise à jour d'une ligne du tableau
 * @param	id			(string) attribut id de l'élément de substitution
 * @param	uid			(int) identifiant du joueur
 * @param	pseudo		(string) nom du joueur
 * @param	infected	(bool) level du joueur (true == infecté)
 */
function tdUpdate(id, uid, pseudo, infected)
{
	if(uid == 0)
	{
		document.getElementById('avatar_'+id).style.visibility = 'hidden';
		document.getElementById('avatar_'+id).src = "";
		document.getElementById('uid_'+id).innerHTML = "";
		document.getElementById('pseudo_'+id).innerHTML = "";
	}
	else
	{
		document.getElementById('avatar_'+id).style.visibility = 'visible';
		avatar(id, uid, pseudo, infected);
		document.getElementById('uid_'+id).innerHTML = uid;
		document.getElementById('pseudo_'+id).innerHTML = pseudo;
	}
}