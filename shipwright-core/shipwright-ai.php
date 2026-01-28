<?php
/**
 * Plugin Name: Shipwright AI
 * Description: Production-grade AI Feature Kit (React + Gemini + PHP)
 * Version: 1.0.6
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-messaging-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-commerce-service.php';

class Shipwright_AI {
    public function __construct() {
        new Shipwright_Messaging();
        new Shipwright_Commerce();

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // 👇 İŞTE BU SATIR EKSİKTİ: Scripti "Module" olarak işaretle
        add_filter('script_loader_tag', [$this, 'add_type_attribute'], 10, 3);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Shipwright AI', 'Shipwright AI', 'manage_options', 'shipwright-ai',
            [$this, 'render_app'], 'dashicons-superhero', 2
        );
    }

    public function render_app() {
        echo '<div id="shipwright-root"></div>';
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_shipwright-ai') {
            return;
        }

        $js_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.js';
        $css_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.css';

        wp_enqueue_style('shipwright-css', $css_path, [], '1.0.6');
        
        // true = Footer'da yükle (HTML oluştuktan sonra çalışması için önemli)
        wp_enqueue_script('shipwright-js', $js_path, ['wp-element'], '1.0.6', true);

        wp_localize_script('shipwright-js', 'shipwrightData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    // 👇 Script etiketine type="module" ekleyen sihirli fonksiyon
    public function add_type_attribute($tag, $handle, $src) {
        if ('shipwright-js' !== $handle) {
            return $tag;
        }
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
}

new Shipwright_AI();