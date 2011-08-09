/****

DPCreateForm:
Form to create the table

****/



(function($) {

Class("DPCreateForm", DP, {
	widget: function() {
		var self = this;
		return $('<input type="button" value="Create" />').click(function() {
			$.fancybox(self.initForm(), {
				hideOnContentClick: false,
				enableKeyboardNavigation: false,
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
		var cols = Dibasic.columns;
		
		// table name
		var li = $('<li></li>').appendTo(ul);
		$('<span>Table name:</span>').addClass('input-title').appendTo(li);
		$('<span>'+Dibasic.tableName+'</span>').addClass('input').appendTo(li);
		
		// key
		this._keyInput = new DIText({name:'key', title:'Key:', 'default':'id'});
		this._keyInput.widget().appendTo($('<li></li>').appendTo(ul));
		
		// columns
		li = $('<li></li>').appendTo(ul);
		$('<span>Columns:</span>').addClass('input-title').appendTo(li);
		var colsUl = $('<ul></ul>').addClass('input').appendTo(li);
		for (var i in cols) {
			colsUl.append('<li>'+cols[i].name+' '+cols[i].dataType.toUpperCase()+'</li>');
		}
		
		$('<li></li>').append('<input type="Submit" value="Create" class="button" />').appendTo(ul);
		
		form.find('input:first').focus();
		
		return form;
	},
	
	submit: function() {
		$.post(Dibasic.url({action:'DPCreateForm'}), {key:this._keyInput.val()}, function(data, textStatus) {
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