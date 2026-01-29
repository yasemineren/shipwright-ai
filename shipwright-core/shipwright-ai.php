<?php
/**
 * Plugin Name: Shipwright AI
 * Description: AI Feature Kit
 * Version: 1.2.0
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Shipwright_AI {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
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

        // 1. Dinamik Dosya Bulucu
        // Ezbere 'index.js' aramak yerine, assets klasöründeki .js dosyasını buluyoruz.
        $dist_path = plugin_dir_path(__FILE__) . 'admin/dist/assets/';
        $js_files = glob($dist_path . '*.js');
        $css_files = glob($dist_path . '*.css');

        if (!$js_files) {
            // Eğer dosya yoksa beyaz ekran yerine bu hatayı basar
            wp_die('<h1>HATA: React dosyaları bulunamadı!</h1><p>Aranan yol: ' . $dist_path . '</p>');
        }

        // Bulunan ilk dosyanın ismini al
        $js_file_name = basename($js_files[0]);
        $css_file_name = $css_files ? basename($css_files[0]) : '';

        // 2. URL'leri oluştur
        $js_url = plugin_dir_url(__FILE__) . 'admin/dist/assets/' . $js_file_name;
        $css_url = plugin_dir_url(__FILE__) . 'admin/dist/assets/' . $css_file_name;

        // 3. Yükle
        if ($css_file_name) {
            wp_enqueue_style('shipwright-css', $css_url, [], '1.2.0');
        }
        
        wp_enqueue_script('shipwright-js', $js_url, ['wp-element'], '1.2.0', true);

        wp_localize_script('shipwright-js', 'shipwrightData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    public function add_type_attribute($tag, $handle, $src) {
        if ('shipwright-js' !== $handle) return $tag;
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
}

new Shipwright_AI();