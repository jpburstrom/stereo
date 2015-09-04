<?php
/**
 * Soundcloud helper class 
 **/

class StereoSoundCloud
{
    private $me;
    private $ignore_cache;
    
    function __construct()
    {
        require_once( STEREO_PLUGIN_DIR . "/lib/php-soundcloud/Services/Soundcloud.php" );

        $this->ignore_cache = false;

        if ($clientid = stereo_option("soundcloud_id")) {
            $secret = stereo_option("soundcloud_secret");
            if (!$secret) $secret = "";
            $this->sc = new Services_SoundCloud($clientid, $secret, admin_url("options-general.php?page=stereo_options"));
            $token = get_option('stereo_soundcloud_token');
            if ($token) {
                $this->sc->setAccessToken($token);
                try {
                    $this->me = json_decode($this->sc->get('me'));
                } catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
                    delete_option('stereo_soundcloud_token');
                    //FIXME: message
                }
            }
        }
    }

    function get_authorize_url()
    {
        if ($this->sc)
            return $this->sc->getAuthorizeUrl(array(
                'scope' => 'non-expiring'
            ));
    }


    //Save token from Auth response
    function save_token() 
    {
        if (!isset($_GET['code'])) 
            return false;
        try {
            $token = stereo_sc()->sc->accessToken($_GET['code']);
        } catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
            return $e->getMessage();
        }
        update_option('stereo_soundcloud_token', $token['access_token']);
        return true;
    }

    /**
     * Get token from SoundCloud class
     */
    function get_token() 
    {
        if ($this->sc)
            return $this->sc->getAccessToken();
    }

    /**
     * get a url for removing current token
     */
    function get_remove_token_url() 
    {
        return admin_url('admin-post.php?action=stereo_remove_token');
    }

    /**
     * Admin post action to remove current token
     */
    static function admin_post_remove_token()
    {
        delete_option('stereo_soundcloud_token');
        $notices = get_option('stereo_deferred_admin_notices');
        $notices[] = __('SoundCloud connection removed.');
        update_option('stereo_deferred_admin_notices', $notices);
        wp_redirect('options-general.php?page=stereo_options');
    }
    /**
     * Get json object of tracks and sets
     */
    function ajax_get_tracks($data)
    {
        $sc = stereo_sc();
        if (isset($_POST['reload']) && $_POST['reload'] == "true") {
            $sc->ignore_cache = true;
        }
        $newdata = array();
        $newdata['tracks'] = $sc->get_tracks();
        $newdata['sets'] = $sc->get_sets();
        echo json_encode($newdata);
        die();
    }

    function get_users()
    {
        return array_filter(array_map("trim", explode(",", stereo_option('soundcloud_users'))));
    }

    /**
     * Get all tracks for all users
     */
    function get_tracks()
    {
        $tracks = array();
        foreach ($this->get_users() as $user) {
            $data = json_decode($this->get_query("users/$user/tracks"));
            if ($data) {
                foreach ($data as $track) {
                    $tracks[$track->id] = array($track->title, json_encode($this->_prepare_track_data($track)));
                }
            }
        }
        return $tracks;
    }

    /**
     * Get all sets for all users
     */
    function get_sets()
    {
        $sets = array();
        foreach ($this->get_users() as $user) {
            $data = json_decode($this->get_query("users/$user/playlists"));
            if ($data) {
                foreach ($data as $set) {
                    $tdata = array();
                    foreach ($set->tracks as $track) {
                        $tdata[] = $this->_prepare_track_data($track);
                    }
                    $tdata = (object) $tdata;
                    $sets[$set->id] = array($set->title, json_encode($tdata));
                    
                }
            }
        }

        return $sets;
    }

    private function _prepare_track_data($track) {
        $tmp = array();
        $tmp['id'] = $track->id; 
        $tmp['title'] = $track->title;
        $tmp['stream_url'] = $track->stream_url;
        return (object) $tmp;
    }

    function get_query($query)
    {
        $query_label = "stereo_sc_query_$query";
        $data = $this->ignore_cache ? false : get_transient($query_label);
        if (false === $data) {
            $data = $this->sc->get($query);
            set_transient( $query_label, $data, 60*5 );
        }

        return $data;
    }

    /**
     * Get track by ID
     */
    function get_track($id)
    {
        $track = $this->get_query("tracks/$id");
        return $track;

    }
    
    /**
     * Stream track -- used by stream script
     */
    function stream_track($id)
    {
        if (false !== $sc) {
            try {
                $track = json_decode($this->sc->get("tracks/$id"));
            } catch (Exception $e) {
                header("HTTP/1.1 {$e->getHttpCode()}");
                die();
            }
            if ($track->streamable) {
                $url = $track->stream_url . "?";
                if ($track->sharing == 'private') {
                    $url .= "secret_token=$track->secret_token&";
                } 
                header("Location:" . $url . "client_id=" . stereo_option('soundcloud_id') );
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
            die();
        }
        
    }

    /**
     * Get the handle of the connected user
     */
    function get_connected_user_slug()
    {
        $slug = ($this->me) ? $this->me->permalink : "";
        return $slug;
    }


    /**
    * Display avatar of connected user
     */
    function the_connected_user() 
    {
        if (!$this->get_token() || !$this->me) return;
?>
    <span class="soundcloud-avatar"><img width="64" src="<?php echo $this->me->avatar_url?>" alt="<?php echo $this->me->username?>"/>
    <?php _e('Connected as')?>
    <span class="handle"><?php echo $this->me->permalink?></span></span>
<?php
    }

}

add_action('admin_post_stereo_remove_token', 'StereoSoundCloud::admin_post_remove_token');
add_action('wp_ajax_stereo_get_soundcloud_tracks', 'StereoSoundCloud::ajax_get_tracks');
