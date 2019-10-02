<?php
function wowpi_shortcode_realms($atts)
{
  global $wowpi_plugin_url;
  global $wowpi_options;
  $output = '';  
  
  $pull_realm_atts = shortcode_atts( array(
    'realm' => '',
    'battlegroup' => '',
    'view' => 'realm',
    'show' => 'status', // can be 'status|type|population'
    'class' => '',
    /*
    'order_by' => '1',
    'rows_per_page' => '25',
    'direction' => 'desc',
    'paginate' => 'true',*/
    ), $atts );
  if($pull_realm_atts==null)
  {
    return false;
  }
  
  $realms = wp_kses_post($pull_realm_atts[ 'realm' ]);
  $battlegroups = wp_kses_post($pull_realm_atts['battlegroup']);
  
  
  if(strlen($realms)==0 && strlen($battlegroups)==0)
  {
    $character_data = wowpi_get_character();
    $realms = $character_data['realm'];
    $battlegroups = $character_data['battlegroup'];
  }
  
  $realms_arr = (strlen($realms)==0) ? array() : explode('|',$realms);
  
  foreach($realms_arr as &$realm)
  {
    $realm = wowpi_slugify($realm);
  }
  
  //echo '<pre>';print_r($realms_arr);echo '</pre>';
  
  
  $battlegroups_arr = (strlen($battlegroups)==0) ? array() : explode('|',$battlegroups);
  
  $view = wp_kses_post($pull_realm_atts['view']);
  $show = wp_kses_post($pull_realm_atts['show']);
  $show_arr = explode('|',$show);
  
  $realms_data = wowpi_get_realms();
  //print_r($realms_data);
  
  $the_realms = array();
  if($view=='battlegroup')
  {
    if(empty($battlegroups_arr))
    {
      foreach($realms_arr as $realm_slug)
      {
        $battlegroups_arr[] = $realms_data['data']['realms'][$realm_slug]['battlegroup'];
      }
    }
    if(!empty($battlegroups_arr))
    {
      foreach($battlegroups_arr as $battlegroup)
      {
        $realms_arr = array_merge($realms_arr,$realms_data['data']['battlegroups'][$battlegroup]);
      }
      $realms_arr = array_unique($realms_arr);
    }
  }  
  
  if($realms_arr[0]=='all')
  {
    $the_realms = $realms_data['data']['realms'];
  }
  else
  {
    foreach($realms_arr as $realm_slug)
    {
      $the_realms[$realm_slug] = $realms_data['data']['realms'][$realm_slug];
    }
  }
  
  if(!empty($the_realms))
  {
    $output .= '<table';
    $output .= ' class="'.(isset($pull_guild_atts['class']) && strlen($pull_guild_atts['class'])>0 ? $pull_guild_atts['class'] : 'wowpi_realm_status').'"';
    $output .= '>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th class="realm-name">'.__('Realm','wowpi').'</th>';
    foreach($show_arr as $col)
    {
      $output .= '<th class="'.$col.'">'.wowpi_translate_realms_title($col).'</th>';
    }
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    foreach($the_realms as $realm)
    {
      $output .= '<tr>';
      $output .= '<td class="realm-name">'.$realm['name'].'</td>';
      foreach($show_arr as $col)
      {
        $output .= '<td class="'.$col.'"><span>'.$realm[$col].'</span>';
        if($col=='status')
        {
          $output .= '<img src="'.$wowpi_plugin_url.'assets/images/theme/check-'.(($realm[$col]=='0') ? 'n' : '').'ok.png" />';
        }
        $output .= '</td>';
      }
      $output .= '</tr>';
    }
    $output .= '</tbody>';
    $output .= '</table>';
  }
  return $output;
}

function wowpi_translate_realms_title($of_what)
{
  $returned = '';
  switch($of_what) {
    case 'status' :
      $returned = __('Status','wowpi');
      break;
    case 'population' :
      $returned = __('Population','wowpi');
      break;
    case 'Type' :
      $returned = __('Type','wowpi');
      break;
  }
  return $returned;
}

function wowpi_slugify($str)
{
  $str = strtolower($str);
  
  $search = array('-',' ','\'','(',')');
  $replace = array('','-','','','');
  $str = str_replace($search,$replace,$str);
  
  $foreign_characters = array(
    '/ä|æ|ǽ/' => 'ae',
    '/ö|œ/' => 'oe',
    '/ü/' => 'ue',
    '/Ä/' => 'Ae',
    '/Ü/' => 'Ue',
    '/Ö/' => 'Oe',
    '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|Α|Ά|Ả|Ạ|Ầ|Ẫ|Ẩ|Ậ|Ằ|Ắ|Ẵ|Ẳ|Ặ|А/' => 'A',
    '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|α|ά|ả|ạ|ầ|ấ|ẫ|ẩ|ậ|ằ|ắ|ẵ|ẳ|ặ|а/' => 'a',
    '/Б/' => 'B',
    '/б/' => 'b',
    '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
    '/ç|ć|ĉ|ċ|č/' => 'c',
    '/Д/' => 'D',
    '/д/' => 'd',
    '/Ð|Ď|Đ|Δ/' => 'Dj',
    '/ð|ď|đ|δ/' => 'dj',
    '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Ε|Έ|Ẽ|Ẻ|Ẹ|Ề|Ế|Ễ|Ể|Ệ|Е|Э/' => 'E',
    '/è|é|ê|ë|ē|ĕ|ė|ę|ě|έ|ε|ẽ|ẻ|ẹ|ề|ế|ễ|ể|ệ|е|э/' => 'e',
    '/Ф/' => 'F',
    '/ф/' => 'f',
    '/Ĝ|Ğ|Ġ|Ģ|Γ|Г|Ґ/' => 'G',
    '/ĝ|ğ|ġ|ģ|γ|г|ґ/' => 'g',
    '/Ĥ|Ħ/' => 'H',
    '/ĥ|ħ/' => 'h',
    '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|Η|Ή|Ί|Ι|Ϊ|Ỉ|Ị|И|Ы/' => 'I',
    '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|η|ή|ί|ι|ϊ|ỉ|ị|и|ы|ї/' => 'i',
    '/Ĵ/' => 'J',
    '/ĵ/' => 'j',
    '/Ķ|Κ|К/' => 'K',
    '/ķ|κ|к/' => 'k',
    '/Ĺ|Ļ|Ľ|Ŀ|Ł|Λ|Л/' => 'L',
    '/ĺ|ļ|ľ|ŀ|ł|λ|л/' => 'l',
    '/М/' => 'M',
    '/м/' => 'm',
    '/Ñ|Ń|Ņ|Ň|Ν|Н/' => 'N',
    '/ñ|ń|ņ|ň|ŉ|ν|н/' => 'n',
    '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ο|Ό|Ω|Ώ|Ỏ|Ọ|Ồ|Ố|Ỗ|Ổ|Ộ|Ờ|Ớ|Ỡ|Ở|Ợ|О/' => 'O',
    '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ο|ό|ω|ώ|ỏ|ọ|ồ|ố|ỗ|ổ|ộ|ờ|ớ|ỡ|ở|ợ|о/' => 'o',
    '/П/' => 'P',
    '/п/' => 'p',
    '/Ŕ|Ŗ|Ř|Ρ|Р/' => 'R',
    '/ŕ|ŗ|ř|ρ|р/' => 'r',
    '/Ś|Ŝ|Ş|Ș|Š|Σ|С/' => 'S',
    '/ś|ŝ|ş|ș|š|ſ|σ|ς|с/' => 's',
    '/Ț|Ţ|Ť|Ŧ|τ|Т/' => 'T',
    '/ț|ţ|ť|ŧ|т/' => 't',
    '/Þ|þ/' => 'th',
    '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|Ũ|Ủ|Ụ|Ừ|Ứ|Ữ|Ử|Ự|У/' => 'U',
    '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|υ|ύ|ϋ|ủ|ụ|ừ|ứ|ữ|ử|ự|у/' => 'u',
    '/Ý|Ÿ|Ŷ|Υ|Ύ|Ϋ|Ỳ|Ỹ|Ỷ|Ỵ|Й/' => 'Y',
    '/ý|ÿ|ŷ|ỳ|ỹ|ỷ|ỵ|й/' => 'y',
    '/В/' => 'V',
    '/в/' => 'v',
    '/Ŵ/' => 'W',
    '/ŵ/' => 'w',
    '/Ź|Ż|Ž|Ζ|З/' => 'Z',
    '/ź|ż|ž|ζ|з/' => 'z',
    '/Æ|Ǽ/' => 'AE',
    '/ß/' => 'ss',
    '/Ĳ/' => 'IJ',
    '/ĳ/' => 'ij',
    '/Œ/' => 'OE',
    '/ƒ/' => 'f',
    '/ξ/' => 'ks',
    '/π/' => 'p',
    '/β/' => 'v',
    '/μ/' => 'm',
    '/ψ/' => 'ps',
    '/Ё/' => 'Yo',
    '/ё/' => 'yo',
    '/Є/' => 'Ye',
    '/є/' => 'ye',
    '/Ї/' => 'Yi',
    '/Ж/' => 'Zh',
    '/ж/' => 'zh',
    '/Х/' => 'Kh',
    '/х/' => 'kh',
    '/Ц/' => 'Ts',
    '/ц/' => 'ts',
    '/Ч/' => 'Ch',
    '/ч/' => 'ch',
    '/Ш/' => 'Sh',
    '/ш/' => 'sh',
    '/Щ/' => 'Shch',
    '/щ/' => 'shch',
    '/Ъ|ъ|Ь|ь/' => '',
    '/Ю/' => 'Yu',
    '/ю/' => 'yu',
    '/Я/' => 'Ya',
    '/я/' => 'ya'
  );
  
  $array_from = array_keys($foreign_characters);
	$array_to = array_values($foreign_characters);
  
  return preg_replace($array_from, $array_to, $str);
}