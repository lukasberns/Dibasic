/****

DPAddForm:
Form to add new entries

****/



(function($) {

Class("DPDeleteForm", DP, {
	init: function($super, def) {
		$super(def);
		this._id = null;
		this._data = {};
	},
	
	widget: function(id) {
		var hasP = true; // default to display, remove if hasPermission runs asynchronously
		var button;
		Dibasic.hasPermission('delete', id, function(hasPermission) {
			hasP = hasPermission;
			if (button && !hasPermission) {
				button.remove();
			}
		});
		
		if (!hasP) {
			return null;
		}
		
		var self = this;
		return button = $('<input type="button" value="Delete" />').click(function() {
			Dibasic.DPDBInterface.getData(id, function(data) {
				$.fancybox(self.initForm(data), {
					hideOnContentClick: false,
					overlayShow: true,
					onCleanup: function() { Dibasic.dataRenderer.dehighlight(); }
				});
			});
		});
	},
	
	initForm: function(data) {
		var self = this;
		var form = $('<form class="DPForm" method="post" />');
		
		for (var i in data) { data = data[i]; break; } // get the data of the interested element
		
		this._id = data.id;
		Dibasic.dataRenderer.highlight(this._id);
		
		var candidate = null;
		for (var i = 0, l = Dibasic.columns.length; i < l; i++) {
			var column = Dibasic.columns[i];
			if (!candidate && data[column.name]) {
				candidate = column;
			}
			if (/^DI(Unique)?Text$/.test(column.DIName) && data[column.name]) {
				candidate = column;
				break;
			}
		}
		var excerpt = candidate ? data[candidate.name] : null;
		
		var ul = $('<ul></ul>').appendTo(form);
		$('<li>Do you really want to delete this?</li>').appendTo(ul);
		if (excerpt) { $('<li>Excerpt: '+excerpt+'</li>').appendTo(ul); }
		$('<li><input type="Submit" value="Delete" class="button" /></li>').appendTo(ul);
		
		form.submit(function() {
			self.submit();
			return false;
		});
		
		return form;
	},
	
	submit: function() {
		var self = this;
		$.fancybox.close();
		$.fancybox.showActivity();
		Dibasic.DPDBInterface.remove(this._id, function() {
			$.fancybox.hideActivity();
			$('#Dibasic').trigger('db', {id:self._id});
			$('#Dibasic').trigger('deleted', {id:self._id});
			self.trigger('didSubmit', self._id);
		}, function(response) {
			// error
			$.fancybox.hideActivity();
			alert("Something went wrong while deleting:\n“"+response+'”');
		});
	}
});

})(jQuery);