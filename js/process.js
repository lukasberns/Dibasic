// process html code before inserting it into the dom
// {{ ... }} will be evaluated as js, (like <?...?> in php)
// {{= ... }} will echo the result (DOM | jQuery) in place (like <?=...?> in php)

function process(code, insertInto, prefixCode, suffixCode, callback) {
	prefixCode = prefixCode || '';
	suffixCode = suffixCode || '';
	
	var echoIndex = 10418013087090;
	var echoReplacements = {};
	$(insertInto).append(code.replace(/\{\{(=?)([\s\S]*?)\}\}/g, function(match, echo, js) {
		if (echo) {
			echoIndex++;
			echoReplacements[echoIndex] = eval(prefixCode+js+suffixCode);
			return '<span id="' + echoIndex + '"></span>';
		}
		else {
			eval(prefixCode+js+suffixCode);
			return '';
		}
	}));
	
	for (var index in echoReplacements) {
		var replacement;
		if (typeof echoReplacements[index] == 'string') {
			replacement = document.createTextNode(echoReplacements[index]);
		}
		else {
			replacement = echoReplacements[index];
		}
		$('#'+index).replaceWith(replacement);
	}
	
	if ($.isFunction(callback)) {
		callback();
	}
}