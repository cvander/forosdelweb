/*======================================================================*\
|| #################################################################### ||
|| # Simple Sign Up - Foros del Web                                   # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/
function fdw_AJAX_EmailVerify(objectid) {
	if (!AJAX_Compatible) {
		return;
	}
	this.obj = document.getElementById(objectid);
	
	this.obj.setAttribute("autocomplete", "off");
	this.img = document.getElementById(objectid + "_verifimg");
	this.message = document.getElementById(objectid + "_verifdiv");
	
	this.fragment = "";
	this.ajaxReq = this.timeout = null;
	
	YAHOO.util.Event.on(this.obj, "keyup", fdw_AJAX_EmailVerify.prototype.keyHandler, this, true);
}

fdw_AJAX_EmailVerify.prototype.keyHandler = function(E, O) {
	this.getText();
	
	if (this.fragment.indexOf("@") < 0) {
		return false;
	}
	
	clearTimeout(this.timeout);
	this.timeout = setTimeout(function() { O.doVerify(); }, 500);
}

fdw_AJAX_EmailVerify.prototype.getText = function() {
	this.fragment = PHP.trim(this.obj.value);
}

fdw_AJAX_EmailVerify.prototype.doVerify = function() {
	if (YAHOO.util.Connect.isCallInProgress(this.ajaxReq)) {
		YAHOO.util.Connect.abort(this.ajaxReq);
	}
	this.ajaxReq = YAHOO.util.Connect.asyncRequest("POST", "ajax.php?do=verifyemail",
		{
			success: fdw_AJAX_EmailVerify.prototype.handleRequest,
			failure: vBulletin_AJAX_Error_Handler,
			timeout: vB_Default_Timeout,
			scope: this
		},
		SESSIONURL + "securitytoken=" + SECURITYTOKEN + "&do=verifyemail&email=" + PHP.urlencode(this.fragment)
	);
}

fdw_AJAX_EmailVerify.prototype.handleRequest = function(E) {
	var response = E.responseXML;
	if (response && response.getElementsByTagName("status").length > 0) {
		var status = E.responseXML.getElementsByTagName("status")[0].firstChild.nodeValue;

		this.img.src = E.responseXML.getElementsByTagName("image")[0].firstChild.nodeValue;
		this.img.style.display = "inline";
		
		this.message.style.display = "block";
		this.message.className = (status == "valid") ? "greenbox" : "redbox";
		
		this.message.innerHTML = E.responseXML.getElementsByTagName("message")[0].firstChild.nodeValue;
	}
}