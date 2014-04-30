=== Plugin Name ===
Contributors: sorich87
Tags: loop, loops, the loop, shortcode, widget, posts, pages, custom
post types, users
Requires at least: 3.3
Tested up to: 3.9
Stable tag: 1.0.1

Like Drupal Views but for WordPress, The Loops allows you to query the
database for content and display it in a page without having to write
php code.

== Description ==

The Loops plugin offers you great flexibility for displaying your site
content and users list. It provides a graphical user interface in your
WordPress site admin that allows you to query the database for content
without having to write any PHP code or SQL queries.

With this plugin, you can display the queried content in a page or post
(using shortcodes) or a widget.

[Check out my other free
plugins.](http://wordpress.org/extend/plugins/profile/sorich87)

= Features =

* Works with any theme without modification
* Works with any 'custom post types' plugin
* Display list of posts and users with all the possibilities offered by
WordPress
* Custom templates
* Display the loops anywhere with a shortcode or a widget

= Contributors =
[Contributors are listed
here](https://github.com/sorich87/the-loops/contributors)

= Notes =

For feature request and bug reports, [please use the
forums](http://wordpress.org/tags/the-loop?forum_id=10#postform).

If you are a plugin developer, [we would like to hear from
you](https://github.com/sorich87/the-loops). Any contribution would be
very welcome.

If you are a theme developer or designer, that's even better! [We would
like to hear from you](http://pubpoet.com/contact/) about how to make
the loops shine even more when displayed in your themes, or [any other
idea you have](http://pubpoet.com/contact/).

If you are a user and have a need that the plugin in its current state
doesn't cover, maybe you are willing to sponsorize the development of
some features... [then contact us!](http://pubpoet.com/contact/).

== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'The Loops'
1. Click 'Install Now' and activate the plugin
1. Go the 'Loops' menu under 'Appearance'


Or use a nifty tool by WordPress lead developer Mark Jaquith:

1. Visit [this
link](http://coveredwebservices.com/wp-plugin-install/?plugin=the-loops)
and follow the instructions.


For a manual installation via FTP:

1. Upload the `the-loops` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area
1. Go the 'Loops' menu under 'Appearance'


To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see
the 'Upload' tab) in your WordPress admin area and activate.
1. Go the 'Loops' menu under 'Appearance'

== Frequently Asked Questions ==

= How to use? =

Click on the 'Loops' link in the 'Appearance' menu, click 'Add New' to
add a new loop. Use the shortcode provided or go to the widgets screen
to add the loop to a sidebar.

= How to add custom templates? =

Copy a default template file from the directory 'tl-templates' in the
plugin directory to your theme directory. Then feel free to modify it to
your liking!

Copy a default template file from the directory 'tl-templates' in the
plugin directory to your theme directory. Then feel free to modify it to
your liking!

The template's header must contain the line:
	 * The Loops Template: List of excerpts

= How to add register custom templates directories ? =

Use a function hooked on filter "tl_templates_directories" eg.

	add_filter('tl_templates_directories',my_plugin_tl_templates_directory);

	function my_plugin_tl_templates_directory($directories){
		$directories[]=MYPLUGIN_ABSOLUTE_DIR; //change this to the absolute directory you want to be checked for templates
		return $directories;
	}

The Loops will then check in MYPLUGIN_ABSOLUTE_DIR if there is valid templates which it can use; and they will be available through The Loops options.


== Screenshots ==

1. Loop edit screen

== Changelog ==

= 1.0.1 =
* Fix scripts paths.

= 1.0.0 =
* Allow other plugins to add template directories with the filter 'tl_templates_directories'.

= 0.4 =
* Lists of users.

= 0.3 =
* Support all the wordpress post query possibilities.
* Improved custom templates.

= 0.2 =
* Custom templates.
* Query by author.

= 0.1 =
* First release.

== Upgrade Notice ==

= 1.0.1 =
Bug fix.

= 1.0.0 =
New feature: filter hook for other plugins to add template directories.

= 0.4 =
New feature: loops of users.

= 0.3 =
This version supports all the wordpress post query possibilities and an
improved template structure. It is not backward compatible with the
previous versions. Check your loops after upgrading.

= 0.2 =
New features: custom templates & query by author.

= 0.1 =
First release.
