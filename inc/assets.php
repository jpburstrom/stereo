<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Register assets
 */

function stereo_enqueue_assets() {
    $cssdir = STEREO_PLUGIN_URL . "css";
    $jsdir = STEREO_PLUGIN_URL . "js";
    if (stereo_option('include_css') || is_admin()) {
        wp_register_style('stereo-widget', $cssdir . "/stereo-widget.css");
        wp_enqueue_style('stereo', $cssdir . "/stereo.css");
    }
    //wp_enqueue_style('stereo-icons', stereo_find_asset("icons.css", $cssdir));

    wp_register_script('stereo', "$jsdir/stereo.js", array("backbone")); 
    wp_register_script('stereo-widget', "$jsdir/stereo-widget.js", array("stereo"));


    wp_enqueue_script('stereo');
    wp_localize_script('stereo', 'Stereo', array(
        'options' => array(
            'urlRoot' => stereo_url(),
            'doInit' => true,
            'sm' => array(
                'debugMode' => false,
                'url' => STEREO_PLUGIN_URL . "js/swf/",
            ),
            'history' => array(
                'container' => 'body',
                'enable' => stereo_option('ajax_enable'),
                'elements' => stereo_option('ajax_elements'),
                'ignore' => stereo_option('ajax_ignore'),
                'scrollTime' => (int) stereo_option('ajax_scrollTime')
            ),
            'default_tracks' => get_stereo_default_tracks(),
        )
    ));

}

add_action( 'wp_enqueue_scripts', 'stereo_enqueue_assets' );
