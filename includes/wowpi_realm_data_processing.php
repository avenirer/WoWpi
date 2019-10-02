<?php
function wowpi_get_realms($region = null, $locale = null)
{
  global $wowpi_options;
  $realms = get_option('wowpi_realms');
  //echo '<pre>'; print_r($guilds); echo '</pre>';
  
  // get the caching time in seconds
  $caching_realms = intval($wowpi_options['realm_caching']);
  
  if(isset($realms) && (intval($realms['last_update']) + $caching_realms > intval(current_time('timestamp'))))
  {
    return $realms;
  }
  else
  {
    return wowpi_call_api_realms($region, $locale);
    //echo '<pre>';print_r($realms);echo '</pre>';
  }
}

function wowpi_call_api_realms($region = null, $locale = null)
{
  global $wowpi_options;
  if(!isset($region))
  {
    $region = $wowpi_options['region'];
  }
  if(!isset($locale))
  {
    $locale = $wowpi_options['locale'];
  }
  
  //create the service url
  $service_url = 'https://'.$region.'.api.blizzard.net/wow/realm/status?';
  $service_url .= 'locale='.$locale.'&access_token='.wowpi_getToken();
  
  // the curl thingy
  $curl_response = wowpi_retrieve_data($service_url);
  $decoded = json_decode($curl_response);
  if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occured: ' . $decoded->response->errormessage);
  }
  else
  {
    $the_realms = array();
    $the_realms['last_update'] = current_time('timestamp');
    $the_realms['data'] = array('realms'=>array(),'battlegroups'=>array());
    if(isset($decoded->realms)) {
        foreach ($decoded->realms as $realm) {
            $the_realms['data']['realms'][$realm->slug] = array(
                'name' => $realm->name,
                'slug' => $realm->slug,
                'status' => $realm->status,
                'type' => $realm->type,
                'population' => $realm->population,
                'battlegroup' => $realm->battlegroup,
                'locale' => $realm->locale);
            $the_realms['data']['battlegroups'][$realm->battlegroup][] = $realm->slug;
        }

        //echo '<pre>';print_r($the_realms);echo '</pre>';
        update_option('wowpi_realms', $the_realms);
        return $the_realms;
    }
    return false;
  }
}
