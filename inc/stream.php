<?php 
/**
 * Rewrites
 */

/*
 * Handle main streaming rewrite rules
 */

class StereoStream {

    function __construct() 
    {
        $this->expiry_time = 60*60*24;

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

    function add_cookie()
    {
        $path = trailingslashit(parse_url(site_url(), PHP_URL_PATH));
        setcookie("stereo", $this->create_cookie_string(), time() + $this->expiry_time, $path);
    }

    function get_cookie()
    {
        if (!isset($_COOKIE['stereo'])) 
            return false;
    }

    function validate_cookie() {
        if (!isset($_COOKIE['stereo'])) 
            return false;
        list($time, $hash) = explode("|", $_COOKIE['stereo']);
        return ($this->create_cookie_hash($time) == $hash);
    }

    function add_rewrite_rules()
    {
        $stream = stereo_option('streaming_slug');
        add_rewrite_rule('^' . $stream . '/([^/]*)/?', 'index.php?&stereo_id=$matches[1]','top');
    }

    function stereo_query_vars( $query_vars )
    {
        $query_vars[] = 'stereo_provider';
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
            $this->add_cookie();
            return;
        }

        if (!$this->validate_cookie()){
            header('HTTP/1.1 403 Forbidden');
            die();
        }

        $this->streaming($id);

        header('HTTP/1.1 404 Not Found');
        die();
    }

    function streaming($id)
    {
        echo "here we should add streaming";
        die();
    }

    function wp_streaming($id)
    {
        $url = wp_get_attachment_url($id);
        $name = basename($url);
        $file = str_replace(trailingslashit(site_url()), ABSPATH, $url);

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
        $sc = stereo_init_sc();
        if (false !== $sc) {
            try {
                $track = json_decode($sc->get("tracks/$id"));
            } catch (Exception $e) {
                header("HTTP/1.1 {$e->getHttpCode()}");
                die();
            }
            if ($track->streamable) {
                header("Location:" . $track->stream_url . "?client_id=" . stereo_option('soundcloud_id') );
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
            die();
        }
    }
}

$stereo_stream = new StereoStream();

