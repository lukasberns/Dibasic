/****

DISecureText:
A text field for confidential data

****/



(function($) {

var DUMMY = new Array(8).join('•');
var makeSalt = function() {
	return hex_md5(new Date().getTime() + '_DIBASIC_' + Math.random());
};

Class("DISecureText", DI, {
	_enterDIText: null,
	_repeatDIText: null,
	_salt: null,
	_extraSalt1: null,
	_extraSalt2: null,
	
	init: function($super, def) {
		$super(def);
		this._enterDIText = new DIText({ name: def.name + '_enterDIText', title: def.title });
		this._repeatDIText = new DIText({ name: def.name + '_repeatDIText', title: 'Re-enter password' });
	},
	
	widget: function(formName) {
		var self = this;
		this._salt = makeSalt();
		if (this.definition.extraHashColumnName) {
			this._extraSalt1 = makeSalt();
			this._extraSalt2 = makeSalt();
		}
		this._didSetRules = false;
		
		var enter = this._enterDIText.widget(formName);
		enter.filter('input').attr('type', 'password');
		
		var repeat = this._repeatDIText.widget(formName);
		repeat.filter('input').attr('type', 'password');
		repeat.filter('label').css('color', '#777');
		
		var widget = enter.add($('<div/>').append(repeat).css('marginTop', '10px'));
		var _return;
		
		if (formName == 'DPUpdateForm') {
			var label = $('<label/>').text(this.definition.title).addClass('input-title');
			
			var showLink = $('<input />', {
					type: 'button',
					value: 'Change'
				})
				.css({
					fontSize: '0.8em'
				})
				.addClass('input-spaced')
				.toggle(function() {
					// change password
					widget.insertBefore(this);
					self._setRules();
					label.remove();
					$(this).val('Use old');
					$.fancybox.resize();
				}, function() {
					// use old password
					widget.remove();
					label.insertBefore(this);
					$(this).val('Change');
					$.fancybox.resize();
				});
			
			_return = label.add(showLink);
		}
		else {
			_return = widget;
			setTimeout(function() {
				self._setRules();
			}, 0); // execute in the next run loop = after widget has been appended to the form
		}
		
		return _return;
	},
	
	_didSetRules: false,
	_setRules: function() {
		if (this._didSetRules) {
			return;
		}
		var rules = this.definition.rules;
		this._enterDIText._el.rules('add', { required: true, minlength: (rules && rules.minlength) || 6 });
		this._repeatDIText._el.rules('add', { equalTo: this._enterDIText._el });
		this._didSetRules = true;
	},
	
	val: function(value) {
		if (typeof value !== 'undefined') {
			// set
			if (value) {
				// there is data saved on the server, so display some dummy string here
				// we can’t get any details about the data as it is saved as a hash
			}
			else {
			}
			return this;
		}
		
		if (this._enterDIText._el.is(':visible')) {
			var password = this._enterDIText.val();
			var data = {
				salt: this._salt,
				hash: hex_md5(this._salt + password)
			};
			if (this.definition.extraHashColumnName) {
				data.extraSalt1 = this._extraSalt1;
				data.extraSalt2 = this._extraSalt2;
				data.extraHash = hex_md5(this._extraSalt2 + hex_md5(this._extraSalt1 + password));
			}
			return JSON.stringify(data);
		}
		
		return 'old';
	},
	
	render: function(data) {
		if (!data) { return data; }
		return DUMMY; // obfuscate data (which should be stored unreadable anyway)
	},
	
	validationRules: function() {
		return '';
	}
});

})(jQuery);