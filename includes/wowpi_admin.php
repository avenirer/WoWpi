<?php
// Add settings link on plugin page
function wowpi_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=wowpi">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wowpi_settings_link' );



// Incepem prin a adauga un link in meniul de admin
add_action('admin_menu','wowpi_admin_add_page');
function wowpi_admin_add_page()
{
	add_options_page('WoWpi Settings', 'WoWpi Settings','manage_options','wowpi','wowpi_options_page');
}


// Adaugam pagina catre care trimite linkul din meniul de admin
function wowpi_options_page()
{
	?>
	<div id="theme-options-wrap" class="widefat" style="max-width:700px; margin: 20px auto;">
		<div class="icon32" id="icon-tools"><br /></div>
		<h2>WoWpi Settings</h2>
		In here you can set up the WoWpi plugin.
		<form action="options.php" method="post">
			<?php settings_fields('wowpi_options');?>
			<?php do_settings_sections('wowpi');?>
			<input name="Submit" type="submit" value="<?php echo esc_attr_e('Save Changes');?>" class="button-primary" />
		</form>
	</div>
	<?php	
}

//Acum initiem optiunile necesare pluginului
add_action('admin_init','wowpi_admin_init');
function wowpi_admin_init()
{
	// vom pastra toate valorile intr-un array
	register_setting('wowpi_options','wowpi_options','wowpi_options_validate');
	add_settings_section('wowpi_main','Main Settings','wowpi_section_text','wowpi');
	add_settings_field('wowpi_client_id','WoW Client ID','wowpi_client_id','wowpi','wowpi_main');
	add_settings_field('wowpi_client_secret','WoW Client Secret','wowpi_client_secret','wowpi','wowpi_main');
	//add_settings_field('wowpi_api_key','WoW API Key','wowpi_api_key','wowpi','wowpi_main');
	//add_settings_field('wowpi_secret_key','WoW Secret Key','wowpi_secret_key','wowpi','wowpi_main');
		
	add_settings_section('character_api','Character Section','wowpi_character_section_text','wowpi');
	add_settings_field('wowpi_character_name','Character Name','wowpi_character_name','wowpi','character_api');
	add_settings_field('wowpi_region','Region and Locale','wowpi_region','wowpi','character_api');
	add_settings_field('wowpi_realm','Realm','wowpi_realm','wowpi','character_api');
	
	
	add_settings_section('caching_section','Caching Section','wowpi_caching_section_text','wowpi');
	add_settings_field('wowpi_caching','General Caching period','wowpi_caching','wowpi','caching_section');
	add_settings_field('wowpi_character_caching','Character Caching period','wowpi_character_caching','wowpi','caching_section');
	add_settings_field('wowpi_guild_caching','Guild Caching period','wowpi_guild_caching','wowpi','caching_section');
	add_settings_field('wowpi_caching_realm','Caching for realm status','wowpi_caching_realm','wowpi','caching_section');
	
	
	add_settings_section('styling_section','Styling Section','wowpi_styling_section_text','wowpi');
	add_settings_field('wowpi_styling','CSS Styling','wowpi_styling','wowpi','styling_section');
	add_settings_field('wowpi_wowhead','Add Wowhead or Wowdb Tooltip script','wowpi_tooltips','wowpi','styling_section');
}

function wowpi_section_text()
{
    if(in_array('curl', get_loaded_extensions())) {
        $curlCheck = '<p style="color:green; font-weight:bold;">CURL is available on your webserver.</p>';
    }
    else {
        $curlCheck = '<p style="color:red; font-weight:bold;">CURL IS NOT AVAILABLE ON YOUR WEBSERVER. MOST LIKELY THE PLUGIN WON\'T WORK!</p>';
    }
	echo $curlCheck;
    echo '<p>It is very important to know that before you can use the APIs you need to <a href="https://dev.battle.net/member/register" target="_blank">register to Battle.net API</a>. Once you register, you must create an "application" inside the admin section of Battle.net to get an API key</p>';
}

function wowpi_character_section_text()
{
	echo '<p>In here you should put the main character for your plugin. This will be used as back-up in case it doesn\'t have enough data when asked to output something</p>';
}

function wowpi_caching_section_text()
{
	echo '<p>In order for your site to work well and fast, and considering the fact that querying the Blizzard\'s API takes quite a lot of resources, the plugin would have to cache the data. Once the data is saved unto your website database, the plugin will serve the saved data much faster</p>';
}

function wowpi_styling_section_text()
{
	echo '<p>Of course we also need some styling... Or not... It\'s your choice.</p>';
}

// the client id input form
function wowpi_client_id() {
	global $wowpi_options;
	echo '<input id="wowpi_client_id" name="wowpi_options[client_id]" size="40" type="text" value="'.(isset($wowpi_options['client_id']) ? $wowpi_options['client_id'] : '').'" />';
}

// the secret key input form
function wowpi_client_secret() {
	global $wowpi_options;
	echo '<input id="wowpi_client_secret" name="wowpi_options[client_secret]" size="40" type="text" value="'.(isset($wowpi_options['client_secret']) ? $wowpi_options['client_secret'] : '').'" />';
}

// the api key input form
/*
function wowpi_api_key() {
	global $wowpi_options;
	echo '<input id="wowpi_api_key" name="wowpi_options[api_key]" size="40" type="text" value="'.$wowpi_options['api_key'].'" />';
}

// the secret key input form
function wowpi_secret_key() {
	global $wowpi_options;
	echo '<input id="wowpi_secret_key" name="wowpi_options[secret_key]" size="40" type="text" value="'.$wowpi_options['secret_key'].'" />';
}*/

// the region input form
function wowpi_region(){
	global $wowpi_options;
	echo '<select id="wowpi_region" name="wowpi_options[region]">';
	$regions = array(
		'us' => array('en_US','pt_BR','es_MX'),
		'eu' => array('en_GB','de_DE','es_ES','fr_FR','it_IT','pl_PL','pt_PT','ru_RU'),
		'kr' => array('ko_KR'),
		'tw' => array('zh_TW')
	);
	foreach($regions as $region => $locales)
	{
		echo '<optgroup label="'.$region.'">';
		foreach($locales as $locale)
		{
			echo '<option value="'.$region.'|'.$locale.'"'.($wowpi_options['locale']==$locale ? ' selected' : '').'>'.$locale.'</option>';
		}
		echo '</optgroup>';
	}
	echo '</select>';
}

// the styling
function wowpi_styling(){
	global $wowpi_stylings;
	global $wowpi_options;
	echo '<select id="wowpi_styling" name="wowpi_options[styling]">';
	foreach($wowpi_stylings as $style => $style_name)
	{
		echo '<option value="'.$style.'"'.($wowpi_options['styling']==$style ? ' selected':'').'>'.$style_name.'</option>';
	}
	echo '</select>';
}

// the tooltips
function wowpi_tooltips(){
	global $wowpi_options;
	echo '<select id="wowpi_tooltips" name="wowpi_options[tooltips]">';
	echo '<option value="http://www.wowhead.com/"'.($wowpi_options['tooltips']== 'http://www.wowhead.com/' ? ' selected':'').'>WOWHEAD.com</option>';
	echo '<option value="http://www.wowdb.com/"'.($wowpi_options['tooltips']== 'http://www.wowdb.com/' ? ' selected':'').'>WOWDB.com</option>';
	echo '<option value="0"'.($wowpi_options['tooltips']== '0' ? ' selected':'').'>No tooltips</option>';	
	echo '</select>';
}

// the realm input form
function wowpi_realm() {
	global $wowpi_options;
	echo '<input id="wowpi_realm" name="wowpi_options[realm]" size="40" type="text" value="'.$wowpi_options['realm'].'" />';
}

// the character name input form
function wowpi_character_name() {
	global $wowpi_options;
	echo '<input id="wowpi_character_name" name="wowpi_options[character_name]" size="40" type="text" value="'.$wowpi_options['character_name'].'" />';
}

function wowpi_caching() {
	global $wowpi_options;
	echo '<input id="wowpi_caching" name="wowpi_options[caching]" size="5" type="text" value="'.$wowpi_options['caching'].'" /> hours';
}

function wowpi_character_caching() {
	global $wowpi_options;
	echo '<input id="wowpi_character_caching" name="wowpi_options[character_caching]" size="5" type="text" value="'.$wowpi_options['character_caching'].'" /> hours';
}

function wowpi_guild_caching() {
	global $wowpi_options;
	echo '<input id="wowpi_guild_caching" name="wowpi_options[guild_caching]" size="5" type="text" value="'.$wowpi_options['guild_caching'].'" /> hours';
}

function wowpi_caching_realm() {
	global $wowpi_options;
	echo '<input id="wowpi_realm_caching" name="wowpi_options[realm_caching]" size="5" type="text" value="'.$wowpi_options['realm_caching'].'" /> seconds';
}

// the validation of the form inputs
function wowpi_options_validate($input)
{
	global $wowpi_stylings;
	global $wowpi_options;
	
	// api key
	//$wowpi_options['api_key'] = trim($input['api_key']);
	//if(!preg_match('/^[a-z0-9]{32}$/i', $wowpi_options['api_key']))
	//{
	//	$wowpi_options['api_key'] = '';
	//}
	
	// secret key
	//$wowpi_options['secret_key'] = trim($input['secret_key']);
	//if(!preg_match('/^[a-z0-9]{32}$/i', $wowpi_options['secret_key']))
	//{
	//	$wowpi_options['secret_key'] = '';
	//}

	$wowpi_options['client_id'] = trim($input['client_id']);
	$wowpi_options['client_secret'] = trim($input['client_secret']);
	
	// caching
	$wowpi_options['caching'] = trim($input['caching']);
	if(is_numeric($wowpi_options['caching']) && ($wowpi_options['caching'] >= 0))
	{
		$wowpi_options['caching'] = (int) $wowpi_options['caching'];
	}
	else
	{
		$wowpi_options['caching'] = 12;
	}
	
	// character caching
	$wowpi_options['character_caching'] = trim($input['character_caching']);
	if(is_numeric($wowpi_options['character_caching']) && ($wowpi_options['character_caching'] >= 0))
	{
		$wowpi_options['character_caching'] = (int) $wowpi_options['character_caching'];
	}
	else
	{
		$wowpi_options['character_caching'] = 12;
	}
	
	// guild caching
	$wowpi_options['guild_caching'] = trim($input['guild_caching']);
	if(is_numeric($wowpi_options['guild_caching']) && ($wowpi_options['guild_caching'] >= 0))
	{
		$wowpi_options['guild_caching'] = (int) $wowpi_options['guild_caching'];
	}
	else
	{
		$wowpi_options['guild_caching'] = 24;
	}
	
	// realm caching
	$wowpi_options['realm_caching'] = trim($input['realm_caching']);
	if(is_numeric($wowpi_options['realm_caching']) && ($wowpi_options['realm_caching'] >= 0))
	{
		$wowpi_options['realm_caching'] = (int) $wowpi_options['realm_caching'];
	}
	else
	{
		$wowpi_options['realm_caching'] = 3000;
	}
	
	// region and locale
	$region_locale = explode('|',trim($input['region']));
	$regions = array(
		'us' => array('en_US','pt_BR','es_MX'),
		'eu' => array('en_GB','de_DE','es_ES','fr_FR','it_IT','pl_PL','pt_PT','ru_RU'),
		'kr' => array('ko_KR'),
		'tw' => array('zh_TW')
	);
	if(in_array($region_locale[1],$regions[$region_locale[0]]))
	{
		$wowpi_options['region'] = $region_locale[0];
		$wowpi_options['locale'] = $region_locale[1];
	}
	else
	{
		$wowpi_options['region'] = '';
		$wowpi_options['locale'] = '';
	}
	
	// realm
	$wowpi_options['realm'] = trim(filter_var($input['realm'], FILTER_SANITIZE_STRING));
	
	// character name
	$wowpi_options['character_name'] = trim(filter_var($input['character_name'], FILTER_SANITIZE_STRING));
	
	// styling
	$style = filter_var($input['styling'], FILTER_SANITIZE_STRING);
	$wowpi_options['styling'] = array_key_exists($style, $wowpi_stylings) ? $style : 'wowpi_faction';
	
	// tooltips inclusion
	$tooltips_options = array('http://www.wowhead.com/','http://www.wowdb.com/','0');
	$wowpi_new = $input['tooltips'];
	$wowpi_options['tooltips'] = in_array($wowpi_new,$tooltips_options) ? $wowpi_new : '0';

	foreach ( wp_load_alloptions() as $option => $value ) {
        if ( strpos( $option, 'wowpi_' ) === 0 && $option !== 'wowpi_options' && $option !== 'wowpi_version' ) {
            delete_option( $option );
        }
    }

    global $wowpi_plugin_dir;
    $imageDir = $wowpi_plugin_dir.'assets/images/wow/';
    if(file_exists($imageDir) && wp_is_writable($wowpi_plugin_dir.'assets/images/')) {
        removeDirectory($imageDir);
    }

    $uploadDir = wp_upload_dir();
    $wowpiUploadDir = $uploadDir['basedir'].'/wowpi/';
    if(file_exists($wowpiUploadDir) && wp_is_writable($uploadDir['basedir'].'/')) {
        removeDirectory($wowpiUploadDir);
    }

    // first saves
    wowpi_getRaces();
    wowpi_getClasses();
    wowpi_getRealms();
    wowpi_getCharacterAchievements();
    wowpi_getGuildAchievements();
    wowpi_getCharacterData();
    wowpi_getGuildData();

	return $wowpi_options;
}



?>