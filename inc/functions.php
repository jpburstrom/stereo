<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Common functions 
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
    $opt = $options[$option];
    return $opt;
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
 * Get base url for streaming and info
 */
function stereo_url($append="") {
    return home_url( trailingslashit(stereo_option('rewrite_slug')) . $append);
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
    $out = ($meta['fileid']) ?  stereo_url("stream/" . trailingslashit($trackid) ) : '';
    return $out;
}

/**
 * Print opening tag for playlist item
 */
function the_stereo_track_tag($meta, $tag="li") {
    global $post;
    echo "<$tag class='stereo track"; 
    if ($meta['host'] != "") {
        echo " active' data-stereo-track='$post->ID'>";
    } else {
        echo "'>";
    }
}

/**
 * Print playlist for current $post
 */
function the_stereo_playlist () {
    global $post;
    $connected = p2p_type( 'playlist_to_tracks' )->get_connected( $post, array('posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC') );
?>
    <?php include("views/playlist.php") ?>
<?php
}

/**
 * Check if we have a playlist
 *
 * @return bool
 */ 
function have_stereo_playlist () {
    global $post;
    $connected = p2p_type( 'playlist_to_tracks' )->get_connected( $post, array('posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC') );
    return $connected->have_posts();
}

/**
 * Get default track settings 
 *
 * @return array
 */
function get_stereo_default_tracks() {
   $opt = get_option("stereo_default_tracks");
   switch( $opt["default_track_mode"] ) {
   case "random":
       //Get all tracks
       $p = get_posts(array(
           "post_type" => "stereo_track",
           "posts_per_page" => -1,
       ));
       //If we have a default playlist, remove that option
       unset ($opt["playlist_choice"]);
       break;
   case "playlist":
        //Find all tracks for the chosen playlist
        $p = p2p_type( 'playlist_to_tracks' )->get_connected( $opt["playlist_choice"], array('posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC') )->posts;
        //if we have a track count option, remove it
       unset ($opt["track_count"]);
       break;
   default:
       return false;
       break;
   }

   if ($p) {
       foreach ($p as $post) {
           if (get_stereo_track_meta($post->ID)) {
               $opt["tracks"][] = $post->ID;
           }
       }
   }

   return $opt;
}

/**
 * Soundcloud connection dance
 *
 * @return Services_SoundCloud || false if no client id
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
