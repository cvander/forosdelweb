vBrep = {};

function vbrep_register(postid) {
	vBrep["fdwvotepos_like_" + postid] = new fdw_Ajax_Reputation(postid, "fdwvotepos_like", "pos", "&do=addreputation&btn=like", "updatereason");
	vBrep["fdwvoteneg_like_" + postid] = new fdw_Ajax_Reputation(postid, "fdwvoteneg_like", "neg", "&btn=like", "addreputation", "&btn=like");
	vBrep["fdwvotepos_" + postid] = new fdw_Ajax_Reputation(postid, "fdwvotepos", "pos", "&do=addreputation", "updatereason");
	vBrep["fdwvoteneg_" + postid] = new fdw_Ajax_Reputation(postid, "fdwvoteneg", "neg", "", "addreputation");
	vBrep["fdwposvotes_" + postid] = new fdw_Ajax_Reputation(postid, "fdwposvotes", "pos", "&do=whovoted");
	vBrep["fdwnegvotes_" + postid] = new fdw_Ajax_Reputation(postid, "fdwnegvotes", "neg", "&do=whovoted");
}

function fdw_Ajax_Reputation(postid, btntype, votetype, data, action, submitdata) {
	var buttonobj = fetch_object(btntype + "_" + postid);
	if (!buttonobj) {
		return null;
	}
	
	this.postid = postid;
	this.divname = btntype + "_" + postid + "_menu";
	this.divobj = null;
	this.postobj = fetch_object("post" + postid);
	this.vbmenuname = btntype + "_" + postid;
	this.vbmenu = null;
	this.votetype = votetype;
	this.message = btntype + "_reason_" + postid;
	this.action = action;
	this.data = data;
	this.submitdata = (submitdata ? submitdata : "");
	
	buttonobj.onclick = fdw_Ajax_Reputation.prototype.voteClick;
}

fdw_Ajax_Reputation.prototype.voteClick = function(evt) {
	do_an_e(evt);
	var A = vBrep[this.id];
	if(A.vbmenu == null) {
		A.fetchForm();
	} else {
		if(vBmenu.activemenu != A.vbmenuname) {
			A.vbmenu.show(fetch_object(A.vbmenuname))
		} else {
			A.vbmenu.hide()
		}
	}
}

fdw_Ajax_Reputation.prototype.fetchForm = function() {
	YAHOO.util.Connect.asyncRequest("POST", "reputation.php?p=" + this.postid,
		{
			success : this.fetchingForm,
			failure : vBulletin_AJAX_Error_Handler,
			timeout : vB_Default_Timeout,
			scope : this
		},
		SESSIONURL + "securitytoken=" + SECURITYTOKEN + "&p=" + this.postid + "&ajax=1&reputation=" + this.votetype + this.data
	);
}

fdw_Ajax_Reputation.prototype.stripHtml = function(text) {
	return text.replace(/(\n|\r)/g, '').replace(/<br.*?>/g, "\n").replace(/<.*?>/g, '');
}

fdw_Ajax_Reputation.prototype.readXmlTag = function (objXml, tag, stripHtml, returnNode) {
	var returnValue = false;
	if (objXml.responseXML) {
		var elements = objXml.responseXML.getElementsByTagName(tag);
		if (elements.length > 0) {
			if (returnNode) {
				return elements[0];
			}
			returnValue = elements[0].firstChild.nodeValue;
			if (stripHtml) {
				returnValue = this.stripHtml(returnValue);
			}
		}
	}
	return returnValue;
}

fdw_Ajax_Reputation.prototype.fetchingForm = function(E) {
	var error;
	if (error = this.readXmlTag(E, "error", true)) {
		alert(error);
	} else {
		if (!this.divobj) {
			this.divobj = document.createElement("div");
			this.divobj.id = this.divname;
			this.divobj.style.display = "none";
			this.postobj.parentNode.appendChild(this.divobj);
			this.vbmenu = vbmenu_register(this.vbmenuname, true);
			fetch_object(this.vbmenu.controlkey).onmouseover = null;
		}
		if (this.processResult(E)) {
			this.voidLinks(this.divobj);
			this.vbmenu.show(fetch_object(this.vbmenuname));
		}
	}
}

fdw_Ajax_Reputation.prototype.voidLinks = function(elem) {
	var links = elem.getElementsByTagName("a"), link, i;
	for (i = 0; link = links[i]; i++) {
		YAHOO.util.Event.on(link, "click", do_an_e);
	}
}

fdw_Ajax_Reputation.prototype.keyHandler = function(evt) {
	var keyCode = evt.which || evt.keyCode;
	if (keyCode == 13) {
		do_an_e(evt);
		this.submit();
	}
}

fdw_Ajax_Reputation.prototype.submit = function() {
	var data = SESSIONURL + "securitytoken=" + SECURITYTOKEN +
		"&do=" + this.action +
		"&postid=" + this.postid +
		"&reason=" + PHP.urlencode(fetch_object(this.message).value) +
		"&reputation=" + this.votetype +
		"&ajax=1";
	YAHOO.util.Connect.asyncRequest("POST", "reputation.php?p=" + this.postid,
		{
			success : this.submitListener,
			failure : vBulletin_AJAX_Error_Handler,
			timeout : vB_Default_Timeout,
			scope : this
		},
		data + this.submitdata
	);
}

fdw_Ajax_Reputation.prototype.submitListener = function(E) {
	var error;
	if (error = this.readXmlTag(E, "error", true)) {
		this.vbmenu.hide(fetch_object(this.vbmenuname));
		alert(error);
	} else {
		this.processResult(E);
	}
}

fdw_Ajax_Reputation.prototype.processResult = function(E) {
	var rep, threadvote;
	if ((threadvote = this.readXmlTag(E, "fdwvotebit", false, true)) && window.fdwRate) {
		fdwRate.refresh(threadvote.getAttribute("threadid"), threadvote.firstChild.nodeValue);
	}
	
	if (rep = this.readXmlTag(E, "reputationbit", false, true)) {
		this.divobj.innerHTML = rep.firstChild.nodeValue;
		if (votes = rep.getAttribute("votes")) {
			this.showVotes(votes);
		}
		return true;
	}
}

fdw_Ajax_Reputation.prototype.showVotes = function(votes) {
	fetch_object("fdwvotedisplay_" + this.votetype + "_" + this.postid).innerHTML = votes;
}