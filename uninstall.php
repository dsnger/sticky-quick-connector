<?php

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all ACF options
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'options_sqc_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_options_sqc_%'");