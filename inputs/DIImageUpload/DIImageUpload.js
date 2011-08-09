/****

DIImageUpload:
Upload images

****/



(function($) {

Class("DIImageUpload", DIFileUpload, {
	widget: function($super, formName) {
		var widget = $super(formName);
		return widget;
	},
	
	render: function(value) {
		if (!value) {
			return '';
		}
		return $('<img/>', { src: value });
	}
});

})(jQuery);