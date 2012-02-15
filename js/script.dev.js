
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

	// tags input
	var customTagsInput = function() {
		$(this).tagsInput({
			height           : "5em",
			width            : "24em",
			defaultText      : tlLoops.addAValue,
			delimiter        : "\t"
		})
	};

	$(".tl-parameter").not(".hide-if-js").find(".tl-tagsinput").each(customTagsInput);

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
		tax_table.find('.tl-tagsinput').each(customTagsInput);
	});

	// parameter table deletion
	$(".inside").on("click", ".tl-delete", function(e) {
		e.preventDefault();

		$(this).parents(".tl-parameter").remove();
	});

	// toggle target elements when an input value change to one of the predefined values
	// if no condition is specified, just check if the input value is not empty
	var toggleInput = function (input, conditions, targetsForTrue, targetsForFalse) {
		var match = false
			, value = input.val()
			, valueIsArray = $.isArray(value);

		if ( valueIsArray ) {
			$.each(value, function (i, val) {
				if ( $.inArray(val, conditions) > -1 ) {
					match = true;
					return;
				}
			});
		} else if ( ! conditions ) {
			value = $.trim(value);
			match = value.length > 0;
		} else {
			match = $.inArray(value, conditions) > -1;
		}

		targetsForTrue  = targetsForTrue ? targetsForTrue.join(",") : null;
		targetsForFalse = targetsForFalse ? targetsForFalse.join(",") : null;

		if ( match ) {
			$(targetsForTrue).show('slow');
			$(targetsForFalse).hide('slow');
		} else {
			$(targetsForTrue).hide('slow');
			$(targetsForFalse).show('slow');
		}
	}

	$("#loop_orderby").change(function () {
		toggleInput($(this), ["meta_value", "meta_value_num"], [".tl_meta_key"]);
	});

	$("#loop_pagination").change(function () {
		toggleInput($(this), ["none"], [".tl_offset", ".tl_paged"]);
	});

	$("#loop_post_status").change(function () {
		toggleInput($(this), ["private"], [".tl_readable"]);
	});

	$("#loop_post_type").change(function () {
		toggleInput($(this), ["attachment"], [".tl_post_mime_type"]);
	});

	$("#loop_s").keyup(function () {
		toggleInput($(this), null, [".tl_exact", ".tl_sentence"]);
	});

	$("#loop_date_type").change(function () {
		if ( $(this).val() === "dynamic" ) {
			toggleInput($(this), ["dynamic"], [".tl_days"], [".tl_date", ".tl_period"]);
		} else if ( $(this).val() === "period" ) {
			toggleInput($(this), ["period"], [".tl_period"], [".tl_date", ".tl_days"]);
		} else if ( $(this).val() === "static" ) {
			toggleInput($(this), ["static"], [".tl_date"], [".tl_days", ".tl_period"]);
		}
	});
});
