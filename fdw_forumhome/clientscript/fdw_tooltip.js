fdwTooltips = {
	seconds: 500,
	hideTimer: null,
	showTimer: null,
	activeid: null,
	nextid: 0,
	tips: {},
	initialized: false,
	
	init: function() {
		this.createTooltip();
	},
	
	register: function(linkel, content) {
		if (!this.initialized) {
			this.init();
			this.initialized = true;
		}
		
		var linkobj = YAHOO.util.Dom.get(linkel);
		this.nextid++;
		
		var elementid = (linkobj.id != "") ? linkobj.id : "tooltip" + this.nextid;
		linkobj = linkobj.parentNode;
		
		this.tips[elementid] = {"link": linkobj, "content": content, "id": this.nextid};
		
		YAHOO.util.Event.on(linkobj, "mouseover", this.enterListener, this.tips[elementid], false);
		YAHOO.util.Event.on(linkobj, "mouseout", this.leaveListener, this.tips[elementid], false);
		YAHOO.util.Event.on(linkobj, "mousemove", this.saveXY, this.tips[elementid], false);
	},
	
	createTooltip: function() {
		var tipel = document.createElement("div");
		var tipimg = document.createElement("span");
		tipimg.className = "tooltip-img";
		tipel.appendChild(tipimg);
		var tiptext = document.createElement("div");
		tiptext.className = "tooltip-content";
		tipel.appendChild(tiptext);
		tipel.className = "tooltip";
		document.body.appendChild(tipel);
		
		this.tip = tipel;
		this.tiptext = tiptext;
	},
	
	enterListener: function(e, obj) {
		clearTimeout(fdwTooltips.hideTimer);
		if (fdwTooltips.activeid != obj.id) {
			fdwTooltips.saveXY(e, obj);
			fdwTooltips.showTimer = setTimeout(function() { fdwTooltips.show(obj); }, fdwTooltips.seconds);
		}
	},
	
	leaveListener: function(e, obj) {
		clearTimeout(fdwTooltips.showTimer);
		fdwTooltips.hideTimer = setTimeout(fdwTooltips.hide, fdwTooltips.seconds);
	},
	
	hide: function() {
		fdwTooltips.tip.style.display = "none";
		fdwTooltips.activeid = null;
	},
	
	show: function(obj) {
		fdwTooltips.activeid = obj.id;
		fdwTooltips.tiptext.innerHTML = obj.content;
		fdwTooltips.tip.style.display = "block";
		obj.link.appendChild(fdwTooltips.tip);
		YAHOO.util.Dom.setXY(fdwTooltips.tip, [obj.X - 38, obj.Y + 1]);
	},
	
	saveXY: function(e, obj) {
		var pos = YAHOO.util.Event.getXY(e);
		obj.X = pos[0];
		obj.Y = pos[1];
	}
}