<!-- Chatbot Widget -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-[100] font-sans">
    <!-- Chat Bubble (Toggle Button) -->
    <button id="chatbot-toggle" class="bg-red-600 hover:bg-red-700 text-white rounded-full w-14 h-14 shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 group relative">
        <div class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border-2 border-white"></span>
        </div>
        <i class="bi bi-chat-text-fill text-2xl group-hover:hidden"></i>
        <i class="bi bi-x-lg text-2xl hidden group-hover:block"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bottom-20 right-0 w-[350px] sm:w-[400px] h-[500px] bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 flex flex-col overflow-hidden transition-all duration-300 origin-bottom-right scale-0 opacity-0">
        <!-- Header -->
        <div class="bg-red-600 p-4 text-white flex items-center justify-between shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <i class="bi bi-robot text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">LRMS Assistant</h3>
                    <div class="flex items-center text-[10px] text-red-100 italic">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                        Powered by AI
                    </div>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-white/80 hover:text-white transition">
                <i class="bi bi-dash-lg text-xl"></i>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
            <!-- Welcome Message -->
            <div class="flex justify-start">
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl rounded-tl-none p-3 max-w-[85%] text-sm text-gray-800">
                    Hello! I'm your LRMS Assistant. How can I help you navigate the system today?
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div id="chatbot-suggestions" class="p-2 flex gap-2 overflow-x-auto whitespace-nowrap hidden border-t border-gray-100 bg-white/50">
            <button onclick="sendSuggestion('How to upload a document?')" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-full hover:bg-red-50 hover:border-red-200 transition">How to upload?</button>
            <button onclick="sendSuggestion('What are the user roles?')" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-full hover:bg-red-50 hover:border-red-200 transition">User Roles</button>
            <button onclick="sendSuggestion('Is there a mobile app?')" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-full hover:bg-red-50 hover:border-red-200 transition">Mobile App</button>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-100">
            <form id="chatbot-form" class="flex items-center space-x-2">
                <input type="text" id="chatbot-input" placeholder="Type your message..." class="flex-1 bg-gray-100 border-none rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-red-500 transition-all outline-none">
                <button type="submit" id="chatbot-send" class="bg-red-600 text-white rounded-xl p-2 hover:bg-red-700 transition active:scale-95 disabled:opacity-50">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chatbot-window');
    const chatForm = document.getElementById('chatbot-form');
    const chatInput = document.getElementById('chatbot-input');
    const chatMessages = document.getElementById('chatbot-messages');
    const suggestions = document.getElementById('chatbot-suggestions');
    
    let chatHistory = [];
    let isOpen = false;

    // Toggle Chat Window
    window.toggleChat = function() {
        isOpen = !isOpen;
        if (isOpen) {
            chatWindow.classList.remove('hidden');
            setTimeout(() => {
                chatWindow.classList.remove('scale-0', 'opacity-0');
                chatWindow.classList.add('scale-100', 'opacity-100');
            }, 10);
            suggestions.classList.remove('hidden');
        } else {
            chatWindow.classList.remove('scale-100', 'opacity-100');
            chatWindow.classList.add('scale-0', 'opacity-0');
            setTimeout(() => chatWindow.classList.add('hidden'), 300);
        }
    };

    toggleBtn.addEventListener('click', toggleChat);

    // Send Message
    async function sendMessage(text) {
        if (!text.trim()) return;

        // Add user message to UI
        appendMessage('user', text);
        chatInput.value = '';
        
        // Disable input
        const sendBtn = document.getElementById('chatbot-send');
        sendBtn.disabled = true;
        chatInput.disabled = true;

        // Typing indicator
        const typingId = 'typing-' + Date.now();
        const typingHtml = `
            <div id="${typingId}" class="flex justify-start">
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl rounded-tl-none p-3 max-w-[85%] text-sm text-gray-500 italic">
                    <span class="flex items-center space-x-1">
                        <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce"></span>
                        <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                    </span>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', typingHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/modules/chatbot/api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    history: chatHistory
                })
            });

            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error("Non-JSON response:", responseText);
                document.getElementById(typingId).remove();
                appendMessage('bot', 'Error: The server returned an invalid response. Check console for details.');
                return;
            }
            
            // Remove typing indicator
            document.getElementById(typingId).remove();

            if (result.success) {
                appendMessage('bot', result.answer);
                chatHistory.push({ role: 'user', text: text });
                chatHistory.push({ role: 'model', text: result.answer });
                
                // Keep history manageable
                if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);
            } else {
                let errorMsg = result.error;
                if (result.details && result.details.error && result.details.error.message) {
                    errorMsg += ': ' + result.details.error.message;
                }
                appendMessage('bot', 'Sorry, I encountered an error: ' + errorMsg);
            }
        } catch (error) {
            console.error("Chat Error:", error);
            document.getElementById(typingId).remove();
            appendMessage('bot', 'Failed to connect to the assistant. Error: ' + error.message);
        } finally {
            sendBtn.disabled = false;
            chatInput.disabled = false;
            chatInput.focus();
        }
    }

    function appendMessage(role, text) {
        const isBot = role === 'bot';
        const html = `
            <div class="flex ${isBot ? 'justify-start' : 'justify-end'}">
                <div class="${isBot ? 'bg-white shadow-sm border border-gray-100 rounded-tl-none' : 'bg-red-600 text-white rounded-tr-none'} rounded-2xl p-3 max-w-[85%] text-sm">
                    ${text.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', html);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    window.sendSuggestion = function(text) {
        sendMessage(text);
    };

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(chatInput.value);
    });
})();
</script>
