=== The Loops ===
Contributors: sorich87, saymar90, justinahinon
Tags: tloops, the loop, shortcode, widget, posts, pages, custom post types, users
Requires at least: 3.3
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

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
plugins.](https://profiles.wordpress.org/sorich87/#content-plugins)

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

== Installation ==
1. Go to the \'Add New\' plugins screen in your WordPress admin area
1. Search for \'The Loops\'
1. Click \'Install Now\' and activate the plugin
1. Go the \'Loops\' menu under \'Appearance\'

== Frequently Asked Questions ==
= How to use? =

Click on the \'Loops\' link in the \'Appearance\' menu, click \'Add New\' to
add a new loop. Use the shortcode provided or go to the widgets screen
to add the loop to a sidebar.

= How to add custom templates? =

Copy a default template file from the directory \'tl-templates\' in the
plugin directory to your theme directory. Then feel free to modify it to
your liking!

Copy a default template file from the directory \'tl-templates\' in the
plugin directory to your theme directory. Then feel free to modify it to
your liking!

The template\'s header must contain the line:
	 * The Loops Template: List of excerpts

= How to add register custom templates directories ? =

Use a function hooked on filter \"tl_templates_directories\" eg.

	add_filter(\'tl_templates_directories\',my_plugin_tl_templates_directory);

	function my_plugin_tl_templates_directory($directories){
		$directories[]=MYPLUGIN_ABSOLUTE_DIR; //change this to the absolute directory you want to be checked for templates
		return $directories;
	}

The Loops will then check in MYPLUGIN_ABSOLUTE_DIR if there is valid templates which it can use; and they will be available through The Loops options.

== Screenshots ==
1. Loops edit screen
2. Loops edit screen
3. Loops edit screen

== Changelog ==
= 1.0.2 =
* Fix scripts paths.
* Replace deprecated functions.
* Improvement: make the plugin ready for translation.

= 1.0.1 =
* Fix scripts paths.

= 1.0.0 =
* Allow other plugins to add template directories with the filter \'tl_templates_directories\'.

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
= 1.0.2 =
Bug fix.

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