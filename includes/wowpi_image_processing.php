<?php

function wowpi_retrieve_image($image_name = null, $image_type = 'icon', $image_size = '36', $image_extension = 'jpg')
{
    if(!isset($image_name)) {
        return false;
    }

    global $wowpi_plugin_url;
    global $wowpi_plugin_dir;
    $image = $image_name.'_'.$image_size.'.'.$image_extension;

    $image_dir = $wowpi_plugin_dir.'assets/images/wow/';
    $image_url = $wowpi_plugin_url.'assets/images/wow/'.$image;

    //let's make sure we have the wow image directory
    if ( ! file_exists( $image_dir ) ) {
        wp_mkdir_p( $image_dir );
    }

    if(!file_exists($image_dir.$image))
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'follow_location' => false
                )
            )
        );
        $image_get = file_get_contents('https://render-' . wowpi_getRegion() . '.worldofwarcraft.com/icons/' . $image_size . '/' . $image_name . '.' . $image_extension, false, $context);
        if ($image_get !== false) {
            if(strpos($image_get, '<html>')!== false) {
                $spacer_url = plugins_url().'/wowpi/assets/images/spacer.png';
                $image_get = file_get_contents($spacer_url, false, $context);
                file_put_contents($image_dir . $image, $image_get);
            }
            else {
                $image_get = file_get_contents('https://render-' . wowpi_getRegion() . '.worldofwarcraft.com/icons/' . $image_size . '/' . $image_name . '.' . $image_extension, false, $context);
                file_put_contents($image_dir . $image, $image_get);
            }
            if(getimagesize($image_dir . $image, $image_get) == 0) {
                $spacer_url = plugins_url().'/wowpi/assets/images/spacer.png';
                $image_get = file_get_contents($spacer_url, false, $context);
                file_put_contents($image_dir . $image, $image_get);
            }
        }

        else {
            $image_url = '';

        }
    }
    return $image_url;
}

function wowpi_get_character_image($region = null,$image_uri)
{
    global $wowpi_options;
    global $wowpi_plugin_url;
    $caching = $wowpi_options['character_caching'];

    // if no region was given we get the default region
    $region = wowpi_getRegion($region);

    $image_uri_arr = explode('/',$image_uri);
    $image = explode('-',$image_uri_arr[sizeof($image_uri_arr)-1]);
    $image_uri_arr[sizeof($image_uri_arr)-1] = $image[0];

    $feature_image_uri = 'https://render-'.$region.'.worldofwarcraft.com/character/'.$image_uri_arr[0].'/'.$image_uri_arr[1].'/'.$image_uri_arr[2].'-profilemain.jpg';
    $avatar_image_uri = 'https://render-'.$region.'.worldofwarcraft.com/character/'.$image_uri_arr[0].'/'.$image_uri_arr[1].'/'.$image_uri_arr[2].'-avatar.jpg';

    $upload_dir = wp_upload_dir();
    $wowpi_upload_dir = $upload_dir['basedir'].'/wowpi/';

    //let's make sure we have the image directory
    if ( ! file_exists( $wowpi_upload_dir ) ) {
        wp_mkdir_p( $wowpi_upload_dir );
    }

    if(!file_exists($wowpi_upload_dir.'character_avatar_'.$image[0].'.jpg') || (file_exists($wowpi_upload_dir.'character_avatar_'.$image[0].'.jpg') && ((filemtime($wowpi_upload_dir.'character_avatar_'.$image[0].'.jpg') + intval($caching)*60*60)) < time())) {
        if(get_http_response_code($avatar_image_uri) == "200") {
            $avatar_image_get = file_get_contents($avatar_image_uri);
            if ($avatar_image_get !== false) {
                file_put_contents($wowpi_upload_dir . 'character_avatar_' . $image[0] . '.jpg', $avatar_image_get);
            }
        }
        else {
            $avatar_image_get = file_get_contents($wowpi_plugin_url.'assets/images/theme/murloc.jpg');
            file_put_contents($wowpi_upload_dir.'character_avatar_'.$image[0].'.jpg',$avatar_image_get);
        }

        if(get_http_response_code($feature_image_uri) == "200") {
            $profile_image_get = file_get_contents($feature_image_uri);
            if ($profile_image_get !== false) {
                file_put_contents($wowpi_upload_dir . 'character_profile_' . $image[0] . '.jpg', $profile_image_get);
            }
        }
        else {
            $profile_image_get = file_get_contents($wowpi_plugin_url.'assets/images/theme/murloc.jpg');
            file_put_contents($wowpi_upload_dir.'character_profile_'.$image[0].'.jpg',$profile_image_get);
        }

  }
  return $image[0];  
}


