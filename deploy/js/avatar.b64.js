/*
 * Init
 */

//Créé le flash invisible
var userLink = "";
var attributes = {};
var params = {};
params['allowScriptAccess'] = 'always';
params['menu'] = 'false';
var flashvars = {};
flashvars["canDownload"] = "false";//masque le bouton de DL
swfobject.embedSWF("swf/avatar.swf?v=1.3", "flash", "0", "0", "10.2", "swf/expressinstall.swf", flashvars, params, attributes);

/**
 * Insertion d'un avatar
 * @param	id			(string) attribut id de l'élément de substitution
 * @param	uid			(int) identifiant du joueur
 * @param	pseudo		(string) nom du joueur
 * @param	infected	(bool) level du joueur (true == infecté)
 */
function avatar(id, uid, pseudo, infected, clikable)
{
	var flash = document.getElementById('flash');
	var img = document.getElementById('avatar_'+id);
	if(clikable) {
		img.style.cursor = "pointer";
		img.onclick = function() {
			window.open(this.src,'_avatar');
		}
	}
	img.src = "data:image/png;base64,"+flash.getImage(uid, pseudo, infected, clikable);
}

/**
 * Pagination
 * @param	f	(int) offset de référence du tableau
 */
var limit = 5;
var delay = 0;
function page(f)
{
	delay = 0;
	for(var i= 0; i < table[f].length; i++)
	{
		tdUpdate(i, table[f][i][0], table[f][i][1], table[f][i][2], table[f][i][3]);
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
function tdUpdate(id, uid, pseudo, infected, date)
{
	if(uid == 0)
	{
		document.getElementById('avatar_'+id).style.visibility = 'hidden';
		document.getElementById('avatar_'+id).src = "";
		document.getElementById('uid_'+id).innerHTML = "";
		document.getElementById('pseudo_'+id).innerHTML = "";
		document.getElementById('date_'+id).innerHTML = "";
	}
	else
	{
		document.getElementById('avatar_'+id).style.visibility = 'visible';
		document.getElementById('avatar_'+id).src = "";
		setTimeout(avatar, delay, id, uid, pseudo, infected);
		delay += 60;
		document.getElementById('avatar_'+id).style.cursor = "pointer";
		document.getElementById('avatar_'+id).onclick = function() {
			window.open('?'+userLink+'&act=u/'+uid,'_self');
		}
		document.getElementById('uid_'+id).innerHTML = uid;
		document.getElementById('pseudo_'+id).innerHTML = '<a href="?'+userLink+'&act=u/'+uid+'">'+pseudo+'</a>';
		document.getElementById('date_'+id).innerHTML = date;
	}
}