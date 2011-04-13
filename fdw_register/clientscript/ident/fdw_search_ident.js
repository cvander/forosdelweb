$(document).ready(function() {
	webAddress.personasUserNames[0] = fdwIdentEngine.username;
	webAddress.init();
	webAddress.elt = "#ident_ex";
	webAddress.startAnimate();
	
	$("#ident_text").keydown(function(e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			searchIdent();
		}
	});
	
	$("#ident_btn").attr("disabled", false);
	
	$("#ident_btn").click(function() {
		searchIdent();
	});
});

function errorHandler(e, type) {
	$("#ident_btn").html(fdwIdentEngine.vbphrase['search']);
	$("#ident_res").html(fdwIdentEngine.vbphrase['error'] + ': ' + type);
}

function searchIdent() {
	if (!ident.isSearching()) {
		$("#ident_btn").html(fdwIdentEngine.vbphrase['stop']);
		
		$(document).bind("ident:update", renderProfile);
		$(document).bind("ident:error", errorHandler);
		
		$("#ident_ex").css("display", "none");
		webAddress.stopAnimate();
		
		$("#ident_res").css("display", "block");
		$("#ident_res").html(fdwIdentEngine.vbphrase['searching_details'] + ' <img src="' + fdwIdentEngine.misc + '/progress.gif" alt="" />');
		
		ident.reset();
		ident.search($("#ident_text").val());
	} else {
		$("#ident_btn").html('Find');
		$(document).unbind("ident:update", renderProfile);
		$(document).unbind("ident:update", errorHandler);
		$("#ident_res").html(fdwIdentEngine.vbphrase['search_stopped']);
	}
}