<?php

class StereoOptions {
	
	private $sections;
	private $checkboxes;
	private $settings;
	
	/**
	 * Construct
	 *
	 */
	public function __construct() {
		
		// This will keep track of the checkbox options for the validate_settings function.
		$this->checkboxes = array();
		$this->settings = array();
		$this->dt_settings = array();
		$this->get_settings();

        $this->notices = array(
            'options_nag' => __('Welcome to <strong>Stereo</strong>! Please visit the <a href="options-general.php?page=stereo_options">Stereo options page</a> to configure the plugin.')
        );
		
		$this->sections['slugs']      = __( 'Slug settings' );
		$this->sections['playlist']      = __( 'Playlist settings' );
		$this->sections['taxonomy']      = __( 'Taxonomy settings' );
		$this->sections['artists']      = __( 'Artist settings' );
		$this->sections['soundcloud']      = __( 'SoundCloud settings' );
		$this->sections['ajax']      = __( 'Playback settings' );
		$this->sections['advanced']      = __( 'Advanced' );
		$this->sections['tools']        = __( 'Tools' );
		$this->sections['about']        = __( 'About' );
        //This is for another page
		$this->sections['default_tracks']        = __( 'Default tracks' );

		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_init', array( &$this, 'update_version' ) );
        add_action('admin_notices', array( &$this, 'admin_notices' ) );
        
        add_action( 'wp_ajax_stereo_update_tracks', array( &$this, 'update_tracks') );
		
		if ( ! get_option( 'stereo_options' ) )
			$this->initialize_settings();

	}

    public function admin_notices()
    {
        if ($notices = get_option('stereo_deferred_admin_notices')) {
            foreach ($notices as $notice) {
                if ($this->notices[$notice]) {
                    $notice = $this->notices[$notice];
                } 
                echo "<div class='updated'><p>$notice</p></div>";
            }
            delete_option('stereo_deferred_admin_notices');
        }
    }

    public function update_version() {
        $version = get_option('stereo_version');
        if ($version != STEREO_VERSION) {
            update_option('stereo_version', STEREO_VERSION);
            $notices = get_option('stereo_deferred_admin_notices');
            //$notices[] = __('Stereo is updated to version') . " " . STEREO_VERSION;
            update_option('stereo_deferred_admin_notices', $notices);
        }
    }
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function add_pages() {
		
		$admin_page = add_options_page( 'Stereo', 'Stereo', 'manage_options', 'stereo_options', array( &$this, 'display_page' ) );
        $admin_page_tracks = add_submenu_page('edit.php?post_type=stereo_playlist', 'Default tracks', 'Default tracks', 'edit_posts', 'stereo_default_tracks', array(&$this, 'display_default_tracks'));

        add_action( 'load-' . $admin_page, array( "StereoDocs", 'load' ));
        add_action( 'load-' . $admin_page_tracks, array( "StereoDocs", 'load' ));
		
		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'styles' ) );
		add_action( 'admin_print_scripts-' . $admin_page_tracks, array( &$this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page_tracks, array( &$this, 'styles' ) );
		
	}
	
	/**
	 * Create settings field
	 *
	 * @since 1.0
	 */
	public function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      => 'default_field',
			'title'   => __( 'Default Field' ),
			'desc'    => __( 'This is a default description.' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'soundcloud',
			'choices' => array(),
			'class'   => ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'desc'      => $desc,
			'std'       => $std,
			'choices'   => $choices,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
        if ($section == 'default_tracks') {
            add_settings_field( $id, $title, array( $this, 'display_dt_setting' ), 'stereo_default_tracks', $section, $field_args );
        } else {
            add_settings_field( $id, $title, array( $this, 'display_setting' ), 'stereo_options', $section, $field_args );
        }
	}
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_page() {
        flush_rewrite_rules( );

        include('views/options-page.php');

	}


	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_default_tracks() {
        include('views/options-default-tracks.php');

	}

	/**
	 * Display update tracks page
	 *
	 */
    public function update_tracks() {
        $posts = get_posts('posts_per_page=-1&post_type=stereo_track');
        foreach ($posts as $post) {
            stereo_cpt()->update_track_metadata($post->ID);
        }
        echo "Tracks updated";
        die();
    }
	
	/**
	 * Description for section
	 *
	 * @since 1.0
	 */
	public function display_section() {
		// code
	}
	
	/**
	 * Description for soundcloud section
	 *
	 * @since 1.0
	 */
	public function display_section_soundcloud() {
        $msg = false;
        if (!(stereo_option('soundcloud_id'))) {
            $msg = __('You need to provide a Client ID to connect with SoundCloud');
?>
            <p><strong><?php echo $msg?></strong></p>
            <ol>
                <li><?php _e('<a href="http://soundcloud.com/you/apps/new" target="_blank"/>Register an app</a> over at SoundCloud. You can give it any name, and then put the following URL as <strong>Redirect URI</strong>:') ?><br/>
                <input class="url-select regular-text" value="<?php echo admin_url("options-general.php?page=stereo_options")?>" readonly>
                </li>
                <li><?php _e('Put the Client ID and Secret in the boxes below and save.')?>
                </li>
            </ol>
<?php
        } else if (!(stereo_option('soundcloud_secret'))) {
            $msg = __('You need to provide a SoundCloud Client Secret to play private tracks');
?>
            <p><strong><?php echo $msg?></strong></p>
<?php
            return;
        }
?>

        <div id="soundcloud-connect">
        <?php if (stereo_sc()->save_token() === true || stereo_sc()->get_token() !== null) : ?>
        <p><?php stereo_sc()->the_connected_user() ?></p>
        <p><?php _e('Yes! You can play private songs from this user.')?></p>
        <p>
            <a href="<?php echo stereo_sc()->get_remove_token_url()?>" title="<?php _e('Remove SoundCloud connection from settings')?>"> <?php _e( 'Remove connection' ) ?></a> |
            <a href="http://soundcloud.com/settings/connections" target="_blank" title="<?php _e('Go to your SoundCloud settings to revoke access (will open in new window)')?>"><?php _e('Revoke access')?></a>
        </p>
         <?php else:?>
         
        <p><?php _e("To be able to play private SoundCloud tracks, you need to authorize the Stereo plugin to access them. If you only intend to play public tracks, you don't need to do this."); ?></p>
        <p><a href="<?php echo stereo_sc()->get_authorize_url() ?>" title="<?php _e('Connect with your SoundCloud Account')?>">
        <?php _e( 'Connect with SoundCloud' ) ?></a></p>
        <?php endif; ?>
        </div>
        <p><?php _e("")?></p>
<?php 
	}
	
	/**
	 * Description for About section
	 *
	 * @since 1.0
	 */
	public function display_about_section() {
?>
        <?php include("views/about.php"); ?>
		
<?php
		
	}

	/**
	 * Description for Tools section
	 *
	 * @since 1.0
	 */
	public function display_tools_section() {
?>
        <h4>Update track metadata</h4> 
        <p class="description">Press the button to update all track metadata from the files: Artist, Genre etc. This will not overwrite track titles.</p>
        <p><a id="stereo_update_tracks" class="button button-large" href="options-general.php?page=stereo_update_tracks">Update tracks</a></p>
<?php
		
	}

    public function display_dt_setting( $args = array() ) {
        $this->display_setting($args, 'stereo_default_tracks');
    }
	
	/**
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	public function display_setting( $args = array(), $option = 'stereo_options' ) {

		extract( $args );
		
		$options = get_option( $option );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;
		
		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'heading':
				echo '</td></tr><tr valign="top"><td colspan="2"><h4>' . $desc . '</h4>';
				break;

			case 'body':
				echo '<p>' . $desc . '</p>';
				break;
			
			case 'checkbox':
				
				echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="'.$option.'[' . $id . ']" value="1" ' . checked( $options[$id], 1, false ) . ' /> <label for="' . $id . '">' . $desc . '</label>';
				
				break;
			
			case 'select':
				echo '<select class="select' . $field_class . '" name="'.$option.'[' . $id . ']">';
				
				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				
				echo '</select>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $choices as $value => $label ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="'.$option.'[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';
					if ( $i < count( $options ) - 1 )
						echo '<br />';
					$i++;
				}
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'textarea':
				echo '<textarea class="' . $field_class . '" id="' . $id . '" name="'.$option.'[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'password':
				echo '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="'.$option.'[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'text':
			default:
		 		echo '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="'.$option.'[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '" />';
		 		
		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';
		 		
		 		break;
		 	
		}
		
	}
	
	/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	public function get_settings() {
		
		/* General Settings
		===========================================*/
		
		$this->settings['soundcloud_users'] = array(
			'title'   => __( 'SoundCloud User(s)' ),
			'desc'    => __( 'Your SoundCloud User(s) (Multiple users comma-separated: <code>User1, User2</code>)' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'soundcloud'
		);

		$this->settings['soundcloud_id'] = array(
			'title'   => __( 'SoundCloud Client ID' ),
			'desc'    => __( 'Your SoundCloud Client ID (Register your app <a href="http://soundcloud.com/you/apps/new" target="_blank">here</a>)' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'soundcloud'
		);

		$this->settings['soundcloud_secret'] = array(
			'title'   => __( 'SoundCloud Secret' ),
            'desc'    => __( 'Your SoundCloud Secret (Register your app <a href="http://soundcloud.com/you/apps/new" target="_blank">here</a>) ' ),
			'std'     => '',
			'type'    => 'password',
			'section' => 'soundcloud'
        );
		$this->settings['rewrite_slug'] = array(
			'title'   => __( 'Internal API path' ),
			'desc'    => __( 'Slug for streaming and info API. Make sure it doesn\'t collide with other permalinks.' ),
			'std'     => 'stereo',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_taxonomy_slug'] = array(
			'title'   => __( 'Taxonomy path' ),
			'desc'    => __( 'Slug for URL rewrites' ),
			'std'     => 'playlist_category',
			'type'    => 'text',
			'section' => 'slugs'
		);
		$this->settings['playlist_taxonomy2_slug'] = array(
			'title'   => __( 'Secondary taxonomy path' ),
			'desc'    => __( 'Slug for URL rewrites' ),
			'std'     => 'playlist_roles',
			'type'    => 'text',
			'section' => 'slugs'
		);

		$this->settings['playlist_singular'] = array(
			'title'   => __( 'Playlist singular name' ),
			'desc'    => __( 'Name of the custom post type (could be Playlist, Set, Album...)' ),
			'std'     => 'Playlist',
			'type'    => 'text',
			'section' => 'playlist'
		);
		$this->settings['playlist_plural'] = array(
			'title'   => __( 'Playlist plural name' ),
			'desc'    => __( 'Plural name of the custom post type (could be Playlists, Sets, Albums...)' ),
			'std'     => 'Playlists',
			'type'    => 'text',
			'section' => 'playlist'
		);
		$this->settings['playlist_slug'] = array(
			'title'   => __( 'Playlist path' ),
			'desc'    => __( 'Slug for URL rewrites' ),
			'std'     => 'playlist',
			'type'    => 'text',
			'section' => 'playlist'
		);
		$this->settings['has_playlist_archive'] = array(
			'section' => 'playlist',
			'title'   => sprintf(__( '%s archive' ), stereo_option('playlist_plural')),
			'desc'    => sprintf(__( 'Enable archive for %s' ), stereo_option('playlist_plural')),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['playlist_taxonomy_singular'] = array(
			'title'   => __( 'Taxonomy singular name' ),
			'desc'    => __( 'Name of the taxonomy (could be Group, Category, etc)' ),
			'std'     => 'Playlist category',
			'type'    => 'text',
			'section' => 'taxonomy'
		);
		$this->settings['playlist_taxonomy_plural'] = array(
			'title'   => __( 'Taxonomy plural name' ),
			'desc'    => __( 'Plural name of the playlist taxonomy (could be Groups, Categories, etc)' ),
			'std'     => 'Playlist categories',
			'type'    => 'text',
			'section' => 'taxonomy'
		);
		$this->settings['taxonomy_tags'] = array(
			'section' => 'taxonomy',
			'title'   => sprintf(__( '%s taxonomy type' ), stereo_option('playlist_singular')),
            'desc'    => sprintf(__( 'Make taxonomy (<code>%s</code>) non-hierarchical, like tags.'), stereo_option('playlist_taxonomy_plural') ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['show_second_taxonomy'] = array(
			'section' => 'taxonomy',
            'title'   => "Activate secondary taxonomy",
            'desc'    => sprintf(__( 'Activate the second %s taxonomy (<code>%s</code>)'), stereo_option('playlist_singular'), stereo_option('playlist_taxonomy2_plural') ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['playlist_taxonomy2_singular'] = array(
			'title'   => __( 'Second taxonomy singular name' ),
			'desc'    => __( 'Name of the taxonomy (could be Group, Category, etc)' ),
			'std'     => 'Role',
			'type'    => 'text',
			'section' => 'taxonomy'
		);
		$this->settings['playlist_taxonomy2_plural'] = array(
			'title'   => __( 'Taxonomy plural name' ),
			'desc'    => __( 'Plural name of the playlist taxonomy (could be Groups, Categories, etc)' ),
			'std'     => 'Roles',
			'type'    => 'text',
			'section' => 'taxonomy'
		);
		$this->settings['taxonomy2_tags'] = array(
			'section' => 'taxonomy',
			'title'   => __( 'Secondary taxonomy type' ),
            'desc'    => sprintf(__( 'Make taxonomy (<code>%s</code>) non-hierarchical, like tags.'), stereo_option('playlist_taxonomy2_plural') ),
			'type'    => 'checkbox',
			'std'     => 1 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['playlists_in_main_loop'] = array(
			'section' => 'advanced',
			'title'   => sprintf(__( '%s in blog' ), stereo_option('playlist_plural')),
			'desc'    => sprintf(__( 'Mix in %s in main loop and feed' ), stereo_option('playlist_plural')),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['show_track_ui'] = array(
			'section' => 'advanced',
			'title'   => __( 'Show Track UI' ),
			'desc'    => __( 'Make Tracks visible in the main menu (good for debugging)' ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
        /*
		$this->settings['local_support'] = array(
			'section' => 'advanced',
			'title'   => __( 'Local Track file support' ),
			'desc'    => __( 'Use files uploaded to the WP Media Library' ),
			'type'    => 'checkbox',
			'std'     => 1 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
         */
		$this->settings['enable_cookie'] = array(
			'section' => 'advanced',
			'title'   => __( 'Enable cookie-based hotlink protection' ),
			'desc'    => __( 'If enabled, set a cookie on page view, and check for it when streaming. (This might make it harder to play the songs through hotlinking). If you use page caching, this should probably be turned off.' ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
        $choices = array();
        foreach (get_intermediate_image_sizes() as $choice) {
            $choices[$choice] = ucwords($choice);
        }
		$this->settings['artwork_size'] = array(
			'section' => 'advanced',
			'title'   => __( 'Artwork thumbnail size ' ),
			'desc'    => __( 'Choose a size for the artwork thumbnail' ),
			'type'    => 'select',
			'std'     => 'thumbnail',
            'choices' => $choices
		);

		$this->settings['include_css'] = array(
			'section' => 'advanced',
			'title'   => __( 'Include CSS' ),
			'desc'    => __( 'Include plugin CSS (if not, add your own styles in style.css).' ),
			'type'    => 'checkbox',
			'std'     => '1'
		);

        $this->settings['ajax_enable'] = array(
            'section' => 'ajax',
			'title'   => __( 'Enable continous playback' ),
			'desc'    => __( 'If enabled, sound will continue to play on page navigation.' ),
			'type'    => 'checkbox',
			'std'     => 1
		);

        $this->settings['ajax_elements'] = array(
            'section' => 'ajax',
			'title'   => __( 'Elements to reload' ),
			'desc'    => __( 'Ids of elements to reload. This would be your primary content container, optional sidebar etc.' ),
			'type'    => 'text',
			'std'     => '#wrapper'
		);

        $this->settings['ajax_ignore'] = array(
            'section' => 'ajax',
			'title'   => __( 'Links to ignore' ),
			'desc'    => __( 'The script is already ignoring images, external links and _blank target links. Fill in optional extra selectors to ignore here.' ),
			'type'    => 'text',
			'std'     => ''
		);

        $this->settings['ajax_scrollTime'] = array(
            'section' => 'ajax',
			'title'   => __( 'Time for scrolling animation' ),
            'desc'    => __( 'On loading of a new page, the page will scroll in a semi-nice animation. Choose the time for the animation here.' ),
			'type'    => 'text',
			'std'     => '0'
		);

        $this->settings['create_artist_cpt'] = array(
            'section' => 'artists',
			'title'   => __( 'Create artist post type' ),
            'desc'    => __( 'Create an artist post type' ),
            'type'    => 'checkbox',
            'std'     => 0
        );

		$this->settings['artist_singular'] = array(
			'title'   => __( 'Artist singular name' ),
			'desc'    => __( 'Name of the stereo_artist custom post type (could be Artist, Project...)' ),
			'std'     => 'Artist',
			'type'    => 'text',
			'section' => 'artists'
		);
		$this->settings['artist_plural'] = array(
			'title'   => __( 'Artist plural name' ),
			'desc'    => __( 'Plural name of the stereo_artist custom post type (could be Artists, Projects...)' ),
			'std'     => 'Artists',
			'type'    => 'text',
			'section' => 'artists'
		);

		$this->settings['artist_slug'] = array(
			'title'   => __( 'Artist rewrite slug' ),
			'desc'    => __( 'Slug to use for URL rewrites' ),
			'std'     => 'artist',
			'type'    => 'text',
			'section' => 'artists'
		);
		$this->settings['has_artist_archive'] = array(
			'section' => 'artists',
			'title'   => sprintf(__( '%s archive' ), stereo_option('artist_plural')),
			'desc'    => sprintf(__( 'Enable archive for %s' ), stereo_option('artist_plural')),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);

		
				
		/* Reset
		===========================================*/
		
		$this->settings['reset_theme'] = array(
			'section' => 'tools',
			'title'   => __( 'Reset options' ),
			'type'    => 'checkbox',
			'std'     => 0,
			'class'   => 'warning', // Custom class for CSS
			'desc'    => __( 'Check this box and click "Save Changes" below to reset options to their defaults.' )
		);



		/* Default tracks
		===========================================*/
		
		$this->dt_settings['default_track_mode'] = array(
			'section' => 'default_tracks',
			'title'   => __( 'Pick default tracks from…' ),
			'desc'    => __( 'Choose if and how to pick default tracks. They will be available for playback on all pages without playlists.' ),
			'type'    => 'radio',
			'std'     => 'random',
			'choices' => array(
				//'choice' => 'Choose from tracks below',
				'random' => 'A random selection',
				'playlist' => 'A specific ' . stereo_option('playlist_singular') ,
				0 => 'Please don\'t pick any default tracks',
			)
		);
		$this->dt_settings['track_count'] = array(
			'section' => 'default_tracks',
			'title'   => __( 'Number of random tracks' ),
			'desc'    => __( 'If you have chosen "Random selection" above.' ),
			'type'    => 'select',
			'std'     => 5,
            'choices' => array_combine(range(1, 20),range(1, 20)),
		);
        
        $playlists = array();
        foreach (get_posts(array("posts_per_page" => -1, "post_type" => "stereo_playlist", "post_status" => 'any')) as $p) {
            $playlists[$p->ID] = $p->post_title;
        }

		$this->dt_settings['playlist_choice'] = array(
			'section' => 'default_tracks',
			'title'   => __( 'Choose' ) . " " . stereo_option('playlist_singular') ,
			'desc'    => __( 'Tip: It can be unpublished.' ),
			'type'    => 'select',
			'std'     => 5,
            'choices' => $playlists
		);
		
	}
	
	/**
	 * Initialize settings to their default values
	 * 
	 * @since 1.0
	 */
	public function initialize_settings() {
		
		$default_settings = array();
		foreach ( $this->settings as $id => $setting ) {
			if ( $setting['type'] != 'heading' )
				$default_settings[$id] = $setting['std'];
		}
		
		update_option( 'stereo_options', $default_settings );
		
	}
	
	/**
	* Register settings
	*
	* @since 1.0
	*/
	public function register_settings() {
		
		register_setting( 'stereo_options', 'stereo_options', array ( &$this, 'validate_settings' ) );
		register_setting( 'stereo_default_tracks', 'stereo_default_tracks', array ( &$this, 'validate_dt_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
			if ( $slug == 'about' ) {
				add_settings_section( $slug, $title, array( &$this, 'display_about_section' ), 'stereo_options' );
            } else if ( $slug == 'tools' ) {
				add_settings_section( $slug, $title, array( &$this, 'display_tools_section' ), 'stereo_options' );
            } else if ( $slug == 'default_tracks' ) {
				add_settings_section( $slug, $title, array( &$this, 'display_section' ), 'stereo_default_tracks' );
            } else if ( $slug == 'soundcloud' ) {
				add_settings_section( $slug, $title, array( &$this, 'display_section_soundcloud' ), 'stereo_options' );
            } else {
				add_settings_section( $slug, $title, array( &$this, 'display_section' ), 'stereo_options' );
            }
		}
		
		$this->get_settings();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}
		foreach ( $this->dt_settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}
		
	}
	
	/**
	* jQuery Tabs
	*
	* @since 1.0
	*/
	public function scripts() {
		
		wp_print_scripts( 'jquery-ui-tabs' );
		
	}
	
	/**
	* Styling for the options page
	*
	* @since 1.0
	*/
	public function styles() {
		
		wp_register_style( 'stereo-admin', STEREO_PLUGIN_URL . 'admin/css/admin.css' );
		wp_enqueue_style( 'stereo-admin' );
		
	}
	

	/**
	* Validate settings
	*
	* @since 1.0
	*/
	public function validate_settings( $input ) {
        $this->purge_plugin_cache();
		if ( ! isset( $input['reset_theme'] ) ) {
            $users = array_map('trim', explode(',', $input['soundcloud_users']));
            $users = array_unique(array_merge($users, array(stereo_sc()->get_connected_user_slug())));
            $input['soundcloud_users'] = implode(', ', $users);
            
			$options = get_option( 'stereo_options' );
			
			foreach ( $this->checkboxes as $id ) {
				if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
					$input[$id] = 0;
                elseif (!isset($options[$id]))
                    $input[$id] = $this->settings[$id]["std"];
			}

			
			return $input;
		}

		return false;
		
	}

    public function validate_dt_settings ($input) {
        $this->purge_plugin_cache();
        return $input;
    }

    private function purge_plugin_cache () {
        if( class_exists('W3_Plugin_TotalCacheAdmin') ) {
            $plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');
            $plugin_totalcacheadmin->flush_pgcache();
        }
    }


	
}

$stereo_options = new StereoOptions();

?>
