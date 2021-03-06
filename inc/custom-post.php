<?php 
/**
* Stereo
* Johannes Burström 2013
*
* Stereo custom post type
*/


if (stereo_option('create_artist_cpt'))
    include "custom-post-artist.php";

add_action('init', 'stereo_init_custom_post_type');
function stereo_init_custom_post_type() 
{
    $s = stereo_option("playlist_singular");
    $p = stereo_option("playlist_plural");
    $ts = stereo_option("playlist_taxonomy_singular");
    $tp = stereo_option("playlist_taxonomy_plural");
    $rs = stereo_option("playlist_taxonomy2_singular");
    $rp = stereo_option("playlist_taxonomy2_plural");

    register_post_type( 'stereo_playlist',
        array(
            'labels' => array(
                'name' => $p,
                'singular_name' => $s,
                'menu_name' => $p,
                'add_new' => __( 'Add New' ),
                'add_new_item' => __( 'Add New ' ) . $s,
                'edit' => __( 'Edit' ),
                'edit_item' => __( 'Edit' ) . " "  . $s,
                'new_item' => __( 'New' ) . " " . $s,
                'view' => __( 'View' ) . " " .$s,
                'view_item' => __( 'View' ) . " " .$s,
                'search_items' => __( 'Search' ) . " $p",
                'not_found' => sprintf(__( 'No %s found' ), strtolower($p)),
                'not_found_in_trash' => sprintf(__( 'No %s found in Trash' ), strtolower($p)),
                'parent' => __( 'Parent ' ) . $s

            ),
            'public' => true,
            'has_archive'       => stereo_option("has_playlist_archive"),
            'rewrite' => array('slug' => stereo_option("playlist_slug")),
            'supports' => array("editor", "title", "page-attributes", 'thumbnail')
        )
    );

    $options = array(
        'public' => true,
        'labels' => array( 'name' => "Track" ),
        'supports' => array("title", "page-attributes", 'thumbnail'),
        'hierarchical' => true
    );
    $options['show_ui'] = (1 == stereo_option('show_track_ui'));

    register_post_type( 'stereo_track', $options);

    register_taxonomy( 'stereo_category', array( 'stereo_playlist', 'stereo_artist' ), array(
        'hierarchical'      => false,
        'public'            => true,
        'show_in_nav_menus' => true,
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'rewrite' => array('slug' => stereo_option("playlist_taxonomy_slug")),
        'capabilities'      => array(
            'manage_terms'  => 'edit_posts',
            'edit_terms'    => 'edit_posts',
            'delete_terms'  => 'edit_posts',
            'assign_terms'  => 'edit_posts'
        ),
        'labels'            => array(
            'name'                       => $tp, 
            'singular_name'              => $ts,
            'search_items'               => __( 'Search', 'stereo' ) . " $tp",
            'popular_items'              => __( 'Popular', 'stereo' ) . " $tp",
            'all_items'                  => __( 'All', 'stereo' ) . " $tp",
            'parent_item'                => __( 'Parent', 'stereo' ) . " $ts",
            'parent_item_colon'          => __( 'Parent', 'stereo' ) . " $ts:",
            'edit_item'                  => __( 'Edit', 'stereo' ) . " $ts",
            'update_item'                => __( 'Update', 'stereo' ) . " $ts",
            'add_new_item'               => __( 'New', 'stereo' ) . " $ts",
            'new_item_name'              => __( 'New', 'stereo' ) . " $ts",
            'separate_items_with_commas' => "$tp " . __( 'separated by comma', 'stereo' ),
            'add_or_remove_items'        => __( 'Add or remove', 'stereo' ) . " $tp",
            'choose_from_most_used'      => __( 'Choose from the most used', 'stereo' ) . " $tp",
            'menu_name'                  => $tp,
        ),
    ) );

    if (stereo_option("show_second_taxonomy")) {
        register_taxonomy( 'stereo_role', array( 'stereo_playlist', 'stereo_artist' ), array(
            'hierarchical'      => false,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            //We just append _role to playlist slug
            'rewrite' => array('slug' => stereo_option("playlist_taxonomy2_slug")),
            'capabilities'      => array(
                'manage_terms'  => 'edit_posts',
                'edit_terms'    => 'edit_posts',
                'delete_terms'  => 'edit_posts',
                'assign_terms'  => 'edit_posts'
            ),
            'labels'            => array(
                'name'                       => $rp, 
                'singular_name'              => $rs,
                'search_items'               => __( 'Search', 'stereo' ) . " $rp",
                'popular_items'              => __( 'Popular', 'stereo' ) . " $rp",
                'all_items'                  => __( 'All', 'stereo' ) . " $rp",
                'parent_item'                => __( 'Parent', 'stereo' ) . " $rs",
                'parent_item_colon'          => __( 'Parent', 'stereo' ) . " $rs:",
                'edit_item'                  => __( 'Edit', 'stereo' ) . " $rs",
                'update_item'                => __( 'Update', 'stereo' ) . " $rs",
                'add_new_item'               => __( 'New', 'stereo' ) . " $rs",
                'new_item_name'              => __( 'New', 'stereo' ) . " $rs",
                'separate_items_with_commas' => "$rp " . __( 'separated by comma', 'stereo' ),
                'add_or_remove_items'        => __( 'Add or remove', 'stereo' ) . " $rp",
                'choose_from_most_used'      => __( 'Choose from the most used', 'stereo' ) . " $rp",
                'menu_name'                  => $rp,
            ),
        ) );
    }

}

if (true != stereo_option('taxonomy_tags')) {

    add_action( 'admin_menu', 'stereo_remove_tagsdiv');
    function stereo_remove_tagsdiv() {
        if (true != stereo_option('taxonomy_tags')) {
            remove_meta_box('tagsdiv-stereo_category', 'stereo_playlist', 'normal');
            if (stereo_option('create_artist_cpt'))
                remove_meta_box('tagsdiv-stereo_category', 'stereo_artist', 'normal');
        }
        if (true != stereo_option('taxonomy2_tags')) {
            remove_meta_box('tagsdiv-stereo_role', 'stereo_playlist', 'normal');
            if (stereo_option('create_artist_cpt'))
                remove_meta_box('tagsdiv-stereo_role', 'stereo_artist', 'normal');
        }
    }

    add_action( 'add_meta_boxes', 'stereo_add_tagsdiv');
    function stereo_add_tagsdiv() {
        if (true != stereo_option('taxonomy_tags')) {
            add_meta_box( 'stereo_category', stereo_option("playlist_taxonomy_plural"), 'stereo_category_metabox', 'stereo_playlist' ,'side','core', array('stereo_category'));
            if (stereo_option('create_artist_cpt'))
                add_meta_box( 'stereo_category', stereo_option("playlist_taxonomy_plural"), 'stereo_category_metabox', 'stereo_artist' ,'side','core', array('stereo_category'));
        }
        if (true != stereo_option('taxonomy2_tags')) {
            add_meta_box( 'stereo_role', stereo_option("playlist_taxonomy_plural"), 'stereo_category_metabox', 'stereo_playlist' ,'side','core', array('stereo_role'));
            if (stereo_option('create_artist_cpt'))
                add_meta_box( 'stereo_role', stereo_option("playlist_taxonomy_plural"), 'stereo_category_metabox', 'stereo_artist' ,'side','core', array('stereo_role'));
        }
    }  



    function stereo_category_metabox($post, $args) {  
        $taxonomy = $args['args'][0];

        // all terms of ctax
        $all_ctax_terms = get_terms($taxonomy,array('hide_empty' => 0)); 

        // all the terms currenly assigned to the post
        $all_post_terms = get_the_terms( $post->ID,$taxonomy );  

        // name for each input, notice the extra []
        $name = 'tax_input[' . $taxonomy . '][]';  

        // make an array of the ids of all terms attached to the post
        $array_post_term_ids = array();
        if ($all_post_terms) {
            foreach ($all_post_terms as $post_term) {
                $post_term_id = $post_term->term_id;
                $array_post_term_ids[] = $post_term_id;
            }
        }

    ?>

    <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv"> 

            <input type="hidden" name="<?php echo $name; ?>" value="0" />

            <ul>
    <?php   foreach($all_ctax_terms as $term){
        if (in_array($term->term_id, $array_post_term_ids)) {
            $checked = "checked = ''";
        }
        else {
            $checked = "";
        }
        $id = $taxonomy.'-'.$term->term_id;
        echo "<li id='$id'>";
        echo "<input type='checkbox' name='{$name}' id='in-$id'"
            . $checked ."value='$term->slug' /><label for='in-$id'> $term->name</label><br />";
        echo "</li>";
    }?>
           </ul>
    </div>
    <?php
    }
}

if (!is_admin() && stereo_option('playlists_in_main_loop')) {
    add_filter( 'pre_get_posts', 'stereo_cpt_get_posts' );
}

function stereo_cpt_get_posts( $query ) {

    if((is_home() && $query->is_main_query()) || is_feed()) {              

        $post_types = $query->get('post_type');          

        if(!is_array($post_types) && !empty($post_types))   
            $post_types = explode(',', $post_types);

        if(empty($post_types))                             
            $post_types[] = 'post';         
        $post_types[] = 'stereo_playlist';                       

        $post_types = array_filter(array_map('trim', $post_types));    

        $query->set('post_type', $post_types);         
    }

	return $query;
}
