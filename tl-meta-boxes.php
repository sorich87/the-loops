<?php

/**
 * Meta boxes class
 *
 * @package The_Loops
 * @since 0.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'TL_Meta_Boxes' ) ) :
class TL_Meta_Boxes {

	/**
	 * Meta boxes loaded
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function init() {

		$objects = isset( $_GET['tl_objects'] ) ? $_GET['tl_objects'] : tl_get_loop_object_type( get_the_ID() );

		switch ( $objects ) {
			case 'users' :
				add_meta_box( 'tl_usersgeneraldiv', __( 'General Parameters' ), array( __CLASS__, 'meta_box_users_general' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_usersorderpaginationdiv', __( 'Order & Pagination Parameters' ), array( __CLASS__, 'meta_box_users_order_pagination' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_customfielddiv', __( 'Custom Field Parameters' ), array( __CLASS__, 'meta_box_custom_field' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_usersotherdiv', __( 'Other Parameters' ), array( __CLASS__, 'meta_box_users_other' ), 'tl_loop', 'normal' );
				break;

			default :
				add_meta_box( 'tl_postsgeneraldiv', __( 'General Parameters' ), array( __CLASS__, 'meta_box_posts_general' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_taxonomydiv', __( 'Taxonomy Parameters' ), array( __CLASS__, 'meta_box_taxonomy' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_postsorderpaginationdiv', __( 'Order & Pagination Parameters' ), array( __CLASS__, 'meta_box_posts_order_pagination' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_postsdatediv', __( 'Date Parameters' ), array( __CLASS__, 'meta_box_posts_date' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_customfielddiv', __( 'Custom Field Parameters' ), array( __CLASS__, 'meta_box_custom_field' ), 'tl_loop', 'normal' );
				add_meta_box( 'tl_postsotherdiv', __( 'Other Parameters' ), array( __CLASS__, 'meta_box_posts_other' ), 'tl_loop', 'normal' );
				break;
		}
	}

	/**
	 * Display metabox for setting the content of the loop
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_posts_general() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'authors'        => '',
			'not_found'      => '<p>' . __( 'Nothing found!' ) . '</p>',
			'post_mime_type' => '',
			'post_type'      => array( 'post' ),
			'template'       => 'List of full posts'
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_post_type"><?php _e( 'Display' ); ?></label></th>
		<td>
			<select id="loop_post_type" name="loop[post_type][]" multiple="multiple">
				<?php
				$ptypes = get_post_types( array( 'public' => true ), 'objects' );
				foreach ( $ptypes as $ptype_name => $ptype_obj ) {
					$selected = in_array( $ptype_name, $post_type ) ? ' selected="selected"' : '';
					echo "<option value='" . esc_attr( $ptype_name ) . "'$selected>{$ptype_obj->label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = in_array( 'attachment', $post_type ) ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_post_mime_type<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_post_mime_type"><?php _e( 'Mime types' ); ?></label></th>
		<td>
			<input value="<?php echo esc_attr( $post_mime_type ); ?>" id="loop_post_mime_type" name="loop[post_mime_type]" type="text" class="regular-text" />
			<span class="description"><?php _e( 'For media only. Comma-separated list of mime types.' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_authors"><?php _e( 'Authors' ); ?></label></th>
		<td>
			<input type="text" id="loop_authors" name="loop[authors]" value="<?php echo esc_attr( $authors ); ?>" class="regular-text" />
			<span class="description"><?php _e( "Comma-separated list of authors usernames. Exclude an author by prefixing the username with a '-' (minus) sign." ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_template"><?php _e( 'Template' ); ?></label></th>
		<td>
			<select id="loop_template" name="loop[template]">
				<?php
				$loop_templates = tl_get_loop_templates();
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $template );
					echo "<option value='" . esc_attr( $name ) . "'$selected>{$name}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_not_found"><?php _e( 'Not found text' ); ?></label></th>
		<td>
			<input type="text" id="loop_not_found" name="loop[not_found]" value="<?php echo esc_attr( $not_found ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Text to display when nothing found' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Shortcode' ); ?></th>
		<td>
			<code><?php echo '[the-loop id="' . get_the_ID() . '"]'; ?></code>
			<span class="description"><?php _e( 'To use the shortcode, copy/paste it in the post or page where you want to display the loop' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Widget' ); ?></th>
		<td>
			<span class="description"><?php printf( __( 'To use the widget, <a href="%s">go to the widgets management screen</a> and assign The Loops widget to a sidebar' ), site_url( 'wp-admin/widgets.php' ) ) ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the loop post parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_posts_other() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'exact'         => 0,
			'exclude_posts' => 0,
			'post_parent'   => '',
			'post_status'   => array( 'publish' ),
			'posts'         => '',
			'readable'      => 1,
			's'             => '',
			'sentence'      => 0,
			'sticky_posts'  => 'top'
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );

		// trim because we check for empty value below
		$s = trim( $s );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_post_status"><?php _e( 'Status' ); ?></label></th>
		<td>
			<select id="loop_post_status" name="loop[post_status][]" multiple="multiple">
				<?php
				$pstati = get_post_stati( array( 'show_in_admin_all_list' => true ), 'objects' );
				foreach ( $pstati as $pstatus_name => $pstatus_obj ) {
					$selected = in_array( $pstatus_name, $post_status ) ? ' selected="selected"' : '';
					echo "<option value='" . esc_attr( $pstatus_name ) . "'$selected>{$pstatus_obj->label}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = in_array( 'private', $post_status ) ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_readable<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_readable"><?php _e( 'Permission' ); ?></label></th>
		<td>
			<input<?php checked( $readable, 1 ); ?> type="checkbox" id="loop_readable" name="loop[readable]" value="1" />
			<span class="description"><?php _e( "Hide private posts from users who don't have the appropriate capability" ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_post_parent"><?php _e( 'Item parent' ); ?></label></th>
		<td>
			<input type="text" id="loop_post_parent" name="loop[post_parent]" value="<?php echo esc_attr( $post_parent ); ?>" class="small-text" />
			<span class="description"><?php _e( 'For hierarchical post types, display only children of the post id defined here' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_posts"><?php _e( 'Items' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts" name="loop[posts]" value="<?php echo esc_attr( $posts ); ?>" class="regular-text" />
			<span class="description"><?php _e( "Comma-separated list of item ids to retrieve." ); ?></span>
		</td>
	</tr>
	<tr valign="top" class="tl_exclude_posts">
		<th scope="row"><label for="loop_exclude_posts"><?php _e( 'Exclude items' ); ?></label></th>
		<td>
			<input<?php checked( $exclude_posts, 1 ); ?> type="checkbox" id="loop_exclude_posts" name="loop[exclude_posts]" value="1" />
			<span class="description"><?php _e( 'Exclude the item ids defined above instead of including them' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_s"><?php _e( 'Search terms' ); ?></label></th>
		<td>
			<input type="text" id="loop_s" name="loop[s]" value="<?php echo esc_attr( $s ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Display only the items that match these search terms' ); ?></span>
		</td>
	</tr>
	<?php $maybe_hide = ! empty( $s ) ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_sentence<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_sentence"><?php _e( 'Sentence' ); ?></label></th>
		<td>
			<input<?php checked( $sentence, 1 ); ?> type="checkbox" id="loop_sentence" name="loop[sentence]" value="1" />
			<span class="description"><?php _e( 'Consider the search terms above as a whole sentence to search for' ); ?></span>
		</td>
	</tr>
	<?php $maybe_hide = ! empty( $s ) ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_exact<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_exact"><?php _e( 'Exact matches' ); ?></label></th>
		<td>
			<input<?php checked( $exact, 1 ); ?> type="checkbox" id="loop_exact" name="loop[exact]" value="1" />
			<span class="description"><?php _e( 'Search for exact matches' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Sticky posts' ); ?></th>
		<td>
			<input<?php checked( $sticky_posts, 'top' ); ?> value="top" id="loop_sticky_posts_top" name="loop[sticky_posts]" type="radio" />
			<label for="loop_sticky_posts_top"><?php _e( 'Show sticky posts before all the other posts' ); ?></label><br />

			<input<?php checked( $sticky_posts, 'ignore' ); ?> value="ignore" id="loop_sticky_posts_ignore" name="loop[sticky_posts]" type="radio" />
			<label for="loop_sticky_posts_ignore"><?php _e( 'Show sticky posts in the natural order of the loop' ); ?></label><br />

			<input<?php checked( $sticky_posts, 'only' ); ?> value="only" id="loop_sticky_posts_only" name="loop[sticky_posts]" type="radio" />
			<label for="loop_sticky_posts_only"><?php _e( 'Show sticky posts only' ); ?></label><br />

			<input<?php checked( $sticky_posts, 'hide' ); ?> value="hide" id="loop_sticky_posts_hide" name="loop[sticky_posts]" type="radio" />
			<label for="loop_sticky_posts_hide"><?php _e( 'Hide sticky posts' ); ?></label><br />
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the loop date parameters
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_posts_date() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'date'      => array(
				'min' => '',
				'max' => ''
			),
			'date_type' => 'static',
			'day'       => '',
			'days'      => array(
				'min' => '',
				'max' => ''
			),
			'hour'      => '',
			'minute'    => '',
			'monthnum'  => '',
			'second'    => '',
			'w'         => '',
			'year'      => ''
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_date_type"><?php _e( 'Type' ); ?></label></th>
		<td>
			<select id="loop_date_type" name="loop[date_type]">
				<option<?php selected( $date_type, 'static' ); ?> value="static"><?php _e( 'static' ); ?></option>
				<option<?php selected( $date_type, 'dynamic' ); ?> value="dynamic"><?php _e( 'dynamic' ); ?></option>
				<option<?php selected( $date_type, 'period' ); ?> value="period"><?php _e( 'pediod' ); ?></option>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = 'static' == $date_type ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_date<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop-min-date"><?php _e( 'Date' ); ?></label></th>
		<td>
			<?php
			printf(
				__( 'from %1$s to %2$s' ),
				'<input type="text" class="loop-date" id="loop-min-date" name="loop[date][min]" value="' . esc_attr( $date['min'] ) . '" class="regular-text" />',
				'<input type="text" class="loop-date" id="loop-max-date" name="loop[date][max]" value="' . esc_attr( $date['max'] ) . '" class="regular-text" />'
			);
			?>
			<span class="description"><?php _e( 'If these fields are left empty, infinite values will be used' ); ?></span>
		</td>
	</tr>
	<?php $maybe_hide = 'dynamic' == $date_type ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_days<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop-min-days"><?php _e( 'Date' ); ?></label></th>
		<td>
			<?php
			printf(
				__( 'from %1$s to %2$s days ago' ),
				'<input type="text" id="loop-min-days" name="loop[days][min]" value="' . esc_attr( $days['min'] ) . '" class="small-text" />',
				'<input type="text" id="loop-max-days" name="loop[days][max]" value="' . esc_attr( $days['max'] ) . '" class="small-text" />'
			);
			?>
		</td>
	</tr>
	<?php $maybe_hide = 'period' == $date_type ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_period<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_year"><?php _e( 'Time period' ); ?></label></th>
		<td>
			<input value="<?php echo esc_attr( $year ); ?>" id="loop_year" name="loop[year]" type="text" placeholder="<?php _e( 'year' ); ?>" class="small-text" maxlength="4" />
			<select id="loop_monthnum" name="loop[monthnum]">
				<option value=""><?php _e( 'month' ); ?></option>
				<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
					<option<?php selected( $monthnum, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<select id="loop_w" name="loop[w]">
				<option value=""><?php _e( 'week' ); ?></option>
				<?php for ( $i = 0; $i <= 53; $i++ ) : ?>
					<option<?php selected( $w, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<select id="loop_day" name="loop[day]">
				<option value=""><?php _e( 'day' ); ?></option>
				<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
					<option<?php selected( $day, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<select id="loop_hour" name="loop[hour]">
				<option value=""><?php _e( 'hour' ); ?></option>
				<?php for ( $i = 0; $i <= 23; $i++ ) : ?>
					<option<?php selected( $hour, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<select id="loop_minute" name="loop[minute]">
				<option value=""><?php _e( 'minute' ); ?></option>
				<?php for ( $i = 0; $i <= 60; $i++ ) : ?>
					<option<?php selected( $minute, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<select id="loop_second" name="loop[second]">
				<option value=""><?php _e( 'second' ); ?></option>
				<?php for ( $i = 0; $i <= 60; $i++ ) : ?>
					<option<?php selected( $second, $i ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
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

		$content = tl_get_loop_parameters();

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
						$selected = selected( $taxonomy['taxonomy'], $tax->name );
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
				<input value="<?php echo esc_attr( $taxonomy['terms'] ); ?>" type="text" id="loop_taxonomies_<?php echo $key; ?>_terms" name="loop[taxonomies][<?php echo $key; ?>][terms]" class="regular-text" />
				<span class="description"><?php _e( 'Comma-separated list of slugs' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_exclude"><?php _e( 'Exclude' ); ?></label></th>
			<td>
				<?php $exclude = isset( $taxonomy['exclude'] ) ? $taxonomy['exclude'] : '0'; ?>
				<?php $checked = checked( $exclude, '1', false ); ?>
				<input<?php echo $checked; ?> type="checkbox" id="loop_taxonomies_<?php echo $key; ?>_exclude" name="loop[taxonomies][<?php echo $key; ?>][exclude]" value="1" />
				<span class="description"><?php _e( 'Hide the terms above instead of showing them' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_<?php echo $key; ?>_include_children"><?php _e( 'Include children' ); ?></label></th>
			<td>
				<?php $include_children = isset( $taxonomy['include_children'] ) ? $taxonomy['include_children'] : '0'; ?>
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
		<tr valign="top">
			<th scope="row"><label for="loop_taxonomies_{key}_include_children"><?php _e( 'Include children' ); ?></label></th>
			<td>
				<input type="checkbox" id="loop_taxonomies_{key}_include_children" name="loop[taxonomies][{key}][include_children]" checked="checked" value="1" />
				<span class="description"><?php _e( 'Include children terms (for hierarchical taxonomies)' ); ?></span>
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

		$content = tl_get_loop_parameters();

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
	 * Display metabox for the loop display settings
	 *
	 * @package The_Loops
	 * @since 0.3
	 */
	public static function meta_box_posts_order_pagination() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'meta_key'       => '',
			'offset'         => 0,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'paged'          => 1,
			'pagination'     => 'previous_next',
			'posts_per_page' => get_option( 'posts_per_page' )
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_posts_per_page"><?php _e( 'Items per page' ); ?></label></th>
		<td>
			<input type="text" id="loop_posts_per_page" name="loop[posts_per_page]" value="<?php echo esc_attr( $posts_per_page ); ?>" class="small-text" />
			<span class="description"><?php _e( 'If this is left empty, all the items will be displayed' ); ?></span>
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
					'rand' => __( 'Random order' ), 'comment_count' => __( 'Number of comments' ), 'menu_order' => __( 'Page order' ),
					'meta_value' => __( 'Meta value' ), 'meta_value_num' => __( 'Numeric meta value' )
				);
				foreach ( $orderby_params as $key => $label ) {
					$selected = selected( $key, $orderby );
					echo "<option value='" . esc_attr( $key ) . "'$selected>{$label}</option>";
				}
				?>
			</select>
			<select id="loop_order" name="loop[order]">
				<option value="DESC"<?php selected( 'DESC', $order, true ); ?>><?php _e( 'DESC' ); ?></option>
				<option value="ASC"<?php selected( 'ASC', $order, true ); ?>><?php _e( 'ASC' ); ?></option>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = in_array( $orderby, array( 'meta_value', 'meta_value_num' ) ) ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_meta_key<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_meta_key"><?php _e( 'Meta key' ); ?></label></th>
		<td>
			<input type="text" id="loop_meta_key" name="loop[meta_key]" value="<?php echo esc_attr( $meta_key ); ?>" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_pagination"><?php _e( 'Pagination format' ); ?></label></th>
		<td>
			<select id="loop_pagination" name="loop[pagination]">
				<option<?php selected( $pagination, 'previous_next' ); ?> value="previous_next"><?php _e( 'previous and next links only' ); ?></option>
				<option<?php selected( $pagination, 'numeric' ); ?> value="numeric"><?php _e( 'numeric' ); ?></option>
				<option<?php selected( $pagination, 'none' ); ?> value="none"><?php _e( 'none' ); ?></option>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = 'none' == $pagination ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_offset<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_offset"><?php _e( 'Offset' ); ?></label></th>
		<td>
			<input type="text" id="loop_offset" name="loop[offset]" value="<?php echo esc_attr( $offset ); ?>" class="small-text" />
			<span class="description"><?php _e( 'Number of items to displace or pass over' ); ?></span>
		</td>
	</tr>
	<?php $maybe_hide = 'none' == $pagination ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_paged<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_paged"><?php _e( 'Page' ); ?></label></th>
		<td>
			<input type="text" id="loop_paged" name="loop[paged]" value="<?php echo esc_attr( $paged ); ?>" class="small-text" />
			<span class="description"><?php _e( 'Show the items that would normally show up just on this page number when using a pagination' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting the content of a users loop
	 *
	 * @package The_Loops
	 * @since 0.4
	 */
	public static function meta_box_users_general() {
		global $wp_roles;

		$content = tl_get_loop_parameters();

		$defaults = array(
			'not_found' => '<p>' . __( 'Nothing found!' ) . '</p>',
			'role'      => 'subscriber',
			'template'  => 'List of user bios'
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_role"><?php _e( 'Role' ); ?></label></th>
		<td>
			<select id="loop_role" name="loop[role]">
				<?php
				$available_roles = $wp_roles->get_names();
				foreach ( $available_roles as $key => $name ) {
					$selected = selected( $key, $role, false );
					echo "<option value='$key'$selected>$name</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_template"><?php _e( 'Template' ); ?></label></th>
		<td>
			<select id="loop_template" name="loop[template]">
				<?php
				$loop_templates = tl_get_loop_templates( 'users' );
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $template );
					echo "<option value='" . esc_attr( $name ) . "'$selected>{$name}</option>";
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_not_found"><?php _e( 'Not found text' ); ?></label></th>
		<td>
			<input type="text" id="loop_not_found" name="loop[not_found]" value="<?php echo esc_attr( $not_found ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Text to display when nothing found' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Shortcode' ); ?></th>
		<td>
			<code><?php echo '[the-loop id="' . get_the_ID() . '"]'; ?></code>
			<span class="description"><?php _e( 'To use the shortcode, copy/paste it in the post or page where you want to display the loop' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Widget' ); ?></th>
		<td>
			<span class="description"><?php printf( __( 'To use the widget, <a href="%s">go to the widgets management screen</a> and assign The Loops widget to a sidebar' ), site_url( 'wp-admin/widgets.php' ) ) ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for a users loop display settings
	 *
	 * @package The_Loops
	 * @since 0.4
	 */
	public static function meta_box_users_order_pagination() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'number'         => get_option( 'posts_per_page' ),
			'offset'         => 0,
			'orderby'        => 'display_name',
			'order'          => 'ASC',
			'paged'          => 1,
			'pagination'     => 'previous_next'
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_number"><?php _e( 'Items per page' ); ?></label></th>
		<td>
			<input type="text" id="loop_number" name="loop[number]" value="<?php echo esc_attr( $number ); ?>" class="small-text" />
			<span class="description"><?php _e( 'If this is left empty, all the items will be displayed' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_orderby"><?php _e( 'Sorted by' ); ?></label></th>
		<td>
			<select id="loop_orderby" name="loop[orderby]">
				<?php
				$orderby_params = array(
					'ID' => __( 'ID' ), 'user_login' => __( 'Username' ), 'display_name' => __( 'Display name' ),
					'user_nicename' => __( 'Nicename' ), 'user_email' => __( 'E-mail' ), 'user_url' => __( 'Website' ),
					'user_registered' => __( 'Registration date' ), 'post_count' => __( 'Number of posts' ), 'rand' => __( 'Random order' )
				);
				foreach ( $orderby_params as $key => $label ) {
					$selected = selected( $key, $orderby );
					echo "<option value='" . esc_attr( $key ) . "'$selected>{$label}</option>";
				}
				?>
			</select>
			<select id="loop_order" name="loop[order]">
				<option value="DESC"<?php selected( 'DESC', $order, true ); ?>><?php _e( 'DESC' ); ?></option>
				<option value="ASC"<?php selected( 'ASC', $order, true ); ?>><?php _e( 'ASC' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_pagination"><?php _e( 'Pagination format' ); ?></label></th>
		<td>
			<select id="loop_pagination" name="loop[pagination]">
				<option<?php selected( $pagination, 'previous_next' ); ?> value="previous_next"><?php _e( 'previous and next links only' ); ?></option>
				<option<?php selected( $pagination, 'numeric' ); ?> value="numeric"><?php _e( 'numeric' ); ?></option>
				<option<?php selected( $pagination, 'none' ); ?> value="none"><?php _e( 'none' ); ?></option>
			</select>
		</td>
	</tr>
	<?php $maybe_hide = 'none' == $pagination ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_offset<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_offset"><?php _e( 'Offset' ); ?></label></th>
		<td>
			<input type="text" id="loop_offset" name="loop[offset]" value="<?php echo esc_attr( $offset ); ?>" class="small-text" />
			<span class="description"><?php _e( 'Number of items to displace or pass over' ); ?></span>
		</td>
	</tr>
	<?php $maybe_hide = 'none' == $pagination ? '' : ' hide-if-js'; ?>
	<tr valign="top" class="tl_paged<?php echo $maybe_hide; ?>">
		<th scope="row"><label for="loop_paged"><?php _e( 'Page' ); ?></label></th>
		<td>
			<input type="text" id="loop_paged" name="loop[paged]" value="<?php echo esc_attr( $paged ); ?>" class="small-text" />
			<span class="description"><?php _e( 'Show the items that would normally show up just on this page number when using a pagination' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}

	/**
	 * Display metabox for setting a users loop other parameters
	 *
	 * @package The_Loops
	 * @since 0.4
	 */
	public static function meta_box_users_other() {

		$content = tl_get_loop_parameters();

		$defaults = array(
			'exact'         => 1,
			'exclude_users' => 0,
			'users'         => '',
			'search'        => '',
			'sentence'      => 0,
		);
		$content = wp_parse_args( $content, $defaults );
		extract( $content );
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="loop_users"><?php _e( 'Users' ); ?></label></th>
		<td>
			<input type="text" id="loop_users" name="loop[users]" value="<?php echo esc_attr( $users ); ?>" class="regular-text" />
			<span class="description"><?php _e( "Comma-separated list of user ids to retrieve." ); ?></span>
		</td>
	</tr>
	<tr valign="top" class="tl_exclude_users">
		<th scope="row"><label for="loop_exclude_users"><?php _e( 'Exclude users' ); ?></label></th>
		<td>
			<input<?php checked( $exclude_users, 1 ); ?> id="loop_exclude_users" name="loop[exclude_users]" value="1" type="checkbox" />
			<span class="description"><?php _e( 'Exclude the user ids defined above instead of including them' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="loop_search"><?php _e( 'Search term' ); ?></label></th>
		<td>
			<input type="text" id="loop_search" name="loop[search]" value="<?php echo esc_attr( $search ); ?>" class="regular-text" />
			<span class="description"><?php _e( 'Display only the users that match the ID, username, email, website, or nicename set here.' ); ?></span>
			<br /><span class="description"><?php _e( 'You can include * (wildcards) before and/or after the search term.' ); ?></span>
		</td>
	</tr>
</table>
<?php
	}

}
endif;

