<?php

class StereoDocs {

    private static $docs, $tabs, $sidebar; 

    public static function init () 
    {
        $artist = stereo_option('artist_singular');
        self::$docs = array(
            'General' => <<<HTML
HTML
            ,'The Playlist' => <<<HTML
The Playlist consists of any number of tracks that can be created, deleted and reordered to taste.

The tracks can be empty, meaning that they don't have a corresponding file to play. This can be useful if you want to show an entire track list, with a few playable tracks.
HTML
            ,'Track types' => <<<HTML
The <em>Media Library Tracks</em> are tracks with files that you upload to the regular WordPress media library. 

The <em>SoundCloud Tracks</em> are linked from a SoundCloud user account. To enable these, you have to set up SoundCloud in the <a href='options-general.php?page=stereo_options'>Stereo Options</a>. 

The <em>Empty Tracks</em> are tracks without files. They are simple placeholder tracks with a title.

In the track settings, reachable through the settings icon <span class="dashicons dashicons-admin-tools"></span> on each track, you can switch between track types.
HTML
            ,'Add Tracks' => <<<HTML
<h4>From Media Library:</h4> 
<ol>
<li>Click the <strong>Add Media Library tracks</strong> button</li>
<li>Select any number of mp3 files and click <strong>Select</strong></li>
</ol>

<h4>From SoundCloud:</h4>

<ol>
<li>Click the <strong>Add SoundCloud tracks</strong> button</li>
<li>Two dropdown menus will show up, filled with sets and tracks from your SoundCloud account.</li>
<li>Select a whole set of tracks from the <em>Sets</em> dropdown, or single tracks from the <em>Tracks</em> dropdown.
</ol>

<h4>Empty Tracks:</h4>

<ol>
<li>Click the <strong>Add empty track</strong> button</li>
<li>Give the track a name</li>
</ol>
HTML
            ,'Reorder and Delete' => <<<HTML
<h4>Reorder tracks</h4>

To reorder tracks, just drag and drop the track box to a new position in the playlist.

<h4>Delete tracks</h4>

To delete tracks, click the track settings icon <span class="dashicons dashicons-admin-tools"></span>. Then click the <strong>Delete track</strong> link.
HTML
            ,stereo_option('playlist_singular') => <<<HTML
HTML
        , stereo_option('artist_singular') => sprintf("The playlist can be linked to a single %s. This link is made with the dropdown in the %s box below the title. Depending on your theme and player settings, this can be visible on the site in different ways. \n\nPlease note that you need to create the %s page before you can link to it from this page.\n\n You can also choose to not link to a specific %s by choosing ”Other %s” in the dropdown, and writing another name in the text input.", $artist, $artist, $artist, $artist, $artist )
            ,'Stereo Options' => <<<HTML
HTML
        );

        self::$sidebar = "This is part of the Stereo playlist plugin.\n\n<a href='options-general.php?page=stereo_options'>Settings</a>";

        self::$tabs = array(
            //'edit-stereo_playlist' => array(stereo_option('playlist_singular')),
            'stereo_playlist' => array("The Playlist", "Track types", "Add Tracks", "Reorder and Delete"),
            //'edit-stereo_artist' => array(stereo_option('artist_singular')),
            'stereo_artist' => array(stereo_option('artist_singular')),
            'settings_page_stereo_options' => array('Stereo Options')

        );

        if (true) //(stereo_option('create_artist_cpt'))
            self::$tabs['stereo_playlist'][] = stereo_option('artist_singular');


        add_action ( "load-edit.php", array("StereoDocs", "load") );
        add_action ( "load-post.php", array("StereoDocs", "load") );
        add_action ( "load-post-new.php", array("StereoDocs", "load") );
    }

    public static function load ()
    {
        $screen = get_current_screen();
        if (isset(self::$tabs[$screen->id])) {
            $d = self::$tabs[$screen->id];
            foreach ($d as $title) {
                $screen->add_help_tab( array(
                    'id'      => 'stereo-help-' . sanitize_title($title), // This should be unique for the screen.
                    'title'   => $title,
                    'content' => wpautop(self::$docs[$title])
                ) );
            };
        } 

        if ($screen->post_type == 'stereo_playlist' || $screen->post_type == 'stereo_tracks' || $screen->post_type == 'stereo_artist') {
            $screen->set_help_sidebar(wpautop(self::$sidebar));
        }


    }

}

StereoDocs::init();
