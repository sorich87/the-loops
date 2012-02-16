<?php

/**
 * Return not found text
 *
 * @package The_Loops
 * @since 0.3
 *
 * @param int $tl_loop_id Loop ID.
 */
function tl_not_found_text( $tl_loop_id = 0, $echo = true ) {
	if ( empty( $the_loop_id ) )
		global $the_loop_id;

	$content = tl_get_loop_parameters( $the_loop_id );

	if ( $echo )
		echo $content['not_found'];
	else
		return $content['not_found'];
}

/**
 * Return pagination
 *
 * @package The_Loops
 * @since 0.3
 *
 * @param int $tl_loop_id Loop ID
 */
function tl_pagination( $the_loop_id = 0, $echo = true ) {
	global $wp_query;

	if ( empty( $the_loop_id ) )
		global $the_loop_id;

	$pagination = '';

	$content = tl_get_loop_parameters( $the_loop_id );

	if ( 'widget' != tl_get_loop_context() && ! empty( $content['pagination'] ) ) {
		switch ( $content['pagination'] ) {
			case 'numeric' :
				$pagination = paginate_links( array(
					'base'    => str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
					'format'  => '?paged=%#%',
					'current' => max( 1, get_query_var('paged') ),
					'total'   => $wp_query->max_num_pages
				) );
				break;

			case 'previous_next' :
				$pagination = get_posts_nav_link();
				break;

			default:
				break;
		}
	}

	if ( $echo )
		echo $pagination;
	else
		return $pagination;
}

/**
 * Return loop context. 'widget' or 'shortcode'
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_get_loop_context() {
	global $the_loop_context;
	return $the_loop_context;
}

/**
 * Check if current loop is in a widget
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_in_widget() {
	global $the_loop_context;
	return 'widget' == $the_loop_context;
}

