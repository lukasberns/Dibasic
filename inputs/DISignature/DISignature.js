/****

DISignature:
All things server side

****/



(function($) {

Class("DISignature", DI, {
	widget: function() {
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