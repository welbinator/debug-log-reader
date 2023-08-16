<?php
namespace DebugLogReader;

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

// Register ChatGPT settings
function elr_register_chatgpt_settings() {
    register_setting('elr_chatgpt_options_group', 'elr_chatgpt_api_key');
}
add_action('admin_init', 'DebugLogReader\elr_register_chatgpt_settings');

// Add the ChatGPT settings submenu page
function elr_chatgpt_settings_page() {
    add_submenu_page(
        'debug-log-reader',           // Parent menu slug
        'ChatGPT Settings',           // Page title
        'ChatGPT Settings',           // Menu title
        'manage_options',             // Capability
        'chatgpt-settings',           // Menu slug
        'DebugLogReader\elr_display_chatgpt_settings' // Callback function
    );
}
add_action('admin_menu', 'DebugLogReader\elr_chatgpt_settings_page');

// Display the ChatGPT settings page
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

// Make a request to the ChatGPT API
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
