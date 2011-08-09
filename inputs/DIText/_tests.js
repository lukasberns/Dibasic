module('DIText');

test('Class', function() {
	ok(DIText, 'Class exists');
	ok(new DIText() instanceof DI, 'new DIText() is instance of DI');
});

test('widget()', function() {
	var defaultValue = 'defaultValue';
	var di = new DIText({ 'default': defaultValue });
	var widget = di.widget();
	ok(di._el.jquery, '_el gets set to a jquery object');
	ok(di._el.is('input'), '_el gets set to an $(<input/>)');
	equal(di._el.val(), defaultValue, 'default value gets set');
	ok(di._el.has(di._el), 'returns jquery object that contains _el');
});

test('val()', function() {
	var di = new DIText();
	var testValue = 'testValue';
	
	equal(di.val(), undefined, 'val() prior to a widget() call');
	di.val(testValue);
	equal(di.val(), undefined, 'val("testValue") prior to a widget() call doesn’t change the value');
	
	ok(di.val(testValue) == di, 'val(someValue) returns the DI object');
	
	di.widget();
	equal(di.val(), '', 'intial val() after widget()');
	
	di.val(testValue);
	equal(di._el.val(), testValue, 'gets set properly');
	equal(di.val(), testValue, 'getting works as well');
});