// easy class stuff

function Class(/* prototype || parent, prototype */) {
	var parent, prototype;
	if (toString.call(arguments[0]) == '[object Function]') {
		parent = arguments[0];
		prototype = arguments[1];
	}
	else {
		prototype = arguments[0];
	}
	
	var c = function() {
		if (this.init) {
			this.init.apply(this, arguments);
		}
	};
	c.prototype.constructor = c;
	
	if (parent) {
		var _p = function(){}; // parent without the this.init-calling constructor
		_p.prototype = parent.prototype;
		c.prototype = new _p; // to inherit methods
		c.prototype.superclass = new _p; // to be able to access them when overwritten
		c.prototype.superclass.constructor = _p;
	}
	
	if (prototype) {
		for (var i in prototype) {
			var method = prototype[i];
			
			if (toString.call(method) == '[object Function]' && !method.willCallSuperclass) {
				var firstArgument = method.toString().replace(/\n/g,'').replace(/^.*?\(([^,\)]*).*/, '$1');
				if (firstArgument == '$super') {
					// idea from prototype.js
					c.prototype[i] = (function(method, $super) { return function(){
						var args = Array.prototype.slice.call(arguments);
						var self = this;
						args.unshift(function() {return $super.apply(self,arguments);});
						return method.apply(this, args);
					};})(method, c.prototype.superclass[i]);
					continue;
				}
			}
			c.prototype[i] = method;
		}
	}
	return c;
}

// OOP Inheritance

Function.prototype.inheritsFrom = function(parentClassOrObject) {
	if (parentClassOrObject.constructor == Function) { 
		// Normal Inheritance 
		this.prototype = new parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = {};
		// the following sets the binding of the parent class correctly
		for (var i in parentClassOrObject.prototype) {
			var member = parentClassOrObject.prototype[i];
			if (toString.call(member) == '[object Function]') {
				this.prototype.parent[i] = (function(f,b){return function(){f.apply(b,arguments);};})(member,this);
			}
			else {
				this.prototype.parent[i] = member;
			}
		}
	} 
	else { 
		// Abstract Inheritance 
		console.log('Abstract inheritance is not implemented because of "who needs that?" and the lack of the clone function');
/*		this.prototype = clone(parentClassOrObject);
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject;
		for (var i in parentClassOrObject) {
			var member = parentClassOrObject[i];
			if (toString.call(member) == '[object Function]') {
				this.prototype.parent[i] = (function(f,b){return function(){f.apply(b,arguments)}})(member,this);
			}
			else {
				this.prototype.parent[i] = member;
			}
		}*/
	} 
	return this;
};

Node.prototype.lastAncestorElement = function() {
	// returns the last ancestor node that is an element
	return (this.lastChild && this.lastChild.nodeType == 1) ? this.lastChild.lastAncestorElement() : this;
};


function equals(a, b) {
	// check if to objects are equal
	if (typeof a != 'object' || typeof b != 'object') { return a == b; }
	var eq = true, i;
	for (i in a) { if (a[i] !== b[i]) { eq = false; break; } }
	for (i in b) { if (a[i] !== b[i]) { eq = false; break; } }
	return eq;
};