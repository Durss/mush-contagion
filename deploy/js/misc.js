function checkAll(el,prefix,n){
	var status = el.checked;
	var newTitle = status ? "Tout décocher" : "Cocher tout";
	el.setAttribute('title',newTitle);
	for(var i=1; i <= n; i++){
		var strID = ''+prefix+i;
		document.getElementById(strID).checked = status;
	}
}

function rangeReturn(el,id){
	document.getElementById(id).innerHTML = el.value;
}

function pifometreConfirm(){
	if(! document.forms[0].elements['osef'].checked
	&& ! document.forms[0].elements['setQty'].checked
	&& ! document.forms[0].elements['setFrame'].checked
	&& ! document.forms[0].elements['setActif'].checked){
		alert("Vous devez cocher au moins un critère.\nSi vous ne savez pas, cochez OSEF.");
		return false;
	}
	else return true;	
}

function deleteConfirm(No){
	return confirm('Vous souhaitez effacer le message N°'+No+' ?');
}