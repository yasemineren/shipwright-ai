<?php
/**
 * Plugin Name: Shipwright AI (Diagnostic Mode)
 * Description: Dosya yapısını kontrol eder.
 * Version: 1.1.0
 * Author: Yasemin Eren
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Shipwright_AI {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu() {
        add_menu_page('Shipwright Debug', 'Shipwright Debug', 'manage_options', 'shipwright-ai', [$this, 'render_debug_page'], 'dashicons-search', 2);
    }

    public function render_debug_page() {
        $plugin_dir = plugin_dir_path(__FILE__);
        // Vite config'de ayarladığımız yol:
        $expected_js = 'admin/dist/assets/index.js'; 
        $full_path = $plugin_dir . $expected_js;

        echo '<div style="background:#fff; padding:20px; border:2px solid #333; margin:20px; font-family:monospace;">';
        echo '<h2 style="color:black;">📂 TANI MODU (DIAGNOSTIC MODE)</h2>';
        
        // 1. Dosya Var mı?
        if (file_exists($full_path)) {
            echo '<p style="color:green; font-weight:bold; font-size:18px;">✅ KRİTİK DOSYA BULUNDU: ' . $expected_js . '</p>';
        } else {
            echo '<p style="color:red; font-weight:bold; font-size:18px;">❌ DOSYA YOK: ' . $expected_js . '</p>';
            echo '<p>Aranan Tam Yol: <code>' . $full_path . '</code></p>';
            
            // 2. Klasörde Neler Var? (Hata Ayıklama)
            echo '<h3>📂 Ana Klasör İçeriği:</h3>';
            echo '<pre style="background:#eee; padding:10px;">';
            print_r(scandir($plugin_dir));
            echo '</pre>';

            // 3. Admin Klasörüne Bak
            if (is_dir($plugin_dir . 'admin')) {
                echo '<h3>📂 Admin Klasörü İçeriği:</h3>';
                $admin_files = scandir($plugin_dir . 'admin');
                print_r($admin_files);

                // Dist klasörü var mı?
                if (in_array('dist', $admin_files)) {
                    echo '<h3>📂 Admin/Dist İçeriği:</h3>';
                    print_r(scandir($plugin_dir . 'admin/dist'));
                }
            } else {
                echo '<p style="color:red;">❌ "admin" klasörü bile kopyalanmamış!</p>';
            }
        }
        echo '</div>';
    }
}

new Shipwright_AI();