<?php
/**
 * The Loops plugin
 *
 * The Loops is a plugin that allows you to query the WordPress database for content and display that content in a page without having to write any php code.
 *
 * @package The Loops
 */

/**
 * Plugin Name: The Loops
 * Plugin URI: http://pubpoet.com/plugins/
 * Description: The Loops is a plugin that allows you to query the WordPress database for content and display that content in a page without having to write any php code.
 * Author: PubPoet
 * Author URI: http://pubpoet.com/
 * Version: 0.1
 * License: GPL2
 */
/*  Copyright 2011  Ulrich Sossou  (http://github.com/sorich87)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'The_Loops' ) ) :
/**
 * Main plugin class
 *
 * @package The Loops
 * @since 0.1
 */
class The_Loops {

	/**
	 * Class contructor
	 *
	 * @package The Loops
	 * @since 0.1
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Global variables
	 *
	 * @package The Loops
	 * @since 0.1
	 */
	private function setup_globals() {
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url( $this->file );
	}

	/**
	 * Required files
	 *
	 * @package The Loops
	 * @since 0.1
	 */
	private function includes() {
		require( $this->plugin_dir . 'tl-functions.php' );
		require( $this->plugin_dir . 'tl-widget.php' );

		if ( is_admin() )
			require( $this->plugin_dir . 'tl-admin.php' );
	}

	/**
	 * Setup the plugin main functions
	 *
	 * @package The Loops
	 * @since 0.1
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register loop post type
	 *
	 * @package The Loops
	 * @since 0.1
	 */
	public function register_post_type() {
		$labels = array(
			'name' => _x( 'Loops', 'post type general name'),
			'singular_name' => _x( 'Loop', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'loop' ),
			'add_new_item' => __( 'Add New Loop' ),
			'edit_item' => __( 'Edit Loop' ),
			'new_item' => __( 'New Loop' ),
			'all_items' => __( 'All Loops' ),
			'view_item' => __( 'View Loop' ),
			'search_items' => __( 'Search Loops' ),
			'not_found' =>  __( 'No loops found' ),
			'not_found_in_trash' => __( 'No loops found in Trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Loops' )
		);

		$args = array(
			'labels' => $labels,
			'show_ui' => true,
			'menu_position' => 60,
			'menu_icon' => null,
			'supports' => array( 'title' )
		);

		register_post_type( 'tl_loop', $args );
	}
}

$GLOBALS['tl'] = new The_Loops();

endif;

