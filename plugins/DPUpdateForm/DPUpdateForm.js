/****

DPUpdateForm:
Form to edit entries

****/



(function($) {

Class("DPUpdateForm", DPAddForm, {
	_isOpen: false,
	
	init: function(def) {
		this.definition = def;
		this._id = null; // id of the currently displayed form
		
		var self = this;
		
		$(window).bind('hashchange', function() {
			var id = $.bbq.getState(self.className);
			
			Dibasic.hasPermission('update', id, function(hasPermission) {
				if (!hasPermission) {
					id = null;
					// in case this function ran asynchronously
					if (self._isOpen) {
						$.fancybox.close();
					}
				}
			});
			
			if (id) {
				Dibasic.DPDBInterface.getData(id, function(data) {
					$.fancybox(self.initForm(data), self._fancyboxOptions());
				});
			}
		});
	},
	
	_fancyboxOptions: function($super) {
		var o = $super();
		var self = this;
		o.onClosed = function() {
			self._isOpen = false;
			$.bbq.removeState(self.className);
		};
		return o;
	},
	
	widget: function(id) {
		var hasP = true; // default to display, remove if hasPermission runs asynchronously
		var button;
		Dibasic.hasPermission('update', id, function(hasPermission) {
			hasP = hasPermission;
			if (button && !hasPermission) {
				button.remove();
			}
		});
		
		if (!hasP) {
			return null;
		}
		
		var self = this;
		return button = $('<input type="button" value="Update" />').click(function() {
			var state = {};
			state[self.className] = id;
			$.bbq.pushState(state);
		});
	},
	
	initForm: function(data) {
		this._isOpen = true;
		
		this.trigger('willInitForm');
		var self = this;
		var form = $('<form class="DPForm" method="post" />');
	
		for (var i in data) {
			data = data[i]; // get the data for the interested element
			break;
		}
		
		this._id = data.id;
		Dibasic.dataRenderer.highlight(this._id);
		
		var ul = $('<ul></ul>').appendTo(form);
		var col;
		for (i = 0; col = Dibasic.columns[i]; i++) {
			var widget = col.DI.widget(this.className, this._id);
			if (widget !== false && !col.hide) {
				$('<li />').append(widget).appendTo(ul);
			}
		}
		$('<li/>').append('<input type="Submit" value="Save" class="button" />').appendTo(ul);
		
		Dibasic.resetValues();
		Dibasic.setValues(data);
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
		}, 0);
		this._initialValues = Dibasic.getValues();
		this.trigger('didInitForm', form, data.id);
		
		return form;
	},
	
	submit: function() {
		this.trigger('willSubmit');
		var self = this;
		var data = {};
		var values = Dibasic.getValues();
		data[this._id] = values;
		
		for (var col in values) {
			if (values[col] == this._initialValues[col]) {
				delete values[col]; // only send changed data
			}
		}
		
		this._willSubmit = true;
		$.fancybox.close();
		this._willSubmit = false;
		
		$.fancybox.showActivity();
		Dibasic.DPDBInterface.update(data, function(data) {
			for (var i in data) { data = data[i]; break; }
			$.fancybox.hideActivity();
			$('#Dibasic').trigger('db', data);
			$('#Dibasic').trigger('updated', data);
			self.trigger('didSubmit', data);
		}, function(response) {
			// error
			$.fancybox.hideActivity();
			alert("Something went wrong while updating:\n“"+response+'”');
		});
	}
});

})(jQuery);