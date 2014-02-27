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
 * @param int $loop_id Loop ID.
 * @param string|array $query URL query string or array.
 * @return WP_Query
 */
function tl_query( $loop_id, $query = '' ) {

	$content = tl_get_loop_parameters( $loop_id );

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
	$args['post_parent'] = $content['post_parent'];

	$args['post__in'] = $args['post__not_in'] = array();

	if ( ! empty( $content['posts'] ) ) {
		$posts = _tl_csv_to_array( $content['posts'] );

		if ( empty( $content['exclude_posts'] ) )
			$args['post__in'] = $posts;
		else
			$args['post__not_in'] = $posts;
	}

	// post type and status
	$args['post_type']   = $content['post_type'];
	$args['post_status'] = $content['post_status'];

	// offset
	if ( 'none' == $content['pagination'] )
		$args['offset'] = $content['offset'];

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
		if ( ! empty( $content['year'] ) )
			$args['year'] = $content['year'];

		if ( ! empty( $content['monthnum'] ) )
			$args['monthnum'] = $content['monthnum'];

		if ( ! empty( $content['w'] ) )
			$args['w'] = $content['w'];

		if ( ! empty( $content['day'] ) )
			$args['day'] = $content['day'];

		if ( ! empty( $content['minute'] ) )
			$args['minute'] = $content['minute'];

		if ( ! empty( $content['second'] ) )
			$args['second'] = $content['second'];
	}

	// custom fields
	$args['meta_query'] = _tl_build_meta_query( $content['custom_fields'] );

	// pagination
	$posts_per_page = absint( $content['posts_per_page'] );
	if ( empty( $posts_per_page ) ) {
		$args['nopaging'] = true;
	} else {
		$args['posts_per_page'] = $posts_per_page;
	}

	if ( 'none' == $content['pagination'] ) {
		$args['paged'] = 1;
	} else {
		$args['paged'] = max( 1, get_query_var( 'paged' ) );
	}

	// permission
	if ( in_array( 'private', $content['post_status'] ) && ! empty( $content['readable'] ) ) {
		$args['perm'] = 'readable';
	}

	// attachments mime type
	if ( in_array( 'attachment', $content['post_type'] ) )
		$args['post_mime_type'] = $content['post_mime_type'];

	// search
	if ( ! empty( $content['s'] ) ) {
		$args['s']        = $content['s'];
		$args['exact']    = $content['exact'];
		$args['sentence'] = $content['sentence'];
	}	

	$args = wp_parse_args( $query, $args );

	// if a shortcode is being used, don't display the post in which it was inserted
	if ( 'shortcode' == the_loops()->the_loop_context ) {
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

	$content = tl_get_loop_parameters( the_loops()->the_loop_id );

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
 * Returns a WP_User_Query based on a loop details.
 *
 * @package The_Loops
 * @since 0.4
 *
 * @param int $loop_id Loop ID.
 * @param string|array $query URL query string or array.
 * @return WP_User_Query
 */
function tl_user_query( $loop_id, $query = '' ) {

	$content = tl_get_loop_parameters( $loop_id );

	$args = array();

	// order and orderby
	$args['order'] = $content['order'];
	$args['orderby'] = $content['orderby'];

	// offset and pagination
	if ( 'none' == $content['pagination'] ) {
		$args['offset'] = $content['offset'] + ( $content['paged'] - 1 ) * $content['number'];
	} else {
		$paged = max( 1, get_query_var( 'paged' ) );

		$args['offset'] = ( $paged - 1 ) * $content['number'];
	}
	$args['number'] = absint( $content['number'] );

	// search
	$args['search'] = $content['search'];

	// role
	$args['role'] = $content['role'];

	// user ids
	$users = trim( $content['users'] );
	if ( ! empty( $users ) ) {
		if ( empty( $content['exclude_users'] ) ) {
			$args['include'] = $users;
		} else {
			$args['exclude'] = $users;
		}
	}

	// custom fields
	$args['meta_query'] = _tl_build_meta_query( $content['custom_fields'] );

	$args = wp_parse_args( $query, $args );

	// if pagination is hidden, turn off SQL_CALC_FOUND_ROWS
	if ( 'none' == $content['pagination'] )
		$args['count_total'] = false;

	$args['fields'] = 'all_with_meta';

	return new WP_User_Query( $args );
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
	return array_unique( array_map( 'trim', $array ) );
}

/**
 * Build a meta query array based on form data
 *
 * @package The_Loops
 * @since 0.4
 * @param array $custom_fields Form data
 * @return array Meta query
 */
function _tl_build_meta_query( $custom_fields ) {
	if ( empty( $custom_fields ) )
		return;

	$meta_query = array();

	foreach ( $custom_fields as $custom_field ) {
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

	return $meta_query;
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
	global $wp_query;

	the_loops()->the_loop_id      = $loop_id;
	the_loops()->the_loop_context = $context;
        

	$type = tl_get_loop_object_type( $loop_id );

	switch ( $type ) {
		case 'posts' :
			the_loops()->the_loop_query = tl_query( $loop_id, $args );
			the_loops()->original_query = clone $wp_query;
			$wp_query   = the_loops()->the_loop_query;
			break;

		case 'users' :
			the_loops()->the_loop_user_query = tl_user_query( $loop_id, $args );
			break;
	}
}

/**
 * Clear globals after displaying the loop
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_clear_globals() {
	global $wp_query;

	if ( 'posts' == tl_get_loop_object_type() ) {
		$wp_query = clone the_loops()->original_query;
		wp_reset_query();
	}
        
        the_loops()->the_loop_id = null;
        the_loops()->the_loop_context = null;
        the_loops()->original_query = null;
        the_loops()->the_loop_user_query = null;
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

	$type = tl_get_loop_object_type( $loop_id );

	$loop_templates = tl_get_loop_templates( $type );
        
        if ( empty( $loop_templates ) ) return false;
        
        if (isset( $loop_templates[$template_name] )){
            $single_loop_template = $loop_templates[$template_name];
        }else{ //fallback ! TO FIX best way to select it.
            $single_loop_template = end($loop_templates);
        }

	tl_setup_globals( $loop_id, $args, $context );

	ob_start();

	load_template( $single_loop_template, true );

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
 * Check if a template file is a loop template and corresponds to the specified object type
 *
 * @package The_Loops
 * @since 0.4
 *
 * @param string $file Template file name
 * @param string $objects Template objects type
 * @return string Template name
 */
function tl_is_loop_template( $file, $objects = 'posts' ) {
	$data = get_file_data( $file, array(
		'name'    => 'The Loops Template',
		'objects' => 'The Loops Objects'
	) );

	$template_name    = trim( $data['name'] );
	$template_objects = trim( $data['objects'] );

	if ( empty( $template_name ) )
		return;

	if ( 'all' == $objects 
		|| ( empty( $template_objects ) && 'posts' == $objects )
		|| ( 'posts' != $template_objects && $objects == $template_objects ) 
		)
		return $template_name;
}

/**
 * Get the default Loop Templates corresponding to the specified objects type
 *
 * @package The_Loops
 * @since 0.2
 *
 * @param string $objects Loop objects type
 * @return array Default templates
 */
function tl_get_default_loop_templates( $objects = 'posts' ) {

	$templates_files = scandir( the_loops()->templates_dir );

	$loop_templates = array();
	foreach ( $templates_files as $template ) {
		if ( ! is_file( the_loops()->templates_dir . $template ) )
			continue;

		$is_template = tl_is_loop_template( the_loops()->templates_dir . $template, $objects );

		if ( ! $is_template ) continue;
		
		$loop_templates[$template] = the_loops()->templates_dir . $template;
	}

	return $loop_templates;
}

/**
 * Get all the available Loop Templates corresponding to the specified objects type
 *
 * @package The_Loops
 * @since 0.2
 *
 * @param string $objects Loop objects type
 * @return array Loop templates
 */
function tl_get_loop_templates( $objects = 'posts' ) {
    
        $loop_templates = $tl_templates_directories = $potential_templates = array();
        
        //templates priority : the last directory from the array have the highest priority.
        //this means that child templates will override parent templates which will override default templates.
         
        $tl_templates_directories[] = the_loops()->templates_dir; //the loops templates path
        $tl_templates_directories = apply_filters( 'tl_templates_directories' , $tl_templates_directories ); //allow plugins to add template paths
        $tl_templates_directories[] = get_template_directory(); //parent theme path
        $tl_templates_directories[] = get_stylesheet_directory(); //child theme path
        
        $tl_templates_directories = array_unique( $tl_templates_directories );
        $tl_templates_directories = array_reverse( $tl_templates_directories ); //reverse to have highest priority first

        foreach( (array) $tl_templates_directories as $tl_templates_dir ){

            $files = (array) glob( trailingslashit( $tl_templates_dir ) . "*.php" );

            foreach ( $files as $template ) {
                
                $filename = basename( $template );

                if( in_array( $template , $loop_templates ) ) continue; //for priority
                
                $template_name = tl_is_loop_template( $template, $objects );
                
                if ( $template_name )
                    $loop_templates[$template_name] = $template;
                    
            }
            
        }

	return $loop_templates;
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
function tl_get_loop_parameters( $loop_id = false ) {
    if ( !$loop_id ) $loop_id = get_the_ID();
    return get_post_meta( $loop_id, '_tl_loop_parameters', true );
}

/**
 * Get loop object type
 *
 * @package The_Loops
 * @since 0.4
 *
 * @param int $loop_id Loop ID
 * @return string Object type
 */
function tl_get_loop_object_type( $loop_id = false ) {
        if (!$loop_id) $loop_id = the_loops()->the_loop_id;
	$type = get_post_meta( $loop_id, '_tl_loop_object_type', true );

	if ( empty( $type ) )
		$type = 'posts';

	return $type;
}

