/*======================================================================*\
|| #################################################################### ||
|| # Tag filter - Foros del Web                                       # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

function tagtool_start() {
	fdwTagTool.start();
}

var fdwTagTool = {
	taglist: null,
	tagtext: null,
	taggroups: {},
	start: function() {
		var taggroupsel = document.getElementById("taggroups");
		this.taglist = document.getElementById("taglist");
		this.tagtext = document.getElementById("tagtext_field");
		this.parseGroups();
		this.taglist.innerHTML = "";
		this.groupChange.call(taggroupsel, null, this);
		YAHOO.util.Event.on(taggroupsel, "change", this.groupChange, this);
	},
	
	parseGroups: function() {
		var i, options, option, group, groups = this.taglist.getElementsByTagName("optgroup");
		if (groups.length) {
			for (i = 0; group = groups[i]; i++) {
				this.taggroups[group.id] = {};
				for (j = 0, options = group.getElementsByTagName("option"); option = options[j]; j++) {
					this.taggroups[group.id][option.value] = option.text;
				}
			}
		}
	},
	
	groupChange: function(E, obj) {
		var tagtext = false, group = obj.taggroups["taggroup_" + this.value];
		obj.taglist.innerHTML = "";
		if (!group) {
			return;
		}
		for (tagid in group) {
			if (!tagtext) {
				tagtext = true;
				obj.tagtext.value = group[tagid];
			}
			obj.taglist.options[obj.taglist.options.length] = new Option(group[tagid], tagid);
		}
	}
};