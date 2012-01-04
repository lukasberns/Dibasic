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

var fancyboxCenter = $.fancybox.center;
$.fancybox.center = function() {
	/* if its iphone or ipod disregard alignment and dont center */
	if ((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
	    return ; 
	}
	
	fancyboxCenter.apply(this, arguments);
};

$(function() {
	Dibasic.start();
	$(':button').live('click', function() {
		// firefox fix
		$(this).blur();
	});
});

