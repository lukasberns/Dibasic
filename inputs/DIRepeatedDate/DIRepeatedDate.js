/****

DIRepeatedDate:
E.g. every 1st and 3rd Saturday

****/

(function($) {

var suffix = ['st','nd','rd','th','th'];
var weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

Class("DIRepeatedDate", DI, {
	
	init: function($super, def) {
		$super(def);
		this.initCalendar();
	},
	
	_calendar: null,
	_el: null,
	
	initCalendar: function() {
		this._calendar = $('<table/>').addClass('DIRepeatedDateCalendar input-spaced');
		this._el = [];
		
		var headerRow = $('<tr/>').addClass('headerRow').appendTo(this._calendar);
		$('<td/>').text('').appendTo(headerRow);
		$('<th/>').text('Su').appendTo(headerRow);
		$('<th/>').text('Mo').appendTo(headerRow);
		$('<th/>').text('Tu').appendTo(headerRow);
		$('<th/>').text('We').appendTo(headerRow);
		$('<th/>').text('Th').appendTo(headerRow);
		$('<th/>').text('Fr').appendTo(headerRow);
		$('<th/>').text('Sa').appendTo(headerRow);
		
		for (var week = 1; week <= 5; week++) {
			var row = $('<tr/>').appendTo(this._calendar);
			$('<th/>').text(week+suffix[week-1]).appendTo(row);
			for (var weekday = 0; weekday < 7; weekday++) {
				var td = $('<td/>').appendTo(row);
				this._el.push($('<input/>', { type: 'checkbox' }).appendTo(td));
			}
		}
	},
	
	_currentInEl: null,
	
	widget: function(formName) {
		// the widget (a jQuery object) displayed in forms
		var self = this;
		var name = this.definition.name;
		var id = '__DI__' + name;
		this.setDefault();
		
		var label = $('<label />', {
			'for': id,
			'text': this.definition.title
		}).addClass('input-title');
		
		if (formName != 'DPUpdateForm') {
			this.val(0);
			this._calendar.show();
			return label.add(this._calendar);
		}
		
		this._calendar.hide();
		
		var div = $('<div/>').addClass('input');
		this._currentInEl = $('<span/>').appendTo(div);
		var show = $('<input/>', { type: 'button', value: 'Change' }).click(function() {
			div.remove();
			self._calendar.show();
			$.fancybox.resize();
		}).css({fontSize: '0.8em', marginLeft: '0.5em'}).appendTo(div);
		
		return label.add(this._calendar).add(div);
	},

	val: function($super, value) {
		// get or set the value
		$super(value);
		
		if (typeof value != 'undefined') {
			var bits = DIRepeatedDate.decode(value).split('');
			for (var i in bits) {
				this._el[i].attr('checked', bits[i] == '1');
			}
			if (this._currentInEl) {
				this._currentInEl.text(this.render(value));
			}
			return this;
		}
		var val = '';
		$(this._el).each(function() {
			val += this.attr('checked') ? '1' : '0';
		});
		return DIRepeatedDate.encode(val);
	},
	
	render: function(data) {
		if (data == 0) {
			return 'Never';
		}
		var bits = DIRepeatedDate.decode(data).split('');
		var week = 0;
		var perWeekday = {};
		var txt = [];
		for (var i in bits) {
			if (i % 7 == 0) { week++; }
			if (bits[i] != '1') { continue; }
			var weekday = weekdays[i%7];
			if (!perWeekday[weekday]) {
				perWeekday[weekday] = {};
			}
			perWeekday[weekday][week] = true;
		}
		for (weekday in perWeekday) {
			var t = [];
			for (week in perWeekday[weekday]) {
				t.push(week+suffix[week-1]);
			}
			txt.push(t.join(', ')+' '+weekday);
		}
		
		return txt.join(', ');
	}
});

DIRepeatedDate.encode = function(val) {
	// '00000010000000000000100000000000000' -> 268451840
	return parseInt(val,2);
};

DIRepeatedDate.decode = function(val) {
	// 268451840 -> '00000010000000000000100000000000000'
	var bits = (val-0).toString(2);
	while (bits.length < 35) {
		bits = '0'+bits;
	}
	return bits;
};

})(jQuery);