/****

DIText:
Just a simple text field

****/



(function($) {

Class("DIText", DI, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		this._el = $('<input />', {
			'type': 'text',
			'id': id,
			'name': name
		});
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		return label.add(this._el);
	},
	
	val: function(value) {
		if (typeof value != 'undefined') {
			if (this._elIsSet()) {
				this._el.val(value);
			}
			return this;
		}
		if (this._elIsSet()) {
			return this._el.val();
		}
		return; // i.e. undefined
	}
});

})(jQuery);