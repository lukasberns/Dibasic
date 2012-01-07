/****

DPActionDetails:
Display the details of one action

****/



(function($) {

Class("DPActionDetails", DP, {
	init: function(def) {
		var self = this;
		this.definition = def;
		
		$(window).bind('hashchange', function() {
			var id = $.bbq.getState(self.className);
			
			if (id) {
				self.displayDetails(id);
			}
		});
	},
	
	widget: function(id) {
		var self = this;
		return button = $('<input type="button" value="Details" />').click(function() {
			var state = {};
			state[self.className] = id;
			$.bbq.pushState(state);
		});
	},
	
	_fancyboxOptions: function($super) {
		var self = this;
		return {
			padding: 0,
			enableKeyboardNavigation: false,
			// onCleanup: function() { return self.willCloseFancybox(); },
			onClosed: function() {
				$.bbq.removeState(self.className);
			}
		};
	},
	
	displayDetails: function(id) {
		var self = this;
		$.fancybox.showActivity();
		
		$.get(Dibasic.url({ action: this.className, action_id: id}), function(resp) {
			var data = resp.data;
			var isDelete = (resp.action == 'remove');
			
			var container = $('<div/>').addClass('DPActionDetails');
			
			for (var table in data) {
				if (!data.hasOwnProperty(table)) { continue; }
				
				$('<h1/>').text(table).appendTo(container);
				
				var tableData = data[table];
				for (var tableId in tableData) {
					if (!tableData.hasOwnProperty(tableId)) { continue; }
					
					$('<h2/>').text('id: '+tableId).appendTo(container);
					
					var tableForRow = $('<table/>');
					var rowEl = $('<tr/>');
					$('<th/>').appendTo(rowEl);
					$('<th/>').text('Before').appendTo(rowEl);
					$('<th/>').text('After').appendTo(rowEl);
					rowEl.appendTo(tableForRow);
					
					var row = tableData[tableId];
					for (var i in row) {
						if (!row.hasOwnProperty(i)) { continue; }
						
						var logEntry = row[i];
						var rowEl = $('<tr/>');
						$('<th/>').text(logEntry.key).appendTo(rowEl);
						var o = $('<td/>').text(logEntry.old || '').appendTo(rowEl);
						var c = $('<td/>').text(isDelete ? '' : (logEntry.value || '')).appendTo(rowEl);
						
						var changed = false;
						if (logEntry.changed) {
							c.addClass('changed');
							changed = true;
						}
						if (isDelete) {
							o.addClass('changed');
							changed = true;
						}
						
						if (!changed) {
							rowEl.addClass('nochange');
						}
						
						rowEl.appendTo(tableForRow);
					}
					
					tableForRow.appendTo(container);
				}
			}
			
			var showLabel = $('<label/>').text(' Show unchanged data');
			var checkbox = $('<input type="checkbox"/>').prependTo(showLabel).change(function() {
				if (this.checked) {
					self.showNochange = true;
					container.addClass('show-nochange');
				}
				else {
					self.showNochange = false;
					container.removeClass('show-nochange');
				}
				$.fancybox.resize();
			});
			if (self.showNochange) {
				container.addClass('show-nochange');
				checkbox.attr('checked', 'checked');
			}
			showLabel.appendTo(container);
			
			$.fancybox(container, self._fancyboxOptions());
			$.fancybox.hideActivity();
			
		}, 'json');
	},
});

})(jQuery);