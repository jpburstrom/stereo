<?php 
/*
 * Plugin name: Stereo
 * Description: Your WordPress Music Player
 * Author: Johannes Burström
 *
 *
 */


define('STEREO_PLUGIN_DIR', dirname( __FILE__) . "/" );

require dirname( __FILE__ ) . "/lib/scb/load.php";

scb_init('stereo_init');

function stereo_init() {
	add_action( 'plugins_loaded', 'stereo_load_p2p_core', 20 );
	add_action( 'init', 'stereo_connection_types' );
}

function stereo_load_p2p_core() {
	if ( function_exists( 'p2p_register_connection_type' ) )
		return;

	define( 'P2P_TEXTDOMAIN', 'stereo' );

	require_once dirname( __FILE__ ) . '/lib/p2p-core/init.php';

	// TODO: can't use activation hook
	add_action( 'admin_init', array( 'P2P_Storage', 'install' ) );
}

function stereo_connection_types() {
	p2p_register_connection_type( array(
		'name' => 'playlist_to_tracks',
		'from' => 'stereo_playlist',
		'to' => 'stereo_track',
        'cardinality' => 'one-to-many'
	) );
}

if (is_admin()) {
    require_once("admin/init.php");

}
