var fancyOpts = {
	hideOnContentClick: false
/*	callbackOnShow: function(opts) {
		if (opts.orig.hasClass('reopenButton')) {
			setValues($('#fancy_ajax'), fancyCache.data);
		}
		
		$('.reopenButton').fadeOut('normal', function() {$(this).remove()});
	},
	callbackBeforeClose: function(opts) {
		var values = {};
		getValues($('#fancy_ajax'), values);
		fancyCache.data = values;
		
		$('<a>Reopen</a>').attr('href', opts.href).addClass('reopenButton').addClass('button').css('display', 'none').appendTo('body').fancybox(fancyOpts).fadeIn('normal');
	}*/
};


var fancyCache = {
	url: null,
	data: {}
};

$(function() {
	Dibasic.start();
	$(':button').live('click', function() {
		// firefox fix
		$(this).blur();
	});
});

