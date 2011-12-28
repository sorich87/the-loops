<?php

/**
 * Helper functions
 * 
 * @package The Loops
 * @since 0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns a WP_Query based on a loop details.
 *
 * @package The Loops
 * @since 0.1
 *
 * @param int $id Loop ID.
 * @param string $query URL query string.
 * @return WP_Query
 */
function tl_WP_Query( $id, $query = '' ) {
	if ( empty ( $id ) )
		return;

	$content = get_post_meta( $id, 'tl_loop_content', true );

	$args = array(
		'post_type' => $content['post_type'],
		'orderby' => $content['orderby'],
		'order' => $content['order'],
		'posts_per_page' => (int) $content['posts_per_page'],
		'category__in' => $content['categories'],
		'tag' => str_replace( array( ' , ', ', ', ' ,' ), ',', $content['tags'] )
	);

	$query = wp_parse_args( $query, $args );

	return new WP_Query( $query );
}

