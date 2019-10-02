=== WoWpi ===
Contributors: avenirer
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6PDPV9L47HC86
Tags: World of Warcraft, wow, armory
Requires at least: 3.0.1
Tested up to: 4.9.8
Stable tag: 2.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The WoWpi plugin allows you to retrieve data from Battle.net API regarding your World of Warcraft character and/or guild.

== Description ==

**ATTENTION: AFTER AN UPDATE IT IS IMPORTANT TO GO TO THE SETTINGS OF THE PLUGIN AND DO A SAVE, IN ORDER TO CLEAR THE OLD DATA**

= Highlights =

**Guild Roster**

**Guild Progression (as I see it...)**

**Guild Achievements**

**Guild Tabard**

**Character Datasheets**

**Realm status**

The plugin allows you to get your character data from Battle.net API service. The plugin uses caching of 12 hours, so you don't have to worry about reaching the quota established by the Battle.net. Also, for general data, like classes, factions, races and so on, the cache is set for 14 days. If you however find yourself in the position to show data that appeared between the refresh of caches you can go to the admin section and save your data again, which will simply destroy all cache.

For example, a new expansion appeared in World of Warcraft with and you created a new character of a race that didn't exist before the expansion. In order to show that race you need to refresh the cache. Just go to WoWpi settings and push the save button. That's it.

The plugin allows you to choose if you want to include the Tooltip script from Wowhead (the one that shows a tooltip on mouseover). More info on the Tooltip can be found here (http://www.wowhead.com/tooltips).

Also, when retrieving the Guild Tabard, the plugin uses a personal script located at http://wow-hunter.ro/tabard-creator (by the way, my wow blog is http://wow-hunter.ro)

== Installation ==

The WoWpi plugin allows you to get your character data from Battle.net API service.

1. Upload the plugin files to the `/wp-content/plugins/wowpi/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Create an account on Battle.net API (https://develop.battle.net/).
4. After creating an account on the Battle.net API website, you must add your website as a client (https://develop.battle.net/access/clients). Once created, the app will provide with a Client ID and a Client Secret.
5. On your WordPress, you must go to "Settings" > "WoWpi Settings" screen, and fill those in, including the Region and Locale, Realm, and Character Name.

= Use it inside themes as PHP function =

**wowpi_show_character()**

After you've set up the plugin, you can output the character data in your theme by using the wowpi_show_character() function:

`<?php
if (function_exists('wowpi_show_character')) {
 wowpi_show_character();
}
;?>`

The code above will output character name, profile picture, guild, main title, level, race and class.

For aditional information regarding the character, you must pass the function an array with the data you want retrieved:

- 'achievement_points' - will output the achievement points your character has;

- 'achievement_feed' - will output the achievement feed of your character;

- 'kills' - shows how many honorable kills your character did;

- 'professions' - enumerates the character's professions and their levels;

- 'ilvl' - outputs the item level of the character;

- 'gear' - outputs the gear that the character is having;

- 'artifact_weapon' - outputs the artifact weapon traits;

- 'talents' - shows the talents that the character has enabled.

For example, in order to get the talents and the gear of your character, you can insert the following php code:

`<?php
if (function_exists('wowpi_show_character')) {
 wowpi_show_character(array('talents','gear'));
}
;?>`

You can also insert another character, different than the main one (the one you've set up in the settings), by mentioning the character name as a second paramenter of the function:

`<?php
if (function_exists('wowpi_show_character')) {
 wowpi_show_character(array('kills','professions'),'Thonk');
}
;?>`

**wowpi_get_guild_tabard($guild_name = null, $realm = null)**

Returns you the url of the guild tabard, created by http://tabard.gnomeregan.info/ using the data received from the API.
$guild_name and $realm are optional parameters. If you don't pass them, the tabard retrieved is the one that belongs to the character you've set up in the administration panel of WoWpi.

= Use it as a widget =

You can also use the plugin through its widgets, by going to the Widgets inside your administration interface... I do not think this needs more explaining.

= Shortcodes? =

You can use the following shortcodes inside your posts (or templates...):

`[wowpi_character]`

'[wowpi_character]' allows you to show a character data sheet. The shortcode also accepts suplemental parameters. These are:

*name="Name of character"* - allows you to show characters that don't necessary belongs to you (like the one you've set inside the WoWpi Settings page). You can also use "[username] - Guild name" or [nickname] - Guild name" if you want the plugin to take the name of the character from logged in username/nickname.

*realm="Realm"* - allows you to show characters from another realm

*show="talents,gear"* - allows you to only show specific parts of the character sheet (although the name of the character, title level, race and class will always appear - you can hide them by applying css, if you want to). You can show the following: **achievement_points, achievement_feed, kills, professions, gear, artifact_weapon, talents** (all of them are shown by default)

*type="condensed"* - allows you to choose between to types of tables (and designs). You can choose between "condensed" and "extended".

*class="table table-striped table-condensed"* - you can add a css class to your html table. If you do not mention the class name, the default class name inserted by the plugin will be ".wowpi_character_data".

`[wowpi_guild_members]`

'[wowpi_guild_members]' allows you to show your guild's members. The shortcode also accepts suplemental parameters. These are:

*guild="Name of Guild"* - allows you to show members of another guild, not the guild that your character belongs to (the one you've set inside the WoWpi Settings page)

*realm="Name of realm"* - allows you to show members of another realm's guild

*ranks="9,8"* - allows you to show only members with a specified rank

*rank_names = "0:The Big Boss|1:The subordinates|2:The others"* - if you want to be original, you can name the ranks so instead of "Rank 1" another name may appear, like "The Others". Please take note that, the guild master's rank is "0".

*id="the_roster"* - you can add an id to the html table element if you want to. This should happen only if you have more than one guild roster table on the page.

*class="table table-striped table-condensed"* - you can add a css class to your html table. If you do not mention the class name, the default class name inserted by the plugin will be ".wowpi_guild_roster".

*order_by="1|asc"* - you can set an order in which the members of the guild will be displayed. You need to pass the column number and eventually the direction of the ordering (by default it is ascending: examples: order_by="2"; order_by="4|desc").

*rows_per_page="25"* - you can set the number of rows displayed on each "page" of the table. Imagine you have a huge guild with many players. You don't want to display a huuuuge page with all the members, so you can decide how many members should be displayed on every "page" of the roster.

*direction="ASC"* - you can set the direction by which the ordered members are shown, either ascending (ASC), or descending (desc)

*linkto="advanced"* - as you can imagine, every character inside the roster has a link that sends the users to the official World of Warcraft website (the character's page). By default the page that the user is sent to is the "simple" one. If you want to send the user to the "advanced" view you can pass this parameter with either "simple" or "advanced" as values. You may aswell link the character name to an internal page or another website by passing the base url as value. If for example you have an address where you present each character like: http://wowblog.com/character/CharacterName, you should pass "http://wowblog.com/character/" as value for linkto.

*paginate="false"* - by default, the table is paginated (imagine what would happen if the guild had more than a hundred members...). If you don't want the table to be paginated, you can use this parameter passing it "false" as value.

*hidecolumns="2,3"* - maybe you don't want some columns in the table to be shown. You can do that by passing the "hidecolumns" parameter and giving it the number of the column(s) you want to hide.

*table_style="profile_picture|notop"* - table_style changes the way the table is shown. For example, if we put "profile_picture" as value for table_style, this will make the shortcode to show the profile pictures of the guild members in a column, instead of race and class columns. PLEASE TAKE NOTE THAT, DEPENDING ON THE NUMBER OF MEMBERS IN THE GUILD ROSTER, AT LEAST THE FIRST LOAD MIGHT TAKE "FOREVER" TO RETRIEVE THE AVATARS OF THE CHARACTERS. You can mention more than one elements inside the table_style as values, by separating them with a vertical pipe. Besides the "profile_picture" you may want to eliminate the top part of the table (the one that allows users to set the number of members per page and the search input). You can do this by using the "notop" value.

`[wowpi_tabard]`

'[wowpi_tabard]' allows you to output an image with your guild's tabard. When retrieving the Guild Tabard, the plugin uses my tabard creator (http://wow-hunter.ro/tabard-creator) , and saves the tabard in the uploads directory. The shortcode also accepts suplemental parameters. These are:

*guild="Name of Guild"* - allows you to show the tabard of another guild, not the guild that your character belongs to (the one you've set inside the WoWpi Settings page)

*id="the_one"* - you can add an id to the img element if you want to.

*class="my_tabard"* - you can add a css class to your img. If you do not mention the class name, the default class name inserted by the plugin will be ".crest"

`[wowpi_guild_progression]`

'[wowpi_guild_progression]' shows a table with all the dungeons and raids made by the guild (completed or not).

`[wowpi_realms]`

'[wowpi_realms]' allows you to show your realm status. The shortcode also accepts suplemental parameters. These are:

*realm="Realm name"* - allows you to show a specific realm status (not the one that your character belongs to). You can mention more than one realm if you separate the realms using a pipe "|".

*battlegroup="Name of battlegroup"* - allows you to show the statuses of all the realms that belong to a battlegroup

*view="battlegroup"* - If you don't know what battlegroup your realm is part of, you can name the realm (or don't name it if it's the same as your character's), and use view="battlegroup". All realms of your realm battlegroup will be shown.

*show = "status|type|population"* - if you want to show more than just the status of the realm you can mention what you want to be shown by separating them with a pipe "|". The widget can show the status, the type of the realm (RP, PVP, RPPVP), and the range of the population (low, medium, high).

*class="my_own_css_class"* - you can add a CSS class to your realm status table.

`[wowpi_guild_achievements]`

'[wowpi_guild_achievements]' allows you to show your guild achievements. The shortcode also accepts suplemental parameters. These are:

*guild="Name of Guild"* - allows you to show members of another guild, not the guild that your character belongs to (the one you've set inside the WoWpi Settings page)

*realm="Name of realm"* - allows you to show members of another realm's guild

*class="personal_class"* - you can add a css class to your list.

*limit="none"* - you can set a limit of achievements. You can show only the last 10 of them by using limit="10"

== Screenshots ==

1. WoWpi Settings Panel.
2. WoWpi Character Widget Panel.
3. WoWpi Guild Roster (don't worry about the language...)
4. WoWpi Sidebar character widget
5. WoWpi Character data sheet
6. WoWpi Guild Progression
7. WoWpi Realm Status
8. An implementation of WoWpi
9. Another implementation of WoWpi
10. Eh... another one...


== Frequently Asked Questions ==

= Are there any bugs? =

I sure hope not, but this being my first plugin I can't guarantee for everything. Anyway, you can at any time write me at **avenir.ro@gmail.com** or use the forum to report any issues or ask for more things. Also, please, do tell me if the plugin needs something else added or if you have some styling proposals.

== Changelog ==
= 2.5.2 =
Added BfA dungeons and raids to progression
= 2.5.1 =
Fixing some api calls for guild progression
= 2.5.0 =
Trying to connect using oAuth2
= 2.4.2 =
The character image didn't show. Now it should.
= 2.4.1 =
Forgot what I did
= 2.4 =
Refactoring
= 2.3.6 =
Repaired the showing of allied races thumbs. Thank you @tectas for the fix. The problem was reported here: https://wordpress.org/support/topic/allied-races-image-error/
= 2.3.6 =
Repaired the "murloc" face, by retrieving the character avatar from the right url. Thank you, Bastiaan.
= 2.3.5 =
forgot to mention changes
= 2.3.4 =
bug squashing - if in widget was chosen nickname or password, when no one was logged in an error appeared. some guild achievements didn't have points... hence some errors
= 2.3.3 =
add guild achievement points total
= 2.3.2 =
Allowing users to have more than one guild roster on a page.
= 2.3.1 =
Separating item level from the gear output
= 2.3.0 =
Added item level to the gear output
= 2.2.9 =
Repaired a small bug...
= 2.2.8 =
In the hopes of not dealing with curl anymore, used the native Wordpress HTTP API. Added [wowpi_guild_achievements]
= 2.2.7 =
Changed admin interface and added caching options.
= 2.2.6 =
Added tabard creator to use my script.
= 2.2.5 =
Added some screenshots. Created a new facility that shows the realm status.
= 2.2.4 =
Debuggind links of the guild roster (language specific links). Added option to use nickname inside the shortcode. Added option to link the member to a different page than those on blizzard
= 2.2.3 =
Added option to not show the top of the table (the one that has the search and page length elements).
= 2.2.2 =
Added Guild Progressions the way I think they should be... If you think of another way, please do tell me.
= 2.2.1 =
Started work at Guild Progressions
Repairing some guild roster errors and widget...
= 2.2.0 =
Added possibility for blog admins to allow logged-in users (with their character name as username) to see their own character data on the widget. Needs testing...
Changed the dark and faction styling for the guild roster and widget.
= 2.1.9 =
Added rank naming on guild roster
Styling the names in guild roster with class specific colors (on dark and faction themes, not on light...)
= 2.1.8 =
Update on character data to be more inline with Blizzard's talent calculator
= 2.1.7 =
Artifact weapon traits rank added to tooltips (hence modified the look)
= 2.1.6 =
Radical changes. Execution time reduced to half.
Artifact relics now have the spec added
= 2.1.5 =
Added german language thanks to Santio (http://entropie-gaming.com/hain-der-traeume/gargy-the-owl/)
Added language domain to wowhead tooltips
= 2.1.4 =
Added the possibility for admins to choose what tooltips to get, either from wowhead, or from wowdb, or none.
Worked on the tooltips in order for them to show the correct values and/or sets that are actually on the characters and not generic tooltips.
= 2.1.3 =
Now the images taken from Blizzard are imported locally and then outputed.
Now you can also show artifact weapon on character sheet, thanks to the artifact trait-to-spell json file that can be found here: https://gist.github.com/erorus/06eda88ed9eaf18ad7b4c9cf62eda528
= 2.1.2 =
Images wouldn't show when character shortcode was called, because the class names are different depending on the realm language. So I chose to use the universal language... numbers.
= 2.1.1 =
repairing some bugs regarding two parts classes (death knight, demon hunter)
start work on artifact weapon display
= 2.1.0 =
overhaul of the caching and organizing files
= 2.0.9 =
This is sooooo embarrassing... repaired the caching...
= 2.0.8 =
Added new parameters for wowpi_guild_members: linkto - for setting the page type for the character sheet on blizzard's site, paginate - option regarding the pagination of the table, hidecolumns - option for hiding columns in table
= 2.0.7 =
Some updates on shortcodes for US (sorry for that US users...)
Caching wasn't done properly
Guild Master instead of Guild master
Using "DataTables Table plug-in for jQuery": https://datatables.net/ - reason: paginating the guild roster
= 2.0.6 =
A lot of changes thanks to a user named Kevin Ference
= 2.0.3 =
Inserting table sorter for guild roster
= 2.0.1 and 2.0.2 =
Repairing some errors...
= 2.0.0 =
Started adding language support
= 1.1.9 =
Added a new shortcode [wowpi_character]
Started translations... a reason for version 1.2
= 1.1.8 =
Added "order_by" and "direction" as options for [wowpi_guild_members]
Names of the members in [wowpi_guild_members] will act as links toward the battle.net armory.
= 1.1.7 =
Added the [wowpi_tabard] shortcode and wowpi_get_guild_tabard() function
= 1.1.6 =
Some errors repaired... not worth mentioning
= 1.1.5 =
Added the [wowpi_guild_members] shortcode
= 1.1.4 =
Added option to include the Wowhead tooltip script.
= 1.1.3 =
Changed the class name for the character container. Can break compatibility if you have your own css...
= 1.1.2 =
Working on guild output
= 1.1 =
* [enhancement] added a new view wowpi_show_guild();
* [bug] removed “of the” when the character has no guild;
= 1.0 =
* The first commit
