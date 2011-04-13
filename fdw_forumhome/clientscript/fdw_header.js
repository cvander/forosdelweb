/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

var fdwHeader = {
	initialized: false,
	status: 0,
	spacer: null,
	header: null,
	init: function() {
		this.header = YAHOO.util.Dom.get("content_head");
		
		this.spacer = YAHOO.util.Dom.get("head_spacer");

		var itemSize = YAHOO.util.Dom.getRegion(this.header);
		YAHOO.util.Dom.setStyle(this.spacer, "margin", "0");
		YAHOO.util.Dom.setStyle(this.spacer, "padding", "0");
		YAHOO.util.Dom.setStyle(this.spacer, "height", itemSize.height + "px");
		
		var status = fetch_cookie("fdw_header");
		if (status == 1) {
			this.active();
		}
		
		this.header.ondblclick = this.listener;

		if (!fdwMenus.___show) {
			fdwMenus.___show = fdwMenus.show;
		}
		fdwMenus.show = this.menuFix;
	},
	active: function() {
		this.spacer.style.display = "block";
		this.header.style.position = "fixed";
		this.header.style.width = "100%";
		this.header.style.zIndex = 1000;
		this.saveStatus(1);
	},
	disable: function() {
		this.spacer.style.display = "none";
		this.header.style.position = "relative";
		this.saveStatus(0);
	},
	saveStatus: function(status) {
		var expires = new Date;
		expires.setTime(expires.getTime() + 31536000000);
		set_cookie("fdw_header", status);
		this.status = status;
	},
	listener: function(evt) {
		var evt = evt || window.event;
		var targetEl = evt.target || evt.srcElement;
		if (targetEl.id != fdwHeader.header.id) {
			return;
		}
		if (fdwHeader.status) {
			fdwHeader.disable();
		} else {
			fdwHeader.active();
		}
	},
	menuFix: function(menuid) {
		this.menus[menuid].menu.style.position = fdwHeader.status ? "fixed" : "absolute";
		this.___show(menuid);
	}
}