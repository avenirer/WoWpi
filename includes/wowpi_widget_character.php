<?php
// Creating the widget 
class wowpi_widget_character extends WP_Widget {
    function __construct() {
        parent::__construct('wowpi_widget_character', __('WoWpi Character Widget', 'wowpi_widget_domain'), array( 'description' => __( 'You can simply show your WoW character data by using this widget', 'wowpi_widget_domain' ), ) );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $realm = $instance['realm'];
        $character_name = $instance['character_name'];
        if((strpos($character_name,'[username]')!==false || strpos($character_name,'[nickname]')!==false))
		{
		    if(is_user_logged_in()===FALSE)
			{
				return '';
			}
			$character = explode('-',$character_name);
			$current_user = wp_get_current_user();
			$character_name = (strpos($character_name,'[username]')!==false) ? $current_user->user_login : $current_user->display_name;
			$guild_name = trim($character[1]);
			$guild_members = wowpi_get_guild('members', $guild_name,$realm);
			if(array_key_exists($character_name,$guild_members))
			{
				$realm = $guild_members[$character_name]['realm'];
			}
		}

		$show = $instance['show'];

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
        if($character_name!==false && strlen($character_name)>0)
		{
			wowpi_show_character($show,$character_name,$realm);
		}

    // This is where you run the code and display the output
    echo $args['after_widget'];
  }

  // Widget Backend 
  public function form( $instance ) {
		//print_r($instance);
    global $wowpi_character_showable;
    $options = get_option('wowpi_options');
    if ( isset( $instance[ 'character_name' ] ) ) {
      $character_name = $instance[ 'character_name' ];
    }
    else {
      $character_name = $options['character_name'];
    }
		
		if ( isset( $instance[ 'realm' ] ) ) {
      $realm = $instance[ 'realm' ];
    }
    else {
      $realm = $options['realm'];
    }
		/*
		if ( isset( $instance[ 'reduce_http_req' ] ) ) {
      $http_req = $instance[ 'reduce_http_req' ];
    }*/
    ?>
    <p>
    <label for="<?php echo $this->get_field_id( 'character_name' ); ?>"><?php _e( 'Character name (if you want the character name to be the same as the logged-in username, you should put "<strong>[username] - Guild name</strong>" as value and replace "Guild name" with the blog\'s guild name - you can also put "<strong>[nickname] - Guild name</strong>"):' ); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id( 'character_name' ); ?>" name="<?php echo $this->get_field_name( 'character_name' ); ?>" type="text" value="<?php echo esc_attr( $character_name ); ?>" />
    </p>
		<p>
    <label for="<?php echo $this->get_field_id( 'realm' ); ?>"><?php _e( 'Realm (if you use <strong>[username] - Guild name</strong> or <strong>[nickname] - Guild name</strong> as Character name, you must put the <strong>realm of the guild</strong> here):' ); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id( 'realm' ); ?>" name="<?php echo $this->get_field_name( 'realm' ); ?>" type="text" value="<?php echo esc_attr( $realm ); ?>" />
    </p>
<?php /*
		<p>
			<label for="<?php echo $this->get_field_id('reduce_http_req');?>"><?php _e('Reduce http requests for some images (if you feel the widget is slow):');?></label>
			<select class="widefat" id="<?php echo $this->get_field_name('reduce_http_req');?>" name="<?php echo $this->get_field_name( 'reduce_http_req' ); ?>">
				<option value="yes"<?php if($http_req=='yes') echo ' selected';?>>Yes</option>
				<option value="no"<?php if($http_req=='no') echo ' selected';?>>No</option>				
			</select>
</p>
*/?>
    <?php
    $selectable_inputs = sizeof($wowpi_character_showable);
    for($i=1;$i<=$selectable_inputs;$i++)
    {
      echo '<p>';
      echo '<select class="widefat" id="'.$this->get_field_id('show_'.$i).'" name="'.$this->get_field_name('show_'.$i).'">';
      echo '<option value="nothing">What do you want to show?</option>';
      foreach($wowpi_character_showable as $showable=>$string)
      {
        echo '<option value="'.$showable.'"';
        if(isset($instance['show'][$i]) && ($instance['show'][$i]==$showable)) echo ' selected';
        echo '>'.$string.'</option>';
      }
      echo '</select>';
      echo '</p>';
    }
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    global $wowpi_character_showable;
    $selectable_inputs = sizeof($wowpi_character_showable);
    $instance = array();
    $instance['character_name'] = ( ! empty( $new_instance['character_name'] ) ) ? strip_tags( $new_instance['character_name'] ) : '';
    $instance['realm'] = ( ! empty( $new_instance['realm'] ) ) ? strip_tags( $new_instance['realm'] ) : '';
		//$instance['reduce_http_req'] = strip_tags( $new_instance['reduce_http_req']);
    $show = array();
    for($i=1;$i<=$selectable_inputs;$i++)
    {
      $show[$i]=$new_instance['show_'.$i];
    }
    $instance['show'] = $show;
    return $instance;
  }
} // Class wowpi_widget_character ends here