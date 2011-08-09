/****

DIFileUpload:
Upload files with a progress bar

****/



(function($) {

Class("DIFileUpload", DI, {
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var name = this.definition.name;
		var id = '__DI__' + name;
		var div = $('<div />').addClass('input');
		
		this._el = $('<input type="file" />')
			.attr({
				'id': id,
				'name': name
			})
			.ajaxFileUpload({
				upload_cgi: Dibasic.inputs[this.className].uploadCGI,
				fileprogress_php: Dibasic.inputs[this.className].fileprogressPHP
			})
			.css({
				display: 'block',
				padding: 0
			})
			.appendTo(div);
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		return label.add($('<div class="input">').append(this._el));
	},
	
	val: function($super, value) {
		if ($super()) { return null; }
		
		if (typeof value != 'undefined') {
			this._el.ajaxFileUpload('value', value);
			return this;
		}
		return this._el.ajaxFileUpload('value');
	},
	
	render: function(value) {
		if (!value) {
			return '';
		}
		return $('<a/>', { href: value, target: '_blank' }).text(value.substr(value.lastIndexOf('/')+1));
	}
});

})(jQuery);