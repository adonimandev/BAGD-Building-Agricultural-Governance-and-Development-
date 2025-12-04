// server.js
require('dotenv').config();
const express = require('express');
const { spawn } = require('child_process');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Store chats in memory
let chats = [];
let chatId = 1;

// Chat endpoint - sends prompt to Python script
app.post('/api/chat', async (req, res) => {
  try {
    const { prompt, model = 'llama3' } = req.body;
    
    if (!prompt) {
      return res.status(400).json({ error: 'Prompt is required' });
    }

    // Spawn Python process to handle the Ollama request
    const pythonProcess = spawn('python', ['ollama_chat.py', prompt, model]);
    
    let responseData = '';
    let errorData = '';

    // Collect data from Python script
    pythonProcess.stdout.on('data', (data) => {
      responseData += data.toString();
    });

    pythonProcess.stderr.on('data', (data) => {
      errorData += data.toString();
    });

    // Handle process completion
    pythonProcess.on('close', (code) => {
      if (code !== 0) {
        console.error('Python script error:', errorData);
        return res.status(500).json({ 
          error: 'Python script failed', 
          details: errorData 
        });
      }

      try {
        // Parse the response from Python
        const reply = responseData.trim();
        
        // Store chat
        const chat = {
          id: chatId++,
          prompt,
          reply,
          model,
          created_at: new Date().toISOString()
        };
        
        chats.push(chat);
        res.json(chat);
      } catch (error) {
        console.error('Error parsing Python response:', error);
        res.status(500).json({ error: 'Failed to parse response' });
      }
    });

  } catch (error) {
    console.error('Error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Get all chats
app.get('/api/chats', (req, res) => {
  res.json(chats);
});

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK' });
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});