from flask import Flask, render_template, request, jsonify, session
from flask_cors import CORS
import os
import datetime
import pytz
import requests
import re
import feedparser
import random
from flask_session import Session
from requests.adapters import HTTPAdapter
from requests.packages.urllib3.util.retry import Retry

app = Flask(__name__)
CORS(app, supports_credentials=True)
app.secret_key = os.urandom(24)  # Secure random key
app.config['SESSION_TYPE'] = 'filesystem'
app.config['SESSION_COOKIE_SAMESITE'] = 'None'
app.config['SESSION_COOKIE_SECURE'] = False  # Set to True if using HTTPS
Session(app)

def get_time(timezone="Africa/Nairobi"):
    try:
        tz = pytz.timezone(timezone)
        now = datetime.datetime.now(tz)
        return now.strftime("%I:%M %p %Z")
    except pytz.exceptions.UnknownTimeZoneError:
        return "Invalid timezone. Please specify a valid timezone (e.g., 'Africa/Nairobi')."

def get_date(timezone="Africa/Nairobi"):
    try:
        tz = pytz.timezone(timezone)
        today = datetime.datetime.now(tz).date()
        return today.strftime("%B %d, %Y")
    except pytz.exceptions.UnknownTimeZoneError:
        return "Invalid timezone. Please specify a valid timezone (e.g., 'Africa/Nairobi')."

def get_weather(city):
    try:
        city = re.sub(r'what is the weather in\s+', '', city, flags=re.IGNORECASE).strip()
        if not city:
            return "Please specify a city, e.g., 'what is the weather in London'."
        
        print(f"Debug: Fetching weather for city '{city}'")
        
        session = requests.Session()
        retries = Retry(total=3, backoff_factor=1, status_forcelist=[502, 503, 504])
        session.mount('https://', HTTPAdapter(max_retries=retries))
        
        geocoding_url = f"https://geocoding-api.open-meteo.com/v1/search?name={city}"
        response = session.get(geocoding_url, timeout=5)
        response.raise_for_status()
        data = response.json()
        
        if 'results' not in data or not data['results']:
            return f"I couldn't find '{city}'. Try a different spelling or a nearby city."
        
        latitude = data['results'][0]['latitude']
        longitude = data['results'][0]['longitude']
        
        weather_url = f"https://api.open-meteo.com/v1/forecast?latitude={latitude}&longitude={longitude}&current_weather=true&daily=temperature_2m_max,temperature_2m_min,sunrise,sunset&timezone=auto"
        response = session.get(weather_url, timeout=5)
        response.raise_for_status()
        weather_data = response.json()
        
        current = weather_data['current_weather']
        daily = weather_data['daily']
        
        temperature = current['temperature']
        windspeed = current['windspeed']
        max_temp = daily['temperature_2m_max'][0]
        min_temp = daily['temperature_2m_min'][0]
        sunrise = datetime.datetime.fromisoformat(daily['sunrise'][0]).strftime("%I:%M %p")
        sunset = datetime.datetime.fromisoformat(daily['sunset'][0]).strftime("%I:%M %p")
        
        return (f"The current temperature in {city} is {temperature}°C with a wind speed of {windspeed} km/h. "
                f"Today's high is {max_temp}°C, low is {min_temp}°C. Sunrise at {sunrise}, sunset at {sunset}.")
    
    except requests.exceptions.RequestException as e:
        print(f"Weather API Error: {e}")
        return f"I'm sorry, I am unable to fetch the weather data right now due to a network issue. Error: {str(e)}"
    except Exception as e:
        print(f"Weather General Error: {e}")
        return f"An unexpected error occurred while fetching weather: {str(e)}"

def get_news():
    try:
        print("Debug: Fetching news...")
        rss_url = "https://feeds.bbci.co.uk/news/rss.xml"
        feed = feedparser.parse(rss_url)
        
        if not feed.entries:
            return "No news available right now. Try again later."
        
        headlines = [entry.title for entry in feed.entries[:5]]
        print(f"Debug: Fetched {len(headlines)} headlines")
        
        return "Latest BBC News headlines: " + "; ".join(headlines) + "."
    except Exception as e:
        print(f"News Error: {e}")
        return f"Failed to fetch news: {str(e)}. Try asking about news via general query."

def get_joke():
    jokes = [
        "Why don't scientists trust atoms? Because they make up everything!",
        "What do you call a fake noodle? An impasta.",
        "Why did the bicycle fall over? Because it was two-tired!",
        "What gets wetter the more it dries? A towel.",
        "What did the big flower say to the little flower? 'Hi, bud!'",
        "Why did the scarecrow win an award? Because he was outstanding in his field!",
        "How does a penguin build its house? Igloos it together.",
        "Why don't eggs tell jokes? They'd crack each other up.",
        "What do you call cheese that isn't yours? Nacho cheese.",
        "Why couldn't the leopard play hide and seek? Because he was always spotted."
    ]
    return random.choice(jokes)

def calculate(expression):
    try:
        expr = re.sub(r'[^\d+\-*/(). ]', '', expression)
        result = eval(expr, {"__builtins__": {}}, {})
        return f"The result is {result}."
    except Exception as e:
        return f"Sorry, I couldn't calculate that: {str(e)}. Try something like 'calculate 2+2'."

def get_translation(text, target_lang="Spanish"):
    prompt = f"Translate '{text}' to {target_lang}."
    return get_gemini_response(prompt)

def get_gemini_response(prompt, history_context=""):
    api_key = "AIzaSyAFv_zalssre727dUMv3fn4NCBFh6VcVPQ"  # Replace with your actual Gemini API key
    print(f"Gemini API Key Loaded: {api_key is not None}")
    if not api_key:
        return "Gemini needs an API key—get one at https://ai.google.dev/! Try 'tell me a joke' for now."
    
    # Correct Gemini API endpoint
    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={api_key}"
    headers = {"Content-Type": "application/json"}
    full_prompt = f"{history_context}\nUser: {prompt}\nAssistant: "
    data = {
        "contents": [{
            "parts": [{"text": full_prompt}]
        }],
        "generationConfig": {
            "temperature": 0.7,
            "maxOutputTokens": 500
        }
    }
    
    try:
        session = requests.Session()
        retries = Retry(total=3, backoff_factor=1, status_forcelist=[502, 503, 504])
        session.mount('https://', HTTPAdapter(max_retries=retries))
        
        response = session.post(url, headers=headers, json=data, timeout=20)
        response.raise_for_status()
        result = response.json()
        
        if 'candidates' in result and result['candidates'] and 'content' in result['candidates'][0]:
            generated_text = result['candidates'][0]['content']['parts'][0]['text'].strip()
            return generated_text if generated_text else "No response generated."
        else:
            print(f"Gemini API Response: {result}")
            return "Unexpected API response format. Please try again."
    except requests.exceptions.Timeout:
        print("Gemini API Timeout: Request took too long.")
        return "Sorry, the Gemini API timed out. Please try again or check your network."
    except requests.exceptions.HTTPError as e:
        error_code = e.response.status_code
        if error_code == 429:
            print("Gemini API Error: Rate limit exceeded.")
            return "Rate limit exceeded. Please wait a minute and try again."
        elif error_code == 401:
            print("Gemini API Error: Invalid API key.")
            return "Invalid Gemini API key. Please check your key at https://ai.google.dev/."
        print(f"Gemini API HTTP Error: {e}")
        return f"Failed to get response from Gemini API: {str(e)}. Try a non-API command like 'tell me a joke'."
    except requests.exceptions.RequestException as e:
        print(f"Gemini API Request Error: {e}")
        return f"Failed to get response from Gemini API: {str(e)}. Try a non-API command like 'tell me a joke'."
    except Exception as e:
        print(f"Gemini General Error: {e}")
        return f"An unexpected error occurred with Gemini: {str(e)}"

@app.route('/')
def index():
    if 'chat_history' not in session:
        session['chat_history'] = []
    return render_template('index.html')

@app.route('/process_command', methods=['POST', 'OPTIONS'])
def process_command():
    # Handle preflight OPTIONS request
    if request.method == 'OPTIONS':
        response = jsonify({'status': 'ok'})
        response.headers.add('Access-Control-Allow-Origin', '*')
        response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
        response.headers.add('Access-Control-Allow-Methods', 'POST')
        return response
    
    data = request.get_json()
    command = data.get('command', '').lower()
    
    print(f"Received command: {command}")
    
    if not command:
        return jsonify({'response': "I didn't hear anything.", 'stop': False})
    
    history = session.get('chat_history', [])
    history_context = "\n".join([f"User: {h['user']}\nAssistant: {h['ai']}" for h in history[-5:]])
    history.append({"user": command})
    
    response = ""
    stop = False
    
    try:
        if "who created you" in command or "your creator" in command:
            response = "WILFRED CHARLES SENGATA a student of form five in Kibaha Secondary School."
        elif "what is the time" in command or "what time is it" in command:
            if " in " in command:
                try:
                    timezone = command.split(" in ")[-1].strip()
                    response = f"The current time in {timezone} is {get_time(timezone)}."
                except:
                    response = "Invalid timezone. Using EAT: " + f"The current time is {get_time()}."
            else:
                response = f"The current time is {get_time()}."
        elif "what is the date" in command or "what's today's date" in command:
            if " in " in command:
                try:
                    timezone = command.split(" in ")[-1].strip()
                    response = f"Today's date in {timezone} is {get_date(timezone)}."
                except:
                    response = "Invalid timezone. Using EAT: " + f"Today's date is {get_date()}."
            else:
                response = f"Today's date is {get_date()}."
        elif "what is the weather in" in command:
            city = command.split(" in ")[-1].strip()
            response = get_weather(city)
        elif "news" in command or "headlines" in command:
            response = get_news()
        elif "calculate" in command or "math" in command:
            expression = command.replace("calculate", "").replace("math", "").strip()
            response = calculate(expression)
        elif "flip a coin" in command:
            response = random.choice(["Heads!", "Tails!"])
        elif "roll dice" in command or "roll a die" in command:
            response = f"You rolled a {random.randint(1, 6)}."
        elif "translate" in command:
            parts = command.replace("translate", "").strip().split(" to ")
            text = parts[0].strip()
            target_lang = parts[1].strip() if len(parts) > 1 else "Spanish"
            response = get_translation(text, target_lang)
        elif "tell me a joke" in command or "joke" in command:
            response = get_joke()
        elif "stop" in command or "exit" in command or "bye" in command:
            response = "Goodbye! Have a great day."
            stop = True
            session.pop('chat_history', None)
        else:
            response = get_gemini_response(command, history_context)
        
        history[-1]["ai"] = response
        session['chat_history'] = history[-10:]
        
        print(f"Sending response: {response}")
        return jsonify({'response': response, 'stop': stop})
    
    except Exception as e:
        print(f"Process Command Error: {e}")
        return jsonify({'response': f"An error occurred: {str(e)}", 'stop': False})

if __name__ == '__main__':
    print("Starting Flask server on http://localhost:5000")
    print("CORS enabled for all origins")
    app.run(debug=True, host='0.0.0.0', port=5000)