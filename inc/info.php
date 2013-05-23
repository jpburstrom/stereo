<?php 
/**
 * Rewrites
 */

class StereoPlaylistInfo {
    var $name =     "";
    var $artwork =  "";

    function __construct($playlist) {
        $this->name = $playlist->post_title;
        $a = wp_get_attachment_image_src(get_post_thumbnail_id($playlist->ID), stereo_option("artwork_size"));
        $this->artwork = array(
            "url" => $a[0],
            "width" => $a[1],
            "height" => $a[2]
        );
    }
}

class StereoTrackInfo {

    var $name =     "", 
        $stream_url = "",
        $playlist = null;

    /**
     * Constructor
     * @param $track  stereo_track post object
     */
    function __construct($track) 
    {
        $this->name = $track->post_title;
        $this->stream_url = $track->post_excerpt;

        $playlist = new WP_Query( array(
            'connected_direction' => 'to',
            'connected_type' => 'playlist_to_tracks',
            'connected_items' => $track,
            'nopaging' => true,
        ));
        if ($playlist->found_posts == 1) {
            $playlist = $playlist->posts[0];
            $this->playlist = new StereoPlaylistInfo($playlist);
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
        $stream = stereo_option('streaming_slug');
        add_rewrite_rule('^' . $stream . '/([^/]*)/info/?', 'index.php?stereo_id=$matches[1]&stereo_info=true','top');
    }

    function stereo_query_vars( $query_vars )
    {
        $query_vars[] = 'stereo_info';
        return $query_vars;
    }

    function pre_get_posts($query) 
    {
        if ( !$query->is_main_query() ) {
            return;
        }
        if ( !isset ( $query->query_vars['stereo_info'] )) {
            return;
        }


        if ( is_numeric ( $query->query_vars['stereo_id'] )) {
            $this->info($query->query_vars['stereo_id']);
        }

        header('HTTP/1.1 404 Not Found');
        die();
    }

    function info($id) {
        $q = new WP_Query(array("post_status" => "publish", "post_type" => "stereo_track", "post__in" => array($id)) );
        if ($q->found_posts == 1) {
            $track = new StereoTrackInfo($q->posts[0]);
        }
        echo json_encode($track);
        die();

    }
    function wp_info($id)
    {
        if (wp_get_attachment_url($id)) {
            $info = new StereoTrackInfo();
            echo json_encode($info);
            die();
        }
    }

    function sc_info($id) 
    {
        $sc = stereo_init_sc();
        if (false !== $sc) {
            try {
                $track = json_decode($sc->get("tracks/$id"));
            } catch (Exception $e) {
                header("HTTP/1.1 {$e->getHttpCode()}");
                die();
            }
            echo "this is the SC info";
            die();
        }
    }
}

$stereo_info_rewrite = new StereoInfoRewrite();

