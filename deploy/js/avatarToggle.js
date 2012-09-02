function onReady() {
	var bt = document.getElementById("toggleAvatar");
	if(bt != null) {
		bt.addEventListener("click", onToggleAvatar);
	}
}
function onToggleAvatar() {
	//var uid=
	var url = this.src;
	var toggled = /twino\.png/gi.test(url);
	if (toggled) {
		this.src = url.replace("Twino", "Mush");
	}else {
		this.src = url.replace("Mush", "Twino");
	}
	var params = url.split("?");
	params = params[1].split("&");
	var uid = params[0].split("=")[1]
	var pseudo = params[1].split("=")[1]
	var infected = params[2].split("=")[1]
	if(document.getElementById("avatar_user")) {
		document.getElementById('avatar_user').src = "data:image/png;base64,"+flash.getImage(uid, pseudo, toggled? 0 : infected, true, toggled);
	}
	if(document.getElementById("uAvatar")) {
		document.getElementById('uAvatar').src = "data:image/png;base64,"+flash.getImage(uid, pseudo, toggled? 0 : infected, true, toggled);
	}
}

window.addEventListener("load", onReady);