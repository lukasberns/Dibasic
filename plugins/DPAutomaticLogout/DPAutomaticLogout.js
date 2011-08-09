/****

DPAutomaticLogout:
Warns the user about the session timeout
and goes to the logout page automatically

****/


(function($) {

Class("DPAutomaticLogout", DP, {
	_warningTimer: null,
	_logoutTimer: null,
	
	init: function() {
		this.initTimers();
	},
	
	initTimers: function() {
		var self = this;
		clearTimeout(this._warningTimer);
		clearTimeout(this._logoutTimer);
		
		this._warningTimer = setTimeout(function() {
			self.warn();
		}, 4.75*3600*1000); // after 4'45", 15 min before session timeout
		
		this._logoutTimer = setTimeout(function() {
			self.logout();
		}, 4.99*3600*1000); // after 4'59"50 (10 seconds before the cookie etc. times out)
	},
	
	warn: function() {
		var self = this;
		$.fancybox(this.createWarning(), {
			hideOnContentClick: false,
			overlayShow: true,
			enableKeyboardNavigation: false,
			padding: 0,
			onCleanup: function() { return self.continueLoggedIn(); }
		});
	},
	
	_container: null,
	
	createWarning: function() {
		if (this._container) {
			return this._container;
		}
		
		var self = this;
		this._container = $('<div/>', { id: this.className });
		$('<h1/>').text('This computer will logout soon.').appendTo(this._container);
		$('<p/>').text(
			'The computer will logout automatically two hours after your last action. '
			+'Press the buttons below to continue or to logout.'
			+'You can close this popup to continue as well.').appendTo(this._container);
		$('<input/>', { type: 'button', value: 'Continue' }).click(function() {
			$.fancybox.close();
		}).appendTo(this._container);
		$('<input/>', { type: 'button', value: 'Logout' }).click(function() {
			self.logout();
		}).appendTo(this._container);
		return this._container;
	},
	
	continueLoggedIn: function() {
		// continue by doing an ajax request to the browser
		var self = this;
		$.fancybox.showActivity();
		$.get(Dibasic.url({ action: this.className }), function() {
			self.initTimers();
			$.fancybox.hideActivity();
		});
	},
	
	logout: function() {
		location.href = Dibasic.baseUrl+'Dibasic/auth/logout.php';
	}
});

})(jQuery);