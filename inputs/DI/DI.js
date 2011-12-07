/****

DI:
The basic class all DI... inherit from.

****/

(function($) {

var _DIValidationGUIDIncrement = 0;

Class("DI", {
	init: function(definition) {
		/**
		@param definition:
			an object in this style: {DI:*, className:*, DIName:*, ... (other options)}
		
		
		DI.definition.rules:
			any $.validate rule ('required', {required: true})
			
			or
			
			array of {regexp:/.../, matchMeansValid:(bool), errorString:"..."}
			@errorString is the string to display when invalid (required)
			
			Either @white or @black should be defined
		**/
		
		this.definition = definition || {};
	},
	
	widget: function(formName, id) {
		// the widget (a jQuery object) displayed in forms
		return this._el = null;
	},
	
	_elIsSet: function() {
		// use this method to check if _el is set to a jquery object
		if (!this._el || !this._el.jquery) {
			console.error('DIError: _el was undefined. Please use val() only after making a widget() (columnName: “'+this.definition.name+'”)');
			return false;
		}
		return true;
	},
	
	val: function(value) {
		// get or set the value of the *current* widget
		// inheriting classes usually change the _el’s value (look at DIText.js)
	},
	
	resetValue: function() {
		this.val('');
		this.setDefault();
	},
	
	render: function(data) {
		// how the dataRenderer should render the data (text || jQuery obj)
		return data;
		// alternative (like htmlspecialchars in php):
		// return $('<p />').text(data).html()
	},
	
	setDefault: function() {
		// sets the default value if provided
		if (typeof this.definition['default'] !== 'undefined') {
			this.val(this.definition['default']);
		}
	},
	
	validationRules: function() {
		var self = this;
		var rules;
		//var type = Object.prototype.toString.call(this.definition.rules).replace(/\[.* (.*)\]/, '$1');
		if ($.isArray(this.definition.rules)) {
			// use the isValid function
			rules = {};
			$(this.definition.rules).each(function() {
				if (this.regexp !== undefined && this.matchMeansValid !== undefined) {
					// { regexp: /.../, matchMeansValid: (bool) }
					var ruleName = '__DI_validationRules_'+_DIValidationGUIDIncrement++;
					DI._setupValidationMethod(this, ruleName);
					rules[ruleName] = true;
				}
				else {
					// just some object, merge it into rules
					$.extend(rules, this);
				}
			});
		}
		else {
			// string | object rules, just copy
			// e.g. required, email, {required:true,email:true}
			rules = this.definition.rules;
		}
		return rules;
	}
});

DI._setupValidationMethod = function(rule, name) {
	$.validator.addMethod(
		name,
		function(value, element) {
			return DI._isValid(value, rule);
		},
		rule.message
	);
};

DI._isValid = function(value, rule) {
	if (rule.regexp.test(value) != rule.matchMeansValid) {
		// invalid
		return false;
	}
	return true;
};


})(jQuery);