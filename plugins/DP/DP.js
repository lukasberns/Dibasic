/****

DP:
The basic class all DP... inherit from.

****/



(function($) {

Class("DP", {
	init: function(definition) {
		/**
		@definition:
			an object in this style: {DPName:*, ... (other options)}
		**/
	
		this.definition = definition;
	},
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		return null;
	},
	
	trigger: function(name /* [, data1, data2, ...] */) {
		// fire an event
		$(document).trigger(name+'.'+this.definition.DPName+'.Dibasic', Array.prototype.slice.call(arguments, 1));
	},
	
	bind: function(name, callback) {
		if (callback == undefined) {
			callback = name;
			name = '';
		}
		$(document).bind(name+'.'+this.definition.DPName+'.Dibasic', callback);
	}
});

})(jQuery);