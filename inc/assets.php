<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Register assets
 */

function stereo_enqueue_assets() {
    $cssdir = STEREO_PLUGIN_URL . "css/";
    $jsdir = STEREO_PLUGIN_URL . "js/";
    wp_register_style('stereo-widget', "$cssdir/stereo-widget.css");
    wp_register_script('stereo-widget', "$jsdir/stereo-widget.js", array("jquery")); #XXX
}

add_action( 'wp_enqueue_scripts', 'stereo_enqueue_assets' );
