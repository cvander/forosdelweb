/*======================================================================*\
|| #################################################################### ||
|| # Simple Sign Up - Foros del Web                                   # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/
var fdwRegisterForm = {
	init: function(obj) {
		if (!AJAX_Compatible) {
			return;
		}
		this.button = obj;
		YAHOO.util.Event.on(obj, "click", this.clickListener, this, true);
		YAHOO.util.Event.on(document, "click", this.hide, this, true);
	},
	
	clickListener: function(E) {
		if (!this.menu) {
			this.fetchForm();
		} else {
			if (this.active) {
				this.hide();
			} else {
				this.show();
			}
		}
		YAHOO.util.Event.stopEvent(E);
	},
	
	fetchForm: function() {
		YAHOO.util.Connect.asyncRequest("POST", "register.php?do=register",
			{
				success: this.fetchingForm,
				failure: this.fetchFailure,
				timeout: vB_Default_Timeout,
				scope: this
			},
			"ajax=1&securitytoken=" + SECURITYTOKEN
		);
	},
	
	fetchFailure: function(E) {
		vBulletin_AJAX_Error_Handler(E);
		location.href = "register.php?do=register";
	},

	fetchingForm: function(E) {
		if (E.responseXML) {
			var registerbit = null;
			var errorbit = null;
			
			if (registerbit = E.responseXML.getElementsByTagName("register")[0]) {
				registerbit = registerbit.firstChild.nodeValue;
				
				this.menu = document.createElement("div");
				this.menu.style.display = "none";
				this.menu.style.position = "absolute";
				this.menu.style.width = "500px";
				this.menu.innerHTML = fdwAjaxEval.stripScripts(registerbit);
				document.body.appendChild(this.menu);
				
				YAHOO.util.Event.on(this.menu, "click", function(E) { YAHOO.util.Event.stopPropagation(E); });
				fdwAjaxEval.evalScripts(registerbit);
				
				this.show();
			}
			
			if (errorbit = E.responseXML.getElementsByTagName("error")[0]) {
				alert(this.stripHtml(errorbit.firstChild.nodeValue));
			}
		}
	},
	
	show: function() {
		this.active = true;
		this.menu.style.display = "block";
		var buttonsize = YAHOO.util.Dom.getRegion(this.button);
		var menusize = YAHOO.util.Dom.getRegion(this.menu)
		YAHOO.util.Dom.setXY(this.menu, [buttonsize.left, buttonsize.bottom])
	},
	
	hide: function() {
		this.active = false;
		if (this.menu) {
			this.menu.style.display = "none";
		}
	},
	
	stripHtml: function(text) {
		return text.replace(/(\n|\r)/g, '').replace(/<br.*?>/g, "\n").replace(/<.*?>/g, '');
	},
	
	submit: function(form) {
		document.getElementById("register_submit").disabled = true;
		var queryvars = "ajax=1";
		var elem = null;
		for (var i = 0; elem = form.elements[i]; i++) {
			if (elem.name && !elem.disabled) {
				switch (elem.type) {
					case "text":
					case "textarea":
					case "hidden":
						queryvars += "&" + elem.name + "=" + PHP.urlencode(elem.value);
						break;
					case "checkbox":
					case "radio":
						queryvars += elem.checked ? "&" + elem.name + "=" + PHP.urlencode(elem.value) : "";
						break;
					case "select-one":
						queryvars += "&" + elem.name + "=" + PHP.urlencode(elem.options[elem.selectedIndex].value);
						break;
					case "select-multiple":
						for (var j = 0; j < elem.options.length; j++) {
							queryvars += elem.options[j].selected ? "&" + elem.name + "=" + PHP.urlencode(elem.options[j].value) : "";
						}
						break;
					default:;
				}
			}
		}
		document.body.style.cursor = "wait";
		YAHOO.util.Connect.asyncRequest("POST", form.action,
			{
				success: this.submitHandler,
				failure: vBulletin_AJAX_Error_Handler,
				timeout: vB_Default_Timeout,
				scope: this
			},
			queryvars
		);
		return false;
	},
	
	submitHandler: function(E) {
		document.body.style.cursor = "default";
		if (E.responseXML) {
			var errorbit = null, url = "";
			if (errorbit = E.responseXML.getElementsByTagName("error")[0]) {
				var humanverifybit = null;
				if (humanverifybit = E.responseXML.getElementsByTagName("humanverify")[0]) {
					document.getElementById("register_submit").disabled = false;
					fdwAjaxEval.evalScripts(humanverifybit.firstChild.nodeValue);
				} else {
					this.menu.parentNode.removeChild(this.menu);
					this.menu = null;
				}
				alert(this.stripHtml(errorbit.firstChild.nodeValue));
			} else {
				var welcomebit = null;
				if (welcomebit = E.responseXML.getElementsByTagName("welcome")[0]) {
					var message = this.stripHtml(welcomebit.firstChild.nodeValue);
					alert(message);
					if (url = welcomebit.getAttribute("url")) {
						location.href = url;
					} else {
						location.reload();
					}
				}
			}
		}
	}
}