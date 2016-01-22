<?php

/**
 * Custom post type file
 */
function wp_ppk_register_post_type() {

    $singular = 'Classroom';
    $plular = 'Classrooms';

    $labels = array(
        'name' => $plular,
        'singular_name' => $singular,
        'add_name' => 'Add new',
        'add_new_item' => 'Add new ' . $singular,
        'add_new' => 'Add New ' . $singular,
        'all_items' => $plular,
        'menu_name' => 'Wordpress Management and Reservation of Classrooms',
        'not_found' => 'No classrooms found.',
        'search_items' => 'Search ' . $plular,
        'edit' => 'Edit',
        'edit_item' => 'Edit ' . $singular,
        'view_item' => 'View ' . $singular,
        'not_found_in_trash' => 'No ' . $plular . ' found in trash',
        'new_item' => 'New ' . $singular,
        'viev' => 'View ' . $singular,
        'search_term' => 'Search' . $plular,
        'parent' => 'Parent' . $singular,
        'not_found' => 'No ' . $plular . ' found'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'custom_reservation'),
        'supports' => array('title')
    );

    register_post_type('ppk_classroom', $args);
}

add_action('init', 'wp_ppk_register_post_type');


function wp_ppk_create_reservation_taxonomy() {
    $singular = 'Classroom Type';
    $plural = 'Classroom Types';
    $slug = str_replace(' ', '_', strtolower($singular));
    $labels = array(
        'name' => $plural,
        'singular_name' => $singular,
        'search_items' => 'Search ' . $plural,
        'popular_items' => 'Popular ' . $plural,
        'all_items' => 'All ' . $plural,
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => 'Edit ' . $singular,
        'update_item' => 'Update ' . $singular,
        'add_new_item' => 'Add New ' . $singular,
        'new_item_name' => 'New ' . $singular . ' Name',
        'separate_items_with_commas' => 'Separate ' . $plural . ' with commas',
        'add_or_remove_items' => 'Add or remove ' . $plural,
        'choose_from_most_used' => 'Choose from the most used ' . $plural,
        'not_found' => 'No ' . $plural . ' found.',
        'menu_name' => $plural,
    );
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => $slug),
    );

    register_taxonomy($slug, 'ppk_classroom', $args);
}

add_action('init', 'wp_ppk_create_reservation_taxonomy');
