<?php

function wp_ppk_custom_manage_reservations_menu() {
    add_submenu_page('edit.php?post_type=ppk_classroom', 'Manage Reservatioins', 'Manage Reservatioins', 'manage_options', 'manage-classroom-reservations-menu', 'wp_ppk_manage_reservations_function');
}

function wp_ppk_custom_add_reservation_menu() {
    add_submenu_page('edit.php?post_type=ppk_classroom', 'Add Reservation', 'Add Reservation', 'manage_options', 'add-classroom-reservations-menu', 'wp_ppk_add_reservation_function');
}

add_action('admin_menu', 'wp_ppk_custom_manage_reservations_menu');
add_action('admin_menu', 'wp_ppk_custom_add_reservation_menu');

function wp_ppk_manage_reservations_function() {
    include_once('wp-ppk-manage-reservations.php');
}

function wp_ppk_add_reservation_function() {
    include_once('wp-ppk-add-reservation.php');
}