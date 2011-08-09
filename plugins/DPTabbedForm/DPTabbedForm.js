/****

DPTabbedForm:
Split the form into tabbed sections

****/



(function($) {

Class("DPTabbedForm", DP, {
	_wrapper: null,
	_nav: null,
	
	init: function($super, def) {
		$super(def);
		var self = this;
		Dibasic.DPAddForm.bind('didInitForm.DPUpdateForm', function(e, form) { self.processForm(form); });
		Dibasic.DPAddForm.bind('formIsInvalid.DPUpdateForm', function(e, form) { self.markErrors(form); });
	},
	
	processForm: function(form) {
		var wrapper = $('<div/>').prependTo(form);
		var nav = $('<ul/>').prependTo(wrapper);
		
		this._wrapper = wrapper;
		this._nav = nav;
		
		var all = wrapper.add(nav).add(form).css('visibility', 'hidden'); // reduce the flickering
		
		var lis = form.children('ul').eq(0).children(':not(:last-child)');
		
		var i = 0, j = 0, tab, tabUl, l, tabId;
		while (i < this.definition.tabs.length) {
			tabId = 'DPTabbedForm-tab-'+i;
			$('<a/>', { href:'#'+tabId })
				.text(this.definition.tabs[i].name)
				.appendTo($('<li/>').appendTo(nav));
			tab = $('<div/>', { id: tabId }).appendTo(wrapper);
			tabUl = $('<ul/>').appendTo(tab);
			l = this.definition.tabs[i+1] ? this.definition.tabs[i+1].startAt : lis.length;
			while (j < l) {
				lis.eq(j).appendTo(tabUl);
				j++;
			}
			
			i++;
		}
		
		setTimeout(function() {
			wrapper.tabs({
				show: function(e, ui) {
					$.fancybox.resize();
/*					setTimeout(function() {
						$(':input:nth-child(2)',ui.panel).focus();
						$(':input:first',ui.panel).focus();
					}, 1000);
*/
				}
			});
			all.css('visibility', 'visible');
		}, 0);
	},
	
	markErrors: function(form) {
		var wrapper = this._wrapper
		var nav = this._nav;
		
		setTimeout(function() {
			var firstTabWithErrors = -1;
			wrapper.children(':not(:first-child)').each(function(i) {
				var el = nav.children().eq(i);
				if ($(this).find('.error').filter(function(){return $(this).css('display')!='none';}).length) {
					el.addClass('DPTabbedFormErrorMarkedTab');
					if (el.is('.ui-tabs-selected')) {
						firstTabWithErrors = -2;
					}
					else if (firstTabWithErrors == -1) {
						firstTabWithErrors = i;
					}
				}
				else {
					el.removeClass('DPTabbedFormErrorMarkedTab');
				}
			});
			if (firstTabWithErrors >= 0) {
				// switch to the tab with the error
				wrapper.tabs('select', firstTabWithErrors);
			}
		}, 0);
	}
});

})(jQuery);