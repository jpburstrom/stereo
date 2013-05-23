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

/**
 * Get metadata for track
 *
 * @uses get_post_meta
 */
function get_stereo_track_meta( $trackid ) {
    return get_post_meta($trackid, '_stereo', true);
}

/**
 * Get metadata for audio attachment
 */

function get_stereo_attachment_meta( $attachment_id ) {
    return get_post_meta($attachment_id, "_stereo_metadata", true);
}

/**
 * Get streaming link for track
 */
function get_stereo_streaming_link( $trackid=false) {
    global $post;
    if (false === $trackid) {
        $trackid = $post->ID;
    }
    $meta = get_stereo_track_meta($trackid);
    $out = ($meta['fileid']) ?  site_url( trailingslashit(stereo_option('streaming_slug')) . trailingslashit($trackid) ) : '';
    return $out;
}
            
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
