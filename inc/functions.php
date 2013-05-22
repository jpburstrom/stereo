<?php 
/**
 * Commmon functions
 * Stereo
 *
 * (c) Johannes Burström 2013
 */

/**
 * Get stereo option
 *
 * @return mixed  Option value
 */

function stereo_option( $option ) {
    static $options;
    if ( !isset($options) ) {
        $options = get_option( 'stereo_options' );
    }
    return $options[$option];
}



/*
 * Init and return SoundCloud class
 *
 * @return Services_SoundCloud | bool  false if unsuccessful connection
 */

function stereo_init_sc() 
{
    require_once( STEREO_PLUGIN_DIR . "/lib/php-soundcloud/Services/Soundcloud.php" );

    if ($clientid = stereo_option("soundcloud_id")) {
        //$secret = stereo_option("soundcloud_secret");
        if (!$secret) $secret = null;
        $sc = new Services_SoundCloud($clientid, "");
        return $sc;
    }
    return false;
}
