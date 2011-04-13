function renderProfile(e) {
	$("#foundphotos").html('');
	for (i = 0; i < ident.profiles.length; i++) {
		hCard = ident.profiles[i];
		
		retrieveAvatar(hCard.photo, hCard);
		retrieveAvatar(hCard.logo, hCard);
	}
	
	if (!ident.isSearching()) {
		$("#ident_btn").html(fdwIdentEngine.vbphrase['search']);
		$("#ident_res").html(fdwIdentEngine.vbphrase['search_finalized']);
	}
}

function retrieveAvatar(photo, hCard) {
	if (photo){
		if (photo.length) {
			for (x = 0; x < photo.length; x++) {
				if (ident.isString(photo[x])) {
					var foundPhoto = $('<div class="found-photo"></div>');
					var url = photo[x];
					$('<img title="' + hCard.domain + '" width="48" height="48"  src="' + photo[x] +  '" />').appendTo(foundPhoto);
					
					foundPhoto.appendTo("#foundphotos");
					$('<img class="icon" title="' + hCard.domain + '" src="' + fdwIdentEngine.misc + '/ident/' + hCard.name.replace(/\s/g, '').replace('.','').replace('-','').toLowerCase() +  '.png" />').appendTo(foundPhoto);
					
					foundPhoto.click(function() {
						$("input[name=avatarurl]").val(url);
						check_yes('avatar_yes');
					});
					
					if (!ident.photoMatch) {
						ident.photoMatch = true;
						$("input[name=avatarurl]").val(url);
						check_yes('avatar_yes');
					}
				}
			}
		}
	}
}