/****

DIFileUploadOrURL:
Upload or provide the URL for a file

****/



(function($) {

Class("DIFileUploadOrURL", DIFileUpload, {
	widget: function($super, formName) {
		var widget = $super(formName);
		var self = this;
		
		widget.filter('.input').children().filter('div').css('float', 'left');
		widget.filter('.input').append('or');
		
		$('<input/>', { type: 'button', value: 'URL' }).css({
			fontSize: '0.8em',
/*			position: 'relative',
			top: '0.3em',*/
			margin: '0 1ex'
		}).click(function() {
			self.promptURL();
			return false;
		}).appendTo(widget.filter('.input'));
		
		return widget;
	},
	
	promptURL: function() {
		var url = prompt('Please enter the URL of the file');
		if (url) {
			this._el.ajaxFileUpload('value', url);
		}
	}
});

})(jQuery);