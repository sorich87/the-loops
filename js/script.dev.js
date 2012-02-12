
jQuery(function($) {
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

	$("#tl-add-taxonomy-parameter").click(function(e) {
		e.preventDefault();

		var tax_count, tax_table;

		tax_count = $(".tl-taxonomy-parameter").length - 1;

		tax_table = $(".tl-taxonomy-parameter-template").clone()
			.removeClass("tl-taxonomy-parameter-template hidden")
			.wrap("<div>").parent().html()
			.replace(/{key}/gi, tax_count);

		$(this).before(tax_table);
	});

	$("#tl_taxonomydiv").on("click", ".tl-delete-taxonomy", function(e) {
		e.preventDefault();

		$(this).parents(".tl-taxonomy-parameter").remove();
	});
});
