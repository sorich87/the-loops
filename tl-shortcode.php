<?php

/**
 * Add the loops shortcode which will render a loop from an id provided as attribute
 */
function tl_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'id' => 0,
	), $atts ) );

	$tl_query = tl_WP_Query( $id, 'shortcode' );

	$post_id = get_the_ID();

	ob_start();

	if ( $tl_query->have_posts() ) :
		while( $tl_query->have_posts() ) :
			$tl_query->the_post();

			// Skip the current page
			if ( get_the_ID() == $post_id )
				continue;

			tl_display( $id, 'shortcode' );
		endwhile;
		wp_reset_query();
	else:
		tl_not_found( $id );
	endif;

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}
add_shortcode( 'the-loop', 'tl_shortcode' );
