/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

var fdwMenus = {
	initialized: false,
	timer: 1000,
	hideTimer: null,
	init: function() {
		YAHOO.util.Event.on(document, "click", this.hide, this, true);
		this.initialized = true;
	},
	menus: [],
	register: function(id, side, altmenu, noclick, ajax, noautohide) {
		if (!this.initialized) {
			this.init();
		}
		
		this.menus[id] = { id: id, side: side, button: document.getElementById(id), ajax: ajax };
		if (altmenu == undefined || altmenu === false) {
			this.menus[id].menu = document.getElementById(id + "_menu");
		} else {
			this.menus[id].menu = document.getElementById(altmenu + "_menu");
		}
		
		this.menus[id].menu.style.position = "absolute";
		
		if (noclick == undefined || noclick === false) {
			YAHOO.util.Event.on(this.menus[id].button, "click", this.click, this.menus[id], true);
		}

		YAHOO.util.Event.on(this.menus[id].button, "mouseover", this.mouseover, this.menus[id], true);
		if (!noautohide) {
			YAHOO.util.Event.on(this.menus[id].button, "mouseout", this.menuLeave, this, true);
		}
		
		if (!this.menus[id].menu.hookEvents) {
			this.menus[id].menu.hookEvents = true;
			if (!noautohide) {
				YAHOO.util.Event.on(this.menus[id].menu, "mouseover", this.menuEnter, this, true);
				YAHOO.util.Event.on(this.menus[id].menu, "mouseout", this.menuLeave, this, true);
			}
			YAHOO.util.Event.on(this.menus[id].menu, "click", function(E) { YAHOO.util.Event.stopPropagation(E); });
		}
	},
	click: function(E) {
		if (fdwMenus.active == this.id) {
			fdwMenus.hide();
		} else {
			fdwMenus.show(this.id);
		}
		YAHOO.util.Event.stopEvent(E);
	},
	menuEnter: function() {
		if (this.hideTimer) {
			clearTimeout(this.hideTimer);
		}
	},
	menuLeave: function() {
		this.hideTimer = setTimeout(this.hideFunc, this.timer);
	},
	mouseover: function(E) {
		fdwMenus.show(this.id);
	},
	show: function(menuid) {
		this.hide();
		this.menuEnter();
		
		var menu = this.menus[menuid];
		
		this.active = menuid;
		YAHOO.util.Dom.replaceClass(menu.button, "menulink", "menuactive")
		
		if (menu.ajax && !menu.menu.loaded) {
			this.fetchMenu(menu);
			return;
		}
		
		var buttonSize = YAHOO.util.Dom.getRegion(menu.button);
		menu.menu.style.display = "block";
		var menuSize = YAHOO.util.Dom.getRegion(menu.menu);
		
		if (menuSize.width < 150) {
			menu.menu.style.width = "150px";
			menuSize.width = 150;
		}
		if (menu.side == true) {
			var pos = [buttonSize.right - menuSize.width, buttonSize.bottom - 1];
		} else {
			var pos = [buttonSize.left, buttonSize.bottom - 1];
		}
		
		YAHOO.util.Dom.setXY(menu.menu, pos);
	},
	fetchMenu: function(menu) {
		if (YAHOO.util.Connect.isCallInProgress(menu.ajax.request)) {
			return;
		}
		menu.button.style.cursor = "wait";
		menu.ajax.request = YAHOO.util.Connect.asyncRequest("POST", menu.ajax.url,
			{
				success: this.fetchHandler,
				failure: this.fetchFailure,
				timeout: vB_Default_Timeout,
				scope: menu
			},
			"ajax=1&securitytoken=" + SECURITYTOKEN
		);
	},
	fetchHandler: function(E) {
		if (E.responseXML) {
			var menubit = null;
			var errorbit = null;
			
			if (menubit = E.responseXML.getElementsByTagName(this.ajax.tagname)[0]) {
				menubit = menubit.firstChild.nodeValue;
				
				this.menu.innerHTML = fdwAjaxEval.stripScripts(menubit);
				fdwAjaxEval.evalScripts(menubit);
				
			}
			
			if (errorbit = E.responseXML.getElementsByTagName("error")[0]) {
				this.menu.innerHTML = errorbit.firstChild.nodeValue;
			}
			
			this.button.style.cursor = "default";
			this.menu.loaded = true;
			if (fdwMenus.active == this.id) {
				fdwMenus.show(this.id);
			}
		}
	},
	fetchFailure: function(E) {
		vBulletin_AJAX_Error_Handler(E);
		location.href = this.ajax.url;
	},
	hide: function() {
		if (this.active === null) {
			return;
		}
		var menu = this.menus[this.active];

		YAHOO.util.Dom.replaceClass(menu.button, "menuactive", "menulink");
		menu.menu.style.display = "none";
		this.active = null;
	},
	hideFunc: function() {
		fdwMenus.hide();
	},
	active: null
};