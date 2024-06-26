<?php
/*
Plugin Name: Debug Log Reader
Description: A simple plugin to display the contents of the debug.log file in the WordPress admin area.
Version: 1.0
Author: James Welbes
Author URI: https://yourwebsite.com
*/

require_once __DIR__ . '/includes/admin-menu.php'; // Update this line
require_once __DIR__ . '/includes/ajax-handlers.php'; // Update this line
require_once __DIR__ . '/includes/chatgpt.php'; // Update this line
require_once __DIR__ . '/vendor/autoload.php'; // Update this line

use GuzzleHttp\Client;

function elr_enqueue_scripts($hook) {
    if ('toplevel_page_debug-log-reader' === $hook) {
         // Enqueue the admin.css file
         wp_enqueue_style(
            'elr-admin-css',
            plugin_dir_url(__FILE__) . 'admin/css/admin.css',
            array(),
            '1.0.0'
        );
        wp_enqueue_script(
            'elr-admin-js',
            plugin_dir_url(__FILE__) . 'admin/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'elr-admin-js',
            'elr_vars',
            array(
                'nonce' => wp_create_nonce('elr_clear_debug_log'),
                'chatgpt_nonce' => wp_create_nonce('elr_send_to_chatgpt') // Add this line
            )
        );
    }
}
add_action('admin_enqueue_scripts', 'elr_enqueue_scripts');

// Add the admin_menu action with the correct namespace for the elr_admin_menu() function
add_action('admin_menu', 'DebugLogReader\elr_admin_menu'); // Update this line

// simulate bad code to generate error in logs
function elr_trigger_error_log() {
    // Check if the user has the capability to edit_theme_options (usually administrators)
    if (current_user_can('edit_theme_options')) {
        // Trigger a division by zero error
        $numerator = 10;
        $denominator = 0;
        $result = $numerator / $denominator;
    }
}
// add_action('init', 'elr_trigger_error_log');
