/*======================================================================*\
|| #################################################################### ||
|| # Thread rate - Foros del Web                                      # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

var fdw_Threadlist_Vote = {
	active: null,
	buttons: null,
	voted: {},
	
	init: function(container) {
		if (!AJAX_Compatible || (typeof vb_disable_ajax != "undefined" && vb_disable_ajax > 1)) {
			return;
		}
		
		container = YAHOO.util.Dom.get(container);
		
		this.buttons = document.getElementById("threadlist_vote");
		this.buttons.style.position = "absolute";
		this.buttons.id = "";
		
		var trows, trow, links, link, i, j, threadid, threads = [];
		for (i = 0, trows = container.getElementsByTagName("tr"); trow = trows[i]; i++) {
			for (j = 0, links = trow.getElementsByTagName("a"); link = links[j]; j++) {
				if (!link.rel || link.rel != "fdw::AJAX") {
					continue;
				}
				threadid = link;
				while (threadid = threadid.parentNode) {
					if (threadid.nodeName.toLowerCase() == "td" && threadid.id && threadid.id.indexOf('td_threadtitle_') == 0) {
						trow.threadid = threadid.id.split("_")[2];
						trow.threadtitle = threadid;
						trow.link = link;
						
						YAHOO.util.Event.on(trow, "mouseout", this.threadout, this, false);
						YAHOO.util.Event.on(trow, "mouseover", this.threadhover, this, false);
					}
				}
				break;
			}
		}
	},
	
	threadout: function(E, obj) {
		if (this.buttons) {
			this.buttons.style.display = "none";
		}
	},
	
	threadhover: function(E, obj) {
		if (obj.voted[this.threadid]) {
			return;
		}
		
		if (!this.buttons) {
			this.buttons = obj.buttons.cloneNode(true);
			this.threadtitle.appendChild(this.buttons);
		}
		
		obj.active = this;
		this.buttons.style.display = "block";
		
		var position = YAHOO.util.Dom.getRegion(this.threadtitle);
		var buttonsize = YAHOO.util.Dom.getRegion(this.buttons);
		YAHOO.util.Dom.setXY(this.buttons, [position.left - buttonsize.width - 2, position.top + (position.height / 2) - (buttonsize.height / 2)]);
		
	},
	
	threadVote: function(type) {
		if (!this.active) {
			return;
		}
		if (!this.active.voteobj) {
			this.active.voteobj = new fdw_Thread_Vote(this.active.threadid, this.active.link, type);
		}
		this.active.voteobj.submit();
	}
}

function fdw_Thread_Vote(threadid, link, type) {
	this.threadid = threadid;
	this.link = link;
	this.vote = type;
}

fdw_Thread_Vote.prototype.submit = function() {
	YAHOO.util.Connect.asyncRequest("POST", "misc.php?do=vote",
		{
			success : this.doVoted,
			failure : vBulletin_AJAX_Error_Handler,
			timeout : vB_Default_Timeout,
			scope : this
		},
		"do=vote&ajax=1&securitytoken=" + SECURITYTOKEN + "&t=" + this.threadid + "&vote=" + this.vote + "&list=1"
	);
}

fdw_Thread_Vote.prototype.doVoted = function(E) {
	if (E.responseXML) {
		var error, fdwvotebit;
		if (error = E.responseXML.getElementsByTagName("error")) {
			if (error.length) {
				alert(error[0].firstChild.nodeValue);
			}
		}
		if (fdwvotebit = E.responseXML.getElementsByTagName("fdwvotebit")) {
			if (fdwvotebit.length) {
				var container = document.createElement("div");
				var current = document.getElementById("thread_vote_" + this.threadid);
				container.innerHTML = fdwvotebit[0].firstChild.nodeValue;
				if (!current) {
					this.link.parentNode.insertBefore(container.firstChild, this.link);
				} else {
					this.link.parentNode.replaceChild(container.firstChild, current);
				}
				fdw_Threadlist_Vote.voted[this.threadid] = true;
			}
		}
	}
}