=== Plugin Name ===
Contributors: jpburstrom
Tags: mp3 player, mp3, music, discography, portfolio, sound
Requires at least: 3.9
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An mp3 player and music library plugin, for creating a band site, discography or sound portfolio. 

== Description ==

Stereo is a plugin to create a music library with a configurable player. The plugin creates a special Playlist post type,
which can be used for an album discography or a sound portfolio. 

Stereo comes with the following features:

* The player engine, based on [SoundManager2](http://www.schillmania.com/projects/soundmanager2/)
* A widget with control buttons and a label showing the currently playing track. 
* A custom playlist post type, listing clickable tracks
* An (optional) Continuous Playback mode, allowing site navigation without interrupting the sound. 
* Sound files can be uploaded to the WordPress Media Library, or fetched from SoundCloud.
* Tracks can be 'empty', to control which tracks in a playlist that are streamable.
* Configurable CSS: Choose to include the plugin CSS or not, and override to taste. 

== Installation ==

1. Upload the plugin directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin

== Frequently Asked Questions ==

= How do I enable SoundCloud? =

You need to provide a SoundCloud user name and a [SoundCloud Client ID](http://soundcloud.com/you/apps/new) in the Stereo settings page.

= Can I play private tracks from SoundCloud? =

Not yet.

= How do I enable continuous playback? =

You need to enable it in the settings, and define which elements you want to reload on page load. An example:

* `#banner` is a static header, with main menu etc
* `#primary` is the main blog content, different on every page
* `#sidebar’ is a sidebar that may change on different page templates.

Put the player widget somewhere inside `#banner`, and use `#primary,#sidebar` as the **Elements to reload** setting. If all is well, this should work. But themes are different, and it may require some tinkering.

== Screenshots ==

1. Adding tracks
2. The player widget

== Changelog ==

= 1.0.0 =
* Initial version

== Upgrade Notice ==
