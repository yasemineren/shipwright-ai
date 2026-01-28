import React, { useState } from 'react';
import { MessageSquare, Send, Bot } from 'lucide-react';

declare global {
  interface Window {
    shipwrightData: { root: string; nonce: string };
  }
}

export const MessagingDemo = () => {
  const [input, setInput] = useState('');
  const [messages, setMessages] = useState<{role: 'user' | 'ai', content: string}[]>([
    {role: 'ai', content: 'Merhaba! Size nasıl yardımcı olabilirim? (Örn: Siparişim nerede?)'}
  ]);
  const [loading, setLoading] = useState(false);

  const handleSend = async () => {
    if (!input) return;

    const userMsg = input;
    setMessages(prev => [...prev, {role: 'user', content: userMsg}]);
    setInput('');
    setLoading(true);

    try {
      const res = await fetch(`${window.shipwrightData?.root}shipwright/v1/generate-reply`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.shipwrightData?.nonce
        },
        body: JSON.stringify({ message: userMsg })
      });
      
      const data = await res.json();
      setMessages(prev => [...prev, {role: 'ai', content: data.reply}]);
    } catch (e) {
      setMessages(prev => [...prev, {role: 'ai', content: 'Üzgünüm, bir hata oluştu.'}]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-2xl mt-8 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
      <div className="bg-gray-50 p-4 border-b border-gray-200 flex items-center gap-2">
        <MessageSquare className="text-blue-600" size={20} />
        <h3 className="font-semibold text-gray-800">Smart Inbox Demo</h3>
      </div>
      
      <div className="h-64 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
        {messages.map((m, i) => (
          <div key={i} className={`flex ${m.role === 'user' ? 'justify-end' : 'justify-start'}`}>
            <div className={`max-w-[80%] p-3 rounded-lg text-sm ${
              m.role === 'user' 
                ? 'bg-blue-600 text-white rounded-br-none' 
                : 'bg-white border border-gray-200 text-gray-700 rounded-bl-none shadow-sm'
            }`}>
              {m.role === 'ai' && <Bot size={16} className="mb-1 text-blue-500" />}
              {m.content}
            </div>
          </div>
        ))}
        {loading && <div className="text-gray-400 text-xs animate-pulse">AI yazıyor...</div>}
      </div>

      <div className="p-3 bg-white border-t border-gray-200 flex gap-2">
        <input 
          value={input}
          onChange={(e) => setInput(e.target.value)}
          placeholder="Mesaj yazın..."
          className="flex-1 p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
          onKeyDown={(e) => e.key === 'Enter' && handleSend()}
        />
        <button onClick={handleSend} disabled={loading} className="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
          <Send size={20} />
        </button>
      </div>
    </div>
  );
};