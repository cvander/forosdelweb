/*======================================================================*\
|| #################################################################### ||
|| # Custom ForumJump - Foros del Web                                 # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

if (!window.fdwForumList && window.vB_Popup_Menu) {
	var fdwForumList = {
		minlength: 2,
		lists: {},
		register: function(id, autosubmit, groups, name) {
			if (name) {
				var selector = document.getElementsByName(name)[0];
			} else {
				var selector = document.getElementById(id + "_select");
			}
			if (!selector || selector.nodeName.toUpperCase() != "SELECT") {
				return false;
			}
			this.lists[id] = {
				id: id,
				selector: selector,
				input: document.getElementById(id + "_input"),
				suggest: id + "_suggest",
				autosubmit: autosubmit,
				groups: groups,
				selected: 0
			};
			this.init(id);
		},
		
		init: function(id) {
			var list = this.lists[id];
			list.selector.style.display = "none";
			list.input.style.display = "inline";
			list.input.value = this.trim(list.selector.options[list.selector.selectedIndex].text);
			list.input.onfocus = function() { this.value = ''; };
			
			document.getElementById(id + "_menu").innerHTML = this.fetchForums(list, list.groups, null, true);
			vBmenu.register(id, false, true);
			list.menu = vBmenu.register(list.suggest);
			
			YAHOO.util.Event.on(list.input, "blur", this.change, list, this);
			YAHOO.util.Event.on(list.input, "change", this.change, list, this);
			YAHOO.util.Event.on(list.input, "keypress", this.keypress, list, this);
			YAHOO.util.Event.on(list.input, "keyup", this.keyup, list, this);
		},
		
		fetchForums: function(list, hasGroups, filter, marksel) {
			var code = '<table cellpadding="4" cellspacing="1" border="0">';
			if (hasGroups) {
				var i = 0, groups, group;
				for (groups = list.selector.getElementsByTagName("optgroup"); group = groups[i]; i++) {
					code += '<tr><td class="thead">' + group.label + '</td></tr>';
					code += this.fetchOptions(group, list, filter, marksel);
				}
			} else {
				code += this.fetchOptions(list.selector, list, filter, marksel);
			}
			code += "</table>";
			return code;
		},
		
		fetchOptions: function(group, list, filter, marksel) {
			var code = '', i = 0, options, option;
			if (filter) {
				list.names = [];
				var exp = new RegExp("(" + PHP.preg_quote(filter) + ")", 'ig');
			}
			for (options = group.getElementsByTagName("option"); option = options[i]; i++) {
				var text = option.innerHTML;
				var className = (marksel && option.className == "fjsel") ? "vbmenu_hilite" : "vbmenu_option";
				if (filter) {
					text = this.trim(option.text);
					if (text.toUpperCase().indexOf(filter.toUpperCase()) < 0) {
						continue;
					} else {
						if (list.selection == list.names.length) {
							className = "vbmenu_hilite";
						}
						list.names[list.names.length] = option.value;
						text = text.replace(exp, "<strong>$1</strong>");
					}
				}
				click = " onclick=\"fdwForumList.click(event, '" + list.id + "', '" + option.value + "')\"";
				code += '<tr><td class="' + className + '"><a href="forumdisplay.php?f=' + option.value + '"' + click + '>' + text + '</a></td></tr>';
			}
			return code;
		},
		
		keypress: function(E, list) {
			if (vBmenu.activemenu == list.menu.controlkey && E.keyCode == 13) {
				YAHOO.util.Event.stopEvent(E);
			}
		},
		
		click: function(E, id, value) {
			var list = this.lists[id];
			list.selector.value = value;
			if (list.autosubmit) {
				list.selector.form.submit();
			} else {
				list.input.value = this.trim(list.selector.options[list.selector.selectedIndex].text);
				list.menu.hide();
			}
			YAHOO.util.Event.stopEvent(E);
		},
		
		keyup: function(E, list) {
			if (vBmenu.activemenu == list.menu.controlkey) {
				switch (E.keyCode) {
					case 13:
						this.click(E, list.id, list.names[list.selection]);
						return false;
					case 38:
					case 40:
						this.moveSelection(list, E.keyCode - 38);
						YAHOO.util.Event.stopEvent(E);
						return false;
				}
			}
			if (list.input.value.length >= this.minlength && E.keyCode != 27) {
				list.selection = 0;
				list.menu.menuobj.innerHTML = this.fetchForums(list, false, this.trim(list.input.value));
				if (list.names.length > 0) {
					list.menu.init_menu_contents();
					list.menu.show(list.menu.controlkey);
				}
			} else {
				list.menu.hide();
			}
		},
		
		change: function(E, list) {
			if (this.trim(list.input.value) != "") {
				if (selected = this.searchOption(list.selector, this.trim(list.input.value))) {
					list.selector.value = selected;
				}
			}
			list.input.value = this.trim(list.selector.options[list.selector.selectedIndex].text);
		},
		
		moveSelection: function(list, down) {
			var items = fetch_tags(list.menu.menuobj, "td");
			items[list.selection].className = "vbmenu_option";
			list.selection += down ? 1 : -1;
			if (list.selection < 0) {
				list.selection = list.names.length - 1;
			}
			if (list.selection > list.names.length - 1) {
				list.selection = 0;
			}
			items[list.selection].className = "vbmenu_hilite";
		},
		
		searchOption: function(select, text) {
			var i, option;
			for (i = 0; option = select.options[i]; i++) {
				if (this.trim(option.text).toUpperCase() == text.toUpperCase()) {
					return option.value;
				}
			}
		},
		
		trim: function(text) {
			text = text.replace(new RegExp(String.fromCharCode(160), "g"), "");
			return PHP.trim(text);
		}
	};
	
	vB_Popup_Menu.prototype.__set_menu_position = vB_Popup_Menu.prototype.set_menu_position;
	vB_Popup_Menu.prototype.set_menu_position = function(A) {
		var id = this.menuobj.id.split("_")[0], list = null;
		if (list = fdwForumList.lists[id]) {
			if ((list.id + "_menu") == this.menuobj.id) {
				var pos = YAHOO.util.Dom.getRegion(list.input);
				this.menuobj.style.height = "";
				this.menuobj.style.overflowX = "hidden";
				if (this.menuobj.offsetHeight > 400) {
					this.menuobj.style.height = "400px";
					this.menuobj.style.overflowY = "scroll";
				}
				this.menuobj.style.top = pos.bottom + "px";
				this.menuobj.style.left = (pos.right - this.menuobj.offsetWidth) + "px";
				return;
			}
		}
		this.__set_menu_position(YAHOO.util.Dom.get(A));
	}
}