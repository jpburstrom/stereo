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
    if (isset($options[$option])) {
        $opt = $options[$option];
    }
    return $opt;
}

/**
 * Get metadata for track
 *
 * @uses get_post_meta
 */
function get_stereo_track_meta( $trackid ) {
    $meta = get_post_meta($trackid, '_stereo', true);
    $meta = (!empty($meta)) ? $meta : NULL;
    return $meta;
}

function get_stereo_connected_artist( $id ) {
    $p = get_posts(array( 'connected_type' => 'playlist_to_artist', 'connected_items' => $id, 'nopaging' => true, 'suppress_filters' => false));
    if ($p)
        return $p[0];
    else 
        return $p;
}

function get_stereo_connected_artist_id( $id ) {
    $p = get_posts(array( 'fields' => 'ids', 'connected_type' => 'playlist_to_artist', 'connected_items' => $id, 'nopaging' => true, 'suppress_filters' => false));
    if ($p)
        return $p[0];
    else 
        return $p;
}

function get_stereo_artist_from_playlist( $id = false, $link = false ) {
    global $post;
    if (!$id) {
        $id = $post->ID;
    }
    $artist = get_stereo_connected_artist($id);
    if ($artist) {
        if ($link) {
            $artist = "<a href='" . get_permalink($artist->ID) . "' title='" . __("Open artist page", "stereo") . "'>{$artist->post_title}</a>"; 
        } else {
            $artist = $artist->post_title;
        }
    } else {
        $artist = get_post_meta($id, "_stereo_other_artist", true);
    }
    return $artist; 
}

function the_stereo_artist( $id = false, $link = true ) {
    echo get_stereo_artist_from_playlist($id, $link);
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
 * Get base url for js history
 */
function stereo_history_root($append="") {
    $cmp = parse_url(home_url());
    return $cmp['scheme'] ."://". $cmp['host'];
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
    $out = "";
    if ($meta)
        $out = ($meta['fileid']) ?  stereo_url("stream/" . trailingslashit($trackid) ) : '';
    return $out;
}

/**
    Get playlist query
 */
function get_stereo_playlist_query($id) {
    $id = $id ? $id : get_queried_object_id();
    $connected = new WP_Query( array(
        'connected_type' => 'playlist_to_tracks',
        'connected_items' => $id,
        'connected_query' => array( 'post_status' => 'any' ),
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
    return $connected;
}

/**
 * Print opening tag for playlist item
 */
function the_stereo_track_tag($meta, $tag="li") {
    global $post;
    echo "<$tag class='stereo track"; 
    if ($meta && $meta['host'] != "") {
        echo " active' data-stereo-track='$post->ID'>";
    } else {
        echo "'>";
    }
}

/**
 * Print playlist for current $post
 */
function the_stereo_playlist ($id) {
    global $post;
    //Do not show playlist if password is required
    if (post_password_required())
       return; 
    $connected = get_stereo_playlist_query($id);
?>
    <?php include("views/playlist.php") ?>
<?php
    wp_reset_postdata();
}

/**
 * Check if we have a playlist
 *
 * @return bool
 */ 
function have_stereo_playlist ($id) {
    global $post;
    $connected = get_stereo_playlist_query($id);
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
       //Get all tracks from published playlists
        $connected = new WP_Query( array(
            'connected_type' => 'playlist_to_tracks',
            'connected_items' => 'any',
            'connected_direction' => 'from',
            'connected_query' => array( 'post_status' => 'publish' ),
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        $p = $connected->posts;
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
 * Get StereoSoundCloud instance
 *
 * @return StereoSoundCloud instance
 */
function stereo_sc() {
    static $sc;
    if (!$sc) {
        $sc = new StereoSoundCloud();
    }
    return $sc;
}

