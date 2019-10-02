<?php

function wowpi_shortcode_guild_achievements($atts, $guild_name = '')
{
  global $wowpi_plugin_url;
  global $wowpi_options;  
  $output = '';
  if(strlen($guild_name)==0) $guild_name = null;
  $realm = null;
  
  $pull_guild_atts = shortcode_atts( array(
    'guild' => $guild_name,
    'realm' => $realm,
    'class' => '',
		'limit' => 'none'
    ), $atts );
  if($pull_guild_atts==null)
  {
    return false;
  }
  $guild = wp_kses_post($pull_guild_atts[ 'guild' ]);
  $realm = wp_kses_post($pull_guild_atts[ 'realm' ]);
	$limit = wp_kses_post($pull_guild_atts['limit']);
  
  $achievements = wowpi_get_guild('achievements',$guild_name, $realm = null, null, null);	
	
	$general_guild_achievements = wowpi_getGuildAchievements();
	
	$total_general_achievement_points = $general_guild_achievements['total_points'];
	$total_guild_achievement_points = 0;
	
	/*
	echo '<pre>';
	print_r($general_guild_achievements);
	echo '</pre>';
	*/
	
	$list_achievements = array();
	
	if(empty($achievements)) return true;
	
	$achievement_arr = array();
	foreach($achievements as $achi)
	{
		$achievement_arr[$achi['completed']] = array('id'=>$achi['id'], 'completed' => $achi['completed'], 'the_achievement'=>$general_guild_achievements['achievements'][$achi['id']]);
		if(isset($general_guild_achievements['achievements'][$achi['id']]['points'])) $total_guild_achievement_points += $general_guild_achievements['achievements'][$achi['id']]['points'];
	}
	
	ksort($achievement_arr);
	$achievement_arr = array_reverse($achievement_arr);
	
	if($limit!=='none' && is_numeric($limit))
	{
		$achievement_arr = array_slice($achievement_arr,0,$limit);
	}
	
	/*
	echo '<pre>';
	print_r($achievement_arr);
	echo '</pre>';
	*/
	
  $output = '';
  if(!empty($achievement_arr))
  {
    $output .= '<div class="wowpi_guild_achievement_feed">';
		$output .= '<div class="title">'.__('Guild achievement points:').'</div>';
		$output .= '<div class="total">'.$total_guild_achievement_points.'</div>';
		$output .= '<div class="from">'.__('out of','wowpi').' '.$total_general_achievement_points.'</div>';
    $output .= '<ul>';
    foreach($achievement_arr as $achievement)
    {
      $output .= '<li style="background-image: url(\''.wowpi_retrieve_image($achievement['the_achievement']['icon'], 'icon', 18).'\');"> ';
			$output .= date('d.m.Y',$achievement['completed']).' - '.wowpi_get_tooltip($achievement['id'],'achievement', $achievement['the_achievement']['description']).' <span class="points">'.$achievement['the_achievement']['points'].'</span></li>';
    }
    $output .= '</ul>';
    $output .= '</div>';
  }
  return $output;
}


function wowpi_shortcode_guild_progression($atts, $guild_name = '')
{
  global $wowpi_plugin_url;
  global $wowpi_options;  
  $output = '';
  if(strlen($guild_name)==0) $guild_name = null;
  $realm = null;
  
  $pull_guild_atts = shortcode_atts( array(
    'guild' => $guild_name,
    'realm' => $realm,
    'class' => ''
    ), $atts );
  if($pull_guild_atts==null)
  {
    return false;
  }
  $guild = wp_kses_post($pull_guild_atts[ 'guild' ]);
  $realm = wp_kses_post($pull_guild_atts[ 'realm' ]);
	if(!isset($realm) || strlen($realm)==0) $realm = null;
  
  $progression = wowpi_get_guild_progression($guild_name, $realm);
	
	/*
	echo '<pre>';
	print_r($progression);
	echo '</pre>';
	*/
	if($progression==false)
	{
		return $output;
	}
	
  $output .= '<table class="wowpi_guild_progression">';
  $output .= '<tbody>';
	foreach($progression as $expansion=>$data)
  {
		if(array_key_exists('expanded',$data))
		{
			$output .= '<tr><th class="group';
			$output .= ($data['summary']['not_finished']>'0' ? ' completed' : ' not_completed');
			$output .= '">'.$expansion.'</th>';
			$output .= '<th>'.$data['summary']['finished'].'/'.(intval($data['summary']['finished'])+intval($data['summary']['not_finished'])).'</th>';
			$output .= '</tr>';
			foreach($data['expanded'] as $instance)
				{
					$output .= '<tr><td class="kill';
					$output .= ($instance['completed']=='1' ? ' completed' : ' not_completed');
					$output .= '" colspan="2">'.$instance['details']['description'].'</td>';
				}
		}
		else
		{
			foreach($data as $type => $instances)
			{
				$output .= '<tr><th class="group';
				$output .= ($instances['summary']['not_finished']>'0' ? ' completed' : ' not_completed');
				$output .= '">'.$expansion.' '.$type.'</th>';
				$output .= '<th>'.$instances['summary']['finished'].'/'.(intval($instances['summary']['finished'])+intval($instances['summary']['not_finished'])).'</th>';
				$output .= '</tr>';
				foreach($instances['expanded'] as $instance)
				{
					$output .= '<tr><td class="kill';
					$output .= ($instance['completed']=='1' ? ' completed' : ' not_completed');
					$output .= '" colspan="2">'.$instance['details']['description'].'</td>';
      //echo '<td>'.$kill['completed'].'</td></tr>';
				}
			}
		}
  }
  $output .= '</tbody>';
  $output .= '</table>';
	
	return $output;
}


function wowpi_shortcode_guild_tabard($atts, $guild_name = '')
{
  global $wowpi_plugin_url;
  $races = wowpi_getRaces();
  $classes = wowpi_getClasses();
  
  $output = '';
  if(strlen($guild_name)==0) $guild_name = null;
  $realm = null;
  
  $pull_guild_atts = shortcode_atts( array(
    'guild' => $guild_name,
    'realm' => $realm,
    'id' => '',
    'class' => ''
    ), $atts );
  if($pull_guild_atts==null)
  {
    return false;
  }
  $guild = wp_kses_post($pull_guild_atts[ 'guild' ]);
  $realm = wp_kses_post($pull_guild_atts[ 'realm' ]);
  $guild_tabard = wowpi_get_guild_tabard($guild, $realm);
  if(isset($guild_tabard) && $guild_tabard)
  {
    $output .=  '<img src="'.$guild_tabard.'"';
    $output .= isset($pull_guild_atts['id']) && strlen($pull_guild_atts['id'])>0 ? ' id="'.$pull_guild_atts['id'].'"' : '';
    $output .= ' class="'.(isset($pull_guild_atts['id']) && strlen($pull_guild_atts['class'])>0 ? $pull_guild_atts['class'] : 'crest').'" />';
  }
  return $output;
}

function wowpi_shortcode_guild_members($atts, $guild_name = '')
{
    global $wowpi_plugin_url;
    global $wowpi_options;
    $races = wowpi_getRaces();
    $classes = wowpi_getClasses();

    $output = '';
    if(strlen($guild_name)==0) $guild_name = null;
    $realm = null;

    $pull_guild_atts = shortcode_atts( array(
        'guild' => $guild_name,
        'realm' => wowpi_getRealm(),
        'ranks' => '',
        'id' => 'wowpi_guild_members',
        'class' => '',
        'order_by' => '1',
        'rows_per_page' => '25',
        'direction' => 'desc',
        'paginate' => 'true',
        'linkto' => 'simple',
        'hidecolumns' => '',
        'rank_names' => '',
        'table_style' => ''
    ), $atts );
    if($pull_guild_atts==null) {
        return false;
    }

    $datatable_settings = '';
    $paginate = wp_kses_post($pull_guild_atts[ 'paginate' ]);
    if($paginate == 'false') $datatable_settings .= ' "paging":   false,';
    $guild = wp_kses_post($pull_guild_atts[ 'guild' ]);
    $realm = wp_kses_post($pull_guild_atts[ 'realm' ]);
    $ranks = wp_kses_post($pull_guild_atts[ 'ranks' ]);
    $rank_names = wp_kses_post($pull_guild_atts[ 'rank_names' ]);
    $table_id = wp_kses_post($pull_guild_atts[ 'id' ]);
    $the_ranks = array();

    if(strlen($rank_names) > 0) {
        $rank_names_arr = explode('|',$rank_names);
        foreach($rank_names_arr as $rank) {
            $rank_arr = explode(':',$rank);
            $rank_number = trim($rank_arr[0]);
            $rank_name = '<span>'.$rank_number.'. </span>'.trim($rank_arr[1]);
            $the_ranks[$rank_number] = $rank_name;
        }
    }

    $linkto = wp_kses_post($pull_guild_atts[ 'linkto' ]);
    if(strlen($ranks)>0) {
        $ranks_arr = explode(',',$ranks);
    }

    $hide_columns = wp_kses_post($pull_guild_atts[ 'hidecolumns' ]);
    if(strlen($hide_columns)>0) {
        $hide_arr = explode(',',$hide_columns);
        $datatable_settings .= '"columnDefs": [';
        foreach($hide_arr as $column) {
            $datatable_settings .= '{"targets":'.(trim($column)-1).', "visible":false},';
        }
        $datatable_settings .= '],';
    }

    $table_style = wp_kses_post($pull_guild_atts[ 'table_style' ]);
    $table_style_arr = explode('|',$table_style);
    $table_style = array_flip($table_style_arr);

    if(array_key_exists('notop',$table_style)) {
        $datatable_settings .= '"lengthChange": false, "searching": false,';
    }

    // order by inside table
    $order_by = wp_kses_post($pull_guild_atts['order_by']);
    $order_by_arr = explode('|',$order_by);
    if(sizeof($order_by_arr)==1) $order_by_arr[1] = 'asc';
    $datatable_settings .= '"order":['.(intval($order_by_arr[0])-1).', \''.strtolower($order_by_arr[1]).'\'],';

    // number of rows per page
    $rows_per_page = wp_kses_post($pull_guild_atts[ 'rows_per_page' ]);
    $datatable_settings .= '"pageLength": '.$rows_per_page.',';

    $guild = wowpi_getGuildData($guild,$realm);

    $guild_members = $guild['members'];

    //$guild_members = sort_array_by($guild_members,);
    if(isset($guild_members) && !empty($guild_members)) {
        $output .= '<table id="'.$table_id.'" class="'.(isset($pull_guild_atts['class']) && strlen($pull_guild_atts['class'])>0 ? $pull_guild_atts['class'] : 'wowpi_guild_roster').'"';
        $output .= '>';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>'.__('Name','wowpi').'</th>';
        if(!array_key_exists('profile_picture',$table_style)) {
            $output .= '<th>'.__('Race','wowpi').'</th>';
            $output .= '<th>'.__('Class','wowpi').'</th>';
        }
        else {
            $output .= '<th>'.__('The one','wowpi').'</th>';
        }
        $output .= '<th>'.__('Level','wowpi').'</th>';
        $output .= '<th>'.__('Rank','wowpi').'</th>';
        $output .= '<th>'.__('Achievement Points','wowpi').'</th>';
        $output .= '</thead>';
        $output .= '<tbody>';
        foreach($guild_members as $member) {
            if(isset($ranks_arr) && !in_array($member['rank'],$ranks_arr)) {
                continue;
            }

            $locale = explode('_',wowpi_getLocale());
            $output .= '<tr class="rank_'.$member['rank'].' class_'.$member['class'].' spec_'.strtolower($member['spec']).' race_'.strtolower(str_replace(' ','_',$races[$member['race']]['name'])).' rank_'.$member['rank'].'">';
            if($linkto!='simple' && $linkto!='advanced') {
                $url = $linkto.strtolower(str_replace(' ','-',$member['realm'])).'/'.$member['name'];
            }
            else {
                $url = 'https://'.wowpi_getRegion().'.battle.net/wow/'.$locale[0].'/character/'.strtolower(str_replace(' ','-',$member['realm'])).'/'.$member['name'].'/'.$linkto;
            }
            $output .= '<td class="name"><a href="'.$url.'" target="_blank">'.$member['name'].'</a></td>';
            if(!array_key_exists('profile_picture',$table_style)) {
                $output .= '<td class="race race_'.strtolower(str_replace(' ','_',$races[$member['race']]['name'])).' gender_'.$member['gender'].'"><img src="'.wowpi_retrieve_image('race_'.$member['race'].'_'.$member['gender'], 'icon', 18).'" alt="'.$races[$member['race']]['name'].'" /><span> '.$races[$member['race']]['name'].'</span></td>';
                $output .= '<td class="class class_'.$member['class'].' spec_'.strtolower($member['spec']).' role_'.strtolower($member['role']).'"><img src="'.wowpi_retrieve_image('class_'.$member['class'], 'icon', 18).'" alt="'.$classes[$member['class']]['name'].'" /><span> '.$classes[$member['class']]['name'].'</span></td>';
            }
            else {
                $output .= '<td class="profile_picture">';
                //$output .= $member['thumbnail'];
                $image_file = wowpi_get_character_image(null,$member['thumbnail']);
                $upload_dir = wp_upload_dir();
                $output .= '<img src="'.$upload_dir['baseurl'].'/wowpi/character_avatar_'.$image_file.'.jpg'.'" class="character_image" />';
                $output .= '</td>';
            }
      $output .= '<td class="level">'.$member['level'].'</td>';
      $output .= '<td class="grank grank_'.$member['rank'].'">';
      if($member['rank']=='0')
      {
        $output .= '<img src="'.$wowpi_plugin_url.'assets/images/theme/icon-guildmaster.gif" alt="Guild Master" /> '.(array_key_exists($member['rank'],$the_ranks) ? $the_ranks[$member['rank']] : __('Guild Master','wowpi'));
      }
      else
      {
        $output .= (array_key_exists($member['rank'],$the_ranks) ? $the_ranks[$member['rank']] : (__('Rank','wowpi').' '.$member['rank']));
      }
      $output .= '</td>';
      $output .= '<td class="achievement_points">'.$member['achievementPoints'].' <img src="'.$wowpi_plugin_url.'assets/images/theme/shield.png" /></td>';
      $output .= '</tr>';      
    }
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '<script>jQuery(document).ready(function($){$("#'.$table_id.'").DataTable({'.$datatable_settings.'});});</script>';
  }
  /*
  echo '<pre>';
  print_r($guild_members);
  echo '</pre>';
  */
  return $output;
}