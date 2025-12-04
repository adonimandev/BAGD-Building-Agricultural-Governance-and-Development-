import sys
import requests
import json

def main():
    if len(sys.argv) < 3:
        print("Usage: python ollama_chat.py <prompt> <model>")
        sys.exit(1)
    
    prompt = sys.argv[1]
    model = sys.argv[2]
    
    url = "https://charming-elf-nearly.ngrok-free.app/api/generate"
    
    payload = {
        "model": model,
        "prompt": prompt,
        "stream": False
    }
    
    try:
        response = requests.post(url, json=payload)
        
        if response.status_code == 200:
            data = response.json()
            raw_text = data.get("response", "")
            print(raw_text)
        else:
            print(f"Error: {response.status_code} - {response.text}")
            sys.exit(1)
            
    except Exception as e:
        print(f"Request failed: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()