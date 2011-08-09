/****

DIIgnore:
Does nothing

****/



(function($) {

Class("DIIgnore", DI, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		return false;
	},
	
	_value: null,
	
	val: function(value) {
		if (typeof value != 'undefined') {
			this._value = value;
			return this;
		}
		return this._value;
	}
});

})(jQuery);