/****

registerUndo:
Allows plugins etc. to register undo events.
Only remembers one event and can only undo (lol why?)
Like GMail's undo system

****/

(function($) {

window.registerUndo = function(text, callback) {
	var container = $('<div/>').css({
		position: 'fixed',
		top: 0,
		left: 0,
		width: '100%',
		textAlign: 'center'
	});
	
	var popup = $('<div/>').css({
		display: 'inline-block',
		background: '#ffc',
		padding: '3px 10px'
	});
	
	container.append(popup.text(text)).hide().appendTo('body').fadeIn().delay(5000).fadeOut().click(callback);
};

})(jQuery);