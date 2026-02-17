const appShell = document.getElementById('appShell');
const sidebarToggle = document.getElementById('sidebarToggle');
const userInput = document.getElementById('userInput');
const sendBtn = document.getElementById('sendBtn');
const sendIcon = document.getElementById('sendIcon');
const welcomeScreen = document.getElementById('welcomeScreen');
const chatFlow = document.getElementById('chatFlow');
const settingsBtn = document.getElementById('settingsBtn');
const settingsDropdown = document.getElementById('settingsDropdown');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const attachBtn = document.getElementById('attachBtn');
const attachmentMenu = document.getElementById('attachmentMenu');
const chatSearch = document.getElementById('chatSearch');

let selectedImage = null;

// --- DYNAMIC GREETING & TAGLINE ---
const greetings = [
    "Good Morning", "Good Afternoon", "Good Evening",
    "Hello", "Hi There", "Welcome Back", "Greetings"
];

const taglines = [
    "How can Nexa assist your brilliance today?",
    "What would you like to create today?",
    "Ready to explore new possibilities?",
    "Let's make something amazing together",
    "Your AI companion is ready to help",
    "What's on your mind today?",
    "How may I illuminate your path today?"
];

function updateGreeting() {
    const hour = new Date().getHours();
    const greetingEl = document.getElementById('dynamicGreeting');
    const taglineEl = document.getElementById('dynamicTagline');
    
    if (!greetingEl) return;
    
    let greeting;
    if (hour >= 5 && hour < 12) {
        greeting = "Good Morning";
    } else if (hour >= 12 && hour < 17) {
        greeting = "Good Afternoon";
    } else if (hour >= 17 && hour < 22) {
        greeting = "Good Evening";
    } else {
        greeting = greetings[Math.floor(Math.random() * greetings.length)];
    }
    
    const username = greetingEl.textContent.split(' ').pop();
    greetingEl.textContent = `${greeting} ${username}`;
    
    const randomTagline = taglines[Math.floor(Math.random() * taglines.length)];
    taglineEl.textContent = randomTagline;
}

// --- SIDEBAR TOGGLE ---
sidebarToggle?.addEventListener('click', () => {
    appShell.classList.toggle('collapsed');
});

// --- SETTINGS DROPDOWN ---
settingsBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    settingsDropdown.classList.toggle('active');
});

document.addEventListener('click', () => settingsDropdown?.classList.remove('active'));

// --- ATTACHMENT MENU ---
attachBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    attachmentMenu.classList.toggle('active');
});

document.addEventListener('click', () => attachmentMenu?.classList.remove('active'));

// --- IMAGE UPLOAD HANDLING ---
imageInput?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        selectedImage = file;
        displayImagePreview(file);
    }
});

function displayImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.innerHTML = `
            <div class="preview-item">
                <img src="${e.target.result}" alt="Preview">
                <button class="remove-preview" onclick="removeImage()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
        `;
        imagePreview.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    selectedImage = null;
    imagePreview.innerHTML = '';
    imagePreview.style.display = 'none';
    imageInput.value = '';
}

function openGoogleDrive() {
    alert('Google Drive integration coming soon! Please use "Upload from PC" for now.');
}

// --- CHAT SEARCH ---
chatSearch?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const historyItems = document.querySelectorAll('.history-item');
    
    historyItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// --- DYNAMIC TEXTAREA ---
userInput?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 150) + 'px';
});

// --- SEND MESSAGE ---
async function sendMessage() {
    const text = userInput.value.trim();
    if (!text && !selectedImage) return;

    if (welcomeScreen) {
        welcomeScreen.style.opacity = '0';
        setTimeout(() => { 
            if(welcomeScreen) welcomeScreen.style.display = 'none'; 
        }, 500);
    }

    appendMessage('user', text, selectedImage);
    
    const formData = new FormData();
    formData.append('text', text || 'Describe this image');
    if (selectedImage) {
        formData.append('image', selectedImage);
    }

    userInput.value = '';
    userInput.dispatchEvent(new Event('input'));
    removeImage();

    // Show typing indicator
    const typingDiv = document.createElement('div');
    typingDiv.className = 'msg-row nexa-msg typing-indicator';
    typingDiv.innerHTML = `
        <div class="bubble">
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    chatFlow.appendChild(typingDiv);
    chatFlow.scrollTo({ top: chatFlow.scrollHeight, behavior: 'smooth' });

    try {
        const response = await fetch('/chat', { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        
        // Remove typing indicator
        typingDiv.remove();
        
        appendMessage('nexa', data.reply);
    } catch (e) {
        typingDiv.remove();
        appendMessage('nexa', "I encountered a synchronization error. Please check your connection.");
    }
}

function appendMessage(role, content, imageFile = null) {
    const div = document.createElement('div');
    div.className = `msg-row ${role}-msg`;
    
    let imageHTML = '';
    if (imageFile && role === 'user') {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgContainer = div.querySelector('.msg-image-container');
            if (imgContainer) {
                imgContainer.innerHTML = `<img src="${e.target.result}" alt="Uploaded">`;
            }
        };
        reader.readAsDataURL(imageFile);
        imageHTML = '<div class="msg-image-container"></div>';
    }
    
    div.innerHTML = `
        <div class="bubble">
            ${imageHTML}
            <div class="msg-text">${content}</div>
        </div>
    `;
    
    chatFlow.appendChild(div);
    chatFlow.scrollTo({ top: chatFlow.scrollHeight, behavior: 'smooth' });
}

sendBtn?.addEventListener('click', sendMessage);

userInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// --- QUICK SUGGESTIONS ---
function setQuick(text) {
    userInput.value = text;
    userInput.dispatchEvent(new Event('input'));
    userInput.focus();
}

// --- LOAD CHAT HISTORY ---
async function loadChat(chatId) {
    try {
        const response = await fetch(`/chat/history/${chatId}`);
        const data = await response.json();
        
        if (welcomeScreen) {
            welcomeScreen.style.display = 'none';
        }
        
        chatFlow.innerHTML = '';
        
        data.messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = `msg-row ${msg.role === 'user' ? 'user-msg' : 'nexa-msg'}`;
            
            let imageHTML = '';
            if (msg.image) {
                imageHTML = `<div class="msg-image-container"><img src="data:image/jpeg;base64,${msg.image}" alt="Image"></div>`;
            }
            
            div.innerHTML = `
                <div class="bubble">
                    ${imageHTML}
                    <div class="msg-text">${msg.content}</div>
                </div>
            `;
            
            chatFlow.appendChild(div);
        });
        
        chatFlow.scrollTo({ top: chatFlow.scrollHeight, behavior: 'smooth' });
    } catch (e) {
        console.error('Error loading chat:', e);
    }
}

// --- INITIALIZE ---
updateGreeting();

// Update greeting when page regains focus
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        updateGreeting();
    }
});