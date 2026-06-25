<?php
/**
 * Register Custom Post Type and Taxonomies
 */

function liveradio_register_cpt() {
    $labels = array(
        'name'                  => _x( 'Radio Stations', 'Post Type General Name', 'liveradio' ),
        'singular_name'         => _x( 'Radio Station', 'Post Type Singular Name', 'liveradio' ),
        'menu_name'             => __( 'Radio Stations', 'liveradio' ),
        'name_admin_bar'        => __( 'Radio Station', 'liveradio' ),
        'add_new'               => __( 'Add New', 'liveradio' ),
        'add_new_item'          => __( 'Add New Radio Station', 'liveradio' ),
        'new_item'              => __( 'New Radio Station', 'liveradio' ),
        'edit_item'             => __( 'Edit Radio Station', 'liveradio' ),
        'update_item'           => __( 'Update Radio Station', 'liveradio' ),
        'view_item'             => __( 'View Radio Station', 'liveradio' ),
        'view_items'            => __( 'View Radio Stations', 'liveradio' ),
        'search_items'          => __( 'Search Radio Station', 'liveradio' ),
    );
    $args = array(
        'label'                 => __( 'Radio Station', 'liveradio' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
        'taxonomies'            => array( 'genre', 'country', 'language' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-playlist-audio',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    register_post_type( 'radio_station', $args );

    // Register Newsletter Subscriber CPT
    $sub_labels = array(
        'name'                  => _x( 'Subscribers', 'Post Type General Name', 'liveradio' ),
        'singular_name'         => _x( 'Subscriber', 'Post Type Singular Name', 'liveradio' ),
        'menu_name'             => __( 'Subscribers', 'liveradio' ),
        'all_items'             => __( 'All Subscribers', 'liveradio' ),
    );
    $sub_args = array(
        'label'                 => __( 'Subscriber', 'liveradio' ),
        'labels'                => $sub_labels,
        'supports'              => array( 'title' ),
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-email-alt',
        'capability_type'       => 'post',
        'capabilities'          => array(
            'create_posts' => 'do_not_allow', // Admin shouldn't manually create emails
        ),
        'map_meta_cap'          => true,
    );
    register_post_type( 'newsletter_sub', $sub_args );

    // Register Taxonomies
    register_taxonomy( 'genre', array( 'radio_station' ), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x( 'Genres', 'taxonomy general name', 'liveradio' ),
            'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'liveradio' ),
        ),
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ));

    register_taxonomy( 'country', array( 'radio_station' ), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x( 'Countries', 'taxonomy general name', 'liveradio' ),
            'singular_name'     => _x( 'Country', 'taxonomy singular name', 'liveradio' ),
        ),
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'country' ),
        'show_in_rest'      => true,
    ));

    register_taxonomy( 'language', array( 'radio_station' ), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x( 'Languages', 'taxonomy general name', 'liveradio' ),
            'singular_name'     => _x( 'Language', 'taxonomy singular name', 'liveradio' ),
        ),
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'language' ),
        'show_in_rest'      => true,
    ));
}
add_action( 'init', 'liveradio_register_cpt', 0 );
