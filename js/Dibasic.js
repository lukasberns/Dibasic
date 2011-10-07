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

/**
 * Check if user has permissions to perform a specific action
 * 
 * Since permissions need to be refetched after inserts and updates,
 * the callback maybe called asynchronously. In case the permissions
 * don't need to be refetched, the callback runs synchronously. Thus
 * you can be sure it will run synchronously when being called inside
 * a hasPermission-callback
 * 
 * The value passed to the callback is a boolean if the id was specified,
 * but it might be an array containing the permitted ids if id was not
 * specified. In either case, a simple falsiness check works fine.
 *
 * @param string action One of "select", "insert", "update", "delete", "create", "alter"
 * @param int id (optional) The id to check the permissions for. These overrule the generic permissions
 * @param function callback A callback in the form function(bool hasPermission) { ... }
 * @return void
 */
Dibasic.hasPermission = function(action, /* optional: */ id, callback) {
	if (!Dibasic.permissions) {
		// need to refetch permissions
		$.get(Dibasic.url({ permissions: true }), function(ps) {
			Dibasic.permissions = ps;
			Dibasic.hasPermission(action, id, callback);
		}, 'json');
		return;
	}
	
	var p = Dibasic.permissions[action];
	
	if (!p) {
		callback(false);
		return;
	}
	
	if ($.isFunction(id)) {
		callback = id;
		id = undefined;
	}
	
	if (id !== undefined && Dibasic.deny[action] && $.inArray(id-0, Dibasic.deny[action]) > -1) {
		callback(false);
		return;
	}
	
	switch (action) {
		case 'update':
		case 'delete':
		// you can't update or delete entries you don't have select permission for
		var select = Dibasic.permissions.select;
		if (!select) {
			callback(false);
			return;
		}
		
		if (id !== undefined && $.inArray(id-0, Dibasic.deny.select) > -1) {
			callback(false);
			return;
		}
		
		if ($.isArray(select) && $.inArray(id-0, select) == -1) {
			// update/delete are stricter than the select permissions
			
			if ($.isArray(p)) {
				// if 123 is in select but not in update/delete, deny
				// if 123 in not in select but in update/delete, deny as well
				var update_delete = p;
				p = [];
				for (var i in select) {
					if ($.inArray(select[i], update_delete)) {
						p.push(select[i]);
					}
				}
			}
			else {
				// if update/delete = true, limit them to the select permissions
				p = select;
			}
		}
		break;
	}
	
	if (id === undefined) {
		// generic permission test. might be forbidden for specific ids though
		callback(p);
		return;
	}
	
	if ($.isArray(p) && $.inArray(id-0, p) == -1) {
		callback(false);
		return;
	}
	
	callback(true);
};
