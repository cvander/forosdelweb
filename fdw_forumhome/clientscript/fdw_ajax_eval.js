/*======================================================================*\
|| #################################################################### ||
|| # Simple Sign Up - Foros del Web                                   # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/
var fdwAjaxEval = {
	scriptExp: /<script.*?>(?:\n|\r|.)*?<\/script>/ig,
	scriptExpSingle: /<script.*(?:src="(.*)")?>((?:\n|\r|.)*?)<\/script>/i,
	calledMethods: [],
	loadedScripts: [],
	loadScripts: function(sc) {
		for (i = 0; i < sc.length; i++) {
			this.loadScript(sc[i], true);
		}
	},
	loadScript: function(sc, previous) {
		if (previous) {
			for (i = 0; i < this.loadedScripts.length; i++) {
				if (this.loadedScripts[i] == sc) {
					return false;
				}
			}
		}
		
		var code = document.createElement("script");
		code.src = sc;
		document.body.appendChild(code);
		this.loadedScripts.push(sc);
	},
	waitObject: function(objName, callback) {
		function waitingObject() {
			for (i = 0; i < this.calledMethods.length; i++) {
				if (this.calledMethods[i] == objName) {
					return false;
				}
			}
			if (!window[objName]) {
				var object = this;
				setTimeout(function() { waitingObject.call(object); }, 500);
			} else {
				this.calledMethods.push(objName);
				callback();
			}
		}
		waitingObject.call(this);
	},
	stripScripts: function(text) {
		return text.replace(this.scriptExp, '');
	},
	evalScripts: function(text) {
		var codes = text.match(this.scriptExp);
		var i;
		for (i = 0; i < codes.length; i++) {
			var parts = codes[i].match(this.scriptExpSingle);
			var code = {
				src: parts[1],
				code: parts[2]
			};
			
			if (code.src) {
				this.loadScript(code.src, false);
			} else {
				if (window.execScript) {
					execScript(code.code);
				} else {
					setTimeout(code.code, 0);
				}
			}
		}
	}
}