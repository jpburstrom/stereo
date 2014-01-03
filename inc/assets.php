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

    //Need backbone 1.1
    wp_deregister_script('backbone');
    wp_register_script('backbone', STEREO_PLUGIN_URL . "js/stereo/src/vendor/backbone.js", array('underscore','jquery'), '1.1.0', 1 );

    wp_register_script('stereo', STEREO_PLUGIN_URL . "js/stereo/dist/stereo.js", array("backbone")); //FIXME
    wp_register_script('stereo-widget', "$jsdir/stereo-widget.js", array("stereo"));


    wp_enqueue_script('stereo');
    wp_localize_script('stereo', 'Stereo', array(
        'options' => array(
            'urlRoot' => stereo_url(),
            'doInit' => true,
            'sm' => array(
                'debugMode' => false,
                'url' => STEREO_PLUGIN_URL . "js/stereo/src/vendor/soundmanager2/swf/", //FIXME
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
