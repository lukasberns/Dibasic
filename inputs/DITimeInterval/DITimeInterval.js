/****

DITimeInterval:
Allows to pick a start and end time

****/



(function($) {

Class("DITimeInterval", DITime, {
	init: function($super, def) {
		$super(def);
		
		var self = this;
		Dibasic.DPUpdateForm.bind('didInitForm.DPAddForm', function() {
			self.manipulate();
		});
	},
	
	manipulate: function() {
		// the widget (a jQuery object) displayed in forms
		var end_el = Dibasic.columnWithName(this.definition.endColumnName).DI._el;
		var endContainer = end_el.parent('li');
		this._el.css({ width:'100px' }).after(
			end_el.css({ width:'100px', marginLeft: '10px' })
		).after(' ~');
		endContainer.remove();
	}
});

})(jQuery);