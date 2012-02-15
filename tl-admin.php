<?php

/**
 * Admin class
 *
 * @package The_Loops
 * @since 0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'TL_Admin' ) ) :
class TL_Admin {

	/**
	 * Admin loader
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_publish_meta_box' ) );
		add_action( 'dbx_post_sidebar', array( __CLASS__, 'loop_save_button' ) );
		add_action( 'save_post', array( __CLASS__, 'save_loop' ), 10, 2 );

		add_filter( 'bulk_actions-edit-tl_loop', array( __CLASS__, 'remove_bulk_edit' ) );
		add_filter( 'get_user_option_closedpostboxes_tl_loop', array( __CLASS__, 'closed_meta_boxes' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'remove_quick_edit' ), 10, 2 );
		add_filter( 'post_updated_messages', array( __CLASS__, 'loop_updated_messages' ) );
		add_filter( 'screen_layout_columns', array( __CLASS__, 'loop_screen_layout_columns' ), 10, 2 );
		add_filter( 'script_loader_src', array( __CLASS__, 'disable_autosave' ), 10, 2 );
	}

	/**
	 * Disable autosave on the loop edit screen by removing the src for the autosave script tag
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function disable_autosave( $src, $handle ) {
		if( 'autosave' == $handle && 'tl_loop' == get_current_screen()->id )
			return '';

		return $src;
	}


	/**
	 * Add custom save button to the loop edit screen
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function loop_save_button() {
		if ( 'tl_loop' != get_current_screen()->id )
			return;

		submit_button( __( 'Save Loop' ), 'primary', 'publish', false, array( 'tabindex' => '5', 'accesskey' => 'p' ) );
	}

	/**
	 * Enqueue script and style
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function enqueue_scripts() {
		global $the_loops;

		if ( 'tl_loop' != get_current_screen()->id )
			return;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'tl-jquery-tagsinput', "{$the_loops->plugin_url}js/jquery-tagsinput$suffix.js", array( 'jquery' ), '20120213' );

		wp_enqueue_script( 'the-loops', "{$the_loops->plugin_url}js/script$suffix.js", array( 'jquery-ui-datepicker', 'tl-jquery-tagsinput' ), '20120214' );

		if ( 'classic' == get_user_option( 'admin_color') )
			wp_enqueue_style ( 'jquery-ui-css', "{$the_loops->plugin_url}css/jquery-ui-classic$suffix.css", null, '20120211' );
		else
			wp_enqueue_style ( 'jquery-ui-css', "{$the_loops->plugin_url}css/jquery-ui-fresh$suffix.css", null, '20120211' );

		wp_enqueue_style( 'the-loops', "{$the_loops->plugin_url}css/style$suffix.css", null, '20120213' );

	}

	/**
	 * Hide the screen options from the loop edit screen
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function loop_screen_layout_columns( $columns, $screen_id ) {
		if ( 'tl_loop' == $screen_id )
			add_screen_option( 'layout_columns', array( 'max' => 1, 'default' => 1 ) );

		return $columns;
	}

	/**
	 * Add loop content metabox
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function add_meta_boxes() {
		global $the_loops;

		include( "{$the_loops->plugin_dir}tl-meta-boxes.php" );

		TL_Meta_Boxes::init();
	}

	/**
	 * Define default closed metaboxes
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function closed_meta_boxes( $closed ) {
		if ( false === $closed )
			$closed = array( 'tl_otherdiv', 'tl_customfielddiv', 'tl_datediv', 'tl_orderpaginationdiv', 'tl_taxonomydiv' );

		return $closed;
	}

	/**
	 * Remove publish metabox from the loop edit screen
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function remove_publish_meta_box() {
		remove_meta_box( 'submitdiv', 'tl_loop', 'side' );
	}

	/**
	 * Save loop details
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function save_loop( $post_id, $post ) {
		if ( 'tl_loop' !== $post->post_type )
			return;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['_tlnonce'], 'tl_edit_loop' ) )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		$data = stripslashes_deep( $_POST['loop'] );

		// remove data added by template tables
		array_pop( $data['taxonomies'] );
		array_pop( $data['custom_fields'] );

		update_post_meta( $post_id, '_tl_loop_parameters', $data );
	}

	/**
	 * Messages displayed when a loop is updated.
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function loop_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['tl_loop'] = array(
			1 => sprintf( __( 'Loop updated.' ), esc_url( get_permalink( $post_ID ) ) ),
			4 => __( 'Loop updated.'),
			6 => sprintf( __( 'Loop published.' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Loop saved.' )
		);

		return $messages;
	}

	/**
	 * Remove quick edit
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function remove_quick_edit( $actions, $post ) {
		if( 'tl_loop' == $post->post_type )
			unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}

	/**
	 * Remove bulk edit
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function remove_bulk_edit( $actions ) {
		if ( isset( $actions['edit'] ) )
			unset( $actions['edit'] );

		return $actions;
	}

}
endif;

/**
 * Setup admin
 *
 * @package The_Loops
 * @since 0.1
 */
function tl_admin() {
	TL_Admin::init();
}
add_action ( 'init', 'tl_admin' );

