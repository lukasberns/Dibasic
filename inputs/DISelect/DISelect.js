/****

DISelect:
Lets you choose a value from a <select>

****/



(function($) {

Class("DISelect", DI, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		this._el = $('<select />').attr({
			'id': id,
			'name': name
		});
		var opts = this.definition.options;
		for (var i in opts) {
			$('<option />', {
				'text': opts[i],
				'value': i.substr(1)
			}).appendTo(this._el);
		}
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		return label.add(this._el);
	},
	
	val: function($super, value) {
		if ($super()) { return null; }
		
		if (typeof value != 'undefined') {
			this._el.val(value);
			return this;
		}
		return this._el.val();
	},
	
	render: function(data) {
		// how the dataRenderer should render the data (text || jQuery obj)
		return this.definition.options['_'+data] || data;
	}
	
});

})(jQuery);