
jQuery(function() {
	
	var $ = jQuery;
	
	$("#error_log_table a.toggle_link").click(function(event) {
		event.preventDefault();
		var tr = $(this).parents("tr").next();
		if (tr.is(":visible")) {
			tr.hide();
		} else {
			tr.show();
		}
	});
});
