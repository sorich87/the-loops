<?php
/**
 * Display posts in the loop
 *
 * @package The_Loops
 * @since 0.3
 */
?>
<div class="tl-loop">

	<?php if ( have_posts() ) : ?>

		<?php if ( 'the-loops-title.php' == tl_get_loop_template() ) : ?>
			<ul>
		<?php endif; ?>

		<?php while( have_posts() ) : the_post(); ?>

			<?php tl_display_post(); ?>

		<?php endwhile; ?>

		<?php if ( 'the-loops-title.php' == tl_get_loop_template() ) : ?>
			</ul>
		<?php endif; ?>

	<?php else : ?>

		<div class="tl-not-found"><?php tl_not_found_text(); ?></div>

	<?php endif; ?>

	<div class="tl-pagination"><?php tl_pagination(); ?></div>

</div>
