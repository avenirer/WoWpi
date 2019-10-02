<?php
// Creating the widget 
class wowpi_widget_realms extends WP_Widget {

  function __construct() {
    parent::__construct('wowpi_widget_realms', __('WoWpi Realms Widget', 'wowpi_widget_domain'), array( 'description' => __( 'You can simply show if the realm is online or not by using this widget', 'wowpi_widget_domain' ), ) );
  }

  // Creating widget front-end
  // This is where the action happens
  public function widget( $args, $instance ) {
    $title = (isset($instance['title']) && strlen($instance['title']) > 0) ? $instance['title'] : '';
		$realm = $instance['realm'];
    $view = (isset($instance['view']) && strlen($instance['view']) > 0) ? $instance['view'] : 'Realm';
    //add_filter( 'widget_text', 'do_shortcode' );
    
		echo $args['before_widget'];
    if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
    
    echo do_shortcode('[wowpi_realms realm="'.$realm.'" view="'.$view.'"]');
    // This is where you run the code and display the output
    echo $args['after_widget'];
  }

  // Widget Backend 
  public function form( $instance ) {
		
		$title = (isset($instance['title']) && strlen($instance['title']) > 0) ? $instance['title'] : '';
		
		$realm = '';
		
    if ( isset( $instance[ 'realm' ] ) ) {
      $realm = $instance[ 'realm' ];
    }
    else {
      $character_data = wowpi_get_character();
      $realm = $character_data['realm'];
      $battlegroups = $character_data['battlegroup'];
    }
		if ( isset( $instance[ 'view' ] ) ) {
      $view = $instance[ 'view' ];
    }
		else {$view = 'Realm';}
    ?>
    <p>
  		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget title:' ); ?></label> 
      	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>

	<p>
        <label for="<?php echo $this->get_field_id( 'realm' ); ?>"><?php _e( 'Realm:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'realm' ); ?>" name="<?php echo $this->get_field_name( 'realm' ); ?>" type="text" value="<?php echo esc_attr( $realm ); ?>" />
    </p>
    <p>
    	<label for="<?php echo $this->get_field_id('view');?>"><?php _e('What do you want to show:');?></label>
    	<select class="widefat" id="<?php echo $this->get_field_name('view');?>" name="<?php echo $this->get_field_name( 'view' ); ?>">
    		<option value="realm"<?php if($view=='Realm') echo ' selected';?>>Realm</option>
    		<option value="battlegroup"<?php if($view=='Battlegroup') echo ' selected';?>>Battlegroup</option>				
		</select>
    </p>
<?php
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['realm'] = ( ! empty( $new_instance['realm'] ) ) ? strip_tags( $new_instance['realm'] ) : '';
		$instance['view'] = strip_tags( $new_instance['view']);
    return $instance;
  }
} // Class wowpi_widget_realms ends here