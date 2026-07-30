<!-- Chatbot Widget -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-[100001] font-sans">
    <!-- Chat Bubble (Toggle Button) -->
    <button id="chatbot-toggle" class="bg-red-600 hover:bg-red-700 text-white rounded-full w-14 h-14 shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 group relative overflow-hidden">
        <div class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border-2 border-white"></span>
        </div>
        <i id="chatbot-open-icon" class="bi bi-chat-text-fill text-2xl absolute inset-0 flex items-center justify-center transition-opacity duration-200 opacity-100"></i>
        <i id="chatbot-close-icon" class="bi bi-x-lg text-2xl absolute inset-0 flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bottom-20 right-0 w-[350px] sm:w-[400px] h-[500px] bg-white dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700/50 flex flex-col overflow-hidden transition-all duration-300 origin-bottom-right scale-0 opacity-0">
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
            <button type="button" onclick="toggleChat()" class="text-white/80 hover:text-white transition p-1.5 hover:bg-white/10 rounded-lg bg-transparent border-none">
                <i class="bi bi-dash-lg text-xl leading-none"></i>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-white dark:bg-gray-900/50">
            <!-- Welcome Message -->
            <div class="flex justify-start">
                <div class="bg-gray-50 dark:bg-gray-700 shadow-sm border border-gray-200 dark:border-gray-600 rounded-2xl rounded-tl-none p-3 max-w-[85%] text-sm text-gray-800 dark:text-gray-200">
                    Hello! I'm your LRMS Assistant. How can I help you navigate the system today?
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div id="chatbot-suggestions" class="p-2 flex gap-2 overflow-x-auto whitespace-nowrap hidden border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800/50">
            <?php 
            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
            
            // Define suggestions per role
            $roleSuggestions = [
                'viewer' => [
                    ['text' => 'Search records', 'query' => 'How can I search for documents?'],
                    ['text' => 'System features', 'query' => 'What are the main features of LRMS?'],
                    ['text' => 'User roles', 'query' => 'What can I do with a viewer account?']
                ],
                'staff' => [
                    ['text' => 'How to upload?', 'query' => 'How can I upload a new document?'],
                    ['text' => 'Edit document', 'query' => 'How to edit an existing document?'],
                    ['text' => 'Search records', 'query' => 'How to use advanced filters?']
                ],
                'officer' => [
                    ['text' => 'How to approve?', 'query' => 'How do I approve pending documents?'],
                    ['text' => 'Reports', 'query' => 'How can I generate analytics reports?'],
                    ['text' => 'Advanced search', 'query' => 'How to find specific legislative records?']
                ],
                'administrator' => [
                    ['text' => 'User management', 'query' => 'How to manage system users and roles?'],
                    ['text' => 'Audit logs', 'query' => 'Where can I view all system activity logs?'],
                    ['text' => 'System status', 'query' => 'What is the current status of the server?']
                ]
            ];

            // Default to viewer if role not found
            $suggestionsList = $roleSuggestions[$userRole] ?? $roleSuggestions['viewer'];

            foreach ($suggestionsList as $sugg): 
            ?>
                <button type="button" onclick="sendSuggestion('<?php echo htmlspecialchars($sugg['query'], ENT_QUOTES, 'UTF-8'); ?>')" class="text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 hover:border-red-200 dark:hover:border-red-700 transition">
                    <?php echo e($sugg['text']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <form id="chatbot-form" class="flex items-center space-x-2">
                <input type="text" id="chatbot-input" placeholder="Type your message..." class="flex-1 bg-gray-100 dark:bg-gray-700 border-none rounded-xl px-4 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-red-500 transition-all outline-none">
                <button type="submit" id="chatbot-send" class="bg-red-600 text-white rounded-xl p-2 hover:bg-red-700 transition active:scale-95 disabled:opacity-50">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {    const baseUrl = '<?php echo BASE_URL; ?>';    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chatbot-window');
    const chatForm = document.getElementById('chatbot-form');
    const chatInput = document.getElementById('chatbot-input');
    const chatMessages = document.getElementById('chatbot-messages');
    const suggestions = document.getElementById('chatbot-suggestions');
    const openIcon = document.getElementById('chatbot-open-icon');
    const closeIcon = document.getElementById('chatbot-close-icon');
    const chatbotContainer = document.getElementById('chatbot-container');

    let chatHistory = [];
    let isOpen = false;

    // Client-side HTML encoder. Applied to untrusted text (AI responses,
    // error strings) BEFORE markdown processing so raw HTML tags are
    // neutralised while Markdown syntax (* [ ] ( )) still works correctly.
    function sanitizeForHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Toggle Chat Window
    window.toggleChat = function() {
        isOpen = !isOpen;
        if (isOpen) {
            openIcon.classList.remove('opacity-100');
            openIcon.classList.add('opacity-0');
            closeIcon.classList.remove('opacity-0');
            closeIcon.classList.add('opacity-100');
            chatWindow.classList.remove('hidden');
            setTimeout(() => {
                chatWindow.classList.remove('scale-0', 'opacity-0');
                chatWindow.classList.add('scale-100', 'opacity-100');
            }, 10);
            suggestions.classList.remove('hidden');
            // Save state
            sessionStorage.setItem('chatbot_open', 'true');
        } else {
            closeIcon.classList.remove('opacity-100');
            closeIcon.classList.add('opacity-0');
            openIcon.classList.remove('opacity-0');
            openIcon.classList.add('opacity-100');
            chatWindow.classList.remove('scale-100', 'opacity-100');
            chatWindow.classList.add('scale-0', 'opacity-0');
            setTimeout(() => chatWindow.classList.add('hidden'), 300);
            // Save state
            sessionStorage.setItem('chatbot_open', 'false');
        }
    };

    // Restore state and history on page load
    window.addEventListener('DOMContentLoaded', () => {
        const savedHistory = sessionStorage.getItem('chatbot_history');
        if (savedHistory) {
            chatHistory = JSON.parse(savedHistory);
            chatHistory.forEach(msg => {
                appendMessage(msg.role === 'user' ? 'user' : 'bot', msg.text, false);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        const wasOpen = sessionStorage.getItem('chatbot_open');
        if (wasOpen === 'true') {
            isOpen = false; // set to false so toggleChat makes it true
            toggleChat();
        }

        // Re-evaluate visibility after the DOM is fully loaded to avoid
        // being incorrectly hidden by invisible fixed overlays on mobile refresh.
        updateChatbotVisibility();
    });

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
                <div class="bg-gray-50 dark:bg-gray-700 shadow-sm border border-gray-200 dark:border-gray-600 rounded-2xl rounded-tl-none p-3 max-w-[85%] text-sm text-gray-500 dark:text-gray-400 italic">
                    <span class="flex items-center space-x-1">
                        <span class="w-1 h-1 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce"></span>
                        <span class="w-1 h-1 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-1 h-1 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
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
                
                // Save to session storage
                sessionStorage.setItem('chatbot_history', JSON.stringify(chatHistory));
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

    function appendMessage(role, text, shouldScroll = true) {
        const isBot = role === 'bot';
        
        // HTML-encode first, then apply safe Markdown transformations.
        // Encoding before the regex pass neutralises any raw HTML/script tags
        // in AI responses or error strings before they reach the DOM.
        // The Markdown-relevant characters (* [ ] ( ) \n) are not HTML-special
        // so the patterns still match on encoded text.
        let formattedText = sanitizeForHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            // Restrict to http/https URLs only to block javascript: URIs.
            .replace(/\[(.*?)\]\((https?:\/\/[^)]+)\)/g, (match, title, url) => {
                const fullUrl = url.startsWith('http') ? url : `${baseUrl}/${url.replace(/^\//, '')}`;
                return `<a href="${sanitizeForHtml(fullUrl)}" class="text-red-600 dark:text-red-400 font-semibold underline hover:bg-red-50 dark:hover:bg-red-900/30">${title}</a>`;
            })
            .replace(/\n/g, '<br>');

        const html = `
            <div class="flex ${isBot ? 'justify-start' : 'justify-end'}">
                <div class="${isBot ? 'bg-gray-50 dark:bg-gray-700 shadow-sm border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-tl-none' : 'bg-red-600 text-white rounded-tr-none'} rounded-2xl p-3 max-w-[85%] text-sm">
                    ${formattedText}
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', html);
        if (shouldScroll) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    window.sendSuggestion = function(text) {
        sendMessage(text);
    };

    function updateChatbotVisibility() {
        if (!chatbotContainer) return;
        const modals = document.querySelectorAll('div.fixed.inset-0');
        let anyOpen = false;
        modals.forEach(modal => {
            if (modal === chatbotContainer) return;
            const style = window.getComputedStyle(modal);
            if (style.display === 'none' || style.visibility === 'hidden') return;
            if (parseFloat(style.opacity) < 0.01) return;
            anyOpen = true;
        });
        if (anyOpen) chatbotContainer.classList.add('hidden');
        else chatbotContainer.classList.remove('hidden');
    }

    document.querySelectorAll('div.fixed.inset-0').forEach(modal => {
        const chatbotObserver = new MutationObserver(updateChatbotVisibility);
        chatbotObserver.observe(modal, { attributes: true, attributeFilter: ['class'] });
    });
    updateChatbotVisibility();

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(chatInput.value);
    });
})();
</script>
