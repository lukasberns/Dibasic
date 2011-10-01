/****

DPAlterForm:
Form to alter the table

****/



(function($) {

Class("DPAlterForm", DP, {
	widget: function() {
		if (!Dibasic.hasPermission('alter')) {
			return null;
		}
		
		var self = this;
		return $('<input type="button" value="Alter" />').click(function() {
			$.fancybox(self.initForm(), {
				hideOnContentClick: false,
				overlayShow: true
			});
		});
	},
	
	initForm: function() {
		var form = $('<form class="DPForm" method="post" />');
	
		var self = this;
		form.submit(function() {
			self.submit();
			return false;
		});
		var ul = $('<ul></ul>').appendTo(form);
		var mods = Dibasic.tableModifications;
	
		// table name
		var li = $('<li></li>').appendTo(ul);
		$('<span>Table name:</span>').addClass('input-title').appendTo(li);
		$('<span>'+Dibasic.tableName+'</span>').addClass('input').appendTo(li);
	
		// add's, modify's, remove's
		for (var action in mods) {
			var m = mods[action];
			var actionTitle = action[0].toUpperCase() + action.substr(1);
			var firstRun = true;
		
			for (var i in m) {
				if (firstRun) {
					li = $('<li></li>').appendTo(ul);
					$('<span>'+actionTitle+':</span>').addClass('input-title').appendTo(li);
					var colsUl = $('<ul></ul>').addClass('input').appendTo(li);
					firstRun = false;
				}
				var name = m[i];
				var col = Dibasic.columnWithName(name);
				var dataType = col ? col.dataType.toUpperCase() : '';
				colsUl.append('<li>'+name+' '+dataType+'</li>');
			}
		}
	
		$('<li></li>').append('<input type="Submit" value="Alter" class="button" />').appendTo(ul);
		
		return form;
	},
	
	submit: function() {
		$.get(Dibasic.url({action:'DPAlterForm'}), function(data, textStatus) {
			if (data != 1) {
				// error
				console.log(data);
				return;
			}
			$.fancybox.close();
			$.fancybox.showActivity();
			location.reload();
		});
	}
});

})(jQuery);