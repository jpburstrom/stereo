<?php 
/**
* Stereo
* Johannes Burström 2013
*
* Stereo custom post type
*/

add_action('init', 'stereo_init_custom_post_type');
function stereo_init_custom_post_type() 
{
    $s = stereo_option("playlist_singular");
    $p = stereo_option("playlist_plural");
    $ts = stereo_option("playlist_taxonomy_singular");
    $tp = stereo_option("playlist_taxonomy_plural");

    register_post_type( 'stereo_playlist',
        array(
            'labels' => array(
                'name' => $p,
                'singular_name' => $s,
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

    register_taxonomy( 'stereo_category', array( 'stereo_playlist' ), array(
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

}

