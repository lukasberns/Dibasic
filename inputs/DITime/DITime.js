/****

DITime:
A time picker

****/



(function($) {

Class("DITime", DI, {
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		
		var mask;
		if (this.definition.seconds) {
			mask = '99:99:99';
		}
		else {
			mask = '99:99';
		}
		
		this._el = $('<input />', {
			'type': 'text',
			'id': id,
			'name': name
		}).mask(mask, {placeholder: '0'})
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		
		return label.add(this._el);
	},

	val: function($super, value) {
		// get or set the value
		// change _el’s value es well
		if ($super()) { return null; }
		
		if (typeof value != 'undefined') {
			if (!this.definition.seconds) {
				value = value.replace(/^(\d{2}:\d{2}):\d{2}$/, '$1');
			}
			this._el.val(value);
			return this;
		}
		return this._el.val();
	},

	render: function(data) {
		// how the dataRenderer should render the data (jQuery obj)
		if (!this.definition.seconds) {
			data = data.replace(/^(\d{2}:\d{2}):\d{2}$/, '$1');
		}
		return data;
	}
});

})(jQuery);