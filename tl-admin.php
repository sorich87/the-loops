<?php

/**
 * Admin class
 *
 * @package The Loops
 * @since 0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'TL_Admin' ) ) :
/**
 * Loads admin area
 * 
 * @package The Loops
 * @since 0.1
 */
class TL_Admin {

	/**
	 * Admin loader
	 *
	 * @since 0.1
	 */
	function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_loop' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'loop_updated_messages' ) );
	}

	public function add_meta_boxes() {
		add_meta_box( 'pp_contentdiv', __( 'Content' ), array( $this, 'meta_box_content' ), 'tl_loop', 'normal', 'high' );
	}

	public function meta_box_content() {
		global $post, $post_ID;

		wp_nonce_field( 'tl_edit_loop', '_tlnonce' );

		$defaults = array(
			'post_type' => 'post', 'orderby' => 'title', 'order' => 'ASC',
			'not_found' => '<p>' . __( 'Nothing found!' ) . '</p>',
			'shortcode' => array(
			    'id'             => 0,
			    'posts_per_page' => get_option( 'posts_per_page' ),
			    'format'         => 'full'
			),
			'widget' => array(
			    'expose'         => 0,
			    'posts_per_page' => get_option( 'posts_per_page' ),
			    'format'         => 'titles'
			)
		);

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

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
					echo "<option value='$ptype_name'$selected>{$ptype_obj->label}</option>";
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
					'rand' => __( 'Random order' ), 'comment_count' => __( 'Number of comments' ), 'menu_order' => __( 'Page Order' )
				);
				foreach ( $orderby_params as $key => $label ) {
					$selected = selected( $key, $content['orderby'] );
					echo "<option value='$key'$selected>{$label}</option>";
				}
				?>
			</select>
			<select id="loop_order" name="loop[order]">
				<option value="DESC"<?php selected( 'DESC', $content['order'], true ); ?>><?php _e( 'DESC' ); ?></option>
				<option value="ASC"<?php selected( 'ASC', $content['order'], true ); ?>><?php _e( 'ASC' ); ?></option>
			</select>
		</td>
	</tr>
	<?php $taxs = get_taxonomies( array( 'public' => true ), 'objects' ); ?>
	<?php foreach ( $taxs as $tax ) : ?>
	<tr valign="top">
		<th scope="row"><label for="loop_<?php echo $tax->name; ?>"><?php printf( __( 'Limit to %s' ), $tax->labels->name ); ?></label></th>
		<td>
			<input type="text" id="loop_<?php echo $tax->name; ?>" name="loop[<?php echo $tax->name; ?>]" value="<?php echo isset( $content[$tax->name] ) ? $content[$tax->name] : ''; ?>" class="regular-text" autocomplete="off" />
			<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr valign="top">
		<th scope="row"><label for="loop_not_found"><?php _e( 'Not found text' ); ?></label></th>
		<td>
			<input type="text" id="loop_not_found" name="loop[not_found]" value="<?php echo isset( $content['not_found'] ) ? $content['not_found'] : ''; ?>" class="regular-text" />
			<span class="description"><?php _e( 'Text to display when nothing found' ); ?></span>
		</td>
	</tr>
</table>

<h4><?php _e( 'Shortcode' ); ?></h4>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_shortcode_expose"><?php _e( 'Expose a shortcode' ); ?></label></th>
		<td>
			<code><?php echo '[the-loop id="' . $post->ID . '"]'; ?></code>
			<span class="description"><?php _e( 'Copy/paste this shortcode in the post or page where you want to display the loop' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_posts_per_shortcode"><?php _e( 'Show' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_shortcode" name="loop[shortcode][posts_per_page]" value="<?php echo $content['shortcode']['posts_per_page']; ?>" size="3" />
			<span><?php _e( 'items on the page' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_shortcode_format"><?php _e( 'As a' ); ?></label></th>
		<td>
			<select id="loop_shortcode_format" name="loop[shortcode][format]">
				<?php
				$format_params = array(
					'full'     => __( 'List of full posts' ),
					'excerpts' => __( 'List of excerpts '),
					'titles'   => __( 'List of titles' ),
				);
				foreach ( $format_params as $key => $label ) {
					$selected = selected( $key, $content['shortcode']['format'] );
					echo "<option value='$key'$selected>{$label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
</table>

<h4><?php _e( 'Widget' ); ?></h4>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'Expose a widget' ); ?></th>
		<td>
			<span class="description"><?php printf( __( '<a href="%s">Go to the widgets management screen</a> and assign The Loops Widget to a sidebar' ), site_url( 'wp-admin/widgets.php' ) ) ?></span>
		</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="loop_posts_per_widget"><?php _e( 'Show' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_widget" name="loop[widget][posts_per_page]" size="3" value="<?php echo $content['widget']['posts_per_page']; ?>" />
			<span><?php _e( 'items in the widget' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="loop_widget_format"><?php _e( 'As a' ); ?></label></th>
		<td>
			<select id="loop_widget_format" name="loop[widget][format]">
				<?php
				$format_params = array(
					'full'     => __( 'List of full posts' ),
					'excerpts' => __( 'List of excerpts '),
					'titles'   => __( 'List of titles' ),
				);
				foreach ( $format_params as $key => $label ) {
					$selected = selected( $key, $content['widget']['format'] );
					echo "<option value='$key'$selected>{$label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Save loop details
	 */
	public function save_loop( $post_id, $post ) {
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
	 * @package The Loops
	 * @since 0.1
	 */
	public function loop_updated_messages( $messages ) {
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
 * @package The Loops
 * @since 0.1
 */
function tl_admin() {
	if ( ! is_admin() )
		return;

	global $tl;
	$tl->admin = new TL_Admin();
}
add_action ( 'init', 'tl_admin' );

