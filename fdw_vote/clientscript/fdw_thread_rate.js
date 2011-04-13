/*======================================================================*\
|| #################################################################### ||
|| # Thread rate - Foros del Web                                      # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

fdwRate = {
	doRate: function(F) { this.t = F.t.value; this.form = F; if (F.vote.length) { var vote = (F.vote[0].checked) ? "2" : "1"; } YAHOO.util.Connect.asyncRequest("POST", F.action, { success : this.success, failure : this.fail, timeout : vB_Default_Timeout, scope : this }, "do=vote&ajax=1&securitytoken=" + SECURITYTOKEN + "&t=" + F.t.value + "&vote=" + vote); },
	success: function(E) { if (E.responseXML) { var G, P, R; if (G = E.responseXML.getElementsByTagName("error")) { if (G.length) { fetch_object("fdwvote_form_" + this.t + "_i").innerHTML = G[0].firstChild.nodeValue } }; if (G = E.responseXML.getElementsByTagName("fdwvotebit")) { if (G.length) { this.refresh(this.t, G[0].firstChild.nodeValue); if (P = G[0].getAttribute("post")) { if (window.vBrep && (R = vBrep["fdwvote" + G[0].getAttribute("type") + "_" + P])) { R.showVotes(G[0].getAttribute("votes")); } } } } } },
	refresh: function(T, C) { fetch_object("fdwvote_" + T + "_i").innerHTML = C; fetch_object("fdwvote_" + T).style.display = "block"; fetch_object("fdwvote_form_" + T).style.display = "none"; },
	fail: function(E) { this.form.submit(); }
}