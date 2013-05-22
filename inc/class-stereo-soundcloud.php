<?php
/**
 * Soundcloud helper class 
 **/
class StereoSoundCloud
{
    
    function __construct()
    {

        if ($clientid = stereo_option("soundcloud_id")) {
            $secret = stereo_option("soundcloud_secret");
            if (!$secret) $secret = null;
            $this->sc = new Services_SoundCloud($clientid);
        }
    }

    function get_users()
    {
        return array_map("trim", explode(",", stereo_option('soundcloud_users')));
    }

    function get_tracks()
    {
        $tracks = array();
        foreach ($this->get_users() as $user) {
            $data = json_decode($this->get_query("users/$user/tracks"));
            if ($data) {
                foreach ($data as $track) {
                    $tmp = array();
                    $tmp['uri'] = "soundcloud://tracks/$track->id";
                    $tmp['title'] = $track->title;
                    $tmp = (object) $tmp;
                    $tracks[$track->id] = array($track->title, json_encode($tmp));
                }
            }
        }
        return $tracks;
    }

    function get_sets()
    {
        $sets = array();
        foreach ($this->get_users() as $user) {
            $data = json_decode($this->get_query("users/$user/playlists"));
            if ($data) {
                foreach ($data as $set) {
                    $tdata = array();
                    foreach ($set->tracks as $track) {
                        $tmp = array();
                        $tmp['uri'] = "soundcloud://tracks/$track->id";
                        $tmp['title'] = $track->title;
                        $tdata[] = (object) $tmp;
                    }
                    $tdata = (object) $tdata;
                    $sets[$set->id] = array($set->title, json_encode($tdata));
                    
                }
            }
        }
        return $sets;
    }

    function get_query($query)
    {
        $query_label = "stereo_sc_query_$query";
        $data = get_transient($query_label);
        if (false === $data) {
            $data = $this->sc->get($query);
            set_transient( $query_label, $data, 60*5 );
        }

        return $data;
    }

}


