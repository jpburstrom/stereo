<?php
/**
 * Soundcloud helper class 
 **/
class StereoSoundCloud
{
    
    function __construct()
    {
        $this->sc = stereo_init_sc();
    }

    function get_users()
    {
        return array_map("trim", explode(",", stereo_option('soundcloud_users')));
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
                        $tmp = array();
                        $tmp['uri'] = "soundcloud://tracks/$track->id"; //FIXME
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

    /**
     * Get track by ID
     */
    function get_track($id)
    {
        $track = $this->sc->get("tracks/$id");

        return $track;

    }


}


