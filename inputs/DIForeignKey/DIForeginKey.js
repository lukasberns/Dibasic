/****

DIForeignKey:
Lets you pick a foreign key from another table using a <select>

****/



(function($) {

Class("DIForeignKey", DISelect, {
	init: function($super, defs) {
		$super(defs);
		
		if (defs.table == Dibasic.tableName) {
			// we will need to refresh the data on each change
			Dibasic.DPDBInterface.observe(function() {
				$.get(Dibasic.url({ action: defs.name }), function(data) {
					if (!data[0].match(/^[\[\{]/)) {
						// not json = error
						alert('Something went wrong while refreshing the data in DIForeignKey: '+data);
						return;
					}
					
					defs.options = JSON.parse(data);
				});
			});
		}
	}
});

})(jQuery);