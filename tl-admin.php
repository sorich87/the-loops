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
		add_action( 'before_wp_tiny_mce', array( __CLASS__, 'loop_list_script' ) );
		add_action( 'dbx_post_sidebar', array( __CLASS__, 'loop_save_button' ) );
		add_action( 'save_post', array( __CLASS__, 'save_loop' ), 10, 2 );
		add_action( 'submitpost_box', array( __CLASS__, 'loop_type_tabs' ) );

		add_filter( 'bulk_actions-edit-tl_loop', array( __CLASS__, 'remove_bulk_edit' ) );
		add_filter( 'get_user_option_closedpostboxes_tl_loop', array( __CLASS__, 'closed_meta_boxes' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'add_editor_button' ) );
		add_filter( 'mce_external_plugins', array( __CLASS__, 'add_editor_plugin' ) );
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

		if ( 'tl_loop' != get_current_screen()->id )
			return;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'tl-jquery-tagsinput', the_loops()->plugin_url . "js/jquery-tagsinput$suffix.js", array( 'jquery' ), '20120213' );

		wp_enqueue_script( 'the-loops', the_loops()->plugin_url . "js/script$suffix.js", array( 'jquery-ui-datepicker', 'tl-jquery-tagsinput' ), '20120215' );

		$l10n = array(
			'addAValue' => __( 'add a value' ),
		);
		wp_localize_script( 'the-loops', 'tlLoops', $l10n );

		if ( 'classic' == get_user_option( 'admin_color') )
			wp_enqueue_style ( 'jquery-ui-css', the_loops()->plugin_url . "css/jquery-ui-classic$suffix.css", null, '20120211' );
		else
			wp_enqueue_style ( 'jquery-ui-css', the_loops()->plugin_url . "css/jquery-ui-fresh$suffix.css", null, '20120211' );

		wp_enqueue_style( 'the-loops', the_loops()->plugin_url . "css/style$suffix.css", null, '20120213' );
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
	 * Display tabs to choose the loop type
	 * @package The_Loops
	 * @since 0.4
	 */
	public static function loop_type_tabs() {

		if ( 'tl_loop' != get_current_screen()->id )
			return;

		$objects = isset( $_GET['tl_objects'] ) ? $_GET['tl_objects'] : tl_get_loop_object_type( get_the_ID() );
?>
<h3 class="nav-tab-wrapper">
	<span><?php _e( 'Objects:' ); ?></span>

	<?php $active_class = 'posts' == $objects ? ' nav-tab-active' : ''; ?>
	<a href="<?php echo add_query_arg( 'tl_objects', 'posts' ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php _e( 'Posts' ); ?></a>

	<?php $active_class = 'users' == $objects ? ' nav-tab-active' : ''; ?>
	<a href="<?php echo add_query_arg( 'tl_objects', 'users' ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php _e( 'Users' ); ?></a>
</h3>
<input type="hidden" name="tl_objects" id="tl_objects" value="<?php echo $objects; ?>" />
<?php

		wp_nonce_field( 'tl_edit_loop', '_tlnonce' );
	}

	/**
	 * Add loop content metabox
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function add_meta_boxes() {
		include( the_loops()->plugin_dir . "tl-meta-boxes.php" );

		TL_Meta_Boxes::init();
	}

	/**
	 * Define default closed metaboxes
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function closed_meta_boxes( $closed ) {
		if ( false === $closed ) {
			$closed = array(
				'tl_postsotherdiv', 'tl_postsdatediv', 'tl_postsorderpaginationdiv',
				'tl_usersotherdiv', 'tl_usersorderpaginationdiv',
				'tl_customfielddiv', 'tl_taxonomydiv'
			);
		}

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
		if ( ! empty( $data['taxonomies'] ) )
			array_pop( $data['taxonomies'] );

		if ( ! empty( $data['custom_fields'] ) )
			array_pop( $data['custom_fields'] );

		update_post_meta( $post_id, '_tl_loop_parameters', $data );
		update_post_meta( $post_id, '_tl_loop_object_type', $_POST['tl_objects'] );
	}

	/**
	 * Messages displayed when a loop is updated.
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function loop_updated_messages( $messages ) {

		$messages['tl_loop'] = array(
			1 => sprintf( __( 'Loop updated.' ), esc_url( get_permalink() ) ),
			4 => __( 'Loop updated.'),
			6 => sprintf( __( 'Loop published.' ), esc_url( get_permalink() ) ),
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

	/**
	 * Add button to the tinymce editor
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function add_editor_button( $buttons ) {
		array_push( $buttons, 'separator', 'the_loops_selector' );
		return $buttons;
	}

	/**
	 * Add the tinymce plugin
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function add_editor_plugin( $plugins ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';

		$plugins['the_loops_selector'] = the_loops()->plugin_url . "js/editor-plugin$suffix.js";

		return $plugins;
	}

	/**
	 * Print loops list in javascript for use in the editor
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function loop_list_script() {
		$loops = tl_get_loops();
		$loop_list = array();
		foreach ( $loops as $loop ) {
			$loop_list[] = array(
				'id'   => $loop->ID,
				'name' => $loop->post_title
			);
		}
		$loop_list = json_encode( array(
			'title' => __( 'Insert loop' ),
			'loops' => $loop_list
		) );
?>
<script type='text/javascript'>
/* <![CDATA[ */
var tlLoopList = <?php echo $loop_list; ?>;
/* ]]> */
</script>
<?php
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

