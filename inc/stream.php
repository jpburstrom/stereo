<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Track streaming API endpoint 
 */


/*
 * Handle main streaming rewrite rules
 */

class StereoStream {

    function __construct() 
    {
        $this->expiry_time = 60*60*24; //1 dygn

        add_action('init', array(&$this, 'add_rewrite_rules'));
        add_action('query_vars', array(&$this, 'stereo_query_vars') );
        add_action('pre_get_posts', array(&$this, 'pre_get_posts') );

    }

    private function create_cookie_hash($str) 
    {
         return sha1( $str . wp_salt() );
    }

    private function create_cookie_string()
    {
        $str = time();
        return $str . "|" . $this->create_cookie_hash($str);
    }

    function update_cookie()
    {

        $path = trailingslashit(parse_url(home_url(), PHP_URL_PATH));
        setcookie("stereo", $this->create_cookie_string(), time() + $this->expiry_time, $path);
    }

    function validate_cookie() {
        if (!isset($_COOKIE['stereo'])) 
            return false;
        list($time, $hash) = explode("|", $_COOKIE['stereo']);
        return ($this->create_cookie_hash($time) == $hash);
    }

    function add_rewrite_rules()
    {
        $stream = stereo_option('rewrite_slug');
        add_rewrite_rule('^' . $stream . '/stream/([^/]*)/?', 'index.php?&stereo_id=$matches[1]','top');
    }

    function stereo_query_vars( $query_vars )
    {
        $query_vars[] = 'stereo_id';
        return $query_vars;
    }

    function pre_get_posts($query) 
    {
        if ( !$query->is_main_query()) {
            return;
        }

            if ( !isset ( $query->query_vars['stereo_id'] )) {
                //Here we add a cookie on non-streaming pages
                if (stereo_option('enable_cookie')) $this->update_cookie();
                return;
            }

        if (stereo_option('enable_cookie')) {
            if (!$this->validate_cookie()){
                header('HTTP/1.1 403 Forbidden');
                die("403");
            }
        }

        if ( is_numeric ( $query->query_vars['stereo_id'] )) {
            $this->streaming($query->query_vars['stereo_id']);
        }

        header('HTTP/1.1 404 Not Found');
        die("404");
    }

    function streaming($id)
    {
        $track = get_stereo_track_meta($id);
        switch ($track['host']) {
        case 'wp':
            $this->wp_streaming($track['fileid']);
            break;
        case 'sc':
            $this->sc_streaming($track['fileid']);
            break;
        }
    }

    function wp_streaming($id)
    {
        $url = wp_get_attachment_url($id);
        $upload_dir = wp_upload_dir();
        $name = basename($url);
        $file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);

        $mime_type = "audio/mpeg";

        if(file_exists($file)){
            header("Content-type: {$mime_type}");
            header('Content-length: ' . filesize($file));
            header("Content-Disposition: filename=\"" . $name . "\"");
            header('X-Pad: avoid browser bug');
            header('Cache-Control: no-cache');
            header("Expires: 0");
            readfile($file);
            die();
        }
    }

    function sc_streaming($id) 
    {
        $sc = stereo_sc();
        $sc->stream_track($id);
    }
}

$stereo_stream = new StereoStream();

