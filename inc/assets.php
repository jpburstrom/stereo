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

    if (!is_admin() && !is_active_widget(false, false, 'stereo_player') && !current_theme_supports('stereo-playlists')) {
        return;
    }

    if (stereo_option('include_css') || is_admin()) {
        wp_register_style('stereo-widget', $cssdir . "/stereo-widget.css");
        wp_enqueue_style('stereo', $cssdir . "/stereo.css");
    }
    //wp_enqueue_style('stereo-icons', stereo_find_asset("icons.css", $cssdir));

    if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
        wp_register_script('stereo', "$jsdir/stereo.js", array("jquery")); 
    } else {
        wp_register_script('stereo', "$jsdir/stereo.min.js"); 
    }
    wp_register_script('stereo-widget', "$jsdir/stereo-widget.js", array("stereo"));


    wp_enqueue_script('stereo');
    wp_localize_script('stereo', 'Stereo', array(
        'options' => apply_filters('stereo_options', array(
            'urlRoot' => stereo_url(),
            'doInit' => true,
            'sm' => array(
                'debugMode' => false,
                'url' => STEREO_PLUGIN_URL . "js/swf/",
            ),
            'history' => array(
                'container' => 'body',
                'urlRoot' => home_url(),
                'enable' => stereo_option('ajax_enable'),
                'elements' => stereo_option('ajax_elements'),
                'ignore' => stereo_option('ajax_ignore'),
                'scrollTime' => (int) stereo_option('ajax_scrollTime')
            ),
            'default_tracks' => get_stereo_default_tracks(),
            'controls' => array(
                //Pass an id of the control container, which should exist in the source
                'elements' => "#stereo_controls",
                //Choose which components, and their source order
                'order' => array('Buttons', 'Label', 'Position', 'Time'),
                'label_order' => array('title', 'playlist', 'playlist-artist'),
                'labelTicker' => true
            )
        ))
    ));

}

add_action( 'wp_enqueue_scripts', 'stereo_enqueue_assets' );
