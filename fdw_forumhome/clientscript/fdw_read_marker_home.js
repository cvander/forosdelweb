/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

vB_AJAX_ReadMarker.prototype.update_forum_status = function (B) {
	var A = fetch_object("forumlink_" + B);
	if (A) {
		A.parentNode.className = "nonews";
	}
	var A = fetch_object("forumlink_special_" + B);
	if (A) {
		A.parentNode.className = "nonews";
	}
}