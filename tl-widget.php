<?php

/**
 * Widget class
 *
 * @package The_Loops
 * @since 0.1
 */
class TL_Widget extends WP_Widget {
	function __construct() {
		parent::__construct( 'tl_widget', __( 'The Loops' ), array( 'description' => __( 'Use this widget to add one of your loops as a widget.' ) ) );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;

		$args = array(
			'offset'         => $instance['offset'],
			'posts_per_page' => $instance['posts_per_page']
		);

		echo tl_display_loop( $instance['loop_id'], $instance['template'], $args, 'widget' );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['loop_id']        = absint( $new_instance['loop_id'] );
		$instance['offset']         = absint( $new_instance['offset'] );
		$instance['posts_per_page'] = absint( $new_instance['posts_per_page'] );
		$instance['template']       = $new_instance['template'];
		$instance['title']          = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'loop_id'        => 0,
			'offset'         => 0,
			'posts_per_page' => get_option( 'posts_per_page' ) / 2,
			'template'       => 'List of titles',
			'title'          => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$loop_id        = $instance['loop_id'];
		$posts_per_page = $instance['posts_per_page'];
		$offset         = $instance['offset'];
		$template       = esc_attr( $instance['template'] );
		$title          = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('loop_id'); ?>"><?php _e('Loop:'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('loop_id'); ?>" name="<?php echo $this->get_field_name('loop_id'); ?>">
			<?php
			$loops = tl_get_loops();
			foreach ( $loops as $loop ) {
				$selected = selected( $loop_id, $loop->ID, false );
				echo "<option value='$loop->ID'$selected>$loop->post_title</option>";
			}
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e( 'Number of items:' ); ?></label>
			<input id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="text" value="<?php echo $posts_per_page; ?>" class="small-text" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('offset'); ?>"><?php _e( 'Offset:' ); ?></label>
			<input id="<?php echo $this->get_field_id('offset'); ?>" name="<?php echo $this->get_field_name('offset'); ?>" type="text" value="<?php echo $offset; ?>" class="small-text" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e( 'Template:' ); ?></label>
			<select id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" class="widefat">
				<?php
				$loop_templates = tl_get_loop_templates();
				foreach ( $loop_templates as $name => $file ) {
					$selected = selected( $name, $template, false );
					echo "<option value='". esc_attr( $name ) ."'$selected>{$name}</option>";
				}
				?>
			</select>
		</p>
		<?php
	}
}

/**
 * Register widget
 *
 * @package The_Loops
 * @since 0.1
 */
function tl_widget_init() {
	register_widget('TL_Widget');
}
add_action( 'widgets_init', 'tl_widget_init' );

