/****

DPSectionedForm:
Add section headers to the form

****/



(function($) {

Class("DPSectionedForm", DP, {
	init: function($super, def) {
		$super(def);
		var self = this;
		new DP({}).bind('didInitForm.DPAddForm.DPUpdateForm', function(e, form) { self.processForm(form); });
	},
	
	processForm: function(form) {
		var lis = form.children('ul').eq(0).children(':not(:last-child)');
		
		var i = 0;
		for (var tI in this.definition.titles) {
			var title = this.definition.titles[tI];
			$('<li/>')
				.append(
					$('<h1/>')
					.text(title.text)
					.addClass('DPSectionedForm')
				)
				.insertBefore(
					lis.eq(title.position + (i++))
				);
		}
		
		$.fancybox.resize();
	}
});

})(jQuery);