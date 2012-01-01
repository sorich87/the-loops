<?php
/**
 * The Loops Template: List of excerpts
 *
 * Display a post title and excerpt
 *
 * The "The Loops Template:" bit above allows this to be selectable
 * from a dropdown menu on the edit loop screen.
 *
 * @package The Loops
 * @since 0.2
 */

if ( 'widget' == tl_loop_context() )
	$heading_tag = 'h4';
else
	$heading_tag = 'h2';
?>
<<?php echo $heading_tag; ?>>
	<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
</<?php echo $heading_tag; ?>>
<?php the_excerpt(); ?>
