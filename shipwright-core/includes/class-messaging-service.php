<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Shipwright_Messaging {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('shipwright/v1', '/generate-reply', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_message_request'],
            'permission_callback' => '__return_true' // Demo için açık, production'da nonce şart
        ]);
    }

    public function handle_message_request($request) {
        $params = $request->get_json_params();
        $user_message = $params['message'] ?? '';
        $api_key = get_option('shipwright_openai_key');

        if (empty($api_key)) {
            return new WP_Error('no_key', 'API Key missing', ['status' => 500]);
        }

        // 1. Tool Tanımları (AI'ya yapabileceklerini öğretiyoruz)
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_order_status',
                    'description' => 'Get the current status of a customer order',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => [
                                'type' => 'string',
                                'description' => 'The order ID, e.g. #12345'
                            ]
                        ],
                        'required' => ['order_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_return_policy',
                    'description' => 'Get the return policy details',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ]
                ]
            ]
        ];

        // 2. OpenAI'ya İlk İstek (Soru + Tool Listesi)
        $payload = [
            'model' => 'gpt-3.5-turbo', // veya gpt-4
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful support assistant.'],
                ['role' => 'user', 'content' => $user_message]
            ],
            'tools' => $tools,
            'tool_choice' => 'auto'
        ];

        $response = $this->call_openai($payload, $api_key);
        
        if (is_wp_error($response)) return $response;

        $message = $response['choices'][0]['message'];

        // 3. AI bir Tool çağırmak istedi mi?
        if (isset($message['tool_calls'])) {
            $tool_call = $message['tool_calls'][0];
            $function_name = $tool_call['function']['name'];
            
            // Mock Data (Gerçek veritabanı yerine sahte veri döndürüyoruz)
            $function_result = "";
            if ($function_name === 'get_order_status') {
                $function_result = "Order #12345 is SHIPPED. Tracking URL: ups.com/track/999";
            } elseif ($function_name === 'get_return_policy') {
                $function_result = "Returns are accepted within 30 days of purchase.";
            }

            // 4. Sonucu AI'ya geri gönder ve final cevabı al
            $second_payload = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful support assistant.'],
                    ['role' => 'user', 'content' => $user_message],
                    $message, // AI'nın önceki cevabı (Tool call isteği)
                    [
                        'role' => 'tool',
                        'tool_call_id' => $tool_call['id'],
                        'name' => $function_name,
                        'content' => $function_result
                    ]
                ]
            ];

            $final_response = $this->call_openai($second_payload, $api_key);
            return rest_ensure_response(['reply' => $final_response['choices'][0]['message']['content']]);
        }

        // Tool çağırmadıysa direkt cevabı dön
        return rest_ensure_response(['reply' => $message['content']]);
    }

    private function call_openai($payload, $key) {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'body' => json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $key
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}