/****

DPManageUserPermissions

****/



(function($) {

Class("DPManageUserPermissions", DP, {
	init: function($super, def) {
		$super(def);
		var self = this;
		new DP({}).bind('didInitForm.DPAddForm.DPUpdateForm', function(e, form, id) { self.processForm(form, id); });
		new DP({}).bind('didSubmit.DPAddForm.DPUpdateForm', function(e, data) { self.setPermissions(data); });
		Dibasic.DPDeleteForm.bind('didSubmit', function(e, id) { self.userDeleted(id); });
	},
	
	_DIs: {},
	
	processForm: function(form, id) {
		var self = this;
		var isUpdate = typeof id !== 'undefined';
		
		var lis = form.children('ul').eq(0).children(':not(:last-child)');
		
		var container = $('<div />').css({margin: '8px 0', visibility:'hidden'});
		
		var i = 0;
		$('<li/>')
			.append($('<label class="input-title" />').text('Permissions'))
			.append(container)
			.insertBefore(
				lis.eq(this.definition.position)
			);
		
		var pages = this.definition.pageTitles;
		for (var pageId in pages) {
			var title = this.definition.pageTitles[pageId];
			var DI = new DICheckbox({
				name: '__DPManageUserPermissions__page'+pageId,
				title: title,
				'default': !isUpdate && this.definition.pageDefaultPermissions[pageId]
			});
			container.append($('<div/>').append(DI.widget()));
			DI.setDefault();
			this._DIs[pageId] = DI;
		}
		
		if (isUpdate) {
			// update; get current permissions and set them
			$.get(Dibasic.url({ action: this.className }), { id: id }, function(data) {
				for (var i in data) {
					self._DIs[data[i]] && self._DIs[data[i]].val(true);
				}
				container.css('visibility', 'visible');
			}, 'json');
		}
		else {
			container.css('visibility', 'visible');
		}
		
		$.fancybox.resize();
	},
	
	setPermissions: function(user) {
		var data = {};
		
		for (var id in this._DIs) {
			data[id] = this._DIs[id].val()
		}
		
		$.post(Dibasic.url({ action: this.className, id: user.id }), data, function() {
			Dibasic.DPNavigation.refresh();
		});
	},
	
	userDeleted: function(id) {
		$.post(Dibasic.url({ action: this.className, id: id })); // this deletes all permissions
	}
});

})(jQuery);