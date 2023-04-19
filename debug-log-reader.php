<?php
/*
Plugin Name: Debug Log Reader
Description: A simple plugin to display the contents of the debug.log file in the WordPress admin area.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
*/

require_once plugin_dir_path(__FILE__) . '/includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . '/includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . '/includes/chatgpt.php';
require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';

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





