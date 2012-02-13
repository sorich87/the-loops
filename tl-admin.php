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
	// Database version
	private static $db_version = 2;

	// Holds the current db version retrieved from the database
	private static $current_db_version = 0;

	/**
	 * Admin loader
	 *
	 * @package The_Loops
	 * @since 0.1
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( __CLASS__, 'upgrade_taxonomies' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_publish_meta_box' ) );
		add_action( 'dbx_post_sidebar', array( __CLASS__, 'loop_save_button' ) );
		add_filter( 'get_user_option_closedpostboxes_tl_loop', array( __CLASS__, 'closed_meta_boxes' ) );
		add_filter( 'post_updated_messages', array( __CLASS__, 'loop_updated_messages' ) );
		add_action( 'save_post', array( __CLASS__, 'save_loop' ), 10, 2 );
		add_filter( 'screen_layout_columns', array( __CLASS__, 'loop_screen_layout_columns' ), 10, 2 );
		add_filter( 'script_loader_src', array( __CLASS__, 'disable_autosave' ), 10, 2 );

		self::$current_db_version = get_option( 'tl_db_version' );
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

		wp_enqueue_script( 'the-loops', "{$the_loops->plugin_url}js/script$suffix.js", array( 'jquery-ui-datepicker', 'tl-jquery-tagsinput' ), '20120212' );

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
		add_meta_box( 'tl_generaldiv', __( 'General' ), array( __CLASS__, 'meta_box_general' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_taxonomydiv', __( 'Taxonomy Parameters' ), array( __CLASS__, 'meta_box_taxonomy' ), 'tl_loop', 'normal' );
		add_meta_box( 'tl_customfielddiv', __( 'Custom Field Parameters' ), array( __CLASS__, 'meta_box_custom_field' ), 'tl_loop', 'normal' );
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
		if ( false === $closed )
			$closed = array( 'tl_customfielddiv', 'tl_taxonomydiv', 'tl_widgetdiv' );

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
		global $post_ID;

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
			<span class="description"><?php _e( "Comma-separated list of authors usernames. Exclude an author by prefixing the username with a '-' (minus) sign." ); ?></span>
		</td>
	</tr>
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
	 * Display metabox for setting the loop taxonomy parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_taxonomy() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'taxonomies' => array()
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );

		$taxs = get_taxonomies( array( 'public' => true ), 'objects' );
?>
<?php foreach ( $taxonomies as $key => $taxonomy ) : ?>
	<table class="form-table tl-parameter">
		<tr valign="top">
			<th scope="row">
				<label for="loop_taxonomies_<?php echo $key; ?>_taxonomy"><?php _e( 'Taxonomy' ); ?></label>
			</th>
			<td>
				<select id="loop_taxonomies_<?php echo $key; ?>_taxonomy" name="loop[taxonomies][<?php echo $key; ?>][taxonomy]">
					<?php
					foreach ( $taxs as $tax ) {
						$selected = selected( $content['taxonomies'][$key]['taxonomy'], $tax->name );
						echo "<option value='{$tax->name}'$selected>{$tax->labels->name}</option>";
					}
					?>
				</select>
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_terms"><?php _e( 'Terms' ); ?></label></th>
			<td>
				<input value="<?php echo esc_attr( $content['taxonomies'][$key]['terms'] ); ?>" type="text" id="loop_taxonomies_<?php echo $key; ?>_terms" name="loop[taxonomies][<?php echo $key; ?>][terms]" class="regular-text" />
				<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_exclude"><?php _e( 'Exclude' ); ?></label></th>
			<td>
				<?php $exclude = isset( $content['taxonomies'][$key]['exclude'] ) ? $content['taxonomies'][$key]['exclude'] : '0'; ?>
				<?php $checked = checked( $exclude, '1', false ); ?>
				<input<?php echo $checked; ?> type="checkbox" id="loop_taxonomies_<?php echo $key; ?>_exclude" name="loop[taxonomies][<?php echo $key; ?>][exclude]" value="1" />
				<span class="description"><?php _e( 'Hide the terms above instead of showing them' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_include_children"><?php _e( 'Include children' ); ?></label></th>
			<td>
				<?php $include_children = isset( $content['taxonomies'][$key]['include_children'] ) ? $content['taxonomies'][$key]['include_children'] : '1'; ?>
				<?php $checked = checked( $include_children, '1', false ); ?>
				<input<?php echo $checked; ?> type="checkbox" id="loop_taxonomies_<?php echo $key; ?>_include_children" name="loop[taxonomies][<?php echo $key; ?>][include_children]" value="1" />
				<span class="description"><?php _e( 'Include children terms (for hierarchical taxonomies)' ); ?></span>
			</td>
		</tr>
	</table>
<?php endforeach; ?>

<p><a class="tl-add-parameter button" href="#"><?php _e( 'New Parameter' ); ?></a></p>

<table class="form-table tl-parameter hide-if-js">
		<tr valign="top">
			<th scope="row">
				<label for="loop_taxonomies_{key}_taxonomy"><?php _e( 'Taxonomy' ); ?></label>
			</th>
			<td>
				<select id="loop_taxonomies_{key}_taxonomy" name="loop[taxonomies][{key}][taxonomy]">
					<?php
					foreach ( $taxs as $tax ) {
						echo "<option value='{$tax->name}'>{$tax->labels->name}</option>";
					}
					?>
				</select>
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_terms"><?php _e( 'Terms' ); ?></label></th>
			<td>
				<input value="" type="text" id="loop_taxonomies_{key}_terms" name="loop[taxonomies][{key}][terms]" class="regular-text" />
				<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_exclude"><?php _e( 'Exclude' ); ?></label></th>
			<td>
				<input type="checkbox" id="loop_taxonomies_{key}_exclude" name="loop[taxonomies][{key}][exclude]" value="1" />
				<span class="description"><?php _e( 'Check if you want to hide the terms above instead of showing them' ); ?></span>
			</td>
		</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the loop custom field parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_custom_field() {
		global $post_ID;

		$content = get_post_meta( $post_ID, 'tl_loop_content', true );

		$defaults = array(
			'custom_fields' => array()
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<?php foreach ( $custom_fields as $key => $custom_field ) : ?>
	<table class="form-table tl-parameter">
		<tr valign="top">
			<th scope="row">
				<label for="loop_custom_fields_<?php echo $key; ?>_key"><?php _e( 'Key' ); ?></label>
			</th>
			<td>
				<input value="<?php echo esc_attr( $custom_field['key'] ); ?>" type="text" id="loop_custom_fields_<?php echo $key; ?>_key" name="loop[custom_fields][<?php echo $key; ?>][key]" class="regular-text" />
				<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_compare"><?php _e( 'Comparison' ); ?></label></th>
			<td>
				<select id="loop_custom_fields_<?php echo $key; ?>_compare" name="loop[custom_fields][<?php echo $key; ?>][compare]">
					<option<?php selected( $custom_field['compare'], 'IN' ); ?> value="IN"><?php _e( 'is equal to' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT IN' ); ?> value="NOT IN"><?php _e( 'is not equal to' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'LIKE' ); ?> value="LIKE"><?php _e( 'contains' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT LIKE' ); ?> value="NOT LIKE"><?php _e( "doesn't contain" ); ?></option>
					<option<?php selected( $custom_field['compare'], 'BETWEEN' ); ?> value="BETWEEN"><?php _e( 'is between' ); ?></option>
					<option<?php selected( $custom_field['compare'], 'NOT BETWEEN' ); ?> value="NOT BETWEEN"><?php _e( 'is not between' ); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_values"><?php _e( 'Values' ); ?></label></th>
			<td>
				<input value="<?php echo esc_attr( $custom_field['values'] ); ?>" type="text" id="loop_custom_fields_<?php echo $key; ?>_values" name="loop[custom_fields][<?php echo $key; ?>][values]" class="regular-text tl-tagsinput" />
				<span class="description"><?php _e( 'Press TAB or ENTER to add several values' ); ?></span><br />
				<span class="description"><?php _e( 'Add only one value for "contains" and "doesn\'t contain" comparisons' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_custom_fields_<?php echo $key; ?>_type"><?php _e( 'Type' ); ?></label></th>
			<td>
				<select id="loop_custom_fields_<?php echo $key; ?>_type" name="loop[custom_fields][<?php echo $key; ?>][type]">
					<?php $type = $custom_field['type']; ?>
					<option<?php selected( $custom_field['type'], 'CHAR' ); ?>>CHAR</option>
					<option<?php selected( $custom_field['type'], 'NUMERIC' ); ?>>NUMERIC</option>
					<option<?php selected( $custom_field['type'], 'DECIMAL' ); ?>>DECIMAL</option>
					<option<?php selected( $custom_field['type'], 'SIGNED' ); ?>>SIGNED</option>
					<option<?php selected( $custom_field['type'], 'UNSIGNED' ); ?>>UNSIGNED</option>
					<option<?php selected( $custom_field['type'], 'DATE' ); ?>>DATE</option>
					<option<?php selected( $custom_field['type'], 'DATETIME' ); ?>>DATETIME</option>
					<option<?php selected( $custom_field['type'], 'TIME' ); ?>>TIME</option>
					<option<?php selected( $custom_field['type'], 'BINARY' ); ?>>BINARY</option>
				</select>
				<span class="description"><?php _e( "Leave the default if you don't know what this means" ); ?></span>
			</td>
		</tr>
	</table>
<?php endforeach; ?>

<p><a class="tl-add-parameter button" href="#"><?php _e( 'New Parameter' ); ?></a></p>

<table class="form-table tl-parameter hide-if-js">
	<tr valign="top">
		<th scope="row">
			<label for="loop_custom_fields_{key}_key"><?php _e( 'Key' ); ?></label>
		</th>
		<td>
			<input value="" type="text" id="loop_custom_fields_{key}_key" name="loop[custom_fields][{key}][key]" class="regular-text" />
			<a href="#" class="tl-delete"><?php _e( 'remove' ); ?></a>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_compare"><?php _e( 'Comparison' ); ?></label></th>
		<td>
			<select id="loop_custom_fields_{key}_compare" name="loop[custom_fields][{key}][compare]">
				<option value="IN"><?php _e( 'is equal to' ); ?></option>
				<option value="NOT IN"><?php _e( 'is not equal to' ); ?></option>
				<option value="LIKE"><?php _e( 'contains' ); ?></option>
				<option value="NOT LIKE"><?php _e( "doesn't contain" ); ?></option>
				<option value="BETWEEN"><?php _e( 'is between' ); ?></option>
				<option value="NOT BETWEEN"><?php _e( 'is not between' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_values"><?php _e( 'Values' ); ?></label></th>
		<td>
			<input value="" type="text" id="loop_custom_fields_{key}_values" name="loop[custom_fields][{key}][values]" class="regular-text tl-tagsinput" />
			<span class="description"><?php _e( 'Press TAB or ENTER to add several values' ); ?></span><br />
			<span class="description"><?php _e( 'Add only one value for "contains" and "doesn\'t contain" comparisons' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_custom_fields_{key}_type"><?php _e( 'Type' ); ?></label></th>
		<td>
			<select id="loop_custom_fields_{key}_type" name="loop[custom_fields][{key}][type]">
				<option>CHAR</option>
				<option>NUMERIC</option>
				<option>DECIMAL</option>
				<option>SIGNED</option>
				<option>UNSIGNED</option>
				<option>DATE</option>
				<option>DATETIME</option>
				<option>TIME</option>
				<option>BINARY</option>
			</select>
			<span class="description"><?php _e( "Leave the default if you don't know what this means" ); ?></span>
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

		// remove data added by template tables
		array_pop( $data['taxonomies'] );
		array_pop( $data['custom_fields'] );

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

	/**
	 * Upgrade taxonomies storage from plugins versions before 0.3
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function upgrade_taxonomies() {
		if ( self::$current_db_version >= 2 )
			return;

		$loops = tl_get_loops( array( 'fields' => 'ids' ) );
		if ( ! $loops )
			return;

		$taxs = get_taxonomies( array( 'public' => true ), 'objects' );

		foreach ( $loops as $loop ) {
			$content = get_post_meta( $loop, 'tl_loop_content', true );

			foreach ( $taxs as $tax ) {
				if ( empty( $content[$tax->name] ) )
					continue;

				$content['taxonomies'][] = array(
					'taxonomy'         => $tax->name,
					'terms'            => $content[$tax->name],
					'include_children' => '1'
				);

				unset( $content[$tax->name] );
			}

			update_post_meta( $loop, 'tl_loop_content', $content );
		}

		update_option( 'tl_db_version', self::$db_version );
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

