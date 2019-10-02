<?php
function wowpi_get_character($field = null, $character_name = null, $realm = null, $region = null, $locale = null)
{
  global $wowpi_options;
  $characters = get_option('wowpi_characters');
  $caching = $wowpi_options['character_caching'];
  
  
  // we retrieve the default character name if no name was given
  if(!isset($character_name) && isset($wowpi_options['character_name']) && strlen($wowpi_options['character_name'])>0)
  {
    $character_name = $wowpi_options['character_name'];
  }
  
  //if no field was asked for, we get the "profile" field
  if(!isset($field))
  {
    $field = 'profile';
  }
  
  
  // if no realm is given we get the default realm
  if(!isset($realm))
  {
    $realm = (isset($wowpi_options['realm']) && strlen($wowpi_options['realm'])>0) ? $wowpi_options['realm'] : $realm;
  }
  
  // if no region was given we get the default region
  if(!isset($region))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : $region;
  }
  
  // if no locale was given we get the default locale
  if(!isset($locale))
  {
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : $locale;
    $locale = rawurlencode($locale);
  }
  
  // we try to find out if the cache is overdue, if it's not, we retrieve the character data from database


  if(isset($character_name) && isset($realm) && isset($characters[$realm][$character_name]) && isset($characters[$realm][$character_name]['data'][$field]) && ((intval($characters[$realm][$character_name]['last_update']) + (intval($caching)*60*60)) > current_time('timestamp')))
  {
    $character_data = $characters[$realm][$character_name]['data'];
  }
  
  //else we call the service
  else
  {
    wowpi_call_api_character($characters, $character_name, $realm, $region, $locale);
    $characters = get_option('wowpi_characters');
    $character_data = $characters[$realm][$character_name]['data'];
  }

  $character_data = (array) $character_data;
  if(array_key_exists($field,$character_data)) {
      return $character_data[$field];
  }
  else {
      return false;
  }
}

function wowpi_call_api_character($characters, $character_name, $realm = null, $region = null, $locale = null)
{
  
  // get ALL the fields
  $fields_arr = array(
    'achievements',
    //'feed',
    'guild',
    'items',
    'professions',
    'progression',
    'pvp',
    //'statistics',
    //'stats',
    'talents',
    //'audit',
    //'hunterPets',
    //'mounts',
    //'pets',
    //'petSlots',
    //'quests',
    //'reputation',
    'titles'
  );
  $fields = implode(',',$fields_arr);
  $fields = urlencode($fields);
  
  // retrieve the data from battle.net
  $decoded = wowpi_get_curl('character', $character_name, $fields, $realm, $region, $locale);
  if(!isset($decoded))
  {
    return false;
  }
  
  // let's create the character data
  $last_update = current_time('timestamp');
  $character_data = array(
    'data'=>array(
        'profile'=>array('last_modified' => $decoded->lastModified,
                         'name' => $decoded->name,
                         'realm' => $decoded->realm,
                         'battlegroup' => $decoded->battlegroup,
                         'class' => $decoded->class,
                         'calc_class' => $decoded->calcClass,
                         'race' => $decoded->race,
                         'gender' => $decoded->gender,
                         'level' => $decoded->level,
                         'achievement_points' => $decoded->achievementPoints,
                         'faction' => $decoded->faction)),
    'last_update'=>$last_update
  );
  
  //
  if(isset($decoded->totalHonorableKills))
  {
    $character_data['data']['profile']['honorable_kills'] = $decoded->totalHonorableKills;
  }
  //character image
  if(isset($decoded->thumbnail))
  {
    $character_thumb = $decoded->thumbnail;
    $image_code = wowpi_get_character_image($region,$character_thumb);
    $character_data['data']['profile']['thumbnail'] = $image_code;
  }

  //character guild
  if(isset($decoded->guild))
  {
    $character_data['data']['guild'] = array(
      'name' => $decoded->guild->name,
      'realm' => $decoded->guild->realm,
      'battlegroup' => $decoded->guild->battlegroup);
  }
  
  // the... activity
  /*
  if(isset($decoded->feed))
  {
    //$character_data['data']['activity'] = $decoded->feed;
  }*/
  
  //the... gear
  if(isset($decoded->items))
  {
    $character_data['data']['gear'] = $decoded->items;
  }
  
  //the... audit
  /*
  if(isset($decoded->audit))
  {
    //$character_data['data']['audit'] = $decoded->audit;
  }
  
  //the... stats
  if(isset($decoded->stats))
  {
    //$character_data['data']['stats'] = $decoded->stats;
  }*/
  
  //the... talents
  if(isset($decoded->talents))
  {
    $character_data['data']['talents'] = array();
    
    global $wowpi_plugin_dir;
    $spec_ids_json = file_get_contents($wowpi_plugin_dir.'/includes/spec_ids.json');
    $spec_ids = json_decode($spec_ids_json,true);
    //echo '<pre>';print_r($spec_ids);echo '</pre>';
    //echo $class_id;
    //echo '<pre>'; print_r($decoded->talents); echo '</pre>';

    if(!empty($decoded->talents))
    {
      foreach($decoded->talents as $spec)
      {
        if(isset($spec->talents) && !empty($spec->talents))
        {
          //echo '<pre>'; print_r($talent); echo '</pre>';
          $the_talents = array();
          foreach($spec->talents as $talent)
          {
            $the_talents[$talent->tier] = array('id'=>$talent->spell->id, 'name'=>$talent->spell->name, 'icon'=>$talent->spell->icon,'description'=>$talent->spell->description);
          }
          ksort($the_talents);

          $the_spec = $spec->spec;
          $calc_spec = $spec->calcSpec;
          $spec_combo = $character_data['data']['profile']['calc_class'].$calc_spec;
          $spec_id = (isset($spec_ids[$spec_combo])) ? $spec_ids[$spec_combo] : '0';
          //echo $spec_id;
          //echo '<pre>'; print_r($spec); echo '</pre>';
          $selected = '0';
          if(isset($spec->selected))
          {
            $selected = '1';
            $current_spec = $spec_id;
          }

          $character_data['data']['talents'][] = array('name'=>$the_spec->name,'role'=>$the_spec->role,'background'=>$the_spec->backgroundImage,'icon'=>$the_spec->icon,'description'=>$the_spec->description, 'selected'=>$selected, 'talents'=>$the_talents, 'spec_id' => $spec_id);
        }
      }
    }
    $character_data['data']['talents'] = sortArrayBy($character_data['data']['talents'],'selected', $direction = 'DESC');
    $character_data['data']['profile']['spec_id'] = $current_spec;
    //echo '<pre>';print_r($character_data['data']['talents']);echo '</pre>';
    
  }
  
  //the... professions
  if(isset($decoded->professions))
  {
    $character_data['data']['professions'] = $decoded->professions;
  }
  
  //the... progression
  if(isset($decoded->progression))
  {
    $character_data['data']['progression'] = $decoded->progression;
  }
  
  //the... pvp
  if(isset($decoded->pvp))
  {
    $character_data['data']['pvp'] = $decoded->pvp;
  }
  
  //the... reputation
  /*
  if(isset($decoded->reputation))
  {
    $character_data['data']['reputation'] = $decoded->reputation;
  }*/
  
  //the... achievements
  if(isset($decoded->achievements))
  {
    $achievements_obj = $decoded->achievements;
    $achievements_arr = array();
    $achievement_ids = $achievements_obj->achievementsCompleted;
    $achievement_timestamps = $achievements_obj->achievementsCompletedTimestamp;
    if(sizeof($achievement_ids)>0)
    {
      foreach($achievement_ids as $key=>$id)
      {
        $timestamp = $achievement_timestamps[$key]>0 ? substr($achievement_timestamps[$key], 0,-3) : 0;
        $achievements_arr[] = array('id'=>$id,'completed'=>$timestamp);
      }
    }
    $achievements_arr = sortArrayBy($achievements_arr,'completed', $direction = 'DESC');
    //$achievements_arr = array_slice($achievements_arr, 0, 20);
    
    
    //echo '<pre>';print_r($achievements_arr);echo '</pre>';
    
    $all_achievements = wowpi_getCharacterAchievements();
    //var_dump($all_achievements);
    
    $character_achievements = array();
    $i = 1;
    foreach($achievements_arr as $achievement)
    {
      if($i==21) break;
      if(array_key_exists($achievement['id'],$all_achievements['achievements']))
      {
        $title = $all_achievements['achievements'][$achievement['id']]['t'];
        //$description = $all_achievements['achievements'][$achievement['id']]['d'];
        $points = $all_achievements['achievements'][$achievement['id']]['p'];
        $icon = $all_achievements['achievements'][$achievement['id']]['i'];
        $character_achievements[$achievement['id']] = array('id'=>$achievement['id'],'title'=>$title,'points'=>$points,'icon'=>$icon,'completed'=>$achievement['completed']);
        $i++;
      }
    }
    
    $character_data['data']['achievements'] = $character_achievements;
    
    //echo '<pre>';print_r($reversed);echo '</pre>';
  }
  
  //the... quests
  //unset($decoded->quests);
  
  //the... statistics
  /*if(isset($decoded->statistics))
  {
    //$character_data['data']['statistics'] = $decoded->statistics;
  } */ 
  
  // character titles
  if(isset($decoded->titles))
  {
    $titles_obj = $decoded->titles;
    $titles_arr = array();
    if(sizeof($titles_obj)>0)
    {
      foreach($titles_obj as $title)
      {
        $titles_arr['gained'][$title->id] = array(
          'name'=>$title->name
        );
        if(isset($title->selected))
        {
          $character_data['data']['profile']['title'] = array('id'=>$title->id,'name'=>$title->name);
        }
      }
    }
    $character_data['data']['titles'] = $titles_arr;
  }
  
  //the... hunter pets???
  //unset($decoded->hunterPets);
  
  //the... mounts?
  //unset($decoded->mounts);
  
  //the... pets?
  //unset($decoded->pets);
  
  //the...pet slots?
  //unset($decoded->petSlots);
  
  $characters[$realm][$character_name] = $character_data;
  update_option('wowpi_characters', $characters);
}