<?php
/**
 * The Loops plugin
 *
 * The Loops allows you to query the WordPress database for content and display it in a page without having to write php code.
 *
 * @package The_Loops
 */
/**
 * Plugin Name: The Loops
 * Plugin URI: http://wordpress.org/plugins/the-loops/
 * Description: The Loops allows you to query the WordPress database for content and display it in a page without having to write php code.
 * Author: Ulrich Sossou
 * Author URI: http://ulrichsossou.com/
 * Version: 1.0.1
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
 * @package The_Loops
 * @since 0.1
 */
class The_Loops {
    
        var $the_loop_id = 0;
        var $the_loop_context;
        var $the_loop_query;
        var $the_loop_original_query;
        
        var $the_loop_user_query;
        var $the_loop_user_index;
        var $the_loop_user_count;

        /**
        * @var The one true Instance
        */
        private static $instance;
        
        public static function instance() {
                if ( ! isset( self::$instance ) ) {
                        self::$instance = new The_Loops;
                        self::$instance->setup_globals();
                        self::$instance->includes();
                        self::$instance->setup_hooks();
                }
                return self::$instance;
        }
    
        /**
         * A dummy constructor to prevent from being loaded more than once.
         *
         */
        private function __construct() { /* Do nothing here */ }

	/**
	 * Global variables
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	private function setup_globals() {
		$this->file          = __FILE__;
		$this->basename      = plugin_basename( $this->file );
		$this->plugin_dir    = plugin_dir_path( $this->file );
		$this->plugin_url    = plugin_dir_url( $this->file );
		$this->templates_dir = $this->plugin_dir . 'tl-templates/';
	}

	/**
	 * Required files
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	private function includes() {
		require( $this->plugin_dir . 'tl-functions.php' );
		require( $this->plugin_dir . 'tl-template-tags.php' );
		require( $this->plugin_dir . 'tl-widget.php' );

		if ( is_admin() )
			require( $this->plugin_dir . 'tl-admin.php' );
	}

	/**
	 * Setup the plugin main functions
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register loop post type
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Loops', 'post type general name' ),
			'singular_name'      => _x( 'Loop', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'loop' ),
			'add_new_item'       => __( 'Add New Loop' ),
			'edit_item'          => __( 'Edit Loop' ),
			'new_item'           => __( 'New Loop' ),
			'all_items'          => __( 'Loops' ),
			'view_item'          => __( 'View Loop' ),
			'search_items'       => __( 'Search Loops' ),
			'not_found'          => __( 'No loops found' ),
			'not_found_in_trash' => __( 'No loops found in Trash' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Loops' )
		);

		$args = array(
			'capabilities'    => array(
				'edit_post'          => 'edit_theme_options',
				'delete_post'        => 'edit_theme_options',
				'read_post'          => 'read',
				'edit_posts'         => 'edit_theme_options',
				'edit_others_posts'  => 'edit_theme_options',
				'publish_posts'      => 'edit_theme_options',
				'read_private_posts' => 'edit_theme_options'
			),
			'labels'          => $labels,
			'show_ui'         => true,
			'show_in_menu'    => 'themes.php',
			'supports'        => array( 'title' )
		);

		register_post_type( 'tl_loop', $args );
	}

}

/**
 * The main function responsible for returning the one Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 */
function the_loops() {
        return The_Loops::instance();
}
the_loops();

endif;

