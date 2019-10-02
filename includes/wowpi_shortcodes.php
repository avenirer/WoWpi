<?php

// Let's not forget to register them...
function wowpi_register_shortcodes() {
    add_shortcode('wowpi_character','wowpi_shortcode_character');
    add_shortcode('wowpi_guild_members','wowpi_shortcode_guild_members');
    add_shortcode('wowpi_guild_progression','wowpi_shortcode_guild_progression');
    add_shortcode('wowpi_guild_achievements','wowpi_shortcode_guild_achievements');
    add_shortcode('wowpi_tabard','wowpi_shortcode_guild_tabard');
    add_shortcode('wowpi_realms','wowpi_shortcode_realms');
}
 
add_action( 'init', 'wowpi_register_shortcodes' );