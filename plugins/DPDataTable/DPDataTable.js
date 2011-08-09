/****

DPAddForm:
Form to add new entries

****/



(function($) {

Class("DPDataTable", DPDataTemplate, {
	widget: function() {
		this.table = $('<table id="DPDataTable" />');
		var thead = $('<thead />').appendTo(this.table);
		this.tbody = $('<tbody />').appendTo(this.table);
		this.tfoot = $('<tfoot />').appendTo(this.table);
		
		this.pager = $('<td />', {
			colspan: this.definition.columns.length + 1
		}).appendTo(this.tfoot);
	
		var columns = this.definition.columns;
		for (var i in columns) {
			var col = Dibasic.columnWithName(columns[i]);
			if (!col) {
				throw 'DPDataTable.widget(): The column “'+columns[i]+'” you specified does not appear in the table.';
			}
			var title = col.title;
			$('<th />').append(title).appendTo(thead);
		}
		$('<th />').addClass('controlsColumn').appendTo(thead); // for buttons
		
		this.getData();
		
		return this.table;
	},
	
	displayData: function(callback) {
		// callback is optional, called after table is set
		var self = this;
		this.getData(function(data) {
			var columns = self.definition.columns;
			
			self.tbody.html('');
			for (var id in data) {
				var row = data[id];
				var tr = $('<tr/>');
				for (var j in columns) {
					var name = columns[j];
					var DI = Dibasic.columnWithName(name).DI;
					$('<td/>').append(DI.render(row[name], id, data)).appendTo(tr);
				}
				var buttonWrapper = $('<div class="segmented"/>').css('opacity', 0.5);
				buttonWrapper.append(Dibasic.DPUpdateForm.widget(id));
				buttonWrapper.append(Dibasic.DPDeleteForm.widget(id));
				$('<td/>').addClass('controlsColumn').append(buttonWrapper).appendTo(tr);
				tr.appendTo(self.tbody);
				
				tr.hover(function() {
					$(this).find('.segmented').animate({'opacity':1}, 0);
				}, function() {
					$(this).find('.segmented').animate({'opacity':0.5}, 0);
				});
			}
			
			self.makePager(function(pager) {
				self.pager.html(pager);
			});
			
			self.table.find('.segmented input').focus(function () {
				$(this).parents('.segmented').css('opacity', 1);
			}).blur(function() {
				$(this).parents('.segmented').css('opacity', 0.5);
			});
			
			self.table.find('tr:odd').css('backgroundColor', '#fafafa');
			
			if ($.isFunction(callback)) {
				callback(data);
			}
		});
	},
	
	highlight: function(id, momentaneous) {
		// highlights entry with id
	
		//	var color = momentaneous ? '#ffc' : '#cef';
		color = '#ffc';
	
		for (var i in this._ids) {
			if (id == this._ids[i]) {
				this.table.find('tbody tr:nth-child('+(i-0+1)+')') /* `-0` to convert to number */
					.each(function() {
						$(this).data('backgroundColorBeforeHighlight', $(this).css('backgroundColor'));
					})
					.addClass('highlighted')
					.animate({'backgroundColor':color}, 'normal');
				break;
			}
		}
	
		if (momentaneous) {
			var self = this;
			setTimeout((function(id) {return function() {
				self.dehighlight(id);
			};})(id), 1000);
		}
	},
	
	dehighlight: function(id) {
		var trs = this.table.find('.highlighted');
		if (id != undefined) {
			var filtered = false;
			for (var i in this._ids) {
				if (id == this._ids[i]) {
					trs = trs.filter(':nth-child('+(i-0+1)+')');
					filtered = true;
					break;
				}
			}
			if (!filtered) {
				trs = trs.filter(':nth-child(-1)'); // deselect
			}
		}
		trs.each(function() {
			var color = $(this).data('backgroundColorBeforeHighlight') || '#fff';
			$(this).removeClass('highlighted')
					.animate({'backgroundColor':color}, 'normal');
		});
	}
});

})(jQuery);