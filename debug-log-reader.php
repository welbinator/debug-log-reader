<?php
/*
Plugin Name: Debug Log Reader
Description: A simple plugin to display the contents of the debug.log file in the WordPress admin area.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
*/

// Your plugin code will go here

function elr_read_debug_log() {
    $debug_log_path = ABSPATH . 'wp-content/debug.log'; // Set the path to your debug.log file

    if (file_exists($debug_log_path) && is_readable($debug_log_path)) {
        return file_get_contents($debug_log_path);
    }

    return 'Error: Unable to read the debug.log file.';
}

function elr_admin_menu() {
    add_menu_page(
        'Debug Log Reader',
        'Debug Log Reader',
        'manage_options',
        'debug-log-reader',
        'elr_display_debug_log',
        'dashicons-format-status', // Icon for the menu item
        100 // Position of the menu item, 100 is placed towards the bottom
    );
}
add_action('admin_menu', 'elr_admin_menu');


function elr_display_debug_log() {
    echo '<div class="wrap">';
    echo '<h1>Debug Log Reader</h1>';

    // Add buttons to toggle the display of debug.log content and clear the debug.log file
    echo '<button id="elr-toggle-debug-log" class="button button-primary">Display debug.log contents</button>';
    echo '&nbsp;<button id="elr-clear-debug-log" class="button">Clear debug.log file</button>';

    // Add a container for the debug.log content
    echo '<pre id="elr-debug-log-content" style="display:none;">' . esc_html(elr_read_debug_log()) . '</pre>';

     // Add the "Tell me what's wrong with my site" button and a container for the ChatGPT output
    echo '<button id="elr-tell-me-whats-wrong" class="button" style="display:none; margin-top: 10px;">Tell me what\'s wrong with my site</button>';
    echo '<div id="elr-chatgpt-output-wrapper">';
    echo '<h2 class="chatgpt-output-heading" style="display:none;">Here\'s what\'s wrong with your website</h2>';
    echo '<h3 class="sub-heading" style="display:none;">The Problem:</h3>';
    echo '<p id="elr-chatgpt-output-issue" style="display:none;"></p>';
    echo '<h3 class="sub-heading" style="display:none;">What are some basic WordPress troubleshooting steps?</h3>';
    echo '<p id="elr-chatgpt-output-troubleshooting" style="display:none;"></p>';
    echo '<label for="elr-code-input" style="display:none; margin-top: 10px;">If you\'d like, you can paste in the code of the offending file and I can attempt to assist you further:</label>';
    echo '<textarea id="elr-code-input" style="display:none; width: 100%; height: 150px; margin-top: 5px;"></textarea>';
    echo '<button id="elr-submit-code" class="button" style="display:none; margin-top: 10px;">Submit</button>';
    echo '<p id="elr-chatgpt-output-followup" style="display:none; margin-top: 10px;"></p>';
    echo '</div>';
}





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


//contents deleted notice

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

function elr_chatgpt_settings_page() {
    add_submenu_page(
        'debug-log-reader',           // Parent menu slug
        'ChatGPT Settings',           // Page title
        'ChatGPT Settings',           // Menu title
        'manage_options',             // Capability
        'chatgpt-settings',           // Menu slug
        'elr_display_chatgpt_settings' // Callback function
    );
}
add_action('admin_menu', 'elr_chatgpt_settings_page');


function elr_display_chatgpt_settings() {
    ?>
    <div class="wrap">
        <h1>ChatGPT Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('elr_chatgpt_options_group');
            do_settings_sections('elr_chatgpt_options_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">ChatGPT API Key</th>
                    <td><input type="text" name="elr_chatgpt_api_key" value="<?php echo esc_attr(get_option('elr_chatgpt_api_key')); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function elr_register_chatgpt_settings() {
    register_setting('elr_chatgpt_options_group', 'elr_chatgpt_api_key');
}
add_action('admin_init', 'elr_register_chatgpt_settings');

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

function elr_chatgpt_request($api_key, $input, $model = 'gpt-3.5-turbo') {
    $client = new Client([
        'base_uri' => 'https://api.openai.com/',
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
    ]);

    $params = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI language model that analyzes WordPress debug logs and provides suggestions to resolve the issues'
            ],
            [
                'role' => 'user',
                'content' => $input,
            ],
        ],
    ];

    try {
        $response = $client->post('v1/chat/completions', [
            'json' => $params,
        ]);

        $response_data = json_decode($response->getBody(), true);
        return $response_data['choices'][0]['message']['content'];
    } catch (ClientException $e) {
        return 'Error: ' . $e->getMessage();
    }
}



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





