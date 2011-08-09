/****

DIUniqueText:
A text field where every entry has to be unique

****/



(function($) {

Class("DIUniqueText", DIText, {
	init: function($super, def) {
		$super(def);
		var self = this;
		var url = Dibasic.url({ action: def.name });
		def.rules.remote = {
			'url': url,
			'data': {
				id: function() {
					return self._id;
				}
			}
		};
		
		Dibasic.DPAddForm.bind('didInitForm', function() {
			self._id = '';
		});
		
		Dibasic.DPUpdateForm.bind('didInitForm', function() {
			self._id = Dibasic.DPUpdateForm._id;
		});
	},
	
	_id: ''
});

})(jQuery);