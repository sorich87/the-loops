<?php

/**
 * Widget class
 *
 * @package The Loops
 * @since 0.1
 */
class TL_Widget extends WP_Widget {
	function __construct() {
		parent::WP_Widget( 'tl_widget', 'The Loops', array( 'description' => __( 'Use this widget to add one of your loops as a widget.' ) ) );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		if ( $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;

		echo tl_display_loop( $instance['loop_id'], 'widget', null );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']   = strip_tags($new_instance['title']);
		$instance['loop_id'] = absint($new_instance['loop_id']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'loop_id' => 0 ) );
		$title   = esc_attr($instance['title']);
		$loop_id = $instance['loop_id'];
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
		<?php
	}
}

function tl_widget_init() {
	register_widget('TL_Widget');
}
add_action( 'widgets_init', 'tl_widget_init' );

