<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
delete_option('seggwat_project_key');
delete_option('seggwat_button_color');
delete_option('seggwat_button_position');
delete_option('seggwat_default_behavior');
delete_option('seggwat_language');
delete_option('seggwat_show_powered_by');

// Clean post meta - direct query is appropriate for bulk cleanup during uninstall.
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s)",
        '_seggwat_disable_widget',
        '_seggwat_widget_behavior'
    )
);
