module('DI');

test('jquery', function() {
	ok(jQuery, 'jQuery is not null');
});

test('init()', function() {
	ok(DI, 'Class exists');
	ok(new DI() !== undefined, 'new DI() !== undefined');
	same(new DI().definition, {}, 'new DI() sets this.defintion to an empty object when called without argument');
	var def = {name:'value'};
	equal(new DI(def).definition, def, 'definition gets set properly');
});

test('widget()', function() {
	equal(new DI().widget(), null, 'should return null');
});

test('_elIsSet()', function() {
	var d = new DI();
	ok(!d._elIsSet(), 'returns false if widget() not called');
	
	d._el = null;
	ok(!d._elIsSet(), 'returns false if _el set to null');
	
	d._el = 1;
	ok(!d._elIsSet(), 'returns false if _el set to a static value (here 1)');
	
	d._el = {};
	ok(!d._elIsSet(), 'returns false if _el set to a non-jquery object (here {})');
	
	d._el = $('<div/>');
	ok(d._elIsSet(), 'returns true if _el set to some jquery object');
});

test('render()', function() {
	var data = 'stringData';
	equal(new DI().render(data), data, 'render(data) == data');
});

test('setDefault()', function() {
	var initialVal = 'intial';
	var defaultVal = 'default';
	var di1 = new DI();
	var di2 = new DI({ 'default': defaultVal });
	
	di1.val = di2.val = function(val) {
		this.value = val;
	};
	di1.val(initialVal);
	di2.val(initialVal);
	
	equal(di1.value, initialVal, 'The hack DI.val() function used in this test sets this.value properly.');
	
	di1.setDefault();
	equal(di1.value, initialVal, 'has to leave the value at the initial value if no default value set');
	
	di2.setDefault();
	equal(di2.value, defaultVal, 'should call val(defaultVal) if a default value is set');
});

test('DI._isValid() (class method)', function() {
	ok(DI._isValid('', { regexp: /^$/, matchMeansValid: true }), 'rule with matchMeansValid == true is valid if the regexp matches');
	ok(!DI._isValid('', { regexp: /^.$/, matchMeansValid: true }), 'rule with matchMeansValid == true is invalid if the regexp fails to match');
	ok(!DI._isValid('', { regexp: /^$/, matchMeansValid: false }), 'rule with matchMeansValid == false is invalid if the regexp matches');
	ok(DI._isValid('', { regexp: /^.$/, matchMeansValid: false }), 'rule with matchMeansValid == false is valid if the regexp fails to match');
});

test('DI._setupValidationMethod() (class method)', function() {
	ok(jQuery.validator, 'jQuery-validation plugin is loaded');
	
	var methodName = 'unitTestValidationDummyMethod';
	ok(jQuery.validator.methods[methodName] === undefined, 'dummy method-name used for testing is not set already');
	DI._setupValidationMethod({}, methodName);
	ok(jQuery.isFunction(jQuery.validator.methods[methodName]), 'adds an validation method');
	
	var rules = [
		{ regexp: /^$/, matchMeansValid: true },
		{ regexp: /^.$/, matchMeansValid: true },
		{ regexp: /^$/, matchMeansValid: false },
		{ regexp: /^.$/, matchMeansValid: false },
	];
	for (var i = 0, rule; rule = rules[i++];) {
		delete jQuery.validator.methods[methodName];
		DI._setupValidationMethod(rule, methodName);
		var validationMethod = jQuery.validator.methods[methodName];
		
		equal(validationMethod(''), DI._isValid('', rule), 'Method works the same as DI._isValid if matchMeansValid == '+rule.matchMeansValid+' and regexp '+(rule.regexp.test('')?'matches':'doesn’t match'));
	}
});

test('validationRules()', function() {
	var testString = 'testString';
	equal(new DI({ rules: testString }).validationRules(), testString, 'Just returns definition.rules if rules is a string');
	var testObject = { required: true, email: true };
	same(new DI({ rules: testObject }).validationRules(), testObject, 'Just returns definition.rules if rules is an object');
	
	var regexpRules = [ { regexp: /./, matchMeansValid: true }, { regexp: /_/, matchMeansValid: false } ];
	var standardRules = { required: true, email: true };
	var rules = $.merge($.merge([], regexpRules), [standardRules]);
	var validationRules = new DI({ rules: rules }).validationRules();
	
	ok(jQuery.isPlainObject(validationRules), 'Returns a plain object (i.e. $.isPlainObject) if rules is an array');
	var unaddedRules = (function() { var i = 0; for (var k in standardRules) i++; return i; })();
	var unaddedMethods = regexpRules.length;
	for (var i in validationRules) {
		if (i in standardRules) {
			unaddedRules--;
		}
		else if (jQuery.validator.methods[i]) {
			unaddedMethods--;
		}
	}
	ok(!unaddedRules, 'All standard rules are included (e.g. [...{ required: true }...])');
	ok(!unaddedMethods, 'All methods get set on $.validator (methods generated for [...{ regexp: /.../, matchMeansValid: (bool) }...])');
});


