(function() {
	if(tinymce.majorVersion < 4){
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
	}else{
		tinymce.PluginManager.add('the_loops_selector', function(editor) {
		 
		  editor.addButton('the_loops_selector', function() {
		 
		    var loop;
		    var values = [];
		    for ( i in tlLoopList.loops ) {
		      loop = tlLoopList.loops[i];
		      values.push({
		        text: loop.name, 
		        value: loop.id
		      })
		    }

		    return {
		      type: 'listbox',
		      text: tlLoopList.title,
		      label: 'Select:',
		      fixedWidth: true,
		      onselect: function(e) {
		        editor.insertContent('[the-loop id="' + this.value() + '"]');
		      },
		      values: values,
		    };
		  });
		 
		});
	}
})();
