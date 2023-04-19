<?php
namespace DebugLogReader;

function elr_read_debug_log() {
    $debug_log_path = ABSPATH . 'wp-content/debug.log'; // Set the path to your debug.log file

    if (file_exists($debug_log_path) && is_readable($debug_log_path)) {
        return file_get_contents($debug_log_path);
    }

    return 'Error: Unable to read the debug.log file.';
}

// Add the admin menu page
function elr_admin_menu() {
    add_menu_page(
        'Debug Log Reader',
        'Debug Log Reader',
        'manage_options',
        'debug-log-reader',
        'DebugLogReader\elr_display_debug_log',
        'dashicons-format-status', // Icon for the menu item
        100 // Position of the menu item, 100 is placed towards the bottom
    );
}
add_action('admin_menu', 'DebugLogReader\elr_admin_menu');

// Display the debug log content on the admin menu page
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
    echo '<p id="elr-chatgpt-output-issue" style="display:none;"></p>';
    echo '<p id="elr-chatgpt-output-troubleshooting" style="display:none;"></p>';
    echo '<label for="elr-code-input" style="display:none; margin-top: 10px;">Do you have any followup questions regarding this issue? You\'re welcome to paste in the offending code and perhaps I can troubleshoot it further. Or perhaps you have a question from the plugin or theme developer you would like to ask:</label>';
    echo '<textarea id="elr-code-input" style="display:none; width: 100%; height: 150px; margin-top: 5px;"></textarea>';
    echo '<button id="elr-submit-code" class="button" style="display:none; margin-top: 10px;">Submit</button>';
    echo '<p id="elr-chatgpt-output-followup" style="display:none; margin-top: 10px;"></p>';
    echo '</div>';
}
