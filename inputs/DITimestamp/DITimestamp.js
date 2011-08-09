/****

DITimestamp:
Always up to date

****/



(function($) {

Class("DITimestamp", DI, {
	widget: function() { return false; },
	val: function(value) { return 0; } /* We need to return some non-null value, otherwise DPDBInterface doesn’t call processData on the DITimestamp php class */
});

})(jQuery);