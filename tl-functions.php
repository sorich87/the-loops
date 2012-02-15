<?php

/**
 * Helper functions
 * 
 * @package The_Loops
 * @since 0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns a WP_Query based on a loop details.
 *
 * @package The_Loops
 * @since 0.1
 *
 * @param int $id Loop ID.
 * @param string|array $query URL query string or array.
 * @return WP_Query
 */
function tl_query( $id, $query = '' ) {
	global $the_loop_context, $post_ID;

	$content = tl_get_loop_parameters( $id );

	$args = array();

	// author
	$authors_logins = _tl_csv_to_array( $content['authors'] );
	if ( $authors_logins ) {
		$replacements = 1;

		$authors_ids = array();

		foreach ( $authors_logins as $author_login ) {
			$exclude_author = false;

			if ( strpos( $author_login, '-' ) === 0 ) {
				$exclude_author = true;
				$author_login = str_replace( '-', '', $author_login, $replacements );
			}

			$author_id = get_user_by( 'login', $author_login )->ID;

			if ( $exclude_author )
				$authors_ids[] = "-$author_id";
			else
				$authors_ids[] = $author_id;
		}

		if ( $authors_ids )
			$authors_ids = implode( ',', $authors_ids );

		$args['author'] = $authors_ids;
	}

	// taxonomy
	if ( ! empty( $content['taxonomies'] ) ) {
		$tax_query = array();

		foreach ( $content['taxonomies'] as $taxonomy ) {
			if ( empty( $taxonomy['terms'] ) )
				continue;

			$terms = _tl_csv_to_array( $taxonomy['terms'] );

			$tax_query[] = array(
				'taxonomy'         => $taxonomy['taxonomy'],
				'field'            => 'slug',
				'terms'            => array_map( 'sanitize_title', $terms ),
				'include_children' => empty( $taxonomy['include_children'] ) ? false : true,
				'operator'         => empty( $taxonomy['exclude'] ) ? 'IN' : 'NOT IN'
			);
		}

		if ( $tax_query ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query'] = $tax_query;
		}
	}

	// posts
	$args['post_parent'] = absint( $content['post_parent'] );

	$args['post__in'] = $args['post__not_in'] = array();

	if ( ! empty( $content['posts'] ) ) {
		$posts = _tl_csv_to_array( $content['posts'] );
		$posts = array_map( 'absint', $posts );

		if ( empty( $content['exclude_posts'] ) )
			$args['post__in'] = $posts;
		else
			$args['post__not_in'] = $posts;
	}

	// post type and status
	$args['post_type']   = (array) $content['post_type'];
	$args['post_status'] = (array) $content['post_status'];

	// offset
	if ( 'none' == $content['pagination'] )
		$args['offset'] = absint( $content['offset'] );

	// order and orderby
	$args['order'] = $content['order'];

	if ( in_array( $content['orderby'], array( 'meta_value', 'meta_value_num' ) ) ) {
		$content['meta_key'] = trim ( $content['meta_key'] );

		if ( ! empty( $content['meta_key'] ) ) {
			$args['meta_key'] = $content['meta_key'];
			$args['orderby']  = $content['orderby'];
		}
	} else {
		$args['orderby'] = $content['orderby'];
	}

	// sticky post
	switch( $content['sticky_posts'] ) {
		case 'ignore' :
			$args['ignore_sticky_posts'] = true;
			break;

		case 'only' :
			$args['ignore_sticky_posts'] = true;
			$args['post__in'] = array_merge( $args['post__in'], get_option( 'sticky_posts' ) );
			break;

		case 'hide' :
			$args['ignore_sticky_posts'] = true;
			$args['post__not_in'] = array_merge( $args['post__not_in'], get_option( 'sticky_posts' ) );
			break;

		default:
			break;
	}

	// time
	if ( 'period' == $content['date_type'] ) {
		if ( ! empty( $content['time']['year'] ) )
			$args['year'] = absint( $content['time']['year'] );

		if ( ! empty( $content['time']['monthnum'] ) )
			$args['monthnum'] = $content['time']['monthnum'];

		if ( ! empty( $content['time']['w'] ) )
			$args['w'] = $content['time']['w'];

		if ( ! empty( $content['time']['day'] ) )
			$args['day'] = $content['time']['day'];

		if ( ! empty( $content['time']['minute'] ) )
			$args['minute'] = $content['time']['minute'];

		if ( ! empty( $content['time']['second'] ) )
			$args['second'] = $content['time']['second'];
	}

	// custom field
	if ( ! empty( $content['custom_fields'] ) ) {
		$meta_query = array();

		foreach ( $content['custom_fields'] as $custom_field ) {
			if ( empty( $custom_field['key'] ) )
				continue;

			$values = _tl_csv_to_array( $custom_field['values'], "\t" );

			if ( in_array( $custom_field['compare'], array( 'LIKE', 'NOT LIKE' ) ) )
				$values = $values[0];

			$meta_query[] = array(
				'key'     => trim( $custom_field['key'] ),
				'value'   => $values,
				'compare' => $custom_field['compare'],
				'type'    => $custom_field['type']
			);
		}

		if ( $meta_query ) {
			$meta_query['relation'] = 'AND';
			$args['meta_query'] = $meta_query;
		}
	}

	// pagination
	$posts_per_page = absint( $content['posts_per_page'] );
	if ( empty( $posts_per_page ) ) {
		$args['nopaging'] = true;
	} else {
		$args['posts_per_page'] = $posts_per_page;
	}

	if ( empty( $content['pagination'] ) ) {
		$args['paged'] = 1;
	} else {
		$args['paged'] = max( 1, get_query_var( 'paged' ) );
	}

	// permission
	if ( in_array( 'private', $content['post_status'] ) && ! empty( $content['readable'] ) ) {
		$args['perm'] = 'readable';
	}

	$args = wp_parse_args( $query, $args );

	// if a shortcode is being used, don't display the post in which it was inserted
	if ( 'shortcode' == $the_loop_context ) {
		if ( ! empty( $args['post__in'] ) ) {
			$key = array_search( get_the_ID(), $args['post__in'] );
			unset( $args['posts__in'][$key] );
		} else if ( ! empty( $args['post__not_in'] ) ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'], (array) get_the_ID() );
		} else {
			$args['post__not_in'] = (array) get_the_ID();
		}
	}

	// if pagination is hidden, turn off SQL_CALC_FOUND_ROWS
	if ( 'none' == $content['pagination'] )
		$args['no_found_rows'] = true;

	add_filter( 'posts_where', 'tl_filter_where' );
	$query = new WP_Query( $args );
	remove_filter( 'posts_where', 'tl_filter_where' );

	return $query;
}

/**
 * Filter WP_Query where clause
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_filter_where( $where ) {
	global $the_loop_id;

	$content = tl_get_loop_parameters( $the_loop_id );

	if ( ! in_array( $content['date_type'], array( 'dynamic', 'static' ) ) )
		return $where;

	if ( 'dynamic' == $content['date_type'] ) {
		$min_date = ! empty( $content['days']['min'] ) ? strtotime( "-{$content['days']['min']} days" ) : null;
		$max_date = ! empty( $content['days']['max'] ) ? strtotime( "-{$content['days']['max']} days" ) : null;
	} else if( 'static' == $content['date_type'] ) {
		$min_date = ! empty( $content['date']['min'] ) ? strtotime( $content['date']['min'] ) : null;
		$max_date = ! empty( $content['date']['max'] ) ? strtotime( $content['date']['max'] ) : null;
	}

	if ( $max_date )
		$max_date = $max_date + 60 * 60 * 24;

	$min_date = $min_date ? date( 'Y-m-d', $min_date ) : null;
	$max_date = $max_date ? date( 'Y-m-d', $max_date ) : null;

	if ( $min_date )
		$where .= " AND post_date >= '$min_date'";

	if ( $max_date )
		$where .= " AND post_date < '$max_date'";

	return $where;
}

/**
 * Convert a string of comma-separated values to an array
 *
 * @package The_Loops
 * @since 0.2
 * @params string $string String of comma-separated values
 * @return array Values
 */
function _tl_csv_to_array( $string, $delimiter = ',' ) {
	if ( ! $string )
		return;

	$array = explode( $delimiter, $string );
	return array_map( 'trim', $array );
}

/**
 * Wrapper function for get_posts to get the loops.
 *
 * @package The_Loops
 * @since 0.1
 */
function tl_get_loops( $args = array() ) {
	$defaults = array(
		'post_type' => 'tl_loop',
		'nopaging'  => true
	);

	$args = wp_parse_args( $args, $defaults );

	return get_posts( $args );
}

/**
 * Setup globals before displaying the loop
 *
 * @package The_Loops
 * @since 0.1
 */
function tl_setup_globals( $loop_id, $args, $context ) {
	global $wp_query, $orig_query, $the_loop_id, $the_loop_context;

	$the_loop_id      = $loop_id;
	$the_loop_context = $context;

	$tl_query = tl_query( $loop_id, $args );
	$orig_query = clone $wp_query;
	$wp_query   = clone $tl_query;
}

/**
 * Clear globals after displaying the loop
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_clear_globals() {
	global $wp_query, $orig_query, $the_loop_id, $the_loop_context;

	$wp_query = clone $orig_query;
	wp_reset_query();

	unset( $orig_query, $the_loop_id, $the_loop_context );
}

/**
 * Display a loop
 *
 * @package The_Loops
 * @since 0.1
 *
 * @param int $loop_id Loop ID.
 * @param string $template_name Name of the template to use
 * @param array|string Custom query args
 * @param string Context in which the loop is displayed
 */
function tl_display_loop( $loop_id, $template_name, $args = null, $context = '' ) {
	global $the_loops;

	$loop_templates = tl_get_loop_templates();
	$loop_template = $loop_templates[$template_name];

	tl_setup_globals( $loop_id, $args, $context );

	ob_start();

	include( "{$the_loops->plugin_dir}tl-template-tags.php" );

	tl_locate_template( $loop_template, true );

	$content = ob_get_contents();
	ob_end_clean();

	tl_clear_globals();

	return $content;
}

/**
 * Add the loops shortcode which will render a loop from an id provided as attribute
 *
 * @package The_Loops
 * @since 0.1
 */
function tl_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'id' => 0,
	), $atts ) );

	$details = tl_get_loop_parameters( $id );

	return tl_display_loop( $id, $details['template'], null, 'shortcode' );
}
add_shortcode( 'the-loop', 'tl_shortcode' );

/**
 * Get the default Loop Templates
 *
 * @package The_Loops
 * @since 0.2
 */
function tl_get_default_loop_templates() {
	global $the_loops;

	$templates_files = scandir( $the_loops->templates_dir );

	foreach ( $templates_files as $template ) {
		if ( ! is_file( $the_loops->templates_dir . $template ) )
			continue;

		// don't allow template files in subdirectories
		if ( false !== strpos( $template, '/' ) )
			continue;

		$data = get_file_data( $the_loops->templates_dir. $template, array( 'name' => 'The Loops Template' ) );

		if ( ! empty( $data['name'] ) )
			$loop_templates[trim( $data['name'] )] = $template;
	}

	return $loop_templates;
}

/**
 * Get the Loop Templates available in the current theme or the default ones
 *
 * @package The_Loops
 * @since 0.2
 */
function tl_get_loop_templates() {
	$themes = get_themes();
	$theme = get_current_theme();
	$templates = $themes[$theme]['Template Files'];
	$loop_templates = tl_get_default_loop_templates();

	if ( is_array( $templates ) ) {
		$base = array( trailingslashit(get_template_directory()), trailingslashit(get_stylesheet_directory()) );

		foreach ( $templates as $template ) {
			$basename = str_replace( $base, '', $template );

			// don't allow template files in subdirectories
			if ( false !== strpos( $basename, '/' ) )
				continue;

			if ( 'functions.php' == $basename )
				continue;

			$data = get_file_data( $template, array( 'name' => 'The Loops Template' ) );

			if ( !empty( $data['name'] ) )
				$loop_templates[trim( $data['name'] )] = $basename;
		}
	}

	return $loop_templates;
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the plugin templates dir, then STYLESHEETPATH and TEMPLATEPATH.
 *
 * @package The_Loops
 * @since 0.2
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function tl_locate_template( $template_names, $load = false, $require_once = false ) {
	global $the_loops;

	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( ! $template_name )
			continue;

		if ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/' . $template_name;
			break;
		} else if ( file_exists( TEMPLATEPATH . '/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/' . $template_name;
			break;
		} else if ( file_exists( $the_loops->templates_dir . $template_name ) ) {
			$located = $the_loops->templates_dir . $template_name;
			break;
		}
	}

	if ( $load && '' != $located )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Get loop parameters
 *
 * @package The_Loops
 * @since 0.3
 *
 * @param int  $loop_id Loop ID
 * @return array Loop parameters
 */
function tl_get_loop_parameters( $loop_id ) {
	return get_post_meta( $loop_id, '_tl_loop_parameters', true );
}

