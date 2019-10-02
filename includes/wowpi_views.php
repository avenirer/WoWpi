<?php

function wowpi_show_guild($with = null,$guild_name = null, $realm = null)
{
  global $wowpi_plugin_dir;
  global $wowpi_plugin_url;
  global $wowpi_options;

  $guild = wowpi_get_guild(null,$guild_name,$realm);
  if($guild===false)
  {
    echo '<!--Guild doesn\'t exist. Sorry...-->';
    return false;
    die();
  }

  //echo '<pre>'; print_r($guild); echo '</pre>';

  $guild_members = wowpi_get_guild('members',$guild_name,$realm);

  $total_members = sizeof($guild_members);

  echo '<div class="wowpi_guild_container">';
  echo '<div class="guild_tabard side_'.strtolower($guild['side']).'">';
  $tabard = wowpi_get_guild_tabard($guild_name,$realm);
  echo '<img src="'.$tabard.'" class="crest" />';

  echo '</div>';
  echo '<div class="guild_name">';
  echo $guild['name'];
  echo '</div>';
  echo '<div class="guild_details">';
  echo ucfirst((($guild['side']=='1') ? 'horde' : 'alliance')).' Guild, '.$guild['realm'].', '.$total_members.' members';
  echo '</div>';
  echo '</div>';
  echo '</div>';
  if(isset($with) && !empty($with))
  {
    foreach($with as $content)
    {
      if($content!='nothing' && function_exists('wowpi_show_guild_'.$content))
      {
        $function = 'wowpi_show_guild_'.$content;
        $function($character_name,$realm);
      }
    }
  }
}

function wowpi_show_character($with = array(),$characterName=null,$realm=null,$region=null,$locale=null)
{
    $character = wowpi_getCharacterData($characterName,$realm,$region,$locale);
    if(!isset($character) || empty($character)) {
        echo 'Could not retrieve data for character '.$characterName;
        return ;
    }

    $races = wowpi_getRaces();
    $classes = wowpi_getClasses();



  global $wowpi_options;
  global $wowpi_plugin_dir;
  global $wowpi_plugin_url;

  $guild = $character['guild'];

  $character_name = $character['profile']['name'];
  if(isset($character['titles']) && isset($character['titles']['currentTitle']) && $character['titles']['currentTitle'] != 0)
  {
    $main_title = $character['titles']['titles'][$character['titles']['currentTitle']];
  }

  $realm = $character['profile']['realm'];
  $battlegroup = $character['profile']['battlegroup'];
  //var_dump($classes);
  //var_dump($character['profile']);
  $class = $classes[$character['profile']['class']]['name'];
  $race = $races[$character['profile']['race']]['name'];
  $side = $races[$character['profile']['race']]['side'];
  $gender = $character['profile']['gender'];
  $level = $character['profile']['level'];
  $thumbnail = $character['profile']['thumbnail'];
  $calc_class = $character['profile']['calcClass'];
  $faction = $character['profile']['faction'];
  //$wowpi_guild = array('name' => $guild_data->name, 'members' => $guild_data->members, 'emblem' => $guild_data->emblem, 'icon' => $guild_data->icon, 'icon_color' => $guild_data->iconColor, 'border' => $guild_data->border, 'border_color' => $guild_data->borderColor, 'background' => $guild_data->backgroundColor);

  echo '<div class="wowpi_character_container '.strtolower($side).' '.strtolower($race).' '.str_replace(' ','_',strtolower($class)).'"';
  echo (get_option('styling') == 'wowpi_faction' ? ' style="background-image: url(\''.$wowpi_plugin_url.'assets/images/theme/wowpi_'.strtolower($side).'.jpg\')"' : '');
  echo '>';

  /*
  $class_image = 'assets/images/symbols/class-'.str_replace(' ','',strtolower($class)).'.png';
  if(file_exists($wowpi_plugin_dir.$class_image))
  {
    echo '<img src="'.$wowpi_plugin_url.$class_image.'" class="class_image" />';
  }
  */
  ?>
  <div class="module character">
    <div class="character_name">
      <?php echo $character_name;?>
    </div>
    <?php
    if(!empty($guild) && strlen($guild['name'])>0)
    {
      ?>
    <div class="character_guild">
      <?php
      echo '<div class="character_guild">'.__('of the ','wowpi').' '.$guild['name'].'</div>';
    ?>
    </div>
    <?php
    }
    if(strlen($thumbnail)>0)
    {
        $file_name = wowpi_get_character_image($region, $thumbnail);
        $upload_dir = wp_upload_dir();
        $image = $upload_dir['basedir'].'/wowpi/character_avatar_'.$file_name.'.jpg';
        if(file_exists($image)) {
            echo '<img src="'.$upload_dir['baseurl'].'/wowpi/character_avatar_'.$file_name.'.jpg'.'" class="character_image" />';
        }
    }
    if(isset($main_title))
    {
    ?>
    <div class="character_title">
      <?php echo __('Also known as','wowpi');?><br /><span><?php printf($main_title,$character_name);?></span><br />
      <?php printf( __('Level %1$s %2$s %3$s','wowpi'), $level, $race, $class);?>
    </div>
    <?php
    }
  ?>
  </div>
  <?php
  if(!empty($with))
  {
    foreach($with as $content)
    {
      if($content!='nothing')
      {
        $function = 'wowpi_show_'.$content;
        echo $function($character_name, $realm);
      }
    }
  }
  ?>
   <div class="realm"><?php echo $battlegroup.' / '.$realm;?></div>
</div>
<?php
}

function wowpi_show_achievement_points($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    $achievementPoints = $character['profile']['achievementPoints'];
    $allAchievements = wowpi_getCharacterAchievements();

    $output = '<div class="module achievements">';
    $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('achievement_points').'</div>' : '';
    $output .= '<div class="total">'.$achievementPoints.'</div>';
    $output .= '<div class="from">'.__('out of','wowpi').' '.$allAchievements['total_points'].'</div>';
    return $output;
}

function wowpi_show_achievement_feed($characterName, $realm, $with_title = true, $limit = 10)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    $allAchievements = wowpi_getCharacterAchievements();

    $characterAchievements = $character['achievements'];

    $output = '';
    if(!empty($characterAchievements)) {
        $output .= '<div class="module achievement_feed">';
        $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('achievement_feed').'</div>' : '';
        $output .= '<ul>';

        foreach($characterAchievements as $achievement) {
            //echo '<pre>';print_r($achievement);echo '</pre>';
            if($limit == 0) {
                break;
            }

            if(array_key_exists($achievement['id'], $allAchievements['achievements'])) {
                $image = $allAchievements['achievements'][$achievement['id']]['i'] ? wowpi_retrieve_image($allAchievements['achievements'][$achievement['id']]['i'], 'icon', 18) : false;
                $title = $allAchievements['achievements'][$achievement['id']]['t'] ? $allAchievements['achievements'][$achievement['id']]['t'] : false;
                $points = $allAchievements['achievements'][$achievement['id']]['p'] ? $allAchievements['achievements'][$achievement['id']]['p'] : false;

                if ($title && $points && $image) {
                    $output .= '<li style="background-image: url(\'' . $image . '\');"> ' . date('d.m', $achievement['completed']) . ' - ' . wowpi_get_tooltip($achievement['id'], 'achievement', '<strong>' . $title . '</strong>') . ' <span class="points">' . $points . '</span></li>';
                    $limit--;
                }
            }
        }
        $output .= '</ul>';
        $output .= '</div>';
    }

    return $output;
}

function wowpi_show_kills($characterName, $realm, $with_title = true)
{
    $output = '<div class="module kills">';
    $character = wowpi_getCharacterData($characterName,$realm);
    $honorableKills = $character['profile']['honorableKills'];
    $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('kills').'</div>' : '';
    $output .= '<div class="total">'.$honorableKills.'</div>';
    $output .= '</div>';

    return $output;
}

function wowpi_show_professions($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);

    $primaryProfessions = $character['professions']['primary'];
    $secondaryProfessions = $character['professions']['secondary'];

    $output = '';

    if(!empty($primaryProfessions) || !empty($secondaryProfessions)) {
        $output .= '<div class="module professions">';
        if(!empty($primaryProfessions)) {
            $output .= '<div class="primary">';
            $output .= ($with_title==true) ? '<div class="title">'.__('Primary professions:','wowpi').'</div>' : '';
            foreach($primaryProfessions as $profession) {
                $output .= '<img src="'.wowpi_retrieve_image($profession['icon'], 'icon', 18).'" alt="'.$profession['name'].'" /> '.$profession['name'].'('.$profession['rank'].' out of '.$profession['maxRank'].') ';
            }
            $output .= '</div>';
        }

        if(!empty($secondaryProfessions)) {
            $output .= '<div class="secondary">';
            $output .= ($with_title==true) ? '<div class="title">'.__('Secondary professions:','wowpi').'</div>' : '';
            foreach($secondaryProfessions as $profession) {
                $output .= '<img src="'.wowpi_retrieve_image($profession['icon'], 'icon', 18).'" alt="'.$profession['name'].'" /> '.$profession['name'].'('.$profession['rank'].' out of '.$profession['maxRank'].') ';
            }
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    return $output;
}

function wowpi_show_ilvl($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    $gear = $character['items'];
    $output = '<div class="module gear">';
    $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('ilvl').'</div>' : '';
    $output .= '<div class="itemlvl_total">'.$gear['averageItemLevel'].'</div>';
    $output .= '</div>';
    return $output;
}


function wowpi_show_gear($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    $gear = $character['items']['items'];

    $output = '<div class="module gear">';
    $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('gear').'</div>' : '';
    $items = array('head','neck','shoulder','back','chest','shirt','tabard','wrist','hands','waist','legs','feet','finger1','finger2','trinket1','trinket2','mainHand','offHand');
    foreach($items as $item) {
        if(isset($gear[$item])) {
            $the_item = $gear[$item];
            $advanced = array();
            $advanced['lvl'] = $character['profile']['level'];
            $advanced['itemLevel'] = $gear[$item]['itemLevel'];
            if(isset($the_item['bonuses'])) $advanced['bonus'] = implode(':',$the_item['bonuses']);
            if(isset($the_item['armorSet'])) $advanced['pcs'] = implode(':',$the_item['armorSet']);
            $relics = array();
            if(isset($the_item['relics'])) {
                foreach($the_item['relics'] as $relic) {
                    $relics[] = $relic['itemId'];
                }
            }

            elseif(isset($the_item['tooltipParams']) && isset($the_item['tooltipParams']['gem0'])) {
                $relics[] = $the_item['tooltipParams']['gem0'];
            }

            if(!empty($relics)) {
                $advanced['gems'] = implode(':',$relics);
            }

            $output .= wowpi_get_tooltip($the_item['id'],'item','<img src="'.wowpi_retrieve_image($the_item['icon']).'" class="q'.$the_item['quality'].'">',$advanced,'class="q'.$the_item['quality'].'" title="'.$the_item['name'].'"');
        }
    }

    $output .= '</div>';
    return $output;
}

// MUST BE ELIMINATED? NO MORE ARTIFACT WEAPON
function wowpi_show_artifact_weapon($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    global $wowpi_plugin_dir;
    $specId = $character['talents']['currentSpec'];

    $traits_spells_json = file_get_contents($wowpi_plugin_dir.'/includes/artifact_traits_spells.json');
    $output = '';
    $traits_spells = json_decode($traits_spells_json,true);

    $gear = $character['items']['items'];

    $weapon = $gear['mainHand'];

    if(isset($weapon) && !empty($weapon))
    {
        $artifact_traits = $weapon['artifactTraits'];
        if((!isset($artifact_traits) || empty($artifact_traits)) && isset($gear['offHand']))
        {
            $weapon = $gear['offHand'];
            $artifact_traits = $weapon['artifactTraits'];
        }

        if(isset($artifact_traits) && !empty($artifact_traits)) {
            $output .= '<div class="module artifact_weapon">';
            $output .= '<div class="artifact_traits">';
            $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('artifact_traits').'</div>' : '';
            $artifact_id = $weapon['id'];
            $artifact_weapon = wowpi_getArtifact($artifact_id);

            foreach($artifact_traits as $trait) {
                $trait_id = $trait['id'];
                $rank = $trait['rank']-1;

                if(isset($trait_id) && isset($rank) && isset($traits_spells[$trait_id][$rank])) {
                    $spell_id = $traits_spells[$trait_id][$rank];
                    $spell = wowpi_getSpell($spell_id);
            $link_name = '<img src="' . wowpi_retrieve_image($spell['icon']) . '" alt="' . $spell['name'] . '" />';
            $output .= wowpi_get_tooltip($spell['id'], 'spell', $link_name, array('rank' => $rank), 'class="_q' . $rank . '"');
        }
      }
      $output .= '</div>';
      $output .= '<div class="artifact_relics">';
      $artifact_relics = $weapon['relics'];
      if(isset($artifact_relics) && !empty($artifact_relics))
      {
        $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('artifact_relics').'</div>' : '';
        foreach($artifact_relics as $relic)
        {
          $item = wowpi_getItem($relic['itemId']);
          $output .= wowpi_get_tooltip($relic['itemId'],'item','<img src="'.wowpi_retrieve_image($item['icon']).'" alt="'.$item['name'].'" />',array('spec'=>$specId));
        }
      }
      $output .= '</div>';
      $output .= '</div>';
    }
  }

  return $output;
}

function wowpi_show_activity($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
  $character_activity = $character['feed'];
  if(!empty($character_activity))
  {
    echo '<ul>';
    foreach($character_activity as $activity)
    {
      echo '<li>';
      if($activity['type']=='BOSSKILL')
      {
        echo '<img src="'.wowpi_retrieve_image($activity['achievement']['icon']).'" class="q">';
      }
      if($activity['type']=='ACHIEVEMENT')
      {
        echo '<img src="'.wowpi_retrieve_image($activity['achievement']['icon']).'" class="q">';
      }
      if($activity['type']=='LOOT')
      {
        echo '<img src="'.wowpi_retrieve_image($activity['itemId']).'" class="q">[icondb='.$activity['itemId'].']';
      }
      echo '</li>';
    }
    echo '</ul>';
  }
}

function wowpi_show_talents($characterName, $realm, $with_title = true)
{
    $character = wowpi_getCharacterData($characterName,$realm);
    $character_talents = $character['talents']['talents'];
  //echo '<pre>';print_r($character_talents).'</pre>';


  $output = '';
  $output .= '<div class="module talents">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('talents').'</div>' : '';
  foreach($character_talents as $spec)
  {
    $output .= '<div class="spec'.(($spec['selected']=='1') ? ' selected' : '').'">';
    $output .= '<div class="spec_name">'.$spec['name'].'</div>';
    $output .= '<div class="talents_enum">';
    foreach($spec['talents'] as $talent)
    {
      $output .= wowpi_get_tooltip($talent['id'],'spell','<img src="'.wowpi_retrieve_image($talent['icon']).'" />',array(),'title="'.$talent['name'].'"');
    }
    $output .= '</div>';
    $output .= '</div>';
  }
  $output .= '</div>';
  return $output;
}

function wowpi_translate_title($of_what)
{
  $returned = '';
  switch($of_what) {
    case 'achievement_points' :
      $returned = __('Achievement points:','wowpi');
      break;
    case 'achievement_feed' :
      $returned = __('Latest achievements:','wowpi');
      break;
    case 'kills' :
      $returned = __('Honorable kills:','wowpi');
      break;
    case 'professions' :
      $returned = __('Professions:','wowpi');
      break;
    case 'gear' :
      $returned = __('Gear:','wowpi');
      break;
    case 'artifact_weapon' :
      $returned = __('Artifact Weapon:','wowpi');
      break;
    case 'artifact_traits' :
      $returned = __('Artifact Traits:','wowpi');
      break;
    case 'artifact_relics' :
      $returned = __('Artifact Relics:','wowpi');
      break;
    case 'talents' :
      $returned = __('Talents:','wowpi');
      break;
    case 'ilvl' :
      $returned = __('Item level:');
      break;
  }
  return $returned;
}
