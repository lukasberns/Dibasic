/****

DITextArea:
A <textarea> obj

****/



(function($) {

Class("DITextArea", DIText, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		this._el = $('<textarea/>', {
			'id': id,
			'name': name
		}).css({
			height: '7em'
		});
		if (this.definition.placeholder) {
			// we replace newlines with lots of spaces to get a "newline" effect
			// credits to Thomas Hunter for this hack
			// [http://stackoverflow.com/questions/7189223]
			this._el.attr('placeholder', this.definition.placeholder.replace(/\n/g, new Array(100).join(' ')));
		}
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		return label.add(this._el);
	},
	
	render: function(data) {
		// how the dataRenderer should render the data (text || jQuery obj)
		
		var html = $(data.split(/\n/)).map(function() {
			return $('<span/>').text(this+'').html();
		}).get().join('<br>');
		
		return $('<span/>').html(html)[0].childNodes;
	}
});

})(jQuery);