import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './index.css'

// 1. Konsola "Ben buradayım" mesajı atalım
console.log("🚀 Shipwright React Başlatılıyor...");

// 2. Kök elementi bulalım
const rootElement = document.getElementById('shipwright-root');

if (!rootElement) {
  // Eğer HTML'de kutu yoksa body'ye yazalım
  console.error("❌ Kök element (shipwright-root) bulunamadı!");
  document.body.innerHTML += "<h1 style='color:red; padding:20px'>HATA: React Kök Elementi Bulunamadı!</h1>";
} else {
  // 3. Veri kontrolü yapalım
  if (!window.shipwrightData) {
    console.warn("⚠️ shipwrightData eksik, varsayılanlar atanıyor...");
    window.shipwrightData = { root: '', nonce: '' }; // Çökmemesi için boş veri
  }

  try {
    ReactDOM.createRoot(rootElement).render(
      <React.StrictMode>
        <App />
      </React.StrictMode>,
    )
    console.log("✅ React başarıyla mount edildi.");
  } catch (err) {
    console.error("❌ React Render Hatası:", err);
    rootElement.innerHTML = `<div style='color:red; padding:20px; border:2px solid red'>
      <h3>Kritik Hata</h3>
      <p>React uygulaması başlatılamadı.</p>
      <pre>${err}</pre>
    </div>`;
  }
}