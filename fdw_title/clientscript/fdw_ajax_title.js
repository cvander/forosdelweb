/*======================================================================*\
|| #################################################################### ||
|| # Title verify - Foros del Web                                     # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

function fdw_AJAX_TitleVerify(obj, cleargif) {
	if (!AJAX_Compatible) {
		return;
	}
	this.obj = YAHOO.util.Dom.get(obj);
	
	this.obj.setAttribute("autocomplete", "off");
	this.img = document.createElement("img");
	this.img.src = cleargif;
	YAHOO.util.Dom.insertAfter(this.img, this.obj);
	YAHOO.util.Event.on(this.img, "click", function() { toggle_collapse(this.obj.id); }, this, true);
	
	this.message = document.createElement("div");
	this.message.id = "collapseobj_" + this.obj.id;
	YAHOO.util.Dom.insertAfter(this.message, this.img);
	
	this.fragment = "";
	this.ajaxReq = this.timeout = null;
	
	YAHOO.util.Event.on(this.obj, "keyup", fdw_AJAX_TitleVerify.prototype.keyHandler, this, true);
}

fdw_AJAX_TitleVerify.prototype.keyHandler = function(E, O) {
	this.getText();
	
	clearTimeout(this.timeout);
	this.timeout = setTimeout(function() { O.doVerify(); }, 500);
}

fdw_AJAX_TitleVerify.prototype.getText = function() {
	this.fragment = PHP.trim(this.obj.value);
}

fdw_AJAX_TitleVerify.prototype.doVerify = function() {
	if (YAHOO.util.Connect.isCallInProgress(this.ajaxReq)) {
		YAHOO.util.Connect.abort(this.ajaxReq);
	}
	this.ajaxReq = YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=verifytitle",
		{
			success: fdw_AJAX_TitleVerify.prototype.handleRequest,
			failure: vBulletin_AJAX_Error_Handler,
			timeout: vB_Default_Timeout,
			scope: this
		},
		SESSIONURL + "securitytoken=" + SECURITYTOKEN + "&do=verifytitle&title=" + PHP.urlencode(this.fragment)
	);
}

fdw_AJAX_TitleVerify.prototype.handleRequest = function(E) {
	var response = E.responseXML, messageobj = null, messagetext = null;
	if (response && (messageobj = response.getElementsByTagName("message")[0])) {
		this.img.src = messageobj.getAttribute("image");
		this.img.style.display = "inline";
		
		if (messagetext = messageobj.firstChild) {
			this.message.innerHTML = messagetext.nodeValue;
		} else {
			this.message.innerHTML = "";
		}
	}
}