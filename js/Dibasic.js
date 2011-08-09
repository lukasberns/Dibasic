if (typeof Dibasic == 'undefined') {
	Dibasic = {};
}

Dibasic.columnIds = {};
Dibasic.pluginIds = {};

Dibasic.start = function() {
	// init DPs
	for (var i = 0, p; p = this.plugins[i]; i++) {
		var DPObj = eval(p.DPName);
		p.DP = new DPObj(p);
		this[p.DPName] = p.DP;
	}
	
	// init DIs
	var c;
	for (i = 0, c; c = this.columns[i]; i++) {
		var DIObj = eval(c.DIName); // TODO: is this unsafe?
		c.DI = new DIObj(c);
		this.columnIds[c.name] = i;
	}
	
	this.dataRenderer = this[this.dataRendererName];
	
	this.wrapper = $('#Dibasic');
	if (this.wrapper.length == 0) {
		this.wrapper = $('<div id="Dibasic"></div>').appendTo('body');
	}
	
	// inject the mainStructure
	// {{ ... }} will be evaluated as js, (like <?...?> in php)
	// {{= ... }} will echo the result (DOM | jQuery) in place (like <?=...?> in php)
	
	process(this.mainStructure, this.wrapper);
	
	$.bbq.pushState('-');
	$(window).trigger('hashchange');
};

Dibasic.columnWithName = function(name) {
	return this.columns[this.columnIds[name]];
};

Dibasic.getValues = function() {
	var values = {};
	for (var i = 0, col; col = this.columns[i]; i++) {
		values[col.name] = col.DI.val();
	}
	return values;
};

Dibasic.setValues = function(values) {
	if (typeof values != 'object') { return; }
	for (var i = 0, col; col = this.columns[i]; i++) {
		col.DI.val(values[col.name]);
	}
};

Dibasic.resetValues = function() {
	for (var i = 0, col; col = this.columns[i]; i++) {
		col.DI.resetValue();
	}
};

Dibasic.validationRules = function() {
	var rules = {};
	for (var i = 0, col; col = this.columns[i]; i++) {
		rules[col.name] = col.DI.validationRules();
	}
	return rules;
};

Dibasic.url = function(getParams) {
	var url = this.baseUrl;
	var params = $.extend({}, this.urlParams, getParams);
	if (equals(params, {})) {
		return url;
	}
	url += '?';
	for (var i in params) {
		url += i+'='+urlEncode(params[i])+'&';
	}
	return url.substr(0, url.length-1);
};
