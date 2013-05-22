<?php 
/**
 * Rewrites
 */

/*
 * Handle main info rewrite rules
 */

class StereoTrackInfo {
    var $name =     "", 
        $playlist = "",
        $artwork =  "";
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
        add_rewrite_rule('^stream/([^/]*)/info/?', 'index.php?stereo_id=$matches[2]&stereo_info=true','top');
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

        $this->info($id);

        header('HTTP/1.1 404 Not Found');
        die();
    }

    function info($id) {
        echo "here we should add info";
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

