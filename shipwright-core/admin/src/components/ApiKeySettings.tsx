import React, { useState, useEffect } from 'react';
import { Key, Save, CheckCircle, AlertTriangle } from 'lucide-react';

// WP Global değişken tip tanımlaması
declare global {
  interface Window {
    shipwrightData: { root: string; nonce: string };
  }
}

export const ApiKeySettings = () => {
  const [apiKey, setApiKey] = useState('');
  const [status, setStatus] = useState<'loading' | 'idle' | 'saving' | 'success' | 'error'>('loading');
  const [hasKey, setHasKey] = useState(false);

  // Sayfa açılınca Key var mı kontrol et
  useEffect(() => {
    fetch(`${window.shipwrightData?.root}shipwright/v1/settings/key`, {
      headers: { 'X-WP-Nonce': window.shipwrightData?.nonce }
    })
    .then(res => res.json())
    .then(data => {
      setHasKey(data.has_key);
      setStatus('idle');
    })
    .catch(() => setStatus('error'));
  }, []);

  const handleSave = async () => {
    setStatus('saving');
    try {
      const res = await fetch(`${window.shipwrightData?.root}shipwright/v1/settings/key`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.shipwrightData?.nonce
        },
        body: JSON.stringify({ api_key: apiKey })
      });
      
      if (res.ok) {
        setStatus('success');
        setHasKey(true);
        setApiKey(''); // Inputu temizle
        setTimeout(() => setStatus('idle'), 3000);
      } else {
        setStatus('error');
      }
    } catch {
      setStatus('error');
    }
  };

  return (
    <div className="max-w-xl bg-white p-6 rounded-lg shadow border border-gray-200">
      <div className="flex items-center gap-3 mb-4">
        <div className="p-2 bg-indigo-50 rounded-lg text-indigo-600">
          <Key size={24} />
        </div>
        <div>
          <h2 className="text-xl font-bold text-gray-800">AI Model Configuration</h2>
          <p className="text-sm text-gray-500">Configure your OpenAI or Gemini API Key.</p>
        </div>
      </div>

      <div className="space-y-4">
        <div className="relative">
          <input
            type="password"
            value={apiKey}
            onChange={(e) => setApiKey(e.target.value)}
            placeholder={hasKey ? "•••••••••••••••••••••••• (Key Configured)" : "sk-..."}
            className="w-full p-3 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 outline-none"
          />
          {hasKey && (
            <span className="absolute right-3 top-3 text-green-600 flex items-center gap-1 text-xs font-bold bg-green-50 px-2 py-1 rounded">
              <CheckCircle size={14} /> ACTIVE
            </span>
          )}
        </div>

        <button
          onClick={handleSave}
          disabled={status === 'saving' || !apiKey}
          className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 transition-all"
        >
          {status === 'saving' ? 'Saving...' : 'Save API Key'}
          {status === 'success' && <CheckCircle size={18} />}
        </button>

        {status === 'error' && (
          <p className="text-red-500 text-sm flex items-center gap-1">
            <AlertTriangle size={16} /> Connection failed. Please try again.
          </p>
        )}
      </div>
    </div>
  );
};