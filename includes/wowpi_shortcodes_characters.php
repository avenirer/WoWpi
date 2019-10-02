<?php

function wowpi_shortcode_character($atts,$character)
{
  $output = '';
  if(strlen($character)==0) $character = null;
  $realm = null;
  
  $pull_character_atts = shortcode_atts( array(
    'name' => $character,
    'realm' => $realm,
    'show' => 'achievement_points,kills,professions,gear,talents,artifact_weapon',
    'id' => '',
    'class' => '',
    'type' => 'extended'
    ), $atts );
  if($pull_character_atts==null)
  {
    return false;
  }
  $character_name = wp_kses_post($pull_character_atts[ 'name' ]);
  if(strpos($character_name,'[username]')!==false || strpos($character_name,'[nickname]')!==false)
		{
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
  if(strlen($character_name)==0)
  {
    $character_name = null;
  }
  $realm = wp_kses_post($pull_character_atts[ 'realm' ]);
  if(strlen($realm)==0)
  {
    $realm = null;
  }
  $show_str = wp_kses_post($pull_character_atts['show']);
  $show = explode(',',$show_str);

  global $wowpi_plugin_url;
  $races = wowpi_getRaces();
  global $wowpi_options;
  $classes = wowpi_getClasses();
  $guild = wowpi_get_character('guild',$character_name,$realm);
    
  $character_data = wowpi_get_character(null, $character_name,$realm);
  //$character_stats = wowpi_get_character('stats');
  //$character_titles = wowpi_get_character('titles',$character_name,$realm);
  
  if(empty($character_data) || (array_key_exists('status',$character_data) && $character_data['status']=='nok'))
  {
    echo $character_data['reason'];
    return true;
    exit;
  }
  $character_name = $character_data['name'];
  if(isset($character_data['title']['name']))
  {
    $main_title = $character_data['title']['name'];
  }
  //$character = $character_name;
  //$main_title_id = $character_titles['main'];
  //$main_title = sprintf($character_titles['gained'][$main_title_id]['name'], $character);
  //$wowpi_main_title = $character_titles[$character_titles['main']]['name'];

  $realm = $character_data['realm'];
  $battlegroup = $character_data['battlegroup'];  
  $class = $classes[$character_data['class']]['name'];
  $race = $races[$character_data['race']]['name'];
  $side = $races[$character_data['race']]['side'];
  $gender = $character_data['gender'];
  $level = $character_data['level'];
  $thumbnail = $character_data['thumbnail'];
  $calc_class = $character_data['calc_class'];
  $faction = $character_data['faction'];
  
  $with_title = ($pull_character_atts['type'] == 'extended') ? false : true;  
  $output = '<table';
  $output .= isset($pull_character_atts['id']) && strlen($pull_character_atts['id'])>0 ? ' id="'.$pull_character_atts['id'].'"' : '';
  $output .= ' class="'.(isset($pull_character_atts['class']) && strlen($pull_character_atts['class'])>0 ? $pull_character_atts['class'] : 'wowpi_character_data').' '.$pull_character_atts['type'];
  $output .= ' '.$side.'"';
  $output .= ((!isset($pull_character_atts['class']) || (isset($pull_character_atts['class']) && strlen($pull_character_atts['class'])==0)) && $pull_character_atts['type']=='condensed') ? ' style="background-image: url(\''.$wowpi_plugin_url.'assets/images/symbols/class-'.$character_data['class'].'.png\')"' : '';
  $output .= '>';
  $output .= '<tbody>';
  $output .= '<tr>';
  $output .= ($pull_character_atts['type'] == 'extended') ? '<td rowspan="'.(sizeof($show)+1).'" class="class_crest"><img src="'.$wowpi_plugin_url.'assets/images/symbols/class-'.$character_data['class'].'.png" style="height:auto;" /></td>' : '';
  $output .= ($with_title==false) ? '<th><span>'.__( 'Character:', 'wowpi' ).'</span></th>' : '';
  $output .= '<td class="name">';
  $output .= '<div class="character_name">'.$character_name.'</div>';
  if(isset($main_title))
  {
    $output .= '<div class="title">'.__('Also known as','wowpi').' '.sprintf($main_title,$character_name).'</div>';
  }
  $output .= '<div class="level_race_class">'.sprintf(__('Level %1s %2s %3s','wowpi'), $level, $race, $class).'</div>';
  $output .= '</td>';
  $output .= '</tr>';
  foreach($show as $what)
  {
    $what = trim($what);
    $function = 'wowpi_show_'.strtolower($what);
    if(function_exists($function))
    {
      $output .= '<tr>';
      if($with_title==false)
      {
        $output .= '<th><span>';
        $output .= wowpi_translate_title($what);
        $output .= '</span></th>';
      }
      $output .= '<td class="'.$what.'">';
      $output .= $function($character_data['name'],$character_data['realm'], $with_title);
      $output .= '</td>';
      $output .= '</tr>';
    }
  }
  $output .= '</tbody>';
  $output .= '</table>';
  return $output;
}