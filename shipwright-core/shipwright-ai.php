<?php
/**
 * Plugin Name: Shipwright AI
 * Description: Debug Mode (Safe Start)
 * Version: 1.0.9
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Shipwright_AI {
    public function __construct() {
        // Admin menüsünü ekle
        add_action('admin_menu', [$this, 'add_admin_menu']);
        // Scriptleri yükle
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Shipwright AI',       // Sayfa Başlığı
            'Shipwright AI',       // Menü Adı
            'manage_options',      // Yetki
            'shipwright-ai',       // Slug
            [$this, 'render_app'], // Fonksiyon
            'dashicons-superhero', // İkon
            2                      // Sıra
        );
    }

    public function render_app() {
        // HATA AYIKLAMA KUTUSU
        // React yüklenmese bile bu kutu görünmek ZORUNDA.
        echo '<div style="background:#fff; color:#333; padding:20px; border-left:5px solid #00a32a; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        echo '<h1 style="margin-top:0;">🚀 TEBRİKLER! PHP ÇALIŞIYOR.</h1>';
        echo '<p style="font-size:16px;">Şu an bu yazıyı görüyorsan eklenti başarıyla yüklendi ve aktif edildi demektir.</p>';
        echo '<hr>';
        echo '<p><strong>React Durumu:</strong> Aşağıdaki kutuya React uygulaması yüklenecek...</p>';
        echo '</div>';

        // React'in bağlanacağı yer
        echo '<div id="shipwright-root"></div>';
    }

    public function enqueue_assets($hook) {
        // Sadece kendi sayfamızda çalışsın
        if ($hook !== 'toplevel_page_shipwright-ai') {
            return;
        }

        // Dosya yollarını belirle
        $js_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.js';
        $css_path = plugin_dir_url(__FILE__) . 'admin/dist/assets/index.css';

        // Yükle
        wp_enqueue_style('shipwright-css', $css_path, [], '1.0.9');
        wp_enqueue_script('shipwright-js', $js_path, ['wp-element'], '1.0.9', true);

        // React'e veri gönder
        wp_localize_script('shipwright-js', 'shipwrightData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
        
        // Module tipi ekle (React için şart)
        add_filter('script_loader_tag', function($tag, $handle) {
            if ('shipwright-js' !== $handle) return $tag;
            return '<script type="module" src="' . esc_url(plugin_dir_url(__FILE__) . 'admin/dist/assets/index.js') . '"></script>';
        }, 10, 2);
    }
}

new Shipwright_AI();