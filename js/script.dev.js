
jQuery(function($) {
	// date range picker
	var dates = $("#loop-min-date, #loop-max-date").datepicker({
		changeMonth: true,
		onSelect: function( selectedDate ) {
			var option = this.id == "loop-min-date" ? "minDate" : "maxDate",
				instance = $( this ).data("datepicker"),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings);
			dates.not(this).datepicker("option", option, date);
		}
	});

	// parameter table addition
	$(".tl-add-parameter").click(function(e) {
		e.preventDefault();

		var parent, tax_count, tax_table;

		parent = $(this).parent();

		tax_count = parent.siblings().length - 1;

		tax_table = parent.next().clone()
			.removeClass("hide-if-js")
			.wrap("<div>").parent().html()
			.replace(/{key}/gi, tax_count);

		tax_table = $(tax_table);
		tax_table.insertBefore($(this));

		// tags input
		tax_table.find('.tl-tagsinput').tagsInput({
			height           : "5em",
			width            : "24em",
			defaultText      : "add a value",
			delimiter        : "\t"
		});
	});

	// parameter table deletion
	$(".inside").on("click", ".tl-delete", function(e) {
		e.preventDefault();

		$(this).parents(".tl-parameter").remove();
	});

	// meta key input field toggle
	$("#loop_orderby").change(function() {
		if ( $(this).val() === "meta_value" || $(this).val() === "meta_value_num" )
			$(".tl_meta_key").removeClass("hide-if-js");
		else
			$(".tl_meta_key").addClass("hide-if-js");
	});

	// offset input field toggle
	$("#loop_pagination").change(function() {
		if ( $(this).val() === "none" )
			$(".tl_offset").removeClass("hide-if-js");
		else
			$(".tl_offset").addClass("hide-if-js");
	});
});
