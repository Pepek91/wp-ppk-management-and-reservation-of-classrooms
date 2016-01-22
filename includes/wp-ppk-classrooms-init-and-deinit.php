<?php

function wp_ppk_classroom_install() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'ppk_classroom_reservations';

    $charset_collate = $wpdb->get_charset_collate();
//new
//    CREATE TABLE `wp_ppk_classroom_reservations` (
//    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//    `classroom_id` bigint(20) unsigned NOT NULL,
//    `cyclical` tinyint(4) DEFAULT NULL,
//    `cyclical_untile` date DEFAULT NULL,
//    `reserved_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//    `reserved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//    PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    $sql = "CREATE TABLE $table_name (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `classroom_id` bigint(20) unsigned NOT NULL,
    `cyclical` tinyint(4) DEFAULT NULL,
    `cyclical_untile` date DEFAULT NULL,
    `reserved_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `reserved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
   ) $charset_collate;";

    //old
// $sql = "CREATE TABLE IF NOT EXISTS $table_name (
// `id` int(11) NOT NULL AUTO_INCREMENT,
// `classroom_id` bigint(20) unsigned NOT NULL,
// `date_from` datetime NOT NULL,
// `date_to` datetime NOT NULL,
// `cyclical` tinyint(4) DEFAULT NULL,
// `cyclical_untile` date DEFAULT NULL,
// `reserved_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
// `reserved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
// PRIMARY KEY  (`id`)
//) $charset_collate;";
    
//new
//    CREATE TABLE `wp_ppk_classroom_reservations_dates` (
//    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//    `reservation_id` int(11) unsigned NOT NULL,
//    `date_from` datetime NOT NULL,
//    `date_to` datetime NOT NULL,
//    PRIMARY KEY (`id`)
//   ) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8
    
    $table_name_dates = $wpdb->prefix . 'ppk_classroom_reservations_dates';
    $sql_dates =  "CREATE TABLE $table_name_dates (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `reservation_id` int(11) unsigned NOT NULL,
    `date_from` datetime NOT NULL,
    `date_to` datetime NOT NULL,
    PRIMARY KEY (`id`)
   ) $charset_collate";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
    dbDelta($sql_dates);
}

function wp_ppk_classroom_uninstall() {
    
}
