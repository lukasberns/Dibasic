/****

DPDBInterface:
DB interface to prevent duplicate fetches

****/



(function($) {

Class("DPDBInterface", DP, {
	init: function(def) {
		this.definition = def;
		
		this._data = {};
		this._observers = [];
		
		this._waitingFor = [];
	},
	
	lastEdit: 0, // timestamp of last edit. use this to determine if there might be new data
	
	getData: function(/* id [, callback] || ids [, callback] || from, to [, callback] */) {
		var ids, callback;
		var self = this;
		
		if (typeof arguments[1] == 'number') {
			// from, to
			ids = [];
			for (var i = arguments[0]; i <= arguments[1]; i++) {
				ids.push(i);
			}
			callback = arguments[2];
		}
		else {
			// id(s)
			ids = arguments[0];
			callback = arguments[1];
			if (typeof ids != 'object') {
				// not array
				ids = [ids];
			}
		}
		
		var missingIds = [], waitFor = [];
		var data = {};
	
		var finish = function(data) {
			// sort data, then call callback
			if ($.isFunction(callback)) {
				var sorted = [];
				for (var i in ids) {
					sorted.push(data[ids[i]]);
				}
				callback(sorted);
			}
		}
		
		Dibasic.hasPermission('select', function(hasPermission) {
			if (!hasPermission) {
				finish([]);
				return;
			}

			i = 0;
			for (i in ids) {
				var id = ids[i];
				Dibasic.hasPermission('select', id, function(hasPermission) {
					// hasPermission works synchronously if permissions have been fetched before
					if (!hasPermission) {
						return;
					}

					if (self._data[id] !== undefined) {
						data[id] = $.extend({}, self._data[id]); // clone the object
					}
					else {
						if (self._waitingFor[id]) {
							waitFor.push(id);
						}
						else {
							missingIds.push(id);
						}
					}
				});
			}
			
			if (missingIds.length == 0 && waitFor.length == 0) {
				finish(data);
				return;
			}
			/* else: */
			// get missing data
			
			var waiter = self._makeWaiter(missingIds.concat(waitFor), data, finish);
			self.observe(waiter, true);
			
			if (missingIds.length != 0) {
				for (i in missingIds) {
					self._waitingFor[missingIds[i]] = true;
				}
				$.post(Dibasic.url({action:'DPDBInterface'}), {'get':missingIds.join(',')}, function(missingData, textStatus) {
					if (!missingData[0].match(/[\{\[]/)) {
						alert('DPDBInterface.getData() error: The server returned an unexpected output:\n\n“'+missingData+'”');
					}
					missingData = JSON.parse(missingData);
					for (var id in missingData) {
						self._data[id] = missingData[id];
						delete self._waitingFor[id];
					}
					self._fire(missingData);
				});
			}
		});
	},
	
	setData: function(/* id, data || data */) {
		// if you only specify @data, input an object with the id as key and the data as value
		// e.g. {1:{name: 'John'}, 13:{name: 'Mary'}}
	
		// won’t be edited on server -- use insert / update for that
	
		var data;
		if (arguments.length == 2) {
			data = {};
			data[arguments[0]] = arguments[1];
		}
		else {
			data = arguments[0];
		}
	
		for (var id in data) {
			this._data[id] = data[id];
		}
	
		this._fire(data);
	},
	
	insert: function(data, callback, errorCallback) {
		// you don’t know the id yet, so data is e.g. just {name: 'John'}
		var self = this;
		delete Dibasic.permissions;
		$.post(Dibasic.url({action:'DPDBInterface'}), {'insert':JSON.stringify(data)}, function(data, textStatus) {
			if (data[0] != '{') {
				// not json, error
				if ($.isFunction(errorCallback)) {
					errorCallback(data);
				}
				else {
					throw 'DPDBInterface.insert() error: The server returned an unexpected output: '+data;
				}
				return;
			}
			data = JSON.parse(data);
			var newData = {};
			
			self._data[data.id] = newData[data.id] = data;
			self.lastEdit = new Date().getTime();
			if ($.isFunction(callback)) {
				callback(newData);
			}
			self._fire(newData);
		});
	},
	
	update: function(data, callback, errorCallback) {
		var self = this;
		delete Dibasic.permissions;
		for (var id in data) {
			this._waitingFor[id] = true;
			delete this._data[id];
		}
		$.post(Dibasic.url({action:'DPDBInterface'}), {'update':JSON.stringify(data)}, function(data, textStatus) {
			if (data[0] != '{') {
				// not json, error
				if ($.isFunction(errorCallback)) {
					errorCallback(data);
				}
				else {
					throw 'DPDBInterface.update() error: The server returned an unexpected output: '+data;
				}
				for (var i in ids) {
					var id = ids[i];
					delete self._waitingFor[id];
				}
				return;
			}
			data = JSON.parse(data);
			for (var id in data) {
				self._data[id] = data[id];
				delete self._waitingFor[id];
			}
			self.lastEdit = new Date().getTime();
			if ($.isFunction(callback)) {
				callback(data);
			}
			self._fire(data);
		});
	},
	
	remove: function(ids, callback, errorCallback) {
		var self = this;
		if (typeof ids != 'object') {
			ids = [ids];
		}
		for (var i in ids) {
			var id = ids[i];
			this._waitingFor[id] = true;
			delete this._data[id];
		}
	
		$.post(Dibasic.url({action:'DPDBInterface'}), {'remove':ids.join(',')}, function(data, textStatus) {
			if (data[0] != '[') {
				// not json, error
				if ($.isFunction(errorCallback)) {
					errorCallback(data);
				}
				else {
					throw 'DPDBInterface.remove() error: The server returned an unexpected output: '+data;
				}
				for (var i in ids) {
					var id = ids[i];
					delete self._waitingFor[id];
				}
				return;
			}
			var removedData = JSON.parse(data);
			var newData = {};
			for (var id in ids) {
				newData[id] = self._data[id] = null;
			}
			self.lastEdit = new Date().getTime();
			if ($.isFunction(callback)) {
				callback(newData);
			}
			self._fire(newData);
		});
	},
	
	refetchData: function(/* (id || ids || from, to) [, callback] */) {
		this._clearCache.apply(this, arguments);
		this.getData.apply(this, arguments);
	},
	
	_clearCache: function(/* void || id || ids || from, to */) {
		if (arguments[0] === undefined) {
			for (var id in this._data) {
				delete this._data[id];
			}
			return;
		}
	
		var ids;
		if (typeof arguments[1] == 'number') {
			// from, to
			ids = [];
			for (var i = arguments[0]; i <= arguments[1]; i++) {
				ids.push(i);
			}
		}
		else {
			// id(s)
			ids = arguments[0];
			if (typeof ids != 'object') {
				// not array
				ids = [ids];
			}
		}
	
		for (i in ids) {
			delete this._data[ids[i]];
		}
	},
	
	observe: function(callback, unshift) {
		// an observer callback will be called every time some data changes
		// the changed data will be passed as argument
		// the callback should return true to delete itself
	
		if (unshift) {
			this._observers.unshift(callback);
		}
		else {
			this._observers.push(callback);
		}
	},
	
	_fire: function(data) {
		for (var i in this._observers) {
			if (this._observers[i](data)) {
				delete this._observers[i];
			}
		}
	},
	
	_makeWaiter: function(waitingIds, data, callback) {
		var self = this;
		return function() {
			var ids = waitingIds.slice(); // since you mustn’t modify an array while enumerating it
			for (var i in ids) {
				var id = ids[i];
				if (self._data[id] !== undefined) {
					data[id] = self._data[id];
					waitingIds.shift();
				}
			}
			if (waitingIds.length == 0) {
				if ($.isFunction(callback)) {
					callback(data);
				}
				return true; // remove this waiter
			}
			return false;
		};
	}
});

})(jQuery);