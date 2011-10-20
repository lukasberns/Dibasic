/****

DICheckbox:
Just a simple checkbox

****/

(function($) {

Class("DICheckbox", DI, {
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		this._el = $('<input />', {
			'type': 'checkbox',
			'id': id,
			'name': name
		});
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		});
		
		return this._el.add(label);
	},

	val: function(value) {
		// get or set the value
		// change _el’s value es well
		if (typeof value != 'undefined') {
			this._el.prop('checked', value-0); // `-0` to convert to int
			return this;
		}
		return this._el.prop('checked');
	},

	render: function(data) {
		// how the dataRenderer should render the data (jQuery obj)
		var el = $('<input/>', {
			type: 'checkbox',
			disabled: true
		});
		if (data-0) {
			el.prop('checked', true);
		}
		return el;
	}
});

})(jQuery);