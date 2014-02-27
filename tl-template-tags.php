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

	$pagination = '';

	$content = tl_get_loop_parameters( $the_loop_id );
	$type    = tl_get_loop_object_type( $the_loop_id );

	if ( ! tl_in_widget() && ! empty( $content['pagination'] ) ) {
		$paged = max( 1, get_query_var('paged') );

		if ( 'users' == $type )
			$total = ceil( the_loops()->the_loop_user_query->get_total() / $content['number'] );
		else
			$total = $wp_query->max_num_pages;

		switch ( $content['pagination'] ) {
			case 'numeric' :
				$pagination = paginate_links( array(
					'base'    => str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
					'format'  => '?paged=%#%',
					'current' => $paged,
					'total'   => $total
				) );
				break;

			case 'previous_next' :
				if ( 'users' == $type ) {
					$links = array();
					if ( 1 < $total ) {
						if ( 1 < $paged ) {
							$link = get_pagenum_link( $paged - 1 );
							$links[] = '<a class="prev" href="' . esc_url( $link ) . '">' . __( '&laquo; Previous Page' ) . '</a>';
						}

						if ( $paged < $total ) {
							$link = get_pagenum_link( $paged + 1 );
							$links[] = '<a class="next" href="' . esc_url( $link ) . '">' . __( 'Next Page &raquo;' ) . '</a>';
						}

						$pagination = implode( ' &#8212; ', $links );
					}
				} else {
					$pagination = get_posts_nav_link();
				}
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
 * Check if current loop is in a widget
 *
 * @package The_Loops
 * @since 0.3
 */
function tl_in_widget() {
	return 'widget' == the_loops()->the_loop_context;
}

/**
 * Whether there are more users available in the loop.
 *
 * @package The_Loops
 * @since 0.4
 *
 * @return bool True if users are available, false if end of loop.
 */
function tl_have_users() {

	if ( null === the_loops()->the_loop_user_index ) {
		the_loops()->the_loop_user_index               = -1;
		the_loops()->the_loop_user_query->results = array_values( the_loops()->the_loop_user_query->results );
		the_loops()->the_loop_user_count               = count( the_loops()->the_loop_user_query->results );
	}

	if ( the_loops()->the_loop_user_index + 1 < the_loops()->the_loop_user_count )
		return true;
	elseif ( the_loops()->the_loop_user_index + 1 == the_loops()->the_loop_user_count && the_loops()->the_loop_user_count > 0 )
		tl_rewind_users();

	return false;
}

/**
 * Reset user index.
 *
 *
 * @package The_Loops
 * @since 0.4
 */
function tl_rewind_users() {

	unset( the_loops()->the_loop_user_index, the_loops()->the_loop_user_count );
}

/**
 * Sets up the current user.
 *
 * Retrieves the next user, sets up the user
 *
 * @package The_Loops
 * @since 0.4
 */
function tl_the_user() {
	$user = tl_next_user();
	wp_set_current_user( $user->ID );
}

/**
 * Set up the next user and iterate current user index.
 *
 * @package The_Loops
 * @since 0.4
 *
 * @return object Next user.
 */
function tl_next_user() {
	the_loops()->the_loop_user_index++;
	return the_loops()->the_loop_user_query->results[the_loops()->the_loop_user_index];
}

