const fetch = require('node-fetch');

const OLLAMA_API_URL = 'https://charming-elf-nearly.ngrok-free.app/api/generate';

async function testOllama() {
  try {
    const response = await fetch(OLLAMA_API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        model: 'llama3',
        prompt: 'Hello, how are you?',
        stream: false
      })
    });

    if (response.ok) {
      const data = await response.json();
      console.log('Ollama response:', data);
    } else {
      console.error('Error:', response.status, response.statusText);
    }
  } catch (error) {
    console.error('Connection failed:', error.message);
  }
}

testOllama();