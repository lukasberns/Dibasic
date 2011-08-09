/****

DIDate:
A Date Picker

****/


(function($) {

Class("DIDate", DI, {
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		this._el = $('<input />', {
			'type': 'text',
			'id': id,
			'name': name
		}).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			navigationAsDateFormat: true,
			showOtherMonths: true
		});
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
			if (value != '0000-00-00') {
				this._el.val(value);
			}
			return this;
		}
		return this._el.val();
	},

	render: function(data) {
		// how the dataRenderer should render the data (jQuery obj)
		if (data == '0000-00-00') {
			return '';
		}
		return data;
	}
});

})(jQuery);