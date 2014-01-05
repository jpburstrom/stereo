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
    wp_register_style('stereo-widget', "$cssdir/stereo-widget.css");
    wp_enqueue_style('stereo', "$cssdir/stereo.css");
    wp_enqueue_style('stereo-icons', "$cssdir/icons.css");

    wp_register_script('stereo', STEREO_PLUGIN_URL . "js/stereo/js/stereo.js", array("backbone")); //FIXME
    wp_register_script('stereo-widget', "$jsdir/stereo-widget.js", array("stereo"));


    wp_enqueue_script('stereo');
    wp_localize_script('stereo', 'Stereo', array(
        'options' => array(
            'urlRoot' => stereo_url(),
            'doInit' => true,
            'sm' => array(
                'debugMode' => false,
                'url' => STEREO_PLUGIN_URL . "js/stereo/swf/",
            ),
            'history' => array(
                'container' => 'body',
                'enable' => stereo_option('ajax_enable'),
                'elements' => stereo_option('ajax_elements'),
                'ignore' => stereo_option('ajax_ignore'),
                'scrollTime' => (int) stereo_option('ajax_scrollTime')
            )
        )
    ));

}

add_action( 'wp_enqueue_scripts', 'stereo_enqueue_assets' );
