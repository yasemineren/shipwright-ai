<?php
/**
 * Plugin Name: Shipwright AI
 * Description: Debug Mode (Force Error Display)
 * Version: 1.0.10
 * Author: Yasemin Eren
 */

// 👇 HATALARI ZORLA GÖSTEREN KOD BLOGU
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 👆 

if ( ! defined( 'ABSPATH' ) ) {
    die("ABSPATH tanımlı değil, direkt erişim yasak."); 
}

class Shipwright_AI {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Shipwright AI',
            'Shipwright AI',
            'manage_options',
            'shipwright-ai',
            [$this, 'render_app'], 
            'dashicons-superhero',
            2
        );
    }

    public function render_app() {
        echo '<div style="background:#fff; border:4px solid red; padding:20px; margin:20px;">';
        echo '<h1 style="color:red; margin:0;">🔴 BURADAYIM!</h1>';
        echo '<p>Eğer bu kutuyu görüyorsan:</p>';
        echo '<ul>';
        echo '<li>✅ Dosya yolu doğru (Plugin yüklendi)</li>';
        echo '<li>✅ PHP çalışıyor</li>';
        echo '<li>❌ Sorun muhtemelen React dosyalarını çağıran koddaydı.</li>';
        echo '</ul>';
        echo '</div>';
    }
}

new Shipwright_AI();