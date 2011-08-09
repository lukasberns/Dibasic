/****

DPNavigation:
Does the navigation of Dibasic

****/



(function($) {

Class("DPNavigation", DP, {
	container: null,
	
	init: function($super, def) {
		$super(def);
		
		this.container = $('<ul/>', {id:'DPNavigation'});
		this.controls = $('<li/>').append($('<a/>', { href: Dibasic.baseUrl+'Dibasic/auth/logout.php' }).text('Logout'));
		this.refresh();
	},
	
	widget: function() {
		return this.container;
	},
	
	refresh: function() {
		var self = this;
		$.get(Dibasic.url({action:'DPNavigation'}), function(data) {
			self.container.html('');
			for (var i in data) {
				if (isNaN(i)) {
					// group name
					var group = data[i];
					if (group.length == 1) {
						// don't display a drop down if there's only one item in it
						var item = group[0];
						item.title = i+' » '+item.title;
						self.makeLink(item).appendTo(
							$('<li/>').appendTo(self.container)
						);
						continue;
					}
					var groupEl = $('<ul/>').appendTo(
						$('<li/>')
							.append(
								$('<a href="#openMenu">'+i+' ▾</a>')
									.addClass('group-title')
									.click(function() {
											var wasClosed = $(this).next('ul').is(':hidden');
											self.container.find('ul').hide();
											if (wasClosed) {
												$(this).next('ul').show();
											};
											return false;
										})
							)
							.appendTo(self.container)
					);
					for (var j in group) {
						var a = self.makeLink(group[j]).appendTo(
							$('<li/>').appendTo(groupEl)
						);
						if (a.hasClass('selected')) {
							groupEl.parent().addClass('hasSelected');
						}
					}
				}
				else {
					// page
					self.makeLink(data[i]).appendTo(
						$('<li/>').appendTo(self.container)
					);
				}
			}
			self.container.append(self.controls);
		}, 'json');
	},
	
	makeLink: function(data) {
		var a = $('<a/>', {
				href: Dibasic.url({ page: data.id }),
				text: data.title
			});
		
		if (data.id == Dibasic.urlParams.page) {
			a.addClass('selected');
		}
		
		return a;
	}
});

})(jQuery);