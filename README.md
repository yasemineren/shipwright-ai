# shipwright-ai
WordPress AI Feature Kit (Site + Commerce + Messaging)
⛴️ Shipwright AI
Bridging the gap between legacy WordPress environments and modern Generative AI.


🎯 Context & Purpose
I built this project specifically for the Applied AI Engineer application.

My goal was to demonstrate:

Full-Stack Capability: Integrating a modern React/TypeScript SPA into a legacy PHP environment.

AI Engineering: Implementing Google Gemini LLM for context-aware messaging and commerce insights.

Rapid Prototyping: Building a production-ready, deployable plugin in a short sprint.

Problem Solving: Solving complex asset loading and CORS issues inherent in browser-based WASM environments (WordPress Playground).

🚀 Live Demo (No Installation Required)
You can test the full plugin running directly in your browser via WordPress Playground. No server or setup needed.

👉 Launch Shipwright AI in Browser

(Note: The demo automatically installs the plugin. You can enter your own Google Gemini API Key in the settings, or view the UI in generic mode.)

✨ Key Features
🤖 AI-Powered Assistant: Direct integration with Google Gemini API for answering user queries.

⚛️ Modern Admin Dashboard: A React + TypeScript Single Page Application (SPA) embedded seamlessly within the WordPress Admin.

🔌 Service-Based Architecture:

Messaging-Service: Handles LLM prompts, context injection, and sanitization.

Commerce-Service: Prepared structure for future WooCommerce product insights.

🛡️ Secure & Robust: Implements WordPress Nonces for API security and dynamic capability checks.

📂 Smart Asset Loading: Custom PHP logic to dynamically resolve Vite-bundled assets (.js, .css), preventing "White Screen of Death" in varying directory structures.

🛠️ Tech Stack
Frontend
React 18 & TypeScript: For a type-safe, component-based UI.

Vite: For ultra-fast bundling and building.

Tailwind CSS: For rapid, responsive styling.

Backend
PHP 8.0+: Core plugin logic.

WordPress REST API: Custom endpoints (/shipwright-ai/v1/chat, /settings) to communicate with the React frontend.

Google Gemini API: The intelligence layer.
