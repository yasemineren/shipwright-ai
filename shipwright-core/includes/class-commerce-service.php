<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Shipwright_Commerce {

    // Demo Ürün Katalogu (Gerçekte WooCommerce'den gelir)
    private $products = [
        101 => ['name' => 'Professional Running Shoes', 'desc' => 'Lightweight, shock-absorbing footwear for marathon runners.'],
        102 => ['name' => 'Elegant Leather Wallet', 'desc' => 'Handcrafted brown leather wallet with multiple card slots.'],
        103 => ['name' => 'Wireless Noise Cancelling Headphones', 'desc' => 'Over-ear headphones with 20h battery life and deep bass.'],
        104 => ['name' => 'Sporty Gym Bag', 'desc' => 'Durable duffle bag, perfect for carrying sneakers and towels.'], 
        105 => ['name' => 'Classic Business Belt', 'desc' => 'Brown leather belt, perfect match for formal suits.'] 
    ];

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('shipwright/v1', '/get-recommendations', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_recommendation'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function handle_recommendation($request) {
        $params = $request->get_json_params();
        $target_id = $params['product_id'] ?? 101;
        $api_key = get_option('shipwright_openai_key');

        if (!isset($this->products[$target_id])) {
            return new WP_Error('not_found', 'Product not found', ['status' => 404]);
        }

        // 1. Hedef ürünün vektörünü (embedding) al
        $target_embedding = $this->get_embedding($this->products[$target_id]['desc'], $api_key);
        
        if (!$target_embedding) {
            // API Key yoksa veya hata varsa boş dönmesin, dummy öneri yapsın (Fallback)
            return rest_ensure_response([
                'target' => $this->products[$target_id],
                'recommendations' => [
                    ['id' => 999, 'name' => 'Demo Recommendation (Add API Key)', 'score' => '0%']
                ]
            ]);
        }

        $scores = [];

        // 2. Diğer tüm ürünlerle karşılaştır
        foreach ($this->products as $id => $product) {
            if ($id == $target_id) continue; // Kendisini önerme

            $embedding = $this->get_embedding($product['desc'], $api_key);
            if ($embedding) {
                // Cosine Similarity (Benzerlik) Hesapla
                $similarity = $this->cosine_similarity($target_embedding, $embedding);
                $scores[] = [
                    'id' => $id,
                    'name' => $product['name'],
                    'score' => round($similarity * 100, 2) . '%'
                ];
            }
        }

        // 3. Puana göre sırala (En yüksek en üstte)
        usort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return rest_ensure_response([
            'target' => $this->products[$target_id],
            'recommendations' => array_slice($scores, 0, 3) // İlk 3 öneri
        ]);
    }

    // Embedding oluşturma (Cache mekanizmalı)
    private function get_embedding($text, $api_key) {
        if (empty($api_key)) return null;

        $cache_key = 'sw_emb_' . md5($text);
        if ($cached = get_transient($cache_key)) {
            return $cached;
        }

        $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
            'body' => json_encode([
                'model' => 'text-embedding-3-small', // Hızlı ve ucuz model
                'input' => $text
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'timeout' => 20
        ]);

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $embedding = $body['data'][0]['embedding'] ?? null;

        if ($embedding) {
            set_transient($cache_key, $embedding, DAY_IN_SECONDS); // 1 gün sakla
        }

        return $embedding;
    }

    // Matematiksel Benzerlik Formülü (Cosine Similarity)
    private function cosine_similarity($vec_a, $vec_b) {
        $dot_product = 0;
        $norm_a = 0;
        $norm_b = 0;

        foreach ($vec_a as $key => $val) {
            $dot_product += $val * $vec_b[$key];
            $norm_a += $val * $val;
            $norm_b += $vec_b[$key] * $vec_b[$key];
        }

        return ($norm_a * $norm_b) ? $dot_product / (sqrt($norm_a) * sqrt($norm_b)) : 0;
    }
}