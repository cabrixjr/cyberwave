import os
import json
from datetime import datetime
from fastapi import FastAPI, Request, Form, UploadFile, File
from fastapi.responses import HTMLResponse, JSONResponse, RedirectResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from starlette.middleware.sessions import SessionMiddleware
from typing import Optional

# --- SIMPLE IN-MEMORY STORAGE (No Database Required) ---
users_db = {}  # {email: {username, password, is_premium}}
chats_db = {}  # {user_id: [{id, title, timestamp, messages}]}
current_user_id = 0

# --- APP CONFIG ---
app = FastAPI()
app.add_middleware(SessionMiddleware, secret_key="NEXA_DEMO_SECRET_KEY", session_cookie="nexa_session")
app.mount("/static", StaticFiles(directory="static"), name="static")
templates = Jinja2Templates(directory="templates")

# --- DEMO AI RESPONSES ---
demo_responses = [
    "That's an interesting question! In this demo mode, I'm using pre-written responses. Once you install Ollama with llama3, I'll be able to provide real AI-powered answers!",
    "I understand what you're asking. This is a demo version, so I can't provide real AI responses yet. But the UI and all features are fully working!",
    "Great question! Currently running in demo mode. When you add Ollama, I'll be able to have real conversations with you using advanced AI.",
    "I'm Nexa AI running in demo mode! All the beautiful UI features work perfectly. Just waiting for Ollama to unlock my full potential!",
    "That's a thoughtful query. In demo mode, I rotate through preset responses. With Ollama installed, I'll provide intelligent, contextual answers.",
]

demo_image_responses = [
    "I can see you've uploaded an image! In demo mode, I can't analyze it yet. Once Moondream is installed via Ollama, I'll be able to describe images in detail!",
    "Nice image! When the Moondream model is added, I'll be able to tell you all about what's in this picture.",
]

response_counter = 0

def get_demo_response(has_image=False):
    global response_counter
    if has_image:
        response = demo_image_responses[response_counter % len(demo_image_responses)]
    else:
        response = demo_responses[response_counter % len(demo_responses)]
    response_counter += 1
    return response

# --- UTILITY FUNCTIONS ---
def simple_hash(password):
    """Simple hash for demo (NOT secure for production)"""
    return str(hash(password))

def verify_hash(password, hashed):
    return str(hash(password)) == hashed

# --- ROUTES ---
@app.get("/", response_class=HTMLResponse)
async def home(request: Request):
    user_id = request.session.get("user_id")
    user = None
    chats = []
    
    if user_id and user_id in chats_db:
        user_email = None
        for email, data in users_db.items():
            if data.get('id') == user_id:
                user = {'username': data['username'], 'email': email, 'is_premium': data.get('is_premium', 0)}
                user_email = email
                break
        
        if user_id in chats_db:
            chats = sorted(chats_db[user_id], key=lambda x: x['timestamp'], reverse=True)[:20]
    
    return templates.TemplateResponse("index.html", {"request": request, "user": user, "chats": chats})

@app.post("/chat")
async def chat(
    request: Request, 
    text: str = Form(...), 
    image: Optional[UploadFile] = File(None)
):
    """Demo Chat Endpoint"""
    user_id = request.session.get("user_id")
    current_chat_id = request.session.get("current_chat_id")
    
    has_image = image is not None
    image_data = None
    
    # Handle image upload
    if image:
        import base64
        image_bytes = await image.read()
        image_data = base64.b64encode(image_bytes).decode('utf-8')
    
    # Create or get chat session
    if not current_chat_id and user_id:
        if user_id not in chats_db:
            chats_db[user_id] = []
        
        # Generate title from first message
        title = ' '.join(text.split()[:6])
        if len(text.split()) > 6:
            title += '...'
        
        chat_id = len(chats_db[user_id])
        new_chat = {
            'id': chat_id,
            'title': title.capitalize(),
            'timestamp': datetime.now().isoformat(),
            'messages': []
        }
        chats_db[user_id].append(new_chat)
        current_chat_id = chat_id
        request.session["current_chat_id"] = current_chat_id
    
    # Save user message
    if current_chat_id is not None and user_id in chats_db:
        user_msg = {
            'role': 'user',
            'content': text,
            'image': image_data,
            'timestamp': datetime.now().isoformat()
        }
        chats_db[user_id][current_chat_id]['messages'].append(user_msg)
    
    # Generate demo response
    reply = get_demo_response(has_image)
    
    # Save AI response
    if current_chat_id is not None and user_id in chats_db:
        ai_msg = {
            'role': 'assistant',
            'content': reply,
            'image': None,
            'timestamp': datetime.now().isoformat()
        }
        chats_db[user_id][current_chat_id]['messages'].append(ai_msg)
    
    return {"reply": reply, "image": image_data}

@app.get("/chat/history/{chat_id}")
async def get_chat_history(chat_id: int, request: Request):
    """Retrieve chat history"""
    user_id = request.session.get("user_id")
    
    if user_id and user_id in chats_db and chat_id < len(chats_db[user_id]):
        return {"messages": chats_db[user_id][chat_id]['messages']}
    
    return {"messages": []}

@app.get("/new-chat")
async def new_chat(request: Request):
    """Start new chat session"""
    request.session.pop("current_chat_id", None)
    return RedirectResponse(url="/", status_code=303)

# --- VOICE CHAT ROUTE ---
@app.get("/voice", response_class=HTMLResponse)
async def voice_page(request: Request):
    user_id = request.session.get("user_id")
    user = None
    
    if user_id:
        for email, data in users_db.items():
            if data.get('id') == user_id:
                user = {'username': data['username'], 'email': email}
                break
    
    return templates.TemplateResponse("voice.html", {"request": request, "user": user})

# --- SETTINGS ROUTE ---
@app.get("/settings", response_class=HTMLResponse)
async def settings_page(request: Request):
    user_id = request.session.get("user_id")
    if not user_id:
        return RedirectResponse(url="/auth", status_code=303)
    
    user = None
    for email, data in users_db.items():
        if data.get('id') == user_id:
            user = {'username': data['username'], 'email': email, 'is_premium': data.get('is_premium', 0)}
            break
    
    return templates.TemplateResponse("settings.html", {"request": request, "user": user})

@app.post("/settings/update")
async def update_settings(
    request: Request,
    username: str = Form(...),
    email: str = Form(...)
):
    user_id = request.session.get("user_id")
    if not user_id:
        return RedirectResponse(url="/auth", status_code=303)
    
    # Update user data
    old_email = None
    for email_key, data in users_db.items():
        if data.get('id') == user_id:
            old_email = email_key
            break
    
    if old_email and old_email in users_db:
        user_data = users_db.pop(old_email)
        user_data['username'] = username
        users_db[email] = user_data
        request.session["username"] = username
    
    return RedirectResponse(url="/settings?success=true", status_code=303)

# --- UPGRADE ROUTE ---
@app.get("/upgrade", response_class=HTMLResponse)
async def upgrade_page(request: Request):
    user_id = request.session.get("user_id")
    user = None
    
    if user_id:
        for email, data in users_db.items():
            if data.get('id') == user_id:
                user = {'username': data['username'], 'email': email}
                break
    
    return templates.TemplateResponse("upgrade.html", {"request": request, "user": user})

# --- AUTH ROUTES ---
@app.get("/auth", response_class=HTMLResponse)
async def auth_page(request: Request):
    return templates.TemplateResponse("auth.html", {"request": request})

@app.post("/register")
async def register(
    request: Request,
    username: str = Form(...),
    email: str = Form(...),
    password: str = Form(...)
):
    global current_user_id
    
    if email in users_db:
        return RedirectResponse(url="/auth?error=exists", status_code=303)
    
    current_user_id += 1
    users_db[email] = {
        'id': current_user_id,
        'username': username,
        'password': simple_hash(password),
        'is_premium': 0
    }
    chats_db[current_user_id] = []
    
    request.session["user_id"] = current_user_id
    request.session["username"] = username
    
    return RedirectResponse(url="/", status_code=303)

@app.post("/login")
async def login(
    request: Request,
    email: str = Form(...),
    password: str = Form(...)
):
    if email not in users_db:
        return RedirectResponse(url="/auth?error=invalid", status_code=303)
    
    user_data = users_db[email]
    if not verify_hash(password, user_data['password']):
        return RedirectResponse(url="/auth?error=invalid", status_code=303)
    
    request.session["user_id"] = user_data['id']
    request.session["username"] = user_data['username']
    
    return RedirectResponse(url="/", status_code=303)

@app.get("/logout")
async def logout(request: Request):
    request.session.clear()
    return RedirectResponse(url="/")

# --- STARTUP MESSAGE ---
@app.on_event("startup")
async def startup_event():
    print("\n" + "="*60)
    print("🚀 NEXA AI - DEMO MODE")
    print("="*60)
    print("✅ Server running successfully!")
    print("📝 Using in-memory storage (no database needed)")
    print("🤖 AI responses are demo mode (install Ollama for real AI)")
    print("🌐 Open: http://127.0.0.1:8000")
    print("="*60 + "\n")