/****

DPDataTemplate:
Displays entries in the database according to a template
Other DPData... classes should inherit from this one

****/



(function($) {

Class("DPDataTemplate", DP, {
	page: 1, // pages start with 1
	perpage: 10,
	pagerPlusMinus: 4,
	filterOption: 0,
	sortOption: 0,
	searchQuery: '',
	
	init: function($super, def) {
		$super(def);
		
		if (def.perpage) {
			this.perpage = def.perpage;
		}
		if (def.pagerPlusMinus) {
			this.pagerPlusMinus = def.pagerPlusMinus;
		}
		
		var self = this;
		
		Dibasic.DPDBInterface.observe(function(data) {
			self.displayData(function() {
				for (var id in data) {
					//self.highlight(id, true);
				}
			});
		});
		
		$(window).bind('hashchange', function() {
			var page = $.bbq.getState(self.className) || 1;
			self.showPage(page);
			
			var sortBy = $.bbq.getState(self.className+'_sortBy') || 0;
			self.sortBy(sortBy);
			
			var filterBy = $.bbq.getState(self.className+'_filterBy') || 0;
			self.filterBy(filterBy);
			
			var search = $.bbq.getState(self.className+'_searchFor') || 0;
			self.searchFor(search);
		});
	},
	
	_ids: [],
	
	widget: function() {
		this.container = $('<div id="'+this.className+'"/>');
		this.getData();
		return this.container;
	},
	
	sortWidget: function() {
		//this.sorter = 
		var sortOptions = this.definition.sortOptions || [];
		if (sortOptions.length < 2) {
			return '';
		}
		
		var self = this;
		
		this.sorter = $('<select/>', { id: this.className+'_sorter' })
			.change(function() {
				var state = {};
				state[self.className+'_sortBy'] = this.value;
				$.bbq.pushState(state);
			});
		
		for (var i in sortOptions) {
			var option = $('<option/>')
							.val(i)
							.text(sortOptions[i])
							.appendTo(this.sorter);
			if (!i) {
				option.attr('selected', 'selected');
			}
		}
		
		var label = $('<label/>', { 'for': this.className+'_sorter' }).text('Sort by: ');
		
		return label.add(this.sorter);
	},
	
	sortBy: function(sortBy) {
		if (!this.sorter || this.sortOption == sortBy) { return; }
		
		if (this.sorter.val() != sortBy) {
			this.sorter.val(sortBy);
		}
		
		this.sortOption = sortBy;
		this.displayData();
	},
	
	filterWidget: function() {
		//this.filter = 
		var filterOptions = this.definition.filterOptions || [];
		if (filterOptions.length < 2) {
			return '';
		}
		
		var self = this;
		
		this.filter = $('<select/>', { id: this.className+'_filter' })
			.change(function() {
				var state = {};
				state[self.className+'_filterBy'] = this.value;
				$.bbq.pushState(state);
			});
		
		for (var i in filterOptions) {
			var option = $('<option/>')
							.val(i)
							.text(filterOptions[i])
							.appendTo(this.filter);
			if (!i) {
				option.attr('selected', 'selected');
			}
		}
		
		var label = $('<label/>', { 'for': this.className+'_filter' }).text('Filter by: ');
		
		return label.add(this.filter);
	},
	
	filterBy: function(filterBy) {
		if (!this.filter || this.filterOption == filterBy) { return; }
		
		if (this.filter.val() != filterBy) {
			this.filter.val(filterBy);
		}
		
		this.filterOption = filterBy;
		this._totalCountFetchedTime = 0; // total count might have changed
		this.displayData();
	},
	
	searchWidget: function() {
		//this.search = 
		var self = this;
		
		var form = $('<form/>')
			.css({
				'float': 'right'
			})
			.submit(function() {
				var state = {};
				state[self.className+'_searchFor'] = self.searchBox.val();
				state[self.className] = 1; // switch back to page 1
				$.bbq.pushState(state);
				return false;
			});
		
		this.searchBox = $('<input/>', { type: 'search' }).appendTo(form);
		$('<input/>', { 'type': 'submit' }).val('Search').css({
			fontSize: '0.8em', 
			margin: '0 1ex'
		}).appendTo(form);
		
		return form;
	},
	
	searchFor: function(search) {
		if (!this.searchBox || this.searchQuery == search) { return; }
		
		if (this.searchBox.val() != search) {
			this.searchBox.val(search);
		}
		
		this.searchQuery = search;
		this._totalCountFetchedTime = 0; // total count might have changed
		this.displayData();
	},
	
	getData: function(callback) {
		// callback will be executed after data is received
		// function(data) { ... } where *data* is the received data
		var self = this;
		this.getTotalCount(function(totalCount) {
			while (totalCount && totalCount == (self.page - 1) * self.perpage) {
				// when the only event on the last page gets deleted, we need to move back one page
				self.page--;
			}
			
			$.get(Dibasic.url({ action:self.className }), {
					getData:true,
					dataPage:self.page,
					perpage:self.perpage,
					sortBy:self.sortOption,
					filterBy:self.filterOption,
					search:self.searchQuery
				}, function(ids, textStatus) {
					self._ids = JSON.parse(ids);
					Dibasic.DPDBInterface.getData(self._ids, function(data) {
						if ($.isFunction(callback)) {
							callback(data);
						}
					});
				});
		});
	},
	
	displayData: function(callback) {
		// callback is optional, called after table is set
		var self = this;
		this.getData(function(data) {
			// display the data according to the template
			
			var dataTmp = window.data;
			var rowTmp = window.row;
			var _rowTmp = window._row;
			
			self.container.html('');
			window.data = data;
			
			var _nn = '';
			var repeaterI = 0;
			var repeats = [];
			
			var tail = self.definition.template.replace(/\{pager\}(?!\})/, function() {
				return '<span class="DPDataTemplate-displayData-pager-placeholder"></span>';
			}).replace(/([\s\S]*?)\[\[([\s\S]*?)\]\]/g, function(match, normal, repeat) {
				
				_nn += normal+'<div id="DPDataTemplate-displayData-repeater-'+repeaterI+'"></div>';
				repeats[repeaterI] = repeat;
				repeaterI++;
				return '';
			});
			
			process(_nn+tail, self.container);
			
			var insert;
			for (repeaterI = 0; repeat = repeats[repeaterI]; repeaterI++) {
				var temp = $('<div/>').appendTo('body');
				for (var rowI in data) {
					window._row = data[rowI]; // raw data is accessible via _row.columnName
					window.row = {};
					var id = window._row[Dibasic.key];
					var repeater = '<div id="DPDataTemplate-displayData-rowData-'+id+'">' + repeat + '</div>';
					for (var i in window._row) {
						window.row['_'+i] = Dibasic.columnWithName(i); // DI-object alias row._columnName
						// data rendered according to corresponding DI-object (row.columnName)
						window.row[i] = window.row['_'+i] ? window.row['_'+i].DI.render(window._row[i], id, data) : window._row[i];
					}
					process(repeater, temp);
				}
				$('#DPDataTemplate-displayData-repeater-'+repeaterI).replaceWith(temp.children());
				temp.remove();
			}
			
			self.makePager(function (pager) {
				$('.DPDataTemplate-displayData-pager-placeholder').replaceWith(pager);
			});
			
			window.data = dataTmp;
			window.row = rowTmp;
			window._row = _rowTmp;
			
			if ($.isFunction(callback)) {
				callback(data);
			}
		});
	},
	
	_totalCount: 0,
	_totalCountFetchedTime: 0,
	
	getTotalCount: function(callback) {
		// callback will be executed after data is received
		// function(totalCount) { ... } where *data* is the totalCount
		var self = this;
		if (!this._totalCountFetchedTime || this._totalCountFetchedTime < Dibasic.DPDBInterface.lastEdit) {
			// fetch the total count on first access and every time the totalCount might have changed
			$.get(Dibasic.url({action:this.className}), {
					getTotalCount:true,
					filterBy:self.filterOption,
					search:self.searchQuery
				}, function(totalCount, textStatus) {
				self._totalCount = JSON.parse(totalCount);
				self._totalCountFetchedTime = new Date().getTime();
				if ($.isFunction(callback)) {
					callback(self._totalCount);
				}
			});
		}
		else {
			// otherwise just call the callback since doing unneccessary http request is expensive
			if ($.isFunction(callback)) {
				callback(self._totalCount);
			}
		}
	},
	
	_makePagerLink: function(page, text) {
		var self = this;
		var link = $('<a/>',{
				href: '#.page'+page,
				text: text||page
			})
			.click(function() {
				var state = {};
				state[self.className] = page;
				$.bbq.pushState(state);
				return false;
			});
		if (page == this.page) {
			link.addClass('selected');
		}
		return link;
	},
	
	makePager: function(callback) {
		// callback will be called with the pager as argument when created
		
		var self = this;
		
		this.getTotalCount(function(totalCount) {
			var pager = $('<div/>').addClass('DPDataTemplatePager');
			
			if (!totalCount) {
				pager.text('No entries found');
			}
			
			if (totalCount <= self.perpage) {
				// if there’d be only one page, return an empty pager as it doesn’t make sense to show one page
				callback(pager);
				return;
			}
			
			var p; // p is the page iterator, ie in this case the starter for the iteration
			var current = self.page, perpage = self.perpage, plusMinus = self.pagerPlusMinus;
			
			var farLeft = 1;											// «
			var lowerLeft = current - plusMinus;						// e.g. -5
			
			var upperRight = current + plusMinus;					// e.g. +5
			var farRight = Math.ceil(totalCount / perpage);				// »
			
			var maxLinks = Math.min((2 * plusMinus) + 3, farRight);
			var createdLinks = 0;

			if (farRight <= maxLinks) {
				// we want to create as many links as possible, so
				// make 1 2 3 4 (5)
				// even when plusMinus = 1
				
				p = farLeft;
			}
			else {
				if (lowerLeft > farLeft + 1) {
					// 1 ... 3 (4) 5
					p = lowerLeft;
					pager.append(self._makePagerLink(farLeft));
					pager.append($('<span>&hellip;</span>'));
					createdLinks++;
				}
				else {
					// 1 2 (3) 4 ...
					p = farLeft;
				}

				// to make
				// 1 ... 4 5 (6)
				// even when plusMinus = 1
				p = Math.min(p, farRight - 2*plusMinus - 1);

				if (upperRight < farRight) {
					// if we need to create the farRight-link, reserve this link count
					createdLinks++;
				}
			}

			while (createdLinks < maxLinks) {
				// create all links
				pager.append(self._makePagerLink(p));
				createdLinks++;
				p++;
			}

			if (farRight > p-1) {
				if (farRight > p) { // this prevents "4 5 6 ... 7"
					pager.append($('<span>&hellip;</span>'));
				}
				pager.append(self._makePagerLink(farRight));
			}
			
			callback(pager);
		});
	},
	
	showPage: function(page) {
		if (this.page == page) {
			return;
		}
		
		this.page = page;
		this.displayData();
	},
	
	highlight: function(id, momentaneous) {
		// highlights entry with id
		
		//	var color = momentaneous ? '#ffc' : '#cef';
		color = '#ffc';
		
		$('#DPDataTemplate-displayData-rowData-'+id)
			.each(function() {
				$(this).data('backgroundColorBeforeHighlight', $(this).css('backgroundColor'));
			})
			.addClass('highlighted')
			.animate({'backgroundColor':color}, 'normal');
	
		if (momentaneous) {
			var self = this;
			setTimeout((function(id) {return function() {
				self.dehighlight(id);
			};})(id), 1000);
		}
	},
	
	dehighlight: function(id) {
		var els;
		if (id != undefined) {
			els = $('#DPDataTemplate-displayData-rowData-'+id);
		}
		else {
			els = $('[id^=DPDataTemplate-displayData-rowData-]');
		}
		els.each(function() {
			var color = $(this).data('backgroundColorBeforeHighlight') || '#fff';
			$(this).removeClass('highlighted')
					.animate({'backgroundColor': color}, 'normal');
		});
	}
});

})(jQuery);