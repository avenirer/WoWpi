<?php
// Register and load the widgets
function wowpi_load_widget() {
	register_widget( 'wowpi_widget_character' );
  register_widget( 'wowpi_widget_realms');
}
add_action( 'widgets_init', 'wowpi_load_widget' );