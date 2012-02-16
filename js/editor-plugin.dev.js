(function() {
	tinymce.create('tinymce.plugins.the_loops_selector', {
		createControl : function(n, cm) {
			if (n === 'the_loops_selector' ) {
				var mlb = cm.createListBox('theLoopsList', {
					title : tlLoopList.title,
					onselect : function(v) {
						tinyMCE.activeEditor.selection.setContent('[the-loop id="' + v + '"]');
					}
				});

				var loop;
				for ( i in tlLoopList.loops ) {
					loop = tlLoopList.loops[i];
					mlb.add(loop.name, loop.id);
				}

				return mlb;
			}

			return null;
		}
	});

	tinymce.PluginManager.add('the_loops_selector', tinymce.plugins.the_loops_selector);
})();
