<?php
/**
 * Plugin Name: Shipwright AI
 * Description: AI Feature Kit with BYOK Architecture.
 * Version: 1.0.0
 * Author: Shipwright Team
 */

if ( ! defined( 'ABSPATH' ) ) exit;
require_once plugin_dir_path(__FILE__) . 'includes/class-messaging-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-commerce-service.php';
class ShipwrightAI {
    public function __construct() {
        new Shipwright_Messaging();
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_app']);
        add_action('rest_api_init', [$this, 'register_api_routes']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Shipwright AI', 
            'Shipwright AI', 
            'manage_options', 
            'shipwright-ai', 
            [$this, 'render_admin_page'], 
            'dashicons-superhero'
        );
    }

    public function render_admin_page() {
        echo '<div id="shipwright-root"></div>';
    }

    public function register_api_routes() {
        // API Key Kaydetme
        register_rest_route('shipwright/v1', '/settings/key', [
            'methods' => 'POST',
            'callback' => [$this, 'save_api_key'],
            'permission_callback' => function() { return current_user_can('manage_options'); }
        ]);

        // API Key Durumu Kontrol (Key'i asla geri döndürmeyiz, sadece var/yok)
        register_rest_route('shipwright/v1', '/settings/key', [
            'methods' => 'GET',
            'callback' => [$this, 'get_key_status'],
            'permission_callback' => function() { return current_user_can('manage_options'); }
        ]);
    }

    public function save_api_key($request) {
        $params = $request->get_json_params();
        $key = sanitize_text_field($params['api_key'] ?? '');

        if (empty($key)) return new WP_Error('invalid_key', 'API Key cannot be empty', ['status' => 400]);

        update_option('shipwright_openai_key', $key);
        return rest_ensure_response(['success' => true]);
    }

    public function get_key_status() {
        $key = get_option('shipwright_openai_key');
        return rest_ensure_response(['has_key' => !empty($key)]);
    }

    public function enqueue_admin_app($hook) {
        if ($hook !== 'toplevel_page_shipwright-ai') return;

        // React Build dosyasını yükle (isim build sonrası hash alabilir, şimdilik sabit varsayıyoruz)
        $script_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.js';
        $style_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.css';

        // Geliştirme ortamında dosya yoksa hata vermesin diye kontrol (Opsiyonel)
        wp_enqueue_script('shipwright-react', $script_path, ['wp-element'], '1.0.0', true);
        wp_enqueue_style('shipwright-style', $style_path);

        // React'a WP verilerini gönder (Nonce ve API URL)
        wp_localize_script('shipwright-react', 'shipwrightData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
}

new ShipwrightAI();