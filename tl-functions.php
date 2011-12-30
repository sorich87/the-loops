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
 * @param int $loop_id Loop ID.
 * @param string $type Type of display (shortcode or widget).
 * @param string|array $query URL query string or array.
 * @return WP_Query
 */
function tl_WP_Query( $loop_id, $type, $query = '' ) {
	if ( empty ( $loop_id ) )
		return;

	$content = get_post_meta( $loop_id, 'tl_loop_content', true );
	$posts_per_page = $content[$type]['posts_per_page'];

	$args = array(
		'post_type'      => $content['post_type'],
		'orderby'        => $content['orderby'],
		'order'          => $content['order'],
		'posts_per_page' => $posts_per_page
	);

	$taxs = get_taxonomies( array( 'public' => true ), 'names' );
	if ( $taxs ) {
		$tax_query = array();
		foreach ( $taxs as $tax ) {
			if ( empty( $content[$tax] ) )
				continue;

			$terms = str_replace( array( ' , ', ', ', ' ,' ), ',', $content[$tax] );
			$terms = explode( ',', $terms );

			$tax_query[] = array(
				'taxonomy' => $tax,
				'field'    => 'slug',
				'terms'    => $terms,
				'operator' => 'IN'
			);
		}

		if ( $tax_query ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query'] = $tax_query;
		}
	}

	$args = wp_parse_args( $query, $args );

	return new WP_Query( $args );
}

/**
 * Wrapper function for get_posts to get the loops.
 *
 * @package The Loops
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
 * Display a loop
 *
 * @package The Loops
 * @since 0.1
 *
 * @param int $loop_id Loop ID.
 * @param string $type Display type. 'shortcode' or 'widget'
 * @param array|string Custom query args
 */
function tl_display_loop( $loop_id, $type, $args = null ) {
	$tl_query = tl_WP_Query( $loop_id, $type, $args );

	ob_start();

	echo '<div class="tl-loop">';

	if ( $tl_query->have_posts() ) :
		while( $tl_query->have_posts() ) :
			$tl_query->the_post();

			echo tl_display_post( $loop_id, $type );
		endwhile;
		wp_reset_query();
	else:
		tl_not_found( $loop_id );
	endif;

	echo '</div>';

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Display one post in the loop
 *
 * @package The Loops
 * @since 0.1
 *
 * @param int $loop_id Loop ID.
 */
function tl_display_post( $loop_id, $type ) {
	$content = get_post_meta( $loop_id, 'tl_loop_content', true );
	$format = $content[$type]['format'];

	if ( 'widget' == $type )
		$heading_tag = 'h4';
	else
		$heading_tag = 'h2';

	ob_start();

	switch ( $format ) {
		case 'titles':
?>
	<p><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></p>
<?php
		break;
	case 'excerpts' :
?>
	<<?php echo $heading_tag; ?>><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></<?php echo $heading_tag; ?>>
	<?php the_excerpt(); ?>
<?php
			break;
		case 'full' :
		default :
?>
	<<?php echo $heading_tag; ?>><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></<?php echo $heading_tag; ?>>
	<?php the_content(); ?>
<?php
			break;
	}

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Display not found text
 *
 * @package The Loops
 * @since 0.1
 *
 * @param int $loop_id Loop ID.
 */
function tl_not_found( $loop_id ) {
	$content = get_post_meta( $loop_id, 'tl_loop_content', true );

	echo $content['not_found'];
}

/**
 * Add the loops shortcode which will render a loop from an id provided as attribute
 *
 * @package The Loops
 * @since 0.1
 */
function tl_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'id' => 0,
	), $atts ) );

	$post_id = get_the_ID();

	// Exclude current post/page where the shortcode will be displayed
	$args = array(
		'post__not_in' => array( $post_id )
	);

	return tl_display_loop( $id, 'shortcode', $args );
}
add_shortcode( 'the-loop', 'tl_shortcode' );

