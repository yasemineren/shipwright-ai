import React, { useState } from 'react';
import { ShoppingBag, Tag, Loader2 } from 'lucide-react';

declare global {
  interface Window {
    shipwrightData: { root: string; nonce: string };
  }
}

// React tarafında listelemek için sabit ürün listesi
const PRODUCTS = [
  { id: 101, name: 'Professional Running Shoes' },
  { id: 102, name: 'Elegant Leather Wallet' },
  { id: 103, name: 'Wireless Noise Cancelling Headphones' },
];

export const CommerceDemo = () => {
  const [selectedProduct, setSelectedProduct] = useState(101);
  const [recommendations, setRecommendations] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  const fetchRecommendations = async (productId: number) => {
    setLoading(true);
    setSelectedProduct(productId);
    setRecommendations([]); // Önce temizle

    try {
      const res = await fetch(`${window.shipwrightData?.root}shipwright/v1/get-recommendations`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.shipwrightData?.nonce
        },
        body: JSON.stringify({ product_id: productId })
      });
      
      const data = await res.json();
      setRecommendations(data.recommendations || []);
    } catch (e) {
      console.error("Hata:", e);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
      <div className="flex items-center gap-2 mb-6 border-b border-gray-100 pb-4">
        <ShoppingBag className="text-purple-600" />
        <div>
          <h3 className="text-lg font-bold text-gray-800">Smart Commerce</h3>
          <p className="text-xs text-gray-500">Vector Embeddings & Cosine Similarity</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        {/* Sol: Ürün Seçimi */}
        <div>
          <h4 className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Select a Product</h4>
          <div className="space-y-2">
            {PRODUCTS.map(p => (
              <button
                key={p.id}
                onClick={() => fetchRecommendations(p.id)}
                className={`w-full text-left p-3 rounded-md border transition-all flex items-center justify-between group ${
                  selectedProduct === p.id 
                    ? 'border-purple-500 bg-purple-50 text-purple-700 shadow-sm' 
                    : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'
                }`}
              >
                <span className="font-medium text-sm">{p.name}</span>
                {selectedProduct === p.id && <Loader2 size={16} className={loading ? "animate-spin" : "hidden"} />}
              </button>
            ))}
          </div>
        </div>

        {/* Sağ: AI Önerileri */}
        <div className="bg-gray-50 rounded-lg p-5 border border-gray-200/60">
          <h4 className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
            AI Recommendations
          </h4>
          
          {loading ? (
            <div className="space-y-3 animate-pulse opacity-60">
              <div className="h-10 bg-gray-200 rounded"></div>
              <div className="h-10 bg-gray-200 rounded"></div>
            </div>
          ) : recommendations.length > 0 ? (
            <div className="space-y-3">
              {recommendations.map((rec: any) => (
                <div key={rec.id} className="flex items-center justify-between p-3 bg-white rounded shadow-sm border border-gray-100 transition-transform hover:scale-[1.02]">
                  <div className="flex items-center gap-3">
                    <div className="bg-green-100 p-1.5 rounded text-green-600">
                      <Tag size={14} />
                    </div>
                    <div>
                      <div className="text-sm font-semibold text-gray-700">{rec.name}</div>
                      <div className="text-[10px] text-gray-400">ID: {rec.id}</div>
                    </div>
                  </div>
                  <span className="text-xs font-bold text-green-700 bg-green-100 px-2 py-1 rounded-full border border-green-200">
                    {rec.score} Match
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center text-gray-400 py-8 text-sm flex flex-col items-center">
              <ShoppingBag size={32} className="opacity-20 mb-2" />
              <span>Select a product to see related items based on semantic meaning.</span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};