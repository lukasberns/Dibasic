/****

DITimestamp:
Always up to date

****/



(function($) {

Class("DITimestamp", DI, {
	widget: function() { return false; },
	val: function(value) { return new Date().getTime(); } /* We need to return some always changing value, otherwise DPDBInterface doesn’t call processData on the DITimestamp php class */
});

})(jQuery);