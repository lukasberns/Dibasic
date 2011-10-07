/****

DPAddForm:
Form to add new entries

****/



(function($) {

var cachedValues, initialValues;

Class("DPAddForm", DP, {
	widget: function() {
		var self = this;
		return $('<input type="button" value="Add" />').click(function() {
			$.fancybox(self.initForm(), self._fancyboxOptions());
		});
	},
	
	_fancyboxOptions: function() {
		var self = this;
		return {
			padding: 0,
			enableKeyboardNavigation: false,
			onCleanup: function() { return self.willCloseFancybox(); }
		};
	},
	
	_initialValues: null,
	
	initForm: function() {
		this.trigger('willInitForm');
		// init the form structure
		var form = $('<form class="DPForm" method="post" />');

		var self = this;
//		var form = $('#fancybox-inner .DPForm').eq(0);
		form.html('');
	
		var ul = $('<ul />').appendTo(form);
		for (var i = 0, col; col = Dibasic.columns[i]; i++) {
			var widget = col.DI.widget(this.className);
			if (widget !== false && !col.hide) {
				$('<li />').append(widget).appendTo(ul);
			}
		}
		$('<li />').append('<input type="Submit" value="Add" class="button" />').appendTo(ul);
	
		form.validate({
			errorElement: 'span',
			errorPlacement: function(error, element) {
				error.appendTo(element.siblings('label'));
			},
			rules: Dibasic.validationRules(),
			submitHandler: function() {
				self.submit();
			},
			invalidHandler: function(e) {
				self.trigger('formIsInvalid', e.currentTarget);
			}
		});
		
		setTimeout(function() {
			form.find(':input:first').focus();
			Dibasic.resetValues();
			self._initialValues = Dibasic.getValues();
			self.trigger('didInitForm', form);
		}, 0);
		
		return form;
	},
	
	_willSubmit: false,
	
	submit: function() {
		this.trigger('willSubmit');
		var self = this;
		
		// we need to get the values before the fancybox is closed
		// otherwise the DIs won't be able to know if their element was visible or not etc.
		var values = Dibasic.getValues();
		
		this._willSubmit = true;
		$.fancybox.close();
		this._willSubmit = false;
		
		$.fancybox.showActivity();
		Dibasic.DPDBInterface.insert(values, function(data) {
			for (var i in data) { data = data[i]; break; }
			$.fancybox.hideActivity();
			$('#Dibasic').trigger('db', data);
			$('#Dibasic').trigger('added', data);
			self.trigger('didSubmit', data);
		}, function(response) {
			// error
			$.fancybox.hideActivity();
			alert("Something went wrong while adding:\n“"+response+'”');
		});
	},
	
	willCloseFancybox: function() {
		var re;
		if (this._willSubmit || equals(this._initialValues, Dibasic.getValues())) {
			re = true;
		}
		else {
			re = confirm('Any entered data will be lost. Close anyway?');
		}
		
		if (re) {
			Dibasic.dataRenderer.dehighlight();
		}
		return re;
	}
});

})(jQuery);