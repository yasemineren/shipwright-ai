<?php
/**
 * Plugin Name: Shipwright AI
 * Description: Production Ready (React + API)
 * Version: 1.2.1
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 👇 BEYNİ GERİ ÇAĞIRIYORUZ (Bu dosyaların yerinde olduğundan emin ol)
require_once plugin_dir_path(__FILE__) . 'includes/class-messaging-service.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-commerce-service.php';

class Shipwright_AI {
    public function __construct() {
        // API Servislerini Başlat
        new Shipwright_Messaging();
        new Shipwright_Commerce();

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

        // Dinamik Dosya Bulucu (Burası Aynen Kalıyor)
        $dist_path = plugin_dir_path(__FILE__) . 'admin/dist/assets/';
        $js_files = glob($dist_path . '*.js');
        $css_files = glob($dist_path . '*.css');

        if (!$js_files) return; // Hata vermesin, sessizce çıksın

        $js_url = plugin_dir_url(__FILE__) . 'admin/dist/assets/' . basename($js_files[0]);
        $css_url = plugin_dir_url(__FILE__) . 'admin/dist/assets/' . ($css_files ? basename($css_files[0]) : '');

        if ($css_files) wp_enqueue_style('shipwright-css', $css_url, [], '1.2.1');
        
        wp_enqueue_script('shipwright-js', $js_url, ['wp-element'], '1.2.1', true);

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