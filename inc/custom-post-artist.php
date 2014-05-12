<?php 
/**
* Stereo
* Johannes Burström 2013
*
* Stereo custom post type
*/

add_action('init', 'stereo_init_custom_post_type_artist');

function stereo_init_custom_post_type_artist() 
{
    $s = stereo_option("artist_singular");
    $p = stereo_option("artist_plural");
	register_post_type( 'stereo_artist', array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'supports'          => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'       => stereo_option("has_artist_archive"),
		'query_var'         => true,
        'rewrite' => array('slug' => stereo_option("artist_slug")),
		'labels'            => array(
            'name' => $p,
            'menu_name' => $p,
            'singular_name' => $s,
            'add_new' => __( 'Add New', 'stereo' ),
            'add_new_item' => __( 'Add New ', 'stereo' ) . $s,
            'edit' => __( 'Edit', 'stereo' ),
            'edit_item' => __( 'Edit', 'stereo' ) . " "  . $s,
            'new_item' => __( 'New', 'stereo' ) . " " . $s,
            'view' => __( 'View', 'stereo' ) . " " .$s,
            'view_item' => __( 'View', 'stereo' ) . " " .$s,
            'search_items' => __( 'Search', 'stereo' ) . " $p",
            'not_found' => sprintf(__( 'No %s found', 'stereo' ), strtolower($p)),
            'not_found_in_trash' => sprintf(__( 'No %s found in Trash', 'stereo' ), strtolower($p)),
            'parent' => __( 'Parent ' ) . $s
		),
	) );

}
