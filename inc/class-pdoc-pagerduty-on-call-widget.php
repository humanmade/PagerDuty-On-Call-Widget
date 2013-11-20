<?php

class PDOC_PagerDuty_On_Call_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'PDOC_PagerDuty_On_Call_Widget',
			__('PagerDuty On-Call', 'pagerduty-widget'),
			array( 'description' => __( 'Show who is currently on call!', 'pagerduty-widget' ) )
		);
	}

	public function widget( $args, $instance ) {
		
		echo $args['before_widget'];

		$transient_key = 'pagerduty_schedule_' . md5( serialize( $instance ) );

		if ( ( $entries = get_transient( $transient_key ) ) === false ) {
			$entries = $this->get_schedule( $instance );
			set_transient( $transient_key, $entries, 60 * 5 );
		}
		
		?>

		<h3 class="widget-title"><?php echo esc_html( $instance['title'] ) ?></h3>

		<ul class="pagerduty-entries">
			<?php foreach ( $entries as $entry ) : ?>
				<li style="line-height: 30px">
					<div style="float: left; margin-right: 10px"><?php echo get_avatar( $entry->user->email, 30 ) ?></div>
					<?php echo esc_html( $entry->user->name ) ?>
				</li>
			<?php endforeach ?>
		</ul>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'pagerduty-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'account' ); ?>"><?php _e( 'Account:', 'pagerduty-widget' ); ?></label><br />
			<input style="width: 130px" class="" id="<?php echo $this->get_field_id( 'account' ); ?>" name="<?php echo $this->get_field_name( 'account' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['account'] ) ? $instance['account'] : '' ); ?>" /> .pagerduty.com
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'api_token' ); ?>"><?php _e( 'API Token:', 'pagerduty-widget' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'api_token' ); ?>" name="<?php echo $this->get_field_name( 'api_token' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['api_token'] ) ? $instance['api_token'] : '' ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'schedule_id' ); ?>"><?php _e( 'Schedule ID:', 'pagerduty-widget' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'schedule_id' ); ?>" name="<?php echo $this->get_field_name( 'schedule_id' ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['schedule_id'] ) ? $instance['schedule_id'] : '' ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		
		return array( 
			'api_token' => sanitize_text_field( $new_instance['api_token'] ),
			'account' => sanitize_key( $new_instance['account'] ),
			'title' => sanitize_text_field( $new_instance['title'] ),
			'schedule_id' => sanitize_text_field( $new_instance['schedule_id'] ),
		);
	}

	private function get_schedule( $instance ) {

		$api = new PDOC_PagerDuty_API( $instance['account'], $instance['api_token'] );

		$data = $api->request( 'GET', '/schedules/' . $instance['schedule_id'] . '/entries', array(
			'since' => date( 'c' ),
			'until' => date( 'c', time() + 1 ),
			'time_zone' => 'UTC'
		));

		return $data->entries;
	}
}

function pdoc_register_on_call_widget() {
	register_widget( 'PDOC_PagerDuty_On_Call_Widget' );
}
add_action( 'widgets_init', 'pdoc_register_on_call_widget' );