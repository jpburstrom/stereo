<?php 

class StereoCustomPost {

    var $meta_fields = array(
    );

    function __construct() {

        //Variables


        //actions
        //add_action('parse_request', array(&$this, 'events_request_filter'), 10);
        add_action('admin_head', array(&$this, 'admin_head'));
        add_action('init', array(&$this, 'create_post_type'));
		add_action("wp_insert_post", array(&$this, "wp_insert_post"), 10, 2);

		add_action( 'admin_enqueue_scripts', array( &$this, 'my_admin_scripts' ) );
        
    }

    function my_admin_scripts() {
        global $current_screen;
        if ($current_screen->post_type == 'stereo_playlist' && $current_screen->base == 'post') {
            //wp_enqueue_script('media-upload');
            //wp_enqueue_script('thickbox');
            //wp_enqueue_script('ui-sortable');
            wp_enqueue_script( 'stereo-admin-cptjs', STEREO_PLUGIN_URL . 'admin/js/cpt.js' );
            wp_enqueue_style( 'stereo-admin-cpt', STEREO_PLUGIN_URL . 'admin/css/cpt.css' );
        }
    }


    //Set up post type 
    public function create_post_type() 
    {
        $s = stereo_option("playlist_singular");
        $p = stereo_option("playlist_plural");

        register_post_type( 'stereo_playlist',
            array(
                'labels' => array(
                    'name' => $p,
                    'singular_name' => $s,
                    'add_new' => __( 'Add New' ),
                    'add_new_item' => __( 'Add New ' ) . $s,
                    'edit' => __( 'Edit' ),
                    'edit_item' => __( 'Edit ' ) . $s,
                    'new_item' => __( 'New ' ) . $s,
                    'view' => __( 'View ' ) . $s,
                    'view_item' => __( 'View ' ) . $s,
                    'search_items' => __( 'Search ' ) . $p,
                    'not_found' => sprintf(__( 'No %s found' ), strtolower($p)),
                    'not_found_in_trash' => sprintf(__( 'No %s found in Trash' ), strtolower($p)),
                    'parent' => __( 'Parent ' ) . $s

                ),
                'public' => true,
                'rewrite' => array('slug' => stereo_option("playlist_slug")),
                'supports' => array("editor", "title", "page-attributes", 'thumbnail')
            )
        );

        $options = array(
            'public' => true,
            'labels' => array( 'name' => "Track" ),
            'supports' => array("editor", "title", "page-attributes", 'thumbnail'),
            'hierarchical' => true
        );
        $options['show_ui'] = (1 == stereo_option('show_track_ui'));

        register_post_type( 'stereo_track', $options);
    }

    //Set up custom boxes for post type
    public function metaboxes() 
    { 
		global $post;
		$custom = get_post_custom($post->ID);

        $connected = p2p_type( 'playlist_to_tracks' )->get_connected( $post, array('orderby' => 'menu_order', 'order' => 'ASC') );

        include ( 'views/metabox.php' );
        
    }

    public function import_button() 
    {
?>
        <p><a id="stereo_local_import" class="stereo-local stereo-import button button-large ">Import MP3 Uploads</a></p>
<?php
        
?>
        <p><a id="stereo_soundcloud_import" class="stereo-sc stereo-import button button-large ">Import tracks from SoundCloud</a></p>
<?php
    }


    //Add meta box to post type. Why this needs to be called from admin_head I don't know.
    public function admin_head() {
        add_meta_box("stereo_meta", "Manage " . stereo_option("playlist_singular"), array(&$this, "metaboxes"),
            "stereo_playlist", "normal", "low");
    }

    //Insert all meta values from the $this->meta_fields variable
	function wp_insert_post($post_id, $post = null)
    {
        //var_export($_POST); die();
        if ( defined('DOING_AJAX') && DOING_AJAX )
            return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;
        if ( !wp_verify_nonce( $_POST['stereo_playlist'], plugin_basename( __FILE__ ) ) )
            return;
        // Check permissions
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
        
		if ($post->post_type == "stereo_playlist")
		{

            $this->add_update_tracks($_POST['stereo_track_number']);

            $this->delete_tracks($_POST['stereo_delete_track']);

            //

			// Loop through the POST data
			foreach ($this->meta_fields as $key => $values)
			{
				$value = @$_POST[$key];
				if (empty($value))
				{
					delete_post_meta($post_id, $key);
					continue;
				}

				// If value is a string it should be unique
				if (!is_array($value))
				{
					// Update meta
					if (!update_post_meta($post_id, $key, $value))
					{
						// Or add the meta data
						add_post_meta($post_id, $key, $value);
					}
				}
				else
				{
					// If passed along is an array, we should remove all previous data
					delete_post_meta($post_id, $key);
					
					// Loop through the array adding new values to the post meta as different entries with the same name
					foreach ($value as $entry)
						add_post_meta($post_id, $key, $entry);
				}
			}
		}
	}

    function create_track($playlist_id, $args) 
    {
        // Initialize the post ID to -1. This indicates no action has been taken.
        $post_id = -1;

        //We should prepare:
        //Name => post_title
        //Track numbering => menu_order
        //url => post_excerpt?

        // Set the page ID so that we know the page was created successfully
        $post_id = wp_insert_post(
            array_merge(array(
                'post_author'	=>	get_current_user_id(),
                'post_title'	=>	'Nameless Track',
                'post_status'	=>	'publish',
                'post_type'		=>	'stereo_track',
                'post_content'  =>  'Hello world!' 
            ), $args)
        );

        if ($post_id < 0) {
            return $post_id;
        }

        //Success, here we go
        
        //Insert metadata
        
        //Set track thumbnail

        //Link track with playlist
        p2p_type( 'playlist_to_tracks' )->connect( $playlist_id, $post_id, array('date' => current_time('mysql') ));

        return $post_id;
    }

    /**
     * Add or update tracks from an array of track numbers
     *
     * @uses StereoCustomPost::prepare_track_postdata
     *
     * @param $tracknumbers Array of track numbers 
     */

    function add_update_tracks($tracknumbers)
    {
        if ($tracknumbers) {
            foreach ($tracknumbers as $key => $number) {
                $args = $this->prepare_track_postdata($key);
                if ($args['post_title']) {
                    $this->create_track($post_id, $args);
                }
            }
        }
    }

    /**
     * Delete tracks
     *
     * @param $ids array of stereo_track post ids
     */

    function delete_tracks($ids)
    {   
        foreach ($ids as $id) {
            if ('stereo_track' == get_post_type($id))
                //Bypass trash functionality
                wp_delete_post($id, true);
        }
    }    

    /**
     * Prepare an array of track data for $key, from $_POST
     *
     * @param $key Track index key
     * @return array Track data 
     */
    function prepare_track_postdata($key) 
    {
        $args = array();
        $args['ID'] = $_POST['stereo_track_ID'][$key];
        $args['post_excerpt'] = $_POST['stereo_track_uri'][$key];
        $args['post_title'] = $_POST['stereo_track_name'][$key];
        $args['menu_order'] = $_POST['stereo_track_number'][$key];
        return $args;
    }

    function track_data_json($id=null)
    {
        if (! isset($id)) {
            $id = get_the_ID();
        }
        $post = get_post($id);

        $d = array();
        $d['uri'] = $post->post_excerpt || false;
        $d['filename'] = basename($d['uri']) || false;
        $d['title'] = get_the_title($id);
        return json_encode($d);
    }


}

$wp_stereo = new StereoCustomPost();
