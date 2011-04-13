/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

if (window.sessionStorage) {
	fdwStorage = {
		get: function(key) {
			return sessionStorage[key];
		},
		set: function(key, value) {
			sessionStorage[key] = value;
		}
	};
} else {
	fdwStorage = {
		parsed: null,
		get: function(key) {
			if (this.parsed === null) {
				this.parsed = this.parse(window.name);
			}
			return this.parsed[key];
		},
		set: function(key, value) {
			if (this.parsed === null) {
				this.parsed = this.parse(window.name);
			}
			this.parsed[key] = value;
			window.name = this.unparse(this.parsed);
		},
		parse: function(text) {
			var vars = text.split(";");
			var values = null;
			var parsed = {};
			for (i = 0; values = vars[i]; i++) {
				var keyvalue = values.split("=");
				if (keyvalue.length == 2) {
					parsed[keyvalue[0]] = unescape(keyvalue[1]);
				}
			}
			return parsed;
		},
		unparse: function(obj) {
			var text = '';
			for (key in obj) {
				text += key + "=" + escape(obj[key]) + ";";
			}
			return text;
		}
	};
}