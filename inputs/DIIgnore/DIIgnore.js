/****

DIIgnore:
Does nothing

****/



(function($) {

Class("DIIgnore", DI, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		if (this.definition.display) {
			var name = this.definition.name;
			var id = '__DI__' + name;
			this._el = $('<input />', {
				'type': 'text',
				'id': id,
				'disabled': true,
				'css': {
					'border-color': 'white',
					'color': 'black'
				}
			});
			
			var label = $('<label />', {
				'for': id,
				'text': this.definition.title
			}).addClass('input-title');
			
			return label.add(this._el);
		}
		return false;
	},
	
	_value: null,
	
	val: function(value) {
		if (typeof value != 'undefined') {
			this._value = value;
			if (this._elIsSet()) {
				this._el.val(value);
			}
			return this;
		}
		return this._value;
	}
});

})(jQuery);