<?php

function wowpi_getRegion($region = null) {
    if(isset($region)) {
        return $region;
    }
    global $wowpi_options;
    return (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : 'eu';
}

function wowpi_getLocale($locale = null) {
    if(isset($locale)) {
        return $locale;
    }
    global $wowpi_options;
    return (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : 'en_GB';
}

function wowpi_getRealm($realm = null) {
    if(isset($realm)) {
        return $realm;
    }
    global $wowpi_options;
    return (isset($wowpi_options['realm']) && strlen($wowpi_options['realm'])>0) ? $wowpi_options['realm'] : '';
}

function wowpi_getApiKey() {
    global $wowpi_options;
    return $wowpi_options['api_key'];
}

function wowpi_getToken() {

    global $wowpi_options;

    $authorization  =  base64_encode ( $wowpi_options['client_id'].":".$wowpi_options['client_secret'] );
    $tokenUrl = wowpi_buildTokenUrl();

    $args = array(
        'headers' => array(
            'Authorization' => 'Basic ' . $authorization,
            'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
        ),
        'body' => 'grant_type=client_credentials'
    );
    $response = wp_remote_post( $tokenUrl, $args );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong while trying to get token: $error_message";
        return false;
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_body = json_decode( $response_body, true );
    
    $access_token = $response_body['access_token'];
    return $access_token;

}

function wowpi_buildTokenUrl() {
    $tokenUrl = 'https://'.wowpi_getRegion().'.battle.net/oauth/token';
    return $tokenUrl;
}

function wowpi_buildServiceUrl($dataType, $dataRequested) {
    $serviceUrl = 'https://'.wowpi_getRegion().'.api.blizzard.com/wow/'.$dataType.'/'.$dataRequested.'?locale='.wowpi_getLocale().'&access_token='.wowpi_getToken();
    return $serviceUrl;
}

function wowpi_getRaces() {
    $returned = get_option('wowpi_character_races');
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {

        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/data/character/races?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded->races)) {
            return $returned['data'];
        }

        $racesObj = $decoded->races;
        if (sizeof($racesObj) > 0) {
            $racesArr = array();
            foreach ($racesObj as $race) {
                $racesArr[$race->id] = array(
                    'mask' => $race->mask,
                    'side' => $race->side,
                    'name' => $race->name
                );
            }
            $returned['data'] = $racesArr;
        }
        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_character_races', $returned);
    }
    $returned = get_option('wowpi_character_races');
    return $returned['data'];
}

function wowpi_getClasses() {
    $returned = get_option('wowpi_character_classes');
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/data/character/classes?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded->classes)) {
            return $returned['data'];
        }
        $classesObj = $decoded->classes;
        if (sizeof($classesObj) > 0) {
            $classes_arr = array();
            foreach ($classesObj as $class) {
                $classes_arr[$class->id] = array(
                    'mask' => $class->mask,
                    'type' => $class->powerType,
                    'name' => $class->name
                );
            }
            $returned['data'] = $classes_arr;
        }
        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_character_classes', $returned);
    }
    $returned = get_option('wowpi_character_classes');
    return $returned['data'];
}

function wowpi_getArtifact($artifactId)
{
    $returned = get_option('wowpi_artifact_weapons_'.$artifactId);
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/item/'.$artifactId.'?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded)) {
            return $returned['data'];
        }

        $artifactArr = array(
            'id' => $decoded->id,
            'name' => $decoded->name,
            'description'=> $decoded->description,
            'icon' => $decoded->icon,
            'bonus_stats' => $decoded->bonusStats,
            'traits' => $decoded->itemSpells,
            'item_class' => $decoded->itemClass,
            'item_sub_class' => $decoded->itemSubClass,
            'weapon_info' => $decoded->weaponInfo,
            'sockets' => $decoded->socketInfo,
            'artifact_id' => $decoded->artifactId
        );

        $returned['data'] = $artifactArr;

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_artifact_weapons_'.$artifactId, $returned);
    }
    $returned = get_option('wowpi_artifact_weapons_'.$artifactId);
    return $returned['data'];
}

function wowpi_getSpell($spellId)
{
    $returned = get_option('wowpi_spells_'.$spellId);
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/spell/'.$spellId.'?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded)) {
            return $returned['data'];
        }

        $spellArr = array(
            'id' => $decoded->id,
            'name' => $decoded->name,
            'description'=> $decoded->description,
            'icon' => $decoded->icon,
            'cast_time' => $decoded->castTime
        );

        $returned['data'] = $spellArr;

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_spells_'.$spellId, $returned);
    }
    $returned = get_option('wowpi_spells_'.$spellId);
    return $returned['data'];
}

function wowpi_getRealms($region = null, $locale = null) {
    $returned = get_option('wowpi_realms_data');
    $region = wowpi_getRegion($region);
    $locale = wowpi_getLocale($locale);
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {

        $serviceUrl = 'https://' . $region . '.api.blizzard.com/wow/realm/status?locale=' . $locale . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);

        if(!isset($decoded->realms)) {
            return $returned['data'][$region];
        }

        $realmsObj = $decoded->realms;
        if (sizeof($realmsObj) > 0) {
            $realmsArr = array();
            foreach ($realmsObj as $realm) {
                $realmsArr[$realm->name] = array(
                    'slug' => $realm->slug,
                    'type' => $realm->type,
                    'population' => $realm->population,
                    'battlegroup' => $realm->battlegroup,
                    'locale' => $realm->locale,
                    'timezone' => $realm->timezone,
                    'connectedRealms' => isset($realm->connectedRealms) ? $realm->connectedRealms : array()
                );
            }
            $returned['data'][$region] = $realmsArr;
        }
        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_realms_data', $returned);
    }
    $returned = get_option('wowpi_realms_data');
    return $returned['data'][$region];
}

function wowpi_getCharacterAchievements() {
    $returned = get_option('wowpi_character_achievements');
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/data/character/achievements?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded->achievements)) {
            return $returned['data'];
        }
        $achievObj = $decoded->achievements;
        if(sizeof($achievObj)>0) {
            $totalAchievementPoints = 0;
            $achievArr = array();
            foreach($achievObj as $achievement) {
                if(isset($achievement->categories)) {
                    foreach($achievement->categories as $achievementsCategory)
                    {
                        $categName = $achievementsCategory->name;
                        foreach($achievementsCategory->achievements as $achiev) {
                            $achievArr[$achiev->id] = array(
                                't'=>$achiev->title.' - '.$achiev->description,
                                //'d'=>$achiev->description,
                                'p'=>$achiev->points,
                                'i'=>$achiev->icon,
                                //'account_wide'=>$achiev->accountWide,
                                //'faction_id'=>$achiev->factionId,
                                //'c'=> $categName,
                            );
                            $totalAchievementPoints += $achiev->points;

                        }
                    }
                }
                else
                {
                    foreach($achievement->achievements as $achiev)
                    {
                        $achievArr[$achiev->id] = array(
                            't'=>$achiev->title,
                            //'description'=>$achiev->description,
                            'p'=>$achiev->points,
                            'i'=>$achiev->icon,
                            //'account_wide'=>$achiev->accountWide,
                            //'faction_id'=>$achiev->factionId
                        );
                        $totalAchievementPoints += $achiev->points;
                    }
                }
            }

        }
        $returned['data']['total_points'] = $totalAchievementPoints;
        $returned['data']['achievements'] = $achievArr;
        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }

        update_option('wowpi_character_achievements', $returned);
    }
    $returned = get_option('wowpi_character_achievements');
    return $returned['data'];
}

function wowpi_getGuildAchievements() {
    $returned = get_option('wowpi_guild_achievements');
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.com/wow/data/guild/achievements?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded->achievements)) {
            return $returned['data'];
        }
        $achievObj = $decoded->achievements;
        if(sizeof($achievObj) > 0) {
            $totalAchievementPoints = 0;
            $achievArr = array();

            foreach($achievObj as $achievementGroup) {
                $groupName = $achievementGroup->name;
                if(isset($achievementGroup->categories)) {
                    foreach($achievementGroup->categories as $achievementsCategory) {
                        $categoryName = $achievementsCategory->name;
                        foreach($achievementsCategory->achievements as $achievement) {
                            $criteriaArr = array();
                            foreach($achievement->criteria as $criteria)
                            {
                                $criteriaArr[$criteria->id] = array(
                                    'id'=>$criteria->id,
                                    'description'=>$criteria->description,
                                    'max'=>$criteria->max
                                );
                            }
                            $name = isset($achievement->title) ? $achievement->title : '';
                            $achievArr[$achievement->id] = array(
                                'id'=>$achievement->id,
                                'name'=>$name,
                                'description'=>$achievement->description,
                                'points'=>$achievement->points,
                                'icon'=>$achievement->icon,
                                'criteria'=>$criteriaArr,
                                'categoryName'=>$categoryName,
                                'groupName'=>$groupName);
                            $totalAchievementPoints += $achievement->points;
                        }
                    }
                }
                foreach($achievementGroup->achievements as $achievement)
                {
                    $criteriaArr = array();
                    foreach($achievement->criteria as $criteria)
                    {
                        $criteriaArr[$criteria->id] = array(
                            'id'=>$criteria->id,
                            'description'=>$criteria->description,
                            'max'=>$criteria->max);
                    }
                    $name = isset($achievement->title) ? $achievement->title : '';
                    $achievArr[$achievement->id] = array(
                        'id'=>$achievement->id,
                        'name'=>$name,
                        'description'=>$achievement->description,
                        'points'=>$achievement->points,
                        'icon'=>$achievement->icon,
                        'criteria'=>$criteriaArr,
                        'groupName' =>$groupName);
                    $totalAchievementPoints += $achievement->points;
                }
            }

            $returned['data']['total_points'] = $totalAchievementPoints;
            $returned['data']['achievements'] = $achievArr;
        }

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_guild_achievements', $returned);
    }
    $returned = get_option('wowpi_guild_achievements');
    return $returned['data'];
}

function wowpi_getCharacterData($characterName = null, $realmName = null, $region = null, $locale = null) {
    global $wowpi_options;
    $characterCaching = $wowpi_options['character_caching']*60*60;

    $characterName = isset($characterName) ? $characterName : $wowpi_options['character_name'];
    $region = wowpi_getRegion($region);
    $locale = wowpi_getLocale($locale);

    $realmName = wowpi_getRealm($realmName);
    $realmsData = wowpi_getRealms($region, $locale);

    $realmSlug = $realmsData[$realmName]['slug'];

    $characterOptionName = 'wowpi_character_data_'.$realmSlug.'_'.urlencode(strtolower($characterName));

    $returned = get_option($characterOptionName);

    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+$characterCaching)<current_time('timestamp'))) {

        // get ALL the fields
        $fieldsArr = array(
            'achievements',
            'feed',
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
        $fieldsStr = urlencode(implode(',',$fieldsArr));

        $serviceUrl = 'https://' . $region . '.api.blizzard.com/wow/character/'.$realmSlug.'/'.$characterName.'?fields='.$fieldsStr.'&locale=' . $locale . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        
        if(isset($decoded->lastModified) && $decoded->lastModified <= $returned['last_update'])
        {
            return $returned['data'];
        }
        $returned['data']['lastModify'] = $decoded->lastModified;
        $returned['data']['profile'] = array(
            'name' => $decoded->name,
            'realm' => $decoded->realm,
            'battlegroup' => $decoded->battlegroup,
            'class' => $decoded->class,
            'race' => $decoded->race,
            'gender' => $decoded->gender,
            'level' => $decoded->level,
            'thumbnail' => $decoded->thumbnail,
            'calcClass' => $decoded->calcClass,
            'faction' => $decoded->faction,
            'achievementPoints' => $decoded->achievementPoints,
            'honorableKills' => $decoded->totalHonorableKills
        );
        $returned['data']['guild'] = wowpi_parseCharacterDataGuild($decoded);
        $returned['data']['items'] = wowpi_parseCharacterDataItems($decoded);
        $returned['data']['professions'] = wowpi_parseCharacterDataProfessions($decoded);
        $returned['data']['titles'] = wowpi_parseCharacterDataTitles($decoded);
        $returned['data']['achievements'] = wowpi_parseCharacterDataAchievements($decoded);
        $returned['data']['talents'] = wowpi_parseCharacterDataTalents($decoded);
        $returned['data']['progression'] = wowpi_parseCharacterDataProgression($decoded);
        $returned['data']['pvp'] = wowpi_parseCharacterDataPvp($decoded);
        $returned['data']['feed'] = wowpi_parseCharacterDataFeed($decoded);

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option($characterOptionName, $returned);
    }
    $returned = get_option($characterOptionName);
    return $returned['data'];

}

function wowpi_getGuildData($guildName = null, $realmName = null, $region = null, $locale = null)
{
    global $wowpi_options;
    $guildCaching = $wowpi_options['guild_caching'] * 60 * 60;

    $region = wowpi_getRegion($region);
    $locale = wowpi_getLocale($locale);

    $realmName = wowpi_getRealm($realmName);
    $realmsData = wowpi_getRealms($region, $locale);
    $realmSlug = $realmsData[$realmName]['slug'];

    if (!isset($guildName) || strlen($guildName)==0) {
        $character = wowpi_getCharacterData();
        $guildName = $character['guild']['name'];
    }

    if (!isset($guildName)) {
        return array();
    }

    $guildOptionName = 'wowpi_guild_data_' . $realmSlug . '_' . urlencode(strtolower($guildName));

    $returned = get_option($guildOptionName);

    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update']) + $guildCaching) < current_time('timestamp'))) {
        // get ALL the fields
        $fieldsArr = array(
            'members',
            'achievements',
            'challenge',
            'news'
        );
        $fieldsStr = urlencode(implode(',', $fieldsArr));

        $serviceUrl = 'https://' . $region . '.api.blizzard.com/wow/guild/' . $realmSlug . '/' . $guildName . '?fields=' . $fieldsStr . '&locale=' . $locale . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);

        if (isset($decoded->lastModified) && ($decoded->lastModified <= $returned['last_update'])) {
            return $returned['data'];
        }

        if(isset($decoded->status) && $decoded->status=='nok')
        {
            return array();
        }

        $returned['data']['lastModify'] = $decoded->lastModified;

        $returned['data']['profile'] = array(
            'name' => $decoded->name,
            'realm' => $decoded->realm,
            'battlegroup' => $decoded->battlegroup,
            'level' => $decoded->level,
            'faction' => $decoded->side,
            'achievementPoints' => $decoded->achievementPoints,
            'emblem' => array(
                'icon' => $decoded->emblem->icon,
                'iconColor' => $decoded->emblem->iconColor,
                'iconColorId' => $decoded->emblem->iconColorId,
                'border' => $decoded->emblem->border,
                'borderColor' => $decoded->emblem->borderColor,
                'borderColorId' => $decoded->emblem->borderColorId,
                'backgroundColor' => $decoded->emblem->backgroundColor,
                'backgroundColorId' => $decoded->emblem->backgroundColorId
            )
        );
        $returned['data']['members'] = wowpi_parseGuildDataMembers($decoded);
        $returned['data']['achievements'] = wowpi_parseGuildDataAchievements($decoded);
        //$returned['data']['challenge'] = wowpi_parseGuildDataChallenge($decoded);
        //$returned['data']['news'] = wowpi_parseGuildDataNews($decoded);

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option($guildOptionName, $returned);
    }
    $returned = get_option($guildOptionName);
    return $returned['data'];
}

function wowpi_parseGuildDataMembers($decoded)
{
    if (!isset($decoded->members) || empty($decoded->members)) {
        return array();
    }

    $membersArr = array();

    foreach ($decoded->members as $member) {
        //var_dump($member);
        $membersArr[] = array(
            'name' => $member->character->name,
            'rank' => $member->rank,
            'realm' => $member->character->realm,
            'class' => $member->character->class,
            'race' => $member->character->race,
            'gender' => $member->character->gender,
            'level' => $member->character->level,
            'achievementPoints' => $member->character->achievementPoints,
            'thumbnail' => $member->character->thumbnail,
            'spec' => isset($member->character->spec->name) ? $member->character->spec->name : '',
            'role' => isset($member->character->spec->role) ? $member->character->spec->role: '',
        );
    }
    return $membersArr;
}

function wowpi_parseGuildDataAchievements($decoded) {

    $achievArr = array();
    if(!isset($decoded->achievements) || empty($decoded->achievements)) {
        return $achievArr;
    }

    $achievementIds = $decoded->achievements->achievementsCompleted;
    $achievementTimestamps = $decoded->achievements->achievementsCompletedTimestamp;
    if(sizeof($achievementIds)>0)
    {
        foreach($achievementIds as $key=>$id)
        {
            $timestamp = $achievementTimestamps[$key]>0 ? substr($achievementTimestamps[$key], 0,-3) : 0;
            $achievArr[] = array('id'=>$id,'completed'=>$timestamp);
        }
    }
    $achievArr = sortArrayBy($achievArr,'completed', $direction = 'DESC');

    return $achievArr;
}

function wowpi_parseGuildDataChallenge($decoded) {
    $challengeArr = array();
    if(!isset($decoded->challenge) || empty($decoded->challenge)) {
        return $challengeArr;
    }

    return $challengeArr;
}

function wowpi_parseGuildDataNews($decoded) {
    $newsArr = array();
    if(!isset($decoded->news) || empty($decoded->news)) {
        return $newsArr;
    }

    return $newsArr;
}


function wowpi_parseCharacterDataItems($decoded) {
    if(!isset($decoded->items) || empty($decoded->items)) {
        return array();
    }

    $itemsArr = array();
    $itemsArr['averageItemLevel'] = $decoded->items->averageItemLevel;
    unset($decoded->items->averageItemLevel);
    $itemsArr['averageItemLevelEquipped'] = $decoded->items->averageItemLevelEquipped;
    unset($decoded->items->averageItemLevelEquipped);

    foreach($decoded->items as $type => $data) {

        $stats = array();
        if(isset($data->stats)) {
            foreach ($data->stats as $stat) {
                $stats[$stat->stat] = array('id' => $stat->stat, 'amount' => $stat->amount);
            }
        }

        $appearance = array();
        if(isset($data->appearance) && !empty($data->appearance)) {
            foreach($data->appearance as $property => $value) {
                $appearance[$property] = $value;
            }
        }

        $tooltipParams = array();
        if(isset($data->tooltipParams)) {
            foreach($data->tooltipParams as $property => $value) {
                $tooltipParams[$property] = $value;
            }
        }

        $itemsArr['items'][$type] = array(
            'id' => $data->id,
            'name' => $data->name,
            'icon' => $data->icon,
            'quality' => $data->quality,
            'itemLevel' => $data->itemLevel,
            'armor' => $data->armor,
            'bonuses' => $data->bonusLists,
            'armorSet' => isset($data->tooltipParams->set) ? $data->tooltipParams->set : array(),
            'artifactId' => $data->artifactId,
            'displayInfoId' => $data->displayInfoId,
            'artifactAppearanceId' => $data->artifactAppearanceId,
            'artifactTraits' => wowpi_parseCharacterDataArtifactTraits($data->artifactTraits),
            'stats' => $stats,
            'appearance' => $appearance,
            'context' => $data->context,
            'tooltipParams' => $tooltipParams
        );

        if(isset($data->weaponInfo)) {
            $itemsArr['items'][$type]['weaponInfo'] = array(
                'damageMin' => $data->weaponInfo->damage->min,
                'damageMax' => $data->weaponInfo->damage->max,
                'damageExactMin' => $data->weaponInfo->damage->exactMin,
                'damageExactMax' => $data->weaponInfo->damage->exactMax,
                'weaponSpeed' => $data->weaponInfo->weaponSpeed,
                'dps' => $data->weaponInfo->dps
            );
        }

        if(isset($data->relics) && !empty($data->relics)) {
            $relics = array();
            foreach($data->relics as $relic) {
                $relics[] = array(
                    'socket' => $relic->socket,
                    'itemId' => $relic->itemId,
                    'context' => $relic->context,
                    'bonuses' => $relic->bonusLists
                );
            }

        }

        if(isset($relics)) {
            $itemsArr['items'][$type]['relics'] = $relics;
        }
    }

    return $itemsArr;
}

function wowpi_parseCharacterDataArtifactTraits($artifactTraits) {

    $traitsArr = array();

    if(empty($artifactTraits)) {
        return $traitsArr;
    }

    foreach($artifactTraits as $trait) {
        $traitsArr[$trait->id] = array(
            'id' => $trait->id,
            'rank' => $trait->rank
        );
    }

    return $traitsArr;
}

function wowpi_parseCharacterDataGuild($decoded) {

    $guildArr = array();
    if(!isset($decoded->guild) || empty($decoded->guild)) {
        return $guildArr;
    }
    $guildArr = array(
        'name' => $decoded->guild->name,
        'realm' => $decoded->guild->realm,
        'battlegroup' => $decoded->guild->battlegroup
    );

    return $guildArr;
}

function wowpi_parseCharacterDataProfessions($decoded) {
    $professionsArr = array();
    if(!isset($decoded->professions) || empty($decoded->professions)) {
        return $professionsArr;
    }

    $profs = $decoded->professions;
    foreach($profs as $type => $professions) {
        foreach($professions as $prof) {
            $professionsArr[$type][$prof->id] = array(
                'id' => $prof->id,
                'name' => $prof->name,
                'icon' => $prof->icon,
                'rank' => $prof->rank,
                'maxRank' => $prof->max,
                'recipes' => $prof->recipes
            );
        }
    }

    return $professionsArr;
}

function wowpi_parseCharacterDataFeed($decoded) {
    $feedArr = array();
    if(!isset($decoded->feed) || empty($decoded->feed)) {
        return $feedArr;
    }


    foreach($decoded->feed as $key => $activity) {
        $feedArr[$key] = array('timestamp' => substr($activity->timestamp, 0,-3));
        if($activity->type == 'LOOT') {
            $feedArr[$key] = array(
                'type' => $activity->type,
                'itemId' => $activity->itemId,
                'context' => $activity->context,
                'bonuses' => $activity->bonusLists,
            );
        }

        else {
            $feedArr[$key]['achievement'][$activity->achievement->id] = array(
                'id' => $activity->achievement->id,
                'title' => $activity->achievement->title,
                'points' => $activity->achievement->points,
                'description' => $activity->achievement->description,
                'rewardItems' => $activity->achievement->rewardItems,
                'icon' => $activity->achievement->icon,
                'criteria' => $activity->achievement->criteria,
                'accountWide' => $activity->achievement->accountWide,
                'factionId' => $activity->achievement->factionId,
            );
        }

        if(isset($activity->featOfStrength)) {
            $feedArr[$key]['featOfStrength'] = $activity->featOfStrength;
        }
    }

    return $feedArr;
}

function wowpi_parseCharacterDataTitles($decoded) {

    $titlesArr = array();
    if(!isset($decoded->titles) || empty($decoded->titles)) {
        return $titlesArr;
    }

    foreach($decoded->titles as $title) {
        $titlesArr['currentTitle'] = 0;
        if(isset($title->selected))
        {
            $titlesArr['currentTitle'] = $title->id;
        }
        $titlesArr['titles'][$title->id] = $title->name;
    }

    return($titlesArr);
}

function wowpi_parseCharacterDataAchievements($decoded) {

    $achievArr = array();
    if(!isset($decoded->achievements) || empty($decoded->achievements)) {
        return $achievArr;
    }

    $achievementIds = $decoded->achievements->achievementsCompleted;
    $achievementTimestamps = $decoded->achievements->achievementsCompletedTimestamp;
    if(sizeof($achievementIds)>0)
    {
        foreach($achievementIds as $key=>$id)
        {
            $timestamp = $achievementTimestamps[$key]>0 ? substr($achievementTimestamps[$key], 0,-3) : 0;
            $achievArr[] = array('id'=>$id,'completed'=>$timestamp);
        }
    }
    $achievArr = sortArrayBy($achievArr,'completed', $direction = 'DESC');

    return $achievArr;
}

function wowpi_parseCharacterDataTalents($decoded = null) {

    if(!isset($decoded->talents) || empty($decoded->talents)) {
        return array();
    }

    global $wowpi_plugin_dir;
    $spec_ids_json = file_get_contents($wowpi_plugin_dir.'/includes/spec_ids.json');
    $specIds = json_decode($spec_ids_json,true);

    $characterTalents = array();

    foreach($decoded->talents as $spec)
    {
        $theTalents = array();
        if(isset($spec->talents) && !empty($spec->talents))
        {
            foreach($spec->talents as $talent)
            {
                if(isset($talent)) {
                    $theTalents[$talent->tier] = array('id' => $talent->spell->id, 'name' => $talent->spell->name, 'icon' => $talent->spell->icon, 'description' => $talent->spell->description);
                }
            }
            ksort($theTalents);

            $theSpec = $spec->spec;
            $specCombo = $decoded->calcClass.$spec->calcSpec;
            $specId = (isset($specIds[$specCombo])) ? $specIds[$specCombo] : '0';

            $selected = '0';
            if(isset($spec->selected))
            {
                $selected = '1';
                $characterTalents['currentSpec'] = $specId;
            }
            $characterTalents['talents'][] = array(
                'name'=>$theSpec->name,
                'role'=>$theSpec->role,
                'background'=>$theSpec->backgroundImage,
                'icon'=>$theSpec->icon,
                'description'=>$theSpec->description,
                'selected'=>$selected,
                'talents'=>$theTalents,
                'spec_id' => $specId);
        }
    }
    return $characterTalents;
}

function wowpi_parseCharacterDataProgression($decoded) {
    if(!isset($decoded->progression) || empty($decoded->progression)) {
        return array();
    }
    wowpi_setProgression($decoded->progression);

    $progressionArr = array();

    foreach($decoded->progression->raids as $raid) {
        $bosses = array();
        foreach($raid->bosses as $boss) {
            $bosses[$boss->id] = array(
                'lfrKills' => isset($boss->lfrKills) ? $boss->lfrKills : 0,
                'lfrTimestamp' => isset($boss->lfrTimestamp) ? substr($boss->lfrTimestamp, 0,-3) : 0,
                'normalKills' => isset($boss->normalKills) ? $boss->normalKills : 0,
                'normalTimestamp' => isset($boss->normalTimestamp) ? substr($boss->normalTimestamp, 0,-3) : 0,
                'heroicKills' => isset($boss->heroicKills) ? $boss->heroicKills : 0,
                'heroicTimestamp' => isset($boss->heroicTimestamp) ? substr($boss->heroicTimestamp, 0,-3) : 0,
                'mythicKills' => isset($boss->mythicKills) ? $boss->mythicKills : 0,
                'mythicTimestamp' => isset($boss->mythicTimestamp) ? substr($boss->mythicTimestamp, 0,-3) : 0,

            );
        }
        $progressionArr[$raid->id] = array(
            'lfr' => $raid->lfr,
            'normal' => $raid->normal,
            'heroic' => $raid->heroic,
            'mythic' => $raid->mythic,
            'bosses' => $bosses
        );
    }
    return $progressionArr;
}

function wowpi_setProgression($decodedProgression = null) {
    $returned = get_option('wowpi_progression_data');
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        if(!isset($decodedProgression) || empty($decodedProgression)) {
            return $returned;
        }
        /*
        echo '<pre>';
        print_r($decodedProgression);
        echo '</pre>';
        exit;
        */
        $progressionArr = array();
        foreach($decodedProgression->raids as $raid) {
            $bosses = array();
            foreach($raid->bosses as $boss) {
                $bosses[$boss->id] = array(
                    'id' => $boss->id,
                    'name' => $boss->name
                );
            }
            $progressionArr[$raid->id] = array(
                'id' => $raid->id,
                'name' => $raid->name,
                'bosses' => $bosses
            );
        }
        $returned['data'] = $progressionArr;

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_progression_data', $returned);
    }
}

function wowpi_getProgression() {
    return get_option('wowpi_progression_data');
}

function wowpi_parseCharacterDataPvp($decoded) {
    $pvpArr = array();
    if(!isset($decoded->pvp) || empty($decoded->pvp)) {
        return $pvpArr;
    }

    foreach($decoded->pvp->brackets as $bracket => $data) {
        $pvpArr[$bracket] = array(
            'slug' => $data->slug,
            'rating' => $data->rating,
            'weeklyPlayed' => $data->weeklyPlayed,
            'weeklyWon' => $data->weeklyWon,
            'weeklyLost' => $data->weeklyLost,
            'seasonPlayed' => $data->seasonPlayed,
            'seasonWon' => $data->seasonWon,
            'seasonLost' => $data->seasonLost
        );
    }

    return $pvpArr;
}

function sortArrayBy($array = array(),$sort_key, $direction = 'ASC')
{
    $direction = strtoupper($direction);
    $sorted = array();
    if(!empty($array))
    {
        foreach ($array as $key => $row)
        {
            $sorted[$key] = $row[$sort_key];
        }
        array_multisort($sorted, constant('SORT_'.$direction), $array);
    }
    return $array;
}

function removeDirectory($path) {
    $files = glob($path . '/*');
    foreach ($files as $file) {
        is_dir($file) ? removeDirectory($file) : unlink($file);
    }
    rmdir($path);
    return;
}