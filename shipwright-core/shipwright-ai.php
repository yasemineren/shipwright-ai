<?php
/**
 * Plugin Name: Shipwright AI
 * Description: Production-grade AI Feature Kit (React + Gemini + PHP)
 * Version: 1.0.8
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Gerekli servisleri dahil et
require_once plugin_dir_path(__FILE__) . 'includes/class-messaging-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-commerce-service.php';

class Shipwright_AI {
    public function __construct() {
        // API Servislerini Başlat
        new Shipwright_Messaging();
        new Shipwright_Commerce();

        // Admin Menüsünü Ekle
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Scripti "Module" olarak işaretle (Vite/React için şart)
        add_filter('script_loader_tag', [$this, 'add_type_attribute'], 10, 3);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Shipwright AI',
            'Shipwright AI',
            'manage_options',
            'shipwright-ai',
            [$this, 'render_app'], // Sayfa içeriğini çizen fonksiyon
            'dashicons-superhero',
            2
        );
    }

    // React Uygulamasının Gömüleceği HTML (DEBUG MODU AKTİF)
    public function render_app() {
        // Kırmızı Debug Kutusu: PHP'nin çalıştığını kanıtlar
        echo '<div style="background:white; padding:20px; border:2px solid red; margin: 20px;">';
        echo '<h2 style="color:red;">🛠️ DEBUG MODU (v1.0.8)</h2>';
        echo '<p><strong>PHP Durumu:</strong> ✅ Çalışıyor!</p>';
        echo '<p><strong>React Root:</strong> 👇 Aşağıda yüklenmeye çalışacak...</p>';
        echo '</div>';

        // React'in bağlanacağı asıl kutu
        echo '<div id="shipwright-root"></div>';
    }

    // JS ve CSS Dosyalarını Yükle
    public function enqueue_assets($hook) {
        // Sadece kendi sayfamızda çalışsın
        if ($hook !== 'toplevel_page_shipwright-ai') {
            return;
        }

        $js_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.js';
        $css_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.css';

        // CSS Yükle (Versiyon 1.0.8 - Cache bozmak için)
        wp_enqueue_style('shipwright-css', $css_path, [], '1.0.8');

        // JS Yükle ve React'e Veri Gönder (Versiyon 1.0.8)
        // true = Footer'da yükle
        wp_enqueue_script('shipwright-js', $js_path, ['wp-element'], '1.0.8', true);

        // React'in beklediği verileri (window.shipwrightData) gönderiyoruz
        wp_localize_script('shipwright-js', 'shipwrightData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    // Script etiketine type="module" ekleyen fonksiyon
    public function add_type_attribute($tag, $handle, $src) {
        if ('shipwright-js' !== $handle) {
            return $tag;
        }
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
}

new Shipwright_AI();