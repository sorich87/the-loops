<?php
/**
 * The Loops Template: List of user bios
 * The Loops Objects: users
 *
 * Display user bios
 *
 * The "The Loops Template:" bit above allows this to be selectable
 * from a dropdown menu on the edit loop screen.
 *
 * The "The Loops Objects:" bit above specify that this template is for 
 * users.
 *
 * @package The Loops
 * @since 0.4
 */
?>

<div class="tl-loop">

	<?php if ( tl_have_users() ) : ?>

		<?php while( tl_have_users() ) : tl_the_user(); ?>
			<?php $user = wp_get_current_user(); ?>

			<?php if ( tl_in_widget() ) : ?>
				<h4 class="vcard">
			<?php else : ?>
				<h2 class="vcard">
			<?php endif; ?>

			<?php if ( '' != $user->get( 'user_url' ) ) : ?>
				<a class="url fn n" href="<?php echo esc_url( $user->get( 'user_url' ) ); ?>" title="<?php esc_attr( $user->get( 'display_name' ) ); ?>" rel="me"><?php echo $user->get( 'display_name' ); ?></a>
			<?php else : ?>
				<?php echo $user->get( 'display_name' ); ?>
			<?php endif; ?>

			<?php if ( tl_in_widget() ) : ?>
				</h4>
			<?php else : ?>
				</h2>
			<?php endif; ?>

			<p><?php echo $user->get( 'description' ); ?></p>

		<?php endwhile; ?>

	<?php else : ?>

		<div class="tl-not-found"><?php tl_not_found_text(); ?></div>

	<?php endif; ?>

	<div class="tl-pagination"><?php tl_pagination(); ?></div>

</div>
