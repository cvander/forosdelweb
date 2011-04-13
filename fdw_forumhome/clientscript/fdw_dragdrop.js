/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

YAHOO.example.DDApp = {
	init: function() {
		this.loadCache();
		new YAHOO.util.DDTarget("content");
		new YAHOO.util.DDTarget("content-min");
		this.applyDD("content");
		this.applyDD("content-min");
	},
	applyDD: function(container) {
		var boxes = YAHOO.util.Dom.getElementsByClassName("title_box", "div", container);
		var box = null;
		for (i = 0; box = boxes[i]; i++) {
			new YAHOO.example.DDList(box.id);
			box.appendChild(document.createElement("span"));
			YAHOO.util.Dom.setStyle(box, "cursor", "move");
		}
	},
	getListPosition: function(obj) {
		var parent = obj.parentNode.parentNode;
		var boxes = YAHOO.util.Dom.getElementsByClassName("title_box", "div", parent);
		var box = null;
		for (i = 0; box = boxes[i]; i++) {
			if (box.id == obj.id) {
				return {"pos": i, "container": parent.className};
			}
		}
	},
	savePositions: function() {
		var request = "do=forumorder&securitytoken=" + SECURITYTOKEN;
		request += this.getPositions("content");
		request += this.getPositions("content-min");
		YAHOO.util.Connect.asyncRequest(
			"POST",
			"ajax.php?do=forumorder",
			function() { return; },
			request
		);
		
		this.saveCache();
	},
	saveCache: function() {
		fdwStorage.set("normal", this.getPositions("content", true));
		fdwStorage.set("min", this.getPositions("content-min", true));
	},
	loadCache: function() {
		var storedpos = null;
		if (storedpos = fdwStorage.get("normal")) {
			if (storedpos != this.getPositions("content", true)) {
				this.loadPositions(storedpos.split(","), "content");
			}
		}
		if (storedpos = fdwStorage.get("min")) {
			if (storedpos != this.getPositions("content-min", true)) {
				this.loadPositions(storedpos.split(","), "content-min");
			}
		}
	},
	loadPositions: function(pos, container) {
		var i = 0;
		var container = YAHOO.util.Dom.get(container);
		var refel = (!container.firstChild) ? null : container.firstChild;
		for (i = 0; i < pos.length; i++) {
			if (pos[i] == "") {
				continue;
			}
			var box = document.getElementById("ddbox_" + pos[i]).parentNode;
			container.insertBefore(box, refel);
			refel = box.nextSibling;
		}
	},
	getPositions: function(container, nouri) {
		var boxes = YAHOO.util.Dom.getElementsByClassName("title_box", "div", container);
		var box = null;
		var request = "";
		var small = (container == 'content-min') ? 1 : 0;
		for (i = 0; box = boxes[i]; i++) {
			if (nouri) {
				request += box.id.split("_")[1] + ",";
			} else {
				request += "&forum[" + box.id.split("_")[1] + "]=" + small;
			}
		}
		return request;
	}
};

YAHOO.example.DDList = function(id, sGroup, config) {
	YAHOO.example.DDList.superclass.constructor.call(this, id, sGroup, config);

	YAHOO.util.Dom.setStyle(this.getDragEl(), "opacity", 0.67); // The proxy is slightly transparent

	this.goingUp = false;
	this.lastY = 0;
	this.lastListPos = null;
};

YAHOO.extend(YAHOO.example.DDList, YAHOO.util.DDProxy, {
	startDrag: function(x, y) {
		var dragEl = this.getDragEl();
		var clickEl = this.getEl();
		
		var tip = YAHOO.util.Dom.getElementsByClassName("tooltip", "div", clickEl.parentNode);
		if (tip.length > 0) {
			tip[0].style.display = "none";
		}
		
		var itemSize = YAHOO.util.Dom.getRegion(clickEl.parentNode);
		YAHOO.util.Dom.setStyle(dragEl, "width", (itemSize.width - 2) + "px");
		YAHOO.util.Dom.setStyle(dragEl, "height", (itemSize.height - 2) + "px");
		YAHOO.util.Dom.setStyle(dragEl, "border", 0);
		this.deltaTrue = this.deltaX;
		
		dragEl.className = clickEl.parentNode.parentNode.className;
		dragEl.innerHTML = '<div style="width: 100%" class="' + clickEl.parentNode.className + '">' + clickEl.parentNode.innerHTML + '</div>';
		
		this.lastListPos = YAHOO.example.DDApp.getListPosition(clickEl);
		YAHOO.util.Dom.setStyle(clickEl.parentNode, "visibility", "hidden");
	},

	endDrag: function(e) {
		YAHOO.util.Dom.setStyle(this.getEl().parentNode, "visibility", "visible");
		YAHOO.util.Dom.setXY(this.getDragEl().id, [0, 0]);
		this.getDragEl().innerHTML = '';
		var currentPos = YAHOO.example.DDApp.getListPosition(this.getEl());
		if (currentPos.pos != this.lastListPos.pos || currentPos.container != this.lastListPos.container) {
			YAHOO.example.DDApp.savePositions();
		}
	},

	onDrag: function(e) {
		var y = YAHOO.util.Event.getPageY(e);

		if (y < this.lastY) {
			this.goingUp = true;
		} else if (y > this.lastY) {
			this.goingUp = false;
		}

		this.lastY = y;
	},

	onDragOver: function(e, id) {
		var srcEl = this.getEl().parentNode;
		var destEl = YAHOO.util.Dom.get(id);
		var dragEl = this.getDragEl();
		
		if (id == "content-min" || id == "content") {
			if (YAHOO.util.Dom.getElementsByClassName("title_box", "div", destEl).length == 0) {
				destEl.insertBefore(srcEl, destEl.firstChild);
				dragEl.className = id;
				var itemSize = YAHOO.util.Dom.getRegion(srcEl);
				YAHOO.util.Dom.setStyle(dragEl, "width", (itemSize.width - 2) + "px");
				YAHOO.util.Dom.setStyle(dragEl, "height", (itemSize.height - 2) + "px");
				if (itemSize.width < this.deltaTrue) {
					this.deltaX = itemSize.width / 2;
				} else {
					this.deltaX = this.deltaTrue;
				}
				YAHOO.util.DragDropMgr.refreshCache();
			}
		}
		
		if (destEl.className == "title_box") {
			var refEl = (this.goingUp) ? destEl.parentNode : destEl.parentNode.nextSibling;

			if (this.goingUp) {
				destEl.parentNode.parentNode.insertBefore(srcEl, destEl.parentNode); // insert above
			} else {
				destEl.parentNode.parentNode.insertBefore(srcEl, destEl.parentNode.nextSibling); // insert below
			}
			if (dragEl.className != destEl.parentNode.parentNode.className) {
				dragEl.className = destEl.parentNode.parentNode.className;
				var itemSize = YAHOO.util.Dom.getRegion(srcEl);
				YAHOO.util.Dom.setStyle(dragEl, "width", (itemSize.width - 2) + "px");
				YAHOO.util.Dom.setStyle(dragEl, "height", (itemSize.height - 2) + "px");
				if (itemSize.width < this.deltaTrue) {
					this.deltaX = itemSize.width / 2;
				} else {
					this.deltaX = this.deltaTrue;
				}
			}

			YAHOO.util.DragDropMgr.refreshCache();
		}
	}
});

YAHOO.util.Event.onDOMReady(YAHOO.example.DDApp.init, YAHOO.example.DDApp, true);