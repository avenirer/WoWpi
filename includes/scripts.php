<?php
/********************************************
* scripts
********************************************/

/**
 * Include CSS and script files for WoWpi.
 */
function wowpi_scripts() {
  $options = get_option('wowpi_options');
  $style = $options['styling'];
  $tooltips = $options['tooltips'];
  if($style!='no_styling')
  {
    global $wowpi_plugin_url;
    wp_register_style('datatable_style', '//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css');
    wp_enqueue_style( 'datatable_style' );
    wp_register_style( 'wowpi_style',  $wowpi_plugin_url . 'assets/css/'.$style.'.css' );
    wp_enqueue_style( 'wowpi_style' );
  }
  if($tooltips=='http://www.wowhead.com/')
  {
    wp_register_script('wowhead','//wow.zamimg.com/widgets/power.js',array('jquery'),false,true);
    wp_enqueue_script('wowhead');
  }
  elseif($tooltips=='http://www.wowdb.com/')
  {
    wp_register_script('wowdb','//static-azeroth.cursecdn.com/current/js/syndication/tt.js',array('jquery'),false,true);
    wp_enqueue_script('wowdb');
  }
  
  wp_register_script('datatable','//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js',array('jquery'),false, true);
  wp_enqueue_script('datatable');
  wp_register_script('wowpi',$wowpi_plugin_url.'assets/js/wowpi.js',array('jquery'),'2.0.5',true);
  wp_enqueue_script('wowpi');
}
add_action( 'wp_enqueue_scripts', 'wowpi_scripts' );