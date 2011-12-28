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
 * @param string $type Type of display (shortcode or widget).
 * @param string $query URL query string.
 * @return WP_Query
 */
function tl_WP_Query( $id, $type, $query = '' ) {
	if ( empty ( $id ) )
		return;

	$content = get_post_meta( $id, 'tl_loop_content', true );

	$args = array(
		'post_type'     => $content['post_type'],
		'orderby'       => $content['orderby'],
		'order'         => $content['order'],
		'category_name' => $content['category'],
		//'tag' => str_replace( array( ' , ', ', ', ' ,' ), ',', $content['post_tag'] )
	);

	$args = wp_parse_args( $query, $args );

	return new WP_Query( $args );
}

/**
 * Display one post in the loop
 *
 * @param string $id Loop ID.
 */
function tl_display( $id, $type ) {
	$content = get_post_meta( $id, 'tl_loop_content', true );
	$format  = $content[$type]['format'];

	switch ( $format ) {
		case 'titles':
?>
	<p><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></p>
<?php
		break;
	case 'excerpts' :
?>
	<h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	<?php the_excerpt(); ?>
<?php
			break;
		case 'full' :
		default :
?>
	<h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	<?php the_content(); ?>
<?php
			break;
	}
}

/**
 * Display not found text
 *
 * @param string $id Loop ID.
 */
function tl_not_found( $id ) {
	$content = get_post_meta( $id, 'tl_loop_content', true );

	echo $content['not_found'];
}

