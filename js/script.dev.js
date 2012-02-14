
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

	// toggle target elements when an input value change to one of the predefined values
	var toggleInput = function (input, targets, values) {
		targets = targets.join(",");

		if ( values.indexOf( input.val() ) >= 0 )
			$(targets).removeClass("hide-if-js");
		else
			$(targets).addClass("hide-if-js");
	}

	$("#loop_orderby").change(function () {
		toggleInput($(this), [".tl_meta_key"], ["meta_value", "meta_value_num"]);
	});

	$("#loop_pagination").change(function () {
		toggleInput($(this), [".tl_offset", ".tl_paged"], ["none"]);
	});
});
