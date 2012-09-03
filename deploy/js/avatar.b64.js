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
 * @param	hd			(bool) Version haute def ou basse def
 * @param	overlay		(bool) affichage ou non de l'overlay "archive"
 */
function avatar(id, uid, pseudo, infected, hd, overlay)
{
	var flash = document.getElementById('flash');
	var img = document.getElementById('avatar_'+id);
	if(hd) {
		img.style.cursor = "pointer";
		img.onclick = function() {
			window.open(this.src,'_avatar');
		}
	}
	img.src = "data:image/png;base64,"+flash.getImage(uid, pseudo, infected, hd, overlay);
}

/**
 * Pagination
 * @param	f	(int) offset de référence du tableau
 */
var minCols = 6;
var minRows = 2;
var delay = 0;
var timeouts = [];
function page(f)
{
	for(var i = 0; i < timeouts.length; ++i) clearTimeout(timeouts[i]);
	delay = 0;
	timeouts = [];
	for(var i= 0; i < table[f].length; i++)
	{
		tdUpdate(i, table[f][i][0], table[f][i][1], table[f][i][2], table[f][i][3]);
	}
	if(table[f].length < minCols*minRows)
	{
		for(i; i < minCols*minRows; i++) tdUpdate(i, 0, "", 0);
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
		//Si on met une source vide chrome laisse l'ancienne image et le loader de background ne ré-apparait pas :/. Donc on met le bon vieux pixel transparent
		document.getElementById('avatar_'+id).src = "./gfx/pixel.gif";
		timeouts.push(setTimeout(avatar, delay, id, uid, pseudo, infected, false));
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