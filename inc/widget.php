<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Stereo Widget
 */


class StereoWidget extends scbWidget {

	public function __construct() {
        parent::__construct(
            'stereo_widget', // Base ID
            'StereoWidget', // Name
            array( 'description' => __( 'Player widget for Stereo tracks', 'stereo' ), ) // Args
        );

        add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts'), 100 );
	}

    function enqueue_scripts() {
        if( is_active_widget( '', '', $this->id_base ) ) { // check if search widget is used
            if (stereo_option('include_css')) {
                wp_enqueue_style("stereo-widget");
            }
            wp_enqueue_script("stereo-widget");
        }
    }

	public function content( $instance ) {
        include("views/widget-player.php");
	}

 	public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'stereo' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 

        echo $this->input(array(
            "type" => "checkbox",
            "name" => "test",
            "desc" => "Testar",
            "value" => false
        ));

	}

	public function update( $new_instance, $old_instance ) {
        $instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

    public function widget($args, $instance)
    {
        $this->content($instance);
    }

}

add_action( 'widgets_init', function(){
     register_widget( 'StereoWidget' );
});


