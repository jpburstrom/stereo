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

        //add_action('admin_print_scripts', array(&$this, 'my_admin_scripts'));
        //add_action('admin_print_styles', array(&$this, 'my_admin_styles'));
        //
        
    }
    function my_admin_scripts() {
        global $current_screen;
        if ($current_screen->post_type == 'stereo_playlist') {
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_script('ui-sortable');
        }
    }

    function my_admin_styles() {
        global $current_screen;
        if ($current_screen->post_type == 'stereo_playlist') {
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
              'supports' => array("editor", "title", "page-attributes", 'thumbnail')
            )
          );
    }

    //Set up custom boxes for post type
    public function metaboxes() 
    { 
		global $post;
		$custom = get_post_custom($post->ID);
?>
        </script>
        <table class="form-table">
        <tbody>
        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'stereo_playlist' ); ?>
        </tr><td><label for="voider_gallery_subtitle">Subtitle</label>
        </td><td><input id="voider_gallery_subtitle" name="_gallery_subtitle" type="text" value="<?php echo $custom["_gallery_subtitle"][0]?>" />
        </td><tr><td><label for="voider_gallery_year">Year</label>
        </td><td><input id="voider_gallery_year" name="_gallery_year" type="text" value="<?php echo $custom["_gallery_year"][0]?>" />
        </td></tr>
        </tbody> </table>
<?php
        
    }


    //Add meta box to post type. Why this needs to be called from admin_head I don't know.
    public function admin_head() {
        return; //No boxes for now
        add_meta_box("stereo-meta", "Stereo Settings", array(&$this, "metaboxes"),
            "stereo-playlist", "normal", "low");
    }

    //Insert all meta values from the $this->meta_fields variable
	function wp_insert_post($post_id, $post = null)
    {
        if ( defined('DOING_AJAX') && DOING_AJAX )
            return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;
        if ( !wp_verify_nonce( $_POST['voider_gallery'], plugin_basename( __FILE__ ) ) )
            return;
        // Check permissions
        if ( 'page' == $_POST['post_type'] ) 
        {
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        }
        else
        {
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
        }
        
		if ($post->post_type == "stereo-playlist")
		{
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

}

$wp_stereo = new StereoCustomPost();

