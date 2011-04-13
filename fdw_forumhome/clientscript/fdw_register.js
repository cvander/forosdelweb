/*======================================================================*\
|| #################################################################### ||
|| # Simple Sign Up - Foros del Web                                   # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

function verify_passwords(password1, password2, frm)
{
	// do various checks, this will save people noticing mistakes on next page
	if (password1.value == '' || password2.value == '')
	{
		alert(registerPhrase['fill_out_both_password_fields']);
		return false;
	}
	else if (password1.value != password2.value)
	{
		alert(registerPhrase['entered_passwords_do_not_match']);
		return false;
	}
	else
	{
		var junk_output;

		md5hash(password1, document.forms.register.password_md5, junk_output, registerPhrase['nopasswordempty']);
		md5hash(password2, document.forms.register.passwordconfirm_md5, junk_output, registerPhrase['nopasswordempty']);

		return fdwRegisterForm.submit(frm);
	}
	return false;
}

if (!fdwRegisterForm) {
	var fdwRegisterForm = {
		stripHtml: function(text) {
			return text.replace(/(\n|\r)/g, '').replace(/<br.*?>/g, "\n").replace(/<.*?>/g, '');
		},
		submit: function(form) {
			this.form = form;
			this.menu = document.getElementById("register_menu");
			
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
						this.menu.innerHTML = "";
						this.menu.loaded = false;
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
}