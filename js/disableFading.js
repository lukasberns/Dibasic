// Disables the fadeIn and fadeOut animations to improve performance

(function($) {

if ($) {
	$.fn._fadeIn = $.fn.fadeIn;
	$.fn.fadeIn = function(speed, callback) { return this._fadeIn(0, callback); };
	
	$.fn._fadeOut = $.fn.fadeOut;
	$.fn.fadeOut = function(speed, callback) { return this._fadeOut(0, callback); };
}

})(jQuery);