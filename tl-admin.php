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
		add_filter( 'get_user_option_closedpostboxes_tl_loop', array( __CLASS__, 'closed_meta_boxes' ) );
		add_filter( 'post_updated_messages', array( __CLASS__, 'loop_updated_messages' ) );
		add_action( 'save_post', array( __CLASS__, 'save_loop' ), 10, 2 );
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

		wp_enqueue_script( 'the-loops', "{$the_loops->plugin_url}js/script$suffix.js", array( 'jquery-ui-datepicker' ), '20120211' );

		if ( 'classic' == get_user_option( 'admin_color') )
			wp_enqueue_style ( 'jquery-ui-css', "{$the_loops->plugin_url}css/jquery-ui-classic$suffix.css", null, '20120211' );
		else
			wp_enqueue_style ( 'jquery-ui-css', "{$the_loops->plugin_url}css/jquery-ui-fresh$suffix.css", null, '20120211' );

		wp_enqueue_style( 'the-loops', "{$the_loops->plugin_url}css/style$suffix.css", null, '20120211' );

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
		add_meta_box( 'tl_generaldiv', __( 'General' ), array( __CLASS__, 'meta_box_general' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_shortcodediv', __( 'Shortcode' ), array( __CLASS__, 'meta_box_shortcode' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_widgetdiv', __( 'Widget' ), array( __CLASS__, 'meta_box_widget' ), 'tl_loop', 'normal' );
	}

	/**
	 * Define default closed metaboxes
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function closed_meta_boxes( $closed ) {
		if ( false === $closed && 'tl_loop' == get_current_screen()->id )
			$closed = array( 'tl_widgetdiv' );

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
	 * Display metabox for setting the content of the loop
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_general() {
		global $post, $post_ID;

		wp_nonce_field( 'tl_edit_loop', '_tlnonce' );

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'post_type' => 'post', 'orderby' => 'title', 'order' => 'ASC',
			'not_found' => '<p>' . __( 'Nothing found!' ) . '</p>',
			'authors' => '',
			'date' => array(
				'min' => '',
				'max' => '',
			)
		);
		$content = wp_parse_args( $content, $defaults );
?>
<table class="form-table">
	<tr valign="top">
	<th scope="row"><label for="loop_post_type"><?php _e( 'Display' ); ?></label></th>
		<td>
			<select id="loop_post_type" name="loop[post_type]">
			<option value="any"<?php selected( 'any', $content['post_type'] ); ?>><?php _e( 'Everything' ); ?></option>
				<?php
				$ptypes = get_post_types( array( 'public' => true ), 'objects' );
				foreach ( $ptypes as $ptype_name => $ptype_obj ) {
					$selected = selected( $ptype_name, $content['post_type'], false );
					echo "<option value='" . esc_attr( $ptype_name ) . "'$selected>{$ptype_obj->label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="loop_orderby"><?php _e( 'Sorted by' ); ?></label></th>
		<td>
			<select id="loop_orderby" name="loop[orderby]">
				<?php
				$orderby_params = array(
					'ID' => __( 'ID' ), 'author' => __( 'Author' ), 'title' => __( 'Title' ),
					'date' => __( 'Publication date' ), 'modified' => __( 'Last modified date' ), 'parent' => __( 'Parent ID' ),
					'rand' => __( 'Random order' ), 'comment_count' => __( 'Number of comments' ), 'menu_order' => __( 'Page order' )
				);
				foreach ( $orderby_params as $key => $label ) {
					$selected = selected( $key, $content['orderby'] );
					echo "<option value='" . esc_attr( $key ) . "'$selected>{$label}</option>";
				}
				?>
			</select>
			<select id="loop_order" name="loop[order]">
				<option value="DESC"<?php selected( 'DESC', $content['order'], true ); ?>><?php _e( 'DESC' ); ?></option>
				<option value="ASC"<?php selected( 'ASC', $content['order'], true ); ?>><?php _e( 'ASC' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop-min-date"><?php _e( 'Date range' ); ?></label></th>
		<td>
			from
			<input type="text" class="loop-date" id="loop-min-date" name="loop[date][min]" value="<?php echo esc_attr( $content['date']['min'] ); ?>" class="regular-text" />
			to
			<input type="text" class="loop-date" id="loop-max-date" name="loop[date][max]" value="<?php echo esc_attr( $content['date']['max'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'If these fields are left empty, infinite values will be used' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_authors"><?php _e( 'Authors' ); ?></label></th>
		<td>
			<input type="text" id="loop_authors" name="loop[authors]" value="<?php echo esc_attr( $content['authors'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Comma-separated list of authors usernames' ); ?></span>
		</td>
	</tr>
	<?php $taxs = get_taxonomies( array( 'public' => true ), 'objects' ); ?>
	<?php foreach ( $taxs as $tax ) : ?>
	<tr valign="top">
		<th scope="row"><label for="loop_<?php echo $tax->name; ?>"><?php printf( __( 'Limit to %s' ), $tax->labels->name ); ?></label></th>
		<td>
			<input type="text" id="loop_<?php echo $tax->name; ?>" name="loop[<?php echo $tax->name; ?>]" value="<?php echo isset( $content[$tax->name] ) ? esc_attr( $content[$tax->name] ) : ''; ?>" class="regular-text" />
			<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr valign="top">
		<th scope="row"><label for="loop_not_found"><?php _e( 'Not found text' ); ?></label></th>
		<td>
			<input type="text" id="loop_not_found" name="loop[not_found]" value="<?php echo esc_attr( $content['not_found'] ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Text to display when nothing found' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}


	/**
	 * Display metabox for setting the loop shortcode
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_shortcode() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'shortcode' => array(
			    'id'             => 0,
			    'posts_per_page' => get_option( 'posts_per_page' ),
			    'template'       => 'List of full posts'
			)
		);
		$content = wp_parse_args( $content, $defaults );

		$loop_templates = tl_get_loop_templates();
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_posts_per_shortcode"><?php _e( 'Show' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_shortcode" name="loop[shortcode][posts_per_page]" value="<?php echo esc_attr( $content['shortcode']['posts_per_page'] ); ?>" size="3" />
			<span><?php _e( 'items on the page' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_shortcode_template"><?php _e( 'Template' ); ?></label></th>
		<td>
			<select id="loop_shortcode_template" name="loop[shortcode][template]">
				<?php
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $content['shortcode']['template'] );
					echo "<option value='" . esc_attr( $name ) . "'$selected>{$name}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Shortcode' ); ?></th>
		<td>
			<code><?php echo '[the-loop id="' . $post_ID . '"]'; ?></code>
			<span class="description"><?php _e( 'Copy/paste this shortcode in the post or page where you want to display the loop' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}


	/**
	 * Display metabox for setting the loop widget
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_widget() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'widget' => array(
			    'expose'         => 0,
			    'posts_per_page' => get_option( 'posts_per_page' ),
			    'template'       => 'List of titles'
			)
		);
		$content = wp_parse_args( $content, $defaults );

		$loop_templates = tl_get_loop_templates();
?>
<table class="form-table">
	<tr valign="top">
	<th scope="row"><label for="loop_posts_per_widget"><?php _e( 'Show' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_widget" name="loop[widget][posts_per_page]" size="3" value="<?php echo esc_attr( $content['widget']['posts_per_page'] ); ?>" />
			<span><?php _e( 'items in the widget' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="loop_widget_template"><?php _e( 'Template' ); ?></label></th>
		<td>
			<select id="loop_widget_template" name="loop[widget][template]">
				<?php
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $content['widget']['template'] );
					echo "<option value='". esc_attr( $name ) ."'$selected>{$name}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Usage' ); ?></th>
		<td>
			<span class="description"><?php printf( __( '<a href="%s">Go to the widgets management screen</a> and assign The Loops Widget to a sidebar' ), site_url( 'wp-admin/widgets.php' ) ) ?></span>
		</td>
	</tr>
</table>
<?php
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

		update_post_meta( $post_id, 'tl_loop_content', $data );
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
			7 => __( 'Loop saved.' ),
			8 => sprintf( __( 'Loop submitted.' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Loop scheduled for: <strong>%1$s</strong>.' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Loop draft updated.' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
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

