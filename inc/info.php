<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Track info API endpoint
 * 
 */


class StereoPlaylistInfo {
    var $title =     "";
    var $artwork =  "";
    var $url = "";
    var $artist = "";
    var $artist_url = "";

    function __construct($playlist) {
        $this->title = $playlist->post_title;
        $this->url = get_permalink($playlist->ID);
        $artist = get_stereo_connected_artist($playlist->ID);
        if ($artist) {
            $this->artist = $artist->post_title; 
            $this->artist_url = get_permalink($artist->ID);
        } else {
            $this->artist = get_post_meta($playlist->ID, "_stereo_other_artist", true);
        }
        $a = wp_get_attachment_image_src(get_post_thumbnail_id($playlist->ID), stereo_option("artwork_size"));
        $this->artwork = $a ? array(
            "url" => $a[0],
            "width" => $a[1],
            "height" => $a[2]
        ) : null;
    }
}

class StereoTrackInfo {

    var $title = "", 
        $playlist, 
        $artist = "",
        $album = "",
        $year = "",
        $genre = "",
        $stream_url = "";

    /**
     * Constructor  
     * @param $track  stereo_track post object
     */
    function __construct($track, $data, $playlist=false) 
    {
        $this->title = $track->post_title;
        //This should be regenerated every time, yes
        $this->stream_url = get_stereo_streaming_link($track->ID);

        if (!$playlist) {
            $playlist = new WP_Query( array(
                'connected_direction' => 'to',
                'connected_type' => 'playlist_to_tracks',
                'connected_items' => $track,
                'nopaging' => true,
            ));
            if ($playlist->found_posts == 1) {
                $playlist = $playlist->posts[0];
            } 
        }
        $this->playlist = new StereoPlaylistInfo($playlist);
        if ($data) {
            unset ($data['fileid'], $data['host']);
            foreach ($data as $k => $v) {
                $this->$k = ($v) ? $v : '';
            }
        }

    }

}

class StereoInfoRewrite {

    function __construct() 
    {
        $this->expiry_time = 60*60*24;

        add_action('init', array(&$this, 'add_rewrite_rules'));
        add_action('query_vars', array(&$this, 'stereo_query_vars') );
        add_action('pre_get_posts', array(&$this, 'pre_get_posts'), 9 );

    }

    function add_rewrite_rules()
    {
        $stream = stereo_option('rewrite_slug');
        add_rewrite_rule('^' . $stream . '/(tracks|playlists)/([^/]*)/?', 'index.php?stereo_type=$matches[1]&stereo_id=$matches[2]','top');
        add_rewrite_rule('^' . $stream . '/(tracks|playlists)/?', 'index.php?stereo_type=$matches[1]','top');
    }

    function stereo_query_vars( $query_vars )
    {
        $query_vars[] = 'stereo_type';
        return $query_vars;
    }

    function pre_get_posts($query) 
    {
        if ( !$query->is_main_query() ) {
            return;
        }
        if ( !isset ( $query->query_vars['stereo_type'] )) {
            return;
        }

        $ids = explode(",", $query->query_vars['stereo_id']);

        switch($query->query_vars['stereo_type']) {
        case 'tracks': 
            $this->tracks($ids);
            break;
        case 'playlists':
            $this->playlists($ids);
            break;
        }

        header('HTTP/1.1 404 Not Found');
        echo "404";
        die();
    }

    /**
     * Json output for a single or multiple tracks
     */
    function tracks($ids) {
        $options = array("post_status" => "publish", "post_type" => "stereo_track");
        if ($ids) 
            $options['post__in'] = $ids;
        $q = new WP_Query($options);
        $tracks = array();
        if ($q->posts): foreach ($q->posts as $post): 
            $tracks[$post->ID] = new StereoTrackInfo($post, get_stereo_track_meta($post->ID));
        endforeach; 
        else: 
            //If no tracks, return and 404
            return;
        endif;
        //Single track = no array
        if (sizeof($tracks) == 1 && sizeof($ids) == 1) 
            $tracks = array_pop($tracks);
        echo json_encode($tracks);
        die();

    }

    function playlists($ids) {
        if (!$ids) {
            return;
        }
        $options = array("post_status" => "publish", "post_type" => "stereo_playlist");
        $options['post__in'] = $ids;
        $q = new WP_Query($options);
        if (!$q->have_posts()) {
            return;
        }
        $tracklist = array();
        foreach ($q->posts as $p) {
            $tracks = array();
            $connected = p2p_type( 'playlist_to_tracks' )->set_direction( 'from' )->get_connected( $p, 
                array('posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC') );
            if ($connected->have_posts()) {
                while ($connected->have_posts()) {
                    $connected->the_post(); 
                    $tracks[] = new StereoTrackInfo($connected->post, get_stereo_track_meta($connected->post->ID), $p);
                }
            }
            $tracklist[$p->ID] = $tracks;
        }
        if (sizeof($tracklist) == 1 && sizeof($ids) == 1) 
            $tracklist = array_pop($tracklist);
        echo json_encode($tracklist);
        die();
    }
}

$stereo_info_rewrite = new StereoInfoRewrite();

