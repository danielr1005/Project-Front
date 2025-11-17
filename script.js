function initTheme() {
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    updateThemeIcon(theme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
}
document.addEventListener("DOMContentLoaded", initTheme);

// ==================== CHAT EN TIEMPO REAL ====================
let chatPollingInterval = null;
let lastMessageId = 0;

function initChatRealTime(chatId) {
    if (!chatId) return;
    loadNewMessages(chatId);

    chatPollingInterval = setInterval(() => {
        loadNewMessages(chatId);
    }, 2000);

    window.addEventListener('beforeunload', () => {
        if (chatPollingInterval) clearInterval(chatPollingInterval);
    });
}

function loadNewMessages(chatId) {
    if (!chatId) return;

    fetch(`api/get_messages.php?chat_id=${chatId}&last_id=${lastMessageId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages?.length) {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    data.messages.forEach(msg => {
                        addMessageToChat(msg, chatMessages);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    scrollChatToBottom();
                }
            }
        })
        .catch(err => console.error('Error al cargar mensajes:', err));
}

// 🎯 FUNCIÓN CORREGIDA
function addMessageToChat(message, container) {
    if (document.getElementById(`message-${message.id}`)) return;

    const messageDiv = document.createElement('div');
    messageDiv.id = `message-${message.id}`;
    // Usamos el campo 'es_comprador' para determinar la clase
    messageDiv.className = `message ${message.es_comprador == 1 ? 'message-sent' : 'message-received'}`;

    const messageText = document.createElement('p');
    // Usamos innerHTML porque el PHP ya aplicó nl2br y sanitización
    messageText.innerHTML = message.mensaje.replace(/\n/g, '<br>');

    const messageTime = document.createElement('span');
    messageTime.className = 'message-time';
    
    // ✅ CLAVE: Si viene el campo tiempo_relativo (del envío AJAX), lo usamos directamente.
    if (message.tiempo_relativo) {
        messageTime.textContent = message.tiempo_relativo;
    } else {
        // Si no viene (del polling), usamos la función JS local.
        messageTime.textContent = formatMessageTime(message.fecha_formateada || message.fecha_registro);
    }

    messageDiv.appendChild(messageText);
    messageDiv.appendChild(messageTime);
    container.appendChild(messageDiv);
}

// ✅ Mostrar hora correcta (sin restar 5h)
function formatMessageTime(timestamp) {
    const date = new Date(timestamp); // viene ya con zona -05:00 (Bogotá)
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes} min`;
    if (minutes < 1440) return `Hace ${Math.floor(minutes / 60)} h`;

    return date.toLocaleString('es-CO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function updateNotifications(count, chats) {
    const countEl = document.getElementById('notificationCount');
    if (count > 0) countEl.classList.remove('hidden');
    else countEl.classList.add('hidden');
    countEl.textContent = count;

    const chatsList = document.getElementById('chatsList');
    chatsList.innerHTML = '';
    chats.forEach(chat => {
        const div = document.createElement('div');
        div.textContent = chat.nombre;
        chatsList.appendChild(div);
    });
}

// ==================== ENVÍO DE MENSAJES ====================
function scrollChatToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sendMessage(chatId, messageText, callback) {
    if (!messageText.trim() || !chatId) return;

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('mensaje', messageText);

    fetch('api/send_message.php', {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                console.error('Error al enviar mensaje: ' + (data.error || 'Error desconocido'));
                return;
            }

            const chatMessages = document.getElementById('chatMessages');
            // ✅ USAMOS data.message que ahora contiene el campo tiempo_relativo
            if (chatMessages && data.message) {
                addMessageToChat(data.message, chatMessages);
                lastMessageId = Math.max(lastMessageId, data.message.id);
                scrollChatToBottom();
            }

            if (callback) callback();
        })
        .catch(err => {
            console.error('Error al enviar mensaje:', err);
            console.error('Error al enviar mensaje. Por favor intenta de nuevo.');
        });
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', () => {
    initTheme();

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) themeToggle.addEventListener('click', toggleTheme);

    const chatId = window.chatId || getUrlParameter('id');
    if (chatId && document.getElementById('chatMessages')) {
        initChatRealTime(chatId);
    }

    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', e => {
            e.preventDefault();
            const textarea = document.getElementById('messageInput');
            if (textarea && textarea.value.trim()) {
                sendMessage(chatId, textarea.value);
                textarea.value = '';
            }
        });

        const textarea = document.getElementById('messageInput');
        if (textarea) {
            textarea.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
        }
    }

    scrollChatToBottom();
});

function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}