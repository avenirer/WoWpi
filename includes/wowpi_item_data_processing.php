<?php
function wowpi_getItem($itemId)
{
    $returned = get_option('wowpi_items_'.$itemId);
    if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp'))) {
        $serviceUrl = 'https://' . wowpi_getRegion() . '.api.blizzard.net/wow/item/'.$itemId.'?locale=' . wowpi_getLocale() . '&access_token=' . wowpi_getToken();
        $curl_response = wowpi_retrieve_data($serviceUrl);
        $decoded = json_decode($curl_response);
        if(!isset($decoded)) {
            return $returned['data'];
        }

        $itemArr = array(
            'id' => $decoded->id,
            'name' => $decoded->name,
            'description'=> $decoded->description,
            'icon' => $decoded->icon,
            'quality' => $decoded->quality
        );

        $returned['data'] = $itemArr;

        if (isset($returned['data']) && !empty($returned['data'])) {
            $returned['last_update'] = current_time('timestamp');
        }
        update_option('wowpi_items_'.$itemId, $returned);
    }
    $returned = get_option('wowpi_items_'.$itemId);
    return $returned['data'];
}