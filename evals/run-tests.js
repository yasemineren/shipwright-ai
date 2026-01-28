import { GoogleGenerativeAI } from "@google/generative-ai";
import colors from 'colors';
import readline from 'readline';

// Soru sorma arayüzü
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

// TEST SENARYOLARI
const TEST_CASES = [
  {
    id: 'MSG-001',
    category: 'Messaging',
    prompt: 'Siparişim nerede? Sipariş numaram #12345',
    expected_type: 'tool_call',
    expected_value: 'get_order_status',
    description: 'Sipariş durumu sorgusu doğru toolu tetikliyor mu?'
  },
  {
    id: 'MSG-002',
    category: 'Messaging',
    prompt: 'İade politikanız nedir? Ürünü geri verebilir miyim?',
    expected_type: 'tool_call',
    expected_value: 'get_return_policy',
    description: 'İade sorusu politika toolunu tetikliyor mu?'
  },
  {
    id: 'MSG-003',
    category: 'Messaging',
    prompt: 'Merhaba, nasılsınız?',
    expected_type: 'text',
    expected_value: null,
    description: 'Normal sohbette gereksiz tool kullanımı engelleniyor mu?'
  }
];

// GEMINI İÇİN TOOL TANIMLAMASI
const toolsDef = [
  {
    function_declarations: [
      {
        name: "get_order_status",
        description: "Get the current status of a customer order",
        parameters: {
          type: "object",
          properties: {
            order_id: { type: "string", description: "The order ID, e.g. #12345" }
          },
          required: ["order_id"]
        }
      },
      {
        name: "get_return_policy",
        description: "Get the return policy details",
        parameters: {
          type: "object",
          properties: {},
        }
      }
    ]
  }
];

console.log('\n🚢 SHIPWRIGHT AI - REGRESSION TESTING SUITE (GEMINI POWERED)'.bold.cyan);
console.log('==========================================================='.cyan);

rl.question('🔑 Lütfen GOOGLE GEMINI API Key yapıştırıp ENTER\'a bas: '.yellow.bold, async (apiKey) => {
  
  const key = apiKey.trim();
  if (!key) {
    console.log('❌ HATA: API Key boş olamaz!'.red.bold);
    rl.close();
    process.exit(1);
  }

  const genAI = new GoogleGenerativeAI(key);
  // Tool kullanımı için 'gemini-pro' modelini çağırıyoruz
  const model = genAI.getGenerativeModel({ 
      model: "gemini-2.5-flash",
      tools: toolsDef
  });

  let passed = 0;
  let failed = 0;
  let totalLatency = 0;

  console.log('\n🚀 Testler Başlatılıyor...\n'.yellow);

  for (const test of TEST_CASES) {
    process.stdout.write(`TEST ${test.id}: ${test.description} ... `);
    
    const start = Date.now();
    
    try {
      const chat = model.startChat();
      const result = await chat.sendMessage(test.prompt);
      const response = await result.response;
      const functionCalls = response.functionCalls(); // Gemini'de tool çağrısı böyle alınır

      const duration = Date.now() - start;
      totalLatency += duration;

      let testResult = 'fail';
      let reason = '';

      // KONTROL MEKANİZMASI (ASSERTIONS)
      if (test.expected_type === 'tool_call') {
        if (functionCalls && functionCalls.length > 0) {
          const calledFunc = functionCalls[0].name;
          if (calledFunc === test.expected_value) {
            testResult = 'pass';
          } else {
            reason = `Yanlış Tool: ${calledFunc} (Beklenen: ${test.expected_value})`;
          }
        } else {
          reason = 'Tool çağrılmadı (Normal metin döndü)';
        }
      } else if (test.expected_type === 'text') {
        if (!functionCalls || functionCalls.length === 0) {
          testResult = 'pass';
        } else {
          reason = 'Gereksiz yere Tool çağırdı';
        }
      }

      // SONUCU YAZDIR
      if (testResult === 'pass') {
        console.log('PASS ✅'.green.bold + ` (${duration}ms)`.gray);
        passed++;
      } else {
        console.log('FAIL ❌'.red.bold + ` (${duration}ms)`.gray);
        console.log(`   Hata Detayı: ${reason}`.red);
        failed++;
      }

    } catch (error) {
      console.log('ERROR ⚠️'.yellow);
      console.log(`   Detay: ${error.message}`.gray);
      failed++;
    }
  }

  // RAPOR
  console.log('\n📊 TEST SONUÇ RAPORU'.bold.white);
  console.log('-------------------');
  console.log(`Toplam Test: ${TEST_CASES.length}`);
  console.log(`Başarılı:    ${passed}`.green);
  console.log(`Başarısız:   ${failed}`.red);
  
  if (failed === 0) {
    console.log('\n✨ MÜKEMMEL! Production için hazırız. 🚀\n'.bgGreen.black);
  } else {
    console.log('\n⚠️ Bazı testler geçemedi.\n'.bgRed.white);
  }

  rl.close();
});