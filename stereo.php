<?php 
/*
Plugin name: Stereo
Plugin URI: http://wp.ljud.org/stereo
Description: Your WordPress Music Player
Version: 1.0.0
Author: Johannes Burström
Author URI: http://johannes.ljud.org
License: GPL2

Copyright 2013  Johannes Burström  (email : johannes@ljud.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

define('STEREO_PLUGIN_DIR', dirname( __FILE__) . "/" );
define('STEREO_PLUGIN_URL', plugin_dir_url( __FILE__)  );
define('STEREO_VERSION', "1.0.0");

require STEREO_PLUGIN_DIR . "lib/scb/load.php";

scb_init('stereo_init');

function stereo_load_p2p_core() {
	if ( function_exists( 'p2p_register_connection_type' ) )
		return;

	define( 'P2P_TEXTDOMAIN', 'stereo' );

	require_once STEREO_PLUGIN_DIR . 'lib/p2p-core/autoload.php';

    P2P_Storage::init();
    P2P_Query_Post::init();
    register_uninstall_hook( __FILE__, array( 'P2P_Storage', 'uninstall' ) );
}

function stereo_connection_types() {
	p2p_register_connection_type( array(
		'name' => 'playlist_to_tracks',
		'from' => 'stereo_playlist',
		'to' => 'stereo_track',
        'cardinality' => 'one-to-many'
	) );
	p2p_register_connection_type( array(
		'name' => 'playlist_to_artist',
		'from' => 'stereo_playlist',
		'to' => 'stereo_artist',
        'cardinality' => 'many-to-one'
	) );
}

function stereo_init() {
	add_action( 'plugins_loaded', 'stereo_load_p2p_core', 20 );
	add_action( 'init', 'stereo_connection_types' );

    require('inc/functions.php');
    require('inc/info.php');
    require('inc/soundcloud.php');
    require('inc/stream.php');
    require('inc/assets.php');
    require('inc/widget.php');
    require("inc/custom-post.php");

    if (is_admin()) {
        require_once("admin/init.php");
    }


}


function stereo_activation() {
    $notices= get_option('stereo_deferred_admin_notices', array());
    $notices[]= "options_nag";
    update_option('stereo_deferred_admin_notices', $notices);
}

register_activation_hook(__FILE__, 'stereo_activation');
