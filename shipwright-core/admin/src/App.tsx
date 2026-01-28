import React from 'react';
import { ApiKeySettings } from './components/ApiKeySettings';
import { MessagingDemo } from './components/MessagingDemo';
import { CommerceDemo } from './components/CommerceDemo'; // Yeni import

function App() {
  return (
    <div className="min-h-screen bg-gray-50 p-6 md:p-12 font-sans text-slate-800">
      <div className="max-w-5xl mx-auto space-y-10">
        
        {/* Header */}
        <header className="border-b border-gray-200 pb-6 flex justify-between items-end">
          <div>
            <h1 className="text-4xl font-extrabold text-slate-900 tracking-tight">Shipwright AI</h1>
            <p className="text-slate-500 mt-2 text-lg">Production-grade AI Feature Kit for WordPress</p>
          </div>
          <span className="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-200">v1.0.0</span>
        </header>
        
        {/* Settings */}
        <section>
          <ApiKeySettings />
        </section>

        <div className="grid gap-8 lg:grid-cols-1">
            {/* E-Ticaret Modülü */}
            <section>
                <CommerceDemo />
            </section>

            {/* Mesajlaşma Modülü */}
            <section>
                <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        💬 Smart Inbox <span className="text-xs font-normal text-gray-400 border px-2 py-0.5 rounded-full">Tool Calling</span>
                    </h3>
                    <MessagingDemo />
                </div>
            </section>
        </div>

      </div>
    </div>
  );
}

export default App;