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
		
		$this->sections['general']      = __( 'User settings' );
		$this->sections['advanced']      = __( 'Admin' );
		$this->sections['ajax']      = __( 'Ajax' );
		$this->sections['tools']        = __( 'Tools' );
		$this->sections['about']        = __( 'About' );

        $this->sections['default_tracks'] = __( 'Default tracks' );
		
		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
        add_action( 'wp_ajax_stereo_update_tracks', array( &$this, 'update_tracks') );
		
		if ( ! get_option( 'stereo_options' ) )
			$this->initialize_settings();

		
	}
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function add_pages() {
		
		$admin_page = add_options_page( 'Stereo', 'Stereo', 'manage_options', 'stereo_options', array( &$this, 'display_page' ) );
        $admin_page_tracks = add_submenu_page('edit.php?post_type=stereo_playlist', 'Default tracks', 'Default tracks', 'edit_posts', 'stereo_default_tracks', array(&$this, 'display_default_tracks'));
		
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
			'section' => 'general',
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
			'section' => 'general'
		);

		$this->settings['soundcloud_id'] = array(
			'title'   => __( 'SoundCloud Client ID' ),
			'desc'    => __( 'Your SoundCloud Client ID (Register your app <a href="http://soundcloud.com/you/apps/new" target="_blank">here</a>)' ),
			'std'     => 'CLIENT_ID',
			'type'    => 'text',
			'section' => 'general'
		);

        /* For now we don't need this.
		$this->settings['soundcloud_secret'] = array(
			'title'   => __( 'SoundCloud Secret' ),
			'desc'    => __( 'Your SoundCloud Secret (Register your app <a href="http://soundcloud.com/you/apps/new" target="_blank">here</a>)' ),
			'std'     => 'CLIENT_SECRET',
			'type'    => 'text',
			'section' => 'general'
        );
         */

		$this->settings['playlist_singular'] = array(
			'title'   => __( 'Playlist singular name' ),
			'desc'    => __( 'Name of the custom post type (could be Playlist, Set, Album...)' ),
			'std'     => 'Playlist',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_plural'] = array(
			'title'   => __( 'Playlist plural name' ),
			'desc'    => __( 'Plural (more than one) name of the custom post type (could be Playlists, Sets, Albums...)' ),
			'std'     => 'Playlists',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_slug'] = array(
			'title'   => __( 'Playlist rewrite slug' ),
			'desc'    => __( 'Slug to use for URL rewrites' ),
			'std'     => 'playlist',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_taxonomy_singular'] = array(
			'title'   => __( 'Taxonomy singular name' ),
			'desc'    => __( 'Name of the taxonomy (could be Group, Category, etc)' ),
			'std'     => 'Playlist category',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_taxonomy_plural'] = array(
			'title'   => __( 'Taxonomy plural name' ),
			'desc'    => __( 'Plural name of the playlist taxonomy (could be Groups, Categories, etc)' ),
			'std'     => 'Playlist categories',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['playlist_taxonomy_slug'] = array(
			'title'   => __( 'Taxonomy rewrite slug' ),
			'desc'    => __( 'Slug to use for URL rewrites' ),
			'std'     => 'playlist_category',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['taxonomy_tags'] = array(
			'section' => 'advanced',
			'title'   => __( 'Taxonomy have tags' ),
            'desc'    => __( 'If checked, write taxonomy terms as tags. If not checked, choose among already existing terms.' ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['rewrite_slug'] = array(
			'title'   => __( 'Rewrite slug' ),
			'desc'    => __( 'Slug to use for streaming and info API (make sure it doesn\'t collide with other permalinks)' ),
			'std'     => 'stereo',
			'type'    => 'text',
			'section' => 'advanced'
		);
		$this->settings['show_track_ui'] = array(
			'section' => 'advanced',
			'title'   => __( 'Show Track UI' ),
			'desc'    => __( 'If Tracks should be visible in the main menu (good for debugging)' ),
			'type'    => 'checkbox',
			'std'     => 0 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		$this->settings['local_support'] = array(
			'section' => 'advanced',
			'title'   => __( 'Uploaded files support' ),
			'desc'    => __( 'Allow streaming of uploaded files' ),
			'type'    => 'checkbox',
			'std'     => 1 // Set to 1 to be checked by default, 0 to be unchecked by default.
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
			'desc'    => __( 'Include CSS for plugin (if not, add your own styles in style.css).' ),
			'type'    => 'checkbox',
			'std'     => '1'
		);

        $this->settings['ajax_enable'] = array(
            'section' => 'ajax',
			'title'   => __( 'Enable ajax loading of pages' ),
			'desc'    => __( 'If enabled, pages will be ajax-loaded when sound is playing.' ),
			'type'    => 'checkbox',
			'std'     => 1
		);

        $this->settings['ajax_elements'] = array(
            'section' => 'ajax',
			'title'   => __( 'Elements to reload' ),
			'desc'    => __( 'jQuery selector(s) of elements to reload. This would be your primary content container, optional sidebar etc.' ),
			'type'    => 'text',
			'std'     => '#primary'
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
		
        /*
		$this->settings['example_textarea'] = array(
			'title'   => __( 'Example Textarea Input' ),
			'desc'    => __( 'This is a description for the textarea input.' ),
			'std'     => 'Default value',
			'type'    => 'textarea',
			'section' => 'general'
		);
		
		$this->settings['example_checkbox'] = array(
			'section' => 'general',
			'title'   => __( 'Example Checkbox' ),
			'desc'    => __( 'This is a description for the checkbox.' ),
			'type'    => 'checkbox',
			'std'     => 1 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		
		$this->settings['example_heading'] = array(
			'section' => 'general',
			'title'   => '', // Not used for headings.
			'desc'    => 'Example Heading',
			'type'    => 'heading'
		);
		
		$this->settings['example_radio'] = array(
			'section' => 'general',
			'title'   => __( 'Example Radio' ),
			'desc'    => __( 'This is a description for the radio buttons.' ),
			'type'    => 'radio',
			'std'     => '',
			'choices' => array(
				'choice1' => 'Choice 1',
				'choice2' => 'Choice 2',
				'choice3' => 'Choice 3'
			)
		);
		
		$this->settings['example_select'] = array(
			'section' => 'general',
			'title'   => __( 'Example Select' ),
			'desc'    => __( 'This is a description for the drop-down.' ),
			'type'    => 'select',
			'std'     => '',
			'choices' => array(
				'choice1' => 'Other Choice 1',
				'choice2' => 'Other Choice 2',
				'choice3' => 'Other Choice 3'
			)
		);
		
		
		$this->settings['custom_css'] = array(
			'title'   => __( 'Custom Styles' ),
			'desc'    => __( 'Enter any custom CSS here to apply it to your theme.' ),
			'std'     => '',
			'type'    => 'textarea',
			'section' => 'appearance',
			'class'   => 'code'
		);
         */
				
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
			$options = get_option( 'stereo_options' );
			
			foreach ( $this->checkboxes as $id ) {
				if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
					unset( $options[$id] );
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
