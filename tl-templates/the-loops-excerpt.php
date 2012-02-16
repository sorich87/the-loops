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
?>

<div class="tl-loop">

	<?php if ( have_posts() ) : ?>

		<?php while( have_posts() ) : the_post(); ?>

			<?php if ( tl_in_widget() ) : ?>
				<h4>
			<?php else : ?>
				<h2>
			<?php endif; ?>

					<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

			<?php if ( tl_in_widget() ) : ?>
				</h4>
			<?php else : ?>
				</h2>
			<?php endif; ?>

			<?php the_excerpt(); ?>

		<?php endwhile; ?>

	<?php else : ?>

		<div class="tl-not-found"><?php tl_not_found_text(); ?></div>

	<?php endif; ?>

	<div class="tl-pagination"><?php tl_pagination(); ?></div>

</div>
