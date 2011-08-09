/****

DIImageUploadOrURL:
Upload or provide the url of images

****/



(function($) {

Class("DIImageUploadOrURL", DIFileUploadOrURL, {
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