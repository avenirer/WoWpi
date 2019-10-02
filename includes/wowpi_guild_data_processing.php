<?php
function wowpi_get_guild_progression($guild_name = null, $guild_realm = null)
{
  global $wowpi_options;
  $guilds = get_option('wowpi_guilds_progress');
  //echo '<pre>'; print_r($guilds); echo '</pre>';
  $caching = $wowpi_options['guild_caching'];

  if($guild_name==null || $guild_realm == null)
  {
    $character_guild_data = wowpi_get_character('guild');
  }

  //echo '<pre>'; print_r($character_guild_data); echo '</pre>';

  if($guild_name == null && !empty($character_guild_data))
  {
    $guild_name = $character_guild_data['name'];
  }
  elseif($guild_name==null && empty($character_guild_data))
  {
    return false;
    die();
  }
  if($guild_realm==null || strlen($guild_realm)==0)
  {
    if($guild_name != $character_guild_data['name'])
    {
      if($guilds)
      {
        foreach($guilds as $realm=>$guilds_realm)
        {
          if(array_key_exists($guild_name,$guilds_realm))
          {
            $guild_realm = $realm;
          }
        }
      }
    }
    else
    {
      $guild_realm = $character_guild_data['realm'];
    }
  }
  if($guild_realm==null || strlen($guild_realm)==0)
  {
    $guild_realm = $wowpi_options['realm'];
  }

  if(!isset($region))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : $region;
  }

  if(!isset($locale))
  {
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : $locale;
    $locale = rawurlencode($locale);
  }

  if((isset($guild_name) && isset($guild_realm) && isset($guilds[$guild_realm][$guild_name]) && isset($guilds[$guild_realm][$guild_name]['data'])) && (intval($guilds[$guild_realm][$guild_name]['last_update']) + (intval($caching)*60*60) > intval(current_time('timestamp'))))
  {
    $guild_data = $guilds[$guild_realm][$guild_name]['data'];
  }
  else
  {

    $guild_achievements = wowpi_get_guild('achievements', $guild_name, $guild_realm);
    $general_guild_achievements = wowpi_getGuildAchievements();

    /*
    echo '<pre>';
    print_r($general_guild_achievements);
    echo '</pre>';
    */

    $dungeons_raids = array(
        'Battle for Azeroth' => array(
            'dungeon' => array(12999,13000,13001,13002,13003,13004,13005,13006,13007,13008),
            'raid' => array(12537,13319,13320,13420,13010,13321,13421)
        ),
        'Legion' => array(
            'dungeon' =>array(11226,11225,10865,10864,10863,10862,10861,10860,10859,10858,10857,10856),
            'raid' => array(11239,11238,10868,10866)
        ),
        'Draenor' => array(
            'dungeon' =>array(9376,9375,9374,9373,9372,9371,9370,9369),
            'raid' => array(10176,10175,9424,9421,9420,9418,9417,9416)
        ),
        'Pandaria' => array(
            'dungeon' =>array(6772,6771,6770,6769,6768,6767,6766,6666,6764),
            'raid' => array(8789,8511,8510,8140,8139,8138,8137,6709,6708,6677,6676,6675,6670,6669,6668,)
        ),
        'Cataclysm' => array(
            'dungeon' =>array(6122,6121,6120,5771,5770,5142,5141,5140,5139,5138,5137,5136,5135,5134),
            'raid' => array(6125,6123,5984,5983,5464,5463,5462,5461,5425,4987,4986,4985)
        ),
        'Lich King' => array(
            'raid' => array(5023,5022,5021,5020,5019,5018,5017,5016),
            'dungeon'=>array(5114,5113,5112,5111,5106,5105,5104,5103,5102,5101,5100,5099,5098,5097,5096,5095)
        ),
        'Burning Crusade' => array(5092,5091,5090,5089,5088,5087,5086,5084,5082,5081,5080,5079,5078,5077,5076,5073,5069,5075,5074,5072,5071,5070,5068,5067,),
        'Classic' => array(7434,5059,5058,5057,5056,5055,5054,5053,5052,5051,5050,5049,5048,5047,5046,5045,5044,5043,5042,5041,5040,5039,5038,5037)
    );

    foreach($dungeons_raids as $expansion => $dungeons)
    {
      if(array_key_exists('dungeon',$dungeons))
      {
        foreach($dungeons as $type=>$instances)
        {
          $finished = 0;
          $not_finished = 0;
          foreach($instances as $instance)
          {
            if(array_key_exists($instance,$guild_achievements))
            {
              $finished++;
              $completed = '1';
            }
            else
            {
              $not_finished++;
              $completed = '0';
            }
            $progress[$expansion][$type]['expanded'][$instance] = array('completed' => $completed, 'details'=>$general_guild_achievements['achievements'][$instance]);
          }
          $progress[$expansion][$type]['summary'] = array('finished'=>$finished,'not_finished'=>$not_finished);
        }
      }
      else
      {
        foreach($dungeons as $instance)
        {
          if(array_key_exists($instance,$guild_achievements))
          {
            $finished++;
            $completed = '1';
          }
          else
          {
            $not_finished++;
            $completed = '0';
          }
          $progress[$expansion]['expanded'][$instance] = array('completed'=>$completed, 'details'=>$general_guild_achievements['achievements'][$instance]);
        }
        $progress[$expansion]['summary'] = array('finished'=>$finished,'not_finished'=>$not_finished);
      }

    }
    $guilds[$guild_realm][$guild_name]['data'] = $progress;
    $guilds[$guild_realm][$guild_name]['last_update'] = current_time('timestamp');
    /*
    echo '<pre>';
    print_r($guilds);
    echo '</pre>';
    exit;
    */
    update_option('wowpi_guilds_progress', $guilds);
  }
  return $guilds[$guild_realm][$guild_name]['data'];
}
function wowpi_get_guild_tabard($guild_name = null, $realm = null)
{
  global $wowpi_plugin_dir;
  global $wowpi_plugin_url;
  global $wowpi_options;
  $guild = wowpi_get_guild('profile',$guild_name,$realm);
  //echo '<pre>'; print_r($guild); echo '</pre>';

  if($guild['emblem']['icon']>0)
  {
    $image_name = '';

    $faction = ($guild['side'] == 1) ? 'Horde' : 'Alliance';
    $image_name .= $faction;

    $background = substr($guild['emblem']['background'],-6);
    $image_name .= '_'.$background;

    $icon = ($guild['emblem']['icon']<10) ? '0'.(int) $guild['emblem']['icon'] : $guild['emblem']['icon'];
    $image_name .= '_'.$icon;

    $icon_color = substr($guild['emblem']['icon_color'],-6);
    $image_name .= '_'.$icon_color;

    $border = ($guild['emblem']['border']<10) ? '0'.(int) $guild['emblem']['border'] : $guild['emblem']['border'];
    $image_name .= '_'.$border;

    $border_color = substr($guild['emblem']['border_color'],-6);
    $image_name .= '_'.$border_color;

    $image_name .= '.png';

    $upload_dir = wp_upload_dir();

    $wowpi_upload_dir = $upload_dir['basedir'].'/wowpi/';
    //let's make sure we have the image directory
    if ( ! file_exists( $wowpi_upload_dir ) ) {
      wp_mkdir_p( $wowpi_upload_dir );
    }


    //echo $guild_tabard_uri;
    //return true;
    if(!file_exists($wowpi_upload_dir.$image_name))
    {
      $guild_tabard_uri = 'http://wow-hunter.ro/tabard-creator/tabard.php?side='.strtolower($faction).'&backgroundColor='.$background.'&icon='.$icon.'&iconColor='.$icon_color.'border='.$border.'&borderColor='.$border_color.'&asImage=true';
      //$guild_tabard_uri = '//tabard.gnomeregan.info/tabard.php?icon=emblem_'.$icon.'&border=border_'.$border.'&iconcolor='.$icon_color.'&bgcolor=&bordercolor='.$border_color.'&faction='.$faction;

      $tabard_image_get = file_get_contents($guild_tabard_uri);
      file_put_contents($wowpi_upload_dir.$image_name,$tabard_image_get);
    }
    return $upload_dir['baseurl'].'/wowpi/'.$image_name;
  }
}

function wowpi_get_guild($field = null, $guild_name = null, $guild_realm = null, $region = null, $locale = null, $limit = null)
{
  global $wowpi_options;
  $guilds = get_option('wowpi_guilds');
  //echo '<pre>'; print_r($guilds); echo '</pre>';
  $caching = $wowpi_options['guild_caching'];

  if($guild_name==null || $guild_realm == null)
  {
    $character_guild_data = wowpi_get_character('guild');
  }

  //echo '<pre>'; print_r($character_guild_data); echo '</pre>';

  if($guild_name == null && !empty($character_guild_data))
  {
    $guild_name = $character_guild_data['name'];
  }
  elseif($guild_name==null && empty($character_guild_data))
  {
    return false;
    die();
  }
  if(!isset($guild_name))
  {
    return false;
  }
  if($guild_realm==null || strlen($guild_realm)==0)
  {
    if($guild_name != $character_guild_data['name'])
    {
      if($guilds)
      {
        foreach($guilds as $realm=>$guilds_realm)
        {
          if(array_key_exists($guild_name,$guilds_realm))
          {
            $guild_realm = $realm;
          }
        }
      }
    }
    else
    {
      $guild_realm = $character_guild_data['realm'];
    }
  }
  if($guild_realm==null || strlen($guild_realm)==0)
  {
    $guild_realm = $wowpi_options['realm'];
  }

  if($field==null)
  {
    $field = 'achievements';
  }

  if(!isset($region))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : $region;
  }

  if(!isset($locale))
  {
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : $locale;
    $locale = rawurlencode($locale);
  }

  //echo '<pre>';print_r($guilds);echo '</pre>';


  if((isset($guild_name) && isset($guild_realm) && isset($guilds[$guild_realm][$guild_name]) && isset($guilds[$guild_realm][$guild_name]['data'][$field])) && (intval($guilds[$guild_realm][$guild_name]['last_update']) + (intval($caching)*60*60) > intval(current_time('timestamp'))))
  {
    $guild_data = $guilds[$guild_realm][$guild_name]['data'];
  }
  else
  {
    wowpi_call_api_guilds($guilds, $guild_name, $field, $guild_realm, $region, $locale);
    $guilds = get_option('wowpi_guilds');
    //echo '<pre>';print_r($guilds);echo '</pre>';
    if(isset($guilds[$guild_realm][$guild_name]))
    {
      $guild_data = $guilds[$guild_realm][$guild_name]['data'];
    }
    else
    {
      echo 'The guild doesn\'t exist. Make sure you got the name right...';
    }
  }
  $data = $guild_data[$field];
  if(empty($data))
  {
    return false;
  }
  return (array) $data;
}

function wowpi_call_api_guilds($guilds, $guild_name, $field, $realm = null, $region = null, $locale = null)
{
  global $wowpi_options;
  if(!isset($realm))
  {
    $realm = $wowpi_options['realm'];
  }
  if(!isset($region))
  {
    $realm = $wowpi_options['region'];
  }
  if(!isset($locale))
  {
    $locale = $wowpi_options['locale'];
  }
  $retrieve_field = ($field=='profile') ? 'achievements' : $field;

  $decoded = wowpi_get_curl('guild', $guild_name, $retrieve_field, $realm, $region, $locale);
  if($field=='profile')
  {
    if(isset($decoded->status) && ($decoded->status=='nok'))
    {
      $guild = array();
    }
    else
    {
      $guild = array(
        'name' => $decoded->name,
        'realm' => $decoded->realm,
        'battlegroup' => $decoded->battlegroup,
        'level' => $decoded->level,
        'side' => $decoded->side,
        'achievement_points' => $decoded->achievementPoints,
        'emblem' => array(
          'icon'=>$decoded->emblem->icon,
          'icon_color'=>$decoded->emblem->iconColor,
          'border'=>$decoded->emblem->border,
          'border_color'=>$decoded->emblem->borderColor,
          'background'=>$decoded->emblem->backgroundColor
        )
      );
    }
    $guilds[$decoded->realm][$decoded->name]['data']['profile'] = $guild;
  }
  elseif($field=='members')
  {
    if(isset($decoded->status) && ($decoded->status=='nok'))
    {
      $members = array();
    }
    else
    {
      $members = array();
      foreach($decoded->members as $member)
      {
        $spec = array('name'=>'','role'=>'','background_image'=>'','icon'=>'','description'=>'','order'=>'');
        if(isset($member->character->realm) && isset($member->character->name))
        {
          if(isset($member->character->spec))
          {
            $spec = array(
              'name'=>$member->character->spec->name,
              'role'=>$member->character->spec->role,
              'background_image'=>$member->character->spec->backgroundImage,
              'icon'=>$member->character->spec->icon,
              'description'=>$member->character->spec->description,
              'order'=>$member->character->spec->order
            );
          }
          //$image_code = wowpi_get_character_image($region,$member->character->thumbnail);
          $characterName = $member->character->name;
          if(array_key_exists($characterName,$members)) {
              $characterName = $characterName.' ('.$member->character->realm.')';
          }
          $members[$characterName] = array(
            'name'=>$member->character->name,
            'rank'=>$member->rank,
            'realm'=>$member->character->realm,
            'class'=>$member->character->class,
            'race'=>$member->character->race,
            'gender'=>$member->character->gender,
            'level'=>$member->character->level,
            'achievement_points'=>$member->character->achievementPoints,
            'thumbnail'=>$member->character->thumbnail,
            'spec'=> $spec
          );
        }
      }
    }
    //$the_members = sort_array_by($members,'rank');
    $guilds[$decoded->realm][$decoded->name]['data']['members'] = $members;

    //echo '<pre>';print_r($guilds);echo '</pre>';
  }
  elseif($field=='achievements')
  {
    if(isset($decoded->status) && ($decoded->status=='nok'))
    {
      $guild_achievements = array();
    }
    else
    {
      foreach($decoded->achievements->achievementsCompleted as $key => $achievement_id)
      {
        $timestamp = $decoded->achievements->achievementsCompletedTimestamp[$key]>0 ? substr($decoded->achievements->achievementsCompletedTimestamp[$key], 0,-3) : 0;
        $guild_achievements[$achievement_id] = array('id'=>$achievement_id, 'completed'=>$timestamp);
      }
    }
    //echo '<pre>';print_r($guild_achievements);echo '</pre>';
    $guilds[$decoded->realm][$decoded->name]['data']['achievements'] = $guild_achievements;
  }
  else
  {
    $guilds[$decoded->realm][$decoded->name]['data'][$field] = $decoded->{$field};
  }
  $guilds[$decoded->realm][$decoded->name]['last_update'] = current_time('timestamp');
  update_option('wowpi_guilds', $guilds);
}
