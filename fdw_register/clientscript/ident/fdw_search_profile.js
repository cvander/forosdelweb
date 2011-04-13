function renderProfile(e) {
	var hCard = ident.combinedProfile;
	
	if (ident.topDeclaredProfileUrl() != "") {
		$("#tb_homepage").val(ident.topDeclaredProfileUrl());
	} else {
		if (hCard.url) {
			if (hCard.url.length > 0) {
				$("#tb_homepage").val(hCard.url[0].value);
			}
		}
	}
	
	if (ident.topFormattedName() != "") {
		hCard.fn = (hCard.fn) ? hCard.fn : {};
		hCard.fn.value = ident.topFormattedName();
	}
	
	var identity = null;
	hCard.identity = {};
	for (var i = 0; identity = ident.identities[i]; i++) {
		if (identity.name != "") {
			hCard.identity[identity.name.toLowerCase()] = identity.username;
		}
	}
	window.hCard = hCard;
	
	var field = null;
	for (var i = 0; field = fdwIdentEngine.fields[i]; i++) {
		setIdentFieldValue(field, hCard);
	}
}

function setIdentFieldValue(info, hCard) {
	var obj = hCard;
	var parts = info.field.split(".");
	for (var i = 0; i < parts.length; i++) {
		if (obj[parts[i]]) {
			obj = obj[parts[i]];
		} else {
			return false;
		}
	}
	$(info.id).val(obj);
}