import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './index.css'

// EĞER WORDPRESS YOKSA (Geliştirme Modu) SAHTE VERİ EKLE
if (!window.shipwrightData) {
  window.shipwrightData = {
    root: 'http://localhost/wp-json/', // Sahte API adresi
    nonce: '12345' // Sahte güvenlik anahtarı
  };
}

ReactDOM.createRoot(document.getElementById('shipwright-root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)