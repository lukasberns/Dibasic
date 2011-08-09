/****

DIReorder:
Reorder items
Remember to set the dataRenderer’s order to this column (ASC)

****/



(function($) {

Class("DIReorder", DI, {
	_formName: null,
	_DISelect: null,
	
	init: function($super, def) {
		$super(def);
		
		this._DISelect = new DISelect({
			name: def.name+'_DISelect',
			title: 'Insert at',
			options: {
				MIN: 'Beginnning',
				MAX: 'End'
			},
			'default': 'MAX'
		});
	},
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		this._formName = formName;
		if (formName == 'DPAddForm') {
			return $('<div/>').append(this._DISelect.widget(formName)).css({borderTop:'1px solid #ccc', paddingTop:'0.5em'});
		}
		return false;
	},
	
	val: function(value) {
		if (typeof value != 'undefined') {
			this._value = value;
			return this;
		}
		if (this._formName == 'DPAddForm') {
			return this._DISelect.val();
		}
		return this._value;
	},
	
	render: function(value, id, allData) {
		var self = this;
		var up = $('<input/>', { type: 'button', value: '▲' }).click(function() { self.move(id, value, -1); });
		var down = $('<input/>', { type: 'button', value: '▼' }).click(function() { self.move(id, value, 1); });
		var wrapper = $('<div class="segmented"/>');
		
		var foundSelected = before = after = false;
		for (var i in allData) {
			if (i == id) { foundSelected = true; }
			else if (!foundSelected) { before = true; }
			else if (foundSelected) { after = true; }
		}
		
		/* Some code to figure out whether the top and bottom arrows should be shown or not */
		if (before || Dibasic.dataRenderer.page > 1) {
			wrapper.append(up);
		}
		
		if (after) {
			wrapper.append(down);
		}
		else {
			Dibasic.dataRenderer.getTotalCount(function(count) {
				if (count > Dibasic.dataRenderer.perpage*Dibasic.dataRenderer.page) {
					wrapper.append(down);
				}
			});
		}
		
		if (Dibasic.dataRenderer.className == 'DPDataTable') {
			wrapper.css({opacity: 0.5});
		}
		return wrapper.css({fontFamily:'Arial'});
	},
	
	move: function(id, oldvalue, amount) {
		$.fancybox.showActivity();
		
		var name = this.definition.name;
		$.get(Dibasic.url({action:name}), { id: id, oldvalue: oldvalue, move: amount }, function(exchangeWith) {
			var update = {};
			update[id] = {};
			update[id][name] = exchangeWith[name];
			
			update[exchangeWith[Dibasic.key]] = {};
			update[exchangeWith[Dibasic.key]][name] = oldvalue;
			Dibasic.DPDBInterface.update(update, function(data) {
				Dibasic.dataRenderer.dehighlight(exchangeWith[Dibasic.key]);
				$.fancybox.hideActivity();
			});
		}, 'json');
	}
});

})(jQuery);

/*
var draggedRowPosition = 0;
$("DBSTTable tbody").tableDnD({
	onDragStyle: "background: ' . $this->owner->highlight_color_edit . '",
	dragHandle: "DBSIOrderHandle",
	onDragStart: function(table, row) {
		draggedRowPosition = $("DBSTTable tbody tr").index(row);
	},
	onDragMove: DBSTTableZebra,
	onDrop: function(table, row) {
		var vars = "DBSAction=DBSIOrder";
		vars += "&move_id=" + row.attr("class").replace(/^[*]*DBSTDataForId([0-9]+)[*]*$/, "$1");
		vars += "&move_amount=" + ($("DBSTTable tbody tr").index(row) - draggedRowPosition);
		backgroundProcess("' . $this->owner->url . '" + vars);
	},
})

*/