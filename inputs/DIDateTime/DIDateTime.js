/****

DIDateTime:
Allows to pick a date with its time

****/



(function($) {

Class("DIDateTime", DI, {
	
	_DIDate: null,
	_DITime: null,
	
	init: function($super, def) {
		$super(def);
		
		this._DIDate = new DIDate({ name: def.name+'_DIDate', rules: def.rules });
		this._DITime = new DITime({ name: def.name+'_DITime', rules: def.rules, seconds: def.seconds });
	},
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var self = this;
		
		var label = $('<label />', {
			'for': '__DI__' + this.definition.name+'_DIDate',
			'text': this.definition.title
		}).addClass('input-title');
		
		var date = this._DIDate.widget(formName).filter('input').css({ width: '100px' });
		var time = this._DITime.widget(formName).filter('input').css({ width:'100px', marginLeft: '10px' });
		
		this.setDefault();
		
		if (self.definition.rules) {
			setTimeout(function() {
				// executed in next run loop
				// = after the elements have been added to the form and the form got .validate()'d
				date.rules('add', self.definition.rules);
				time.rules('add', self.definition.rules);
			}, 0);
		}
		
		return label.add(date).add(time);
	},

	val: function($super, value) {
		// get or set the value
		// change _el’s value es well
		if (typeof value != 'undefined') {
			value = value.split(' ');
			this._DIDate.val(value[0]);
			this._DITime.val(value[1]);
			return this;
		}
		return this._DIDate.val() + ' ' + this._DITime.val();
	},

	render: function(value) {
		// how the dataRenderer should render the data (jQuery obj)
		if (!this.definition.seconds) {
			value = value.replace(/(\d{2}:\d{2}):\d{2}$/, '$1');
		}
		return value;
	}
});

})(jQuery);