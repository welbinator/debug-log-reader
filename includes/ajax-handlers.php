<?php
namespace DebugLogReader;

// Clear the debug.log file via AJAX
function elr_ajax_clear_debug_log() {
    check_ajax_referer('elr_clear_debug_log', 'nonce');

    $debug_log_path = ABSPATH . 'wp-content/debug.log';

    if (file_exists($debug_log_path) && is_writable($debug_log_path)) {
        file_put_contents($debug_log_path, '');
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_elr_clear_debug_log', 'elr_ajax_clear_debug_log');

// Send a request to ChatGPT via AJAX
function elr_send_to_chatgpt() {
    check_ajax_referer('elr_send_to_chatgpt', 'nonce');

    $api_key = get_option('elr_chatgpt_api_key');
    $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';

    if (!$api_key) {
        wp_send_json_error('Error: ChatGPT API key not found. Please set it in the ChatGPT Settings page.', 400);
    }

    try {
        $response = elr_chatgpt_request($api_key, $prompt);
        wp_send_json_success($response);
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage(), 500);
    }
    
}
add_action('wp_ajax_elr_send_to_chatgpt', 'elr_send_to_chatgpt');
