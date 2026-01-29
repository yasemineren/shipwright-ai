<?php
class Shipwright_Messaging {
    private $namespace = 'shipwright-ai/v1';

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        // 1. Ayarları Kaydetme Rotası (React buraya POST atar)
        register_rest_route($this->namespace, '/settings', [
            'methods' => 'POST',
            'callback' => [$this, 'save_settings'],
            'permission_callback' => [$this, 'check_permission']
        ]);

        // 2. Ayarları Okuma Rotası (React açılınca buraya GET atar)
        register_rest_route($this->namespace, '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'check_permission']
        ]);
        
        // 3. Chat / Mesajlaşma Rotası
        register_rest_route($this->namespace, '/chat', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_chat'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }

    public function check_permission() {
        // Playground'da ve testlerde sorun çıkmaması için şimdilik herkese izin veriyoruz
        // Gerçekte: return current_user_can('manage_options');
        return true; 
    }

    public function save_settings($request) {
        $params = $request->get_json_params();
        
        if (isset($params['apiKey'])) {
            update_option('shipwright_gemini_api_key', sanitize_text_field($params['apiKey']));
            return new WP_REST_Response(['status' => 'success', 'message' => 'API Key Saved'], 200);
        }

        return new WP_REST_Response(['status' => 'error', 'message' => 'No Key Provided'], 400);
    }

    public function get_settings() {
        $api_key = get_option('shipwright_gemini_api_key', '');
        // Güvenlik için key'in tamamını göndermeyelim, sadece var mı yok mu
        return new WP_REST_Response([
            'apiKey' => $api_key ? 'configured' : '', // React tarafı dolu görsün diye
            'hasKey' => !empty($api_key)
        ], 200);
    }
    
    public function handle_chat($request) {
        // Burası Gemini'ye istek atacak kısım (Şimdilik dummy cevap verelim)
        return new WP_REST_Response([
            'reply' => 'Bağlantı başarılı! Gemini entegrasyonu hazır.'
        ], 200);
    }
}