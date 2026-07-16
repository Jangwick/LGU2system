<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clinic AI Triage Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (max-width: 768px) {
            .chat-bubble-user { max-width: 85%; }
            .chat-bubble-bot { max-width: 90%; }
        }
        @media (min-width: 769px) {
            .chat-bubble-user { max-width: 70%; }
            .chat-bubble-bot { max-width: 75%; }
        }
        @media (min-width: 1024px) {
            .chat-bubble-user { max-width: 60%; }
            .chat-bubble-bot { max-width: 65%; }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse-border {
            animation: pulseBorder 2s infinite;
        }
        @keyframes pulseBorder {
            0%, 100% { border-color: rgb(59, 130, 246); }
            50% { border-color: rgb(16, 185, 129); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-green-50 text-gray-800 min-h-screen">
    <div class="w-full h-screen md:h-[90vh] md:max-w-4xl lg:max-w-6xl xl:max-w-7xl md:mx-auto md:my-4 bg-white md:rounded-2xl md:shadow-2xl border flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="px-4 md:px-8 py-3 md:py-6 border-b bg-blue-600 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-stethoscope text-lg md:text-2xl"></i>
                    <div>
                        <h1 class="text-lg md:text-2xl lg:text-3xl font-bold">AI Triage Assistant</h1>
                        <p class="text-xs md:text-sm lg:text-base opacity-90">Sequential Symptom Assessment & Care Guidance</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-75">Status:</div>
                    <div class="flex items-center text-sm">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                        <span id="connection-status">Online</span>
                    </div>
                    <button id="new-conversation-btn" class="mt-1 text-xs bg-blue-500 hover:bg-blue-400 px-2 py-1 rounded">
                        <i class="fas fa-plus mr-1"></i>New Chat
                    </button>
                </div>
            </div>
        </div>

        <!-- Medical Disclaimer -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 px-4 md:px-8 py-3">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3 text-sm md:text-base"></i>
                <div class="text-xs md:text-sm">
                    <p class="font-semibold text-yellow-800">Medical Disclaimer:</p>
                    <p class="text-yellow-700">This chatbot provides preliminary health guidance only. It does not replace professional medical diagnosis or treatment. Seek immediate medical attention for emergencies.</p>
                </div>
            </div>
        </div>

        <!-- Emergency Alert -->
        <div id="emergency-alert" class="bg-red-100 border-l-4 border-red-500 px-4 md:px-8 py-3 hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-start">
                    <i class="fas fa-ambulance text-red-600 mr-3 text-lg animate-pulse"></i>
                    <div>
                        <p class="font-bold text-red-800 text-sm md:text-base">⚠️ EMERGENCY DETECTED - Seek Immediate Medical Attention</p>
                        <p class="text-xs md:text-sm text-red-700">Based on your symptoms, please contact emergency services or visit the clinic immediately.</p>
                    </div>
                </div>
                <button id="call-clinic" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs md:text-sm font-semibold flex-shrink-0">
                    <i class="fas fa-phone mr-1"></i> Call Clinic
                </button>
            </div>
        </div>

        <!-- Assessment Progress -->
        <div id="assessment-progress" class="px-4 md:px-8 py-2 bg-blue-50 border-b hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                    <span class="text-sm font-medium text-blue-800">Assessment Progress:</span>
                    <span id="progress-status" class="text-sm text-blue-600">Gathering Information</span>
                </div>
                <div id="progress-indicator" class="flex items-center space-x-1">
                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                    <span class="text-xs text-blue-600">In Progress</span>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chat-container" class="flex-1 overflow-y-auto px-4 md:px-8 py-4 space-y-4 bg-gray-50">
            <!-- Welcome Message -->
            <div class="flex justify-start fade-in">
                <div class="flex items-start space-x-3 chat-bubble-bot">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-blue-500 to-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-white text-sm md:text-base"></i>
                    </div>
                    <div class="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border">
                        <p class="text-gray-800 text-sm md:text-base">👋 Hello! I'm your AI Triage Assistant. I'll guide you through a step-by-step assessment of your symptoms.</p>
                        <p class="text-xs md:text-sm text-gray-600 mt-2">I'll ask you specific questions to better understand your condition and provide appropriate care recommendations.</p>
                        <p class="text-xs md:text-sm text-blue-600 mt-2 font-medium">Please start by describing your current symptoms or health concerns.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Question Prompt -->
        <div id="next-question-prompt" class="px-4 md:px-8 py-3 bg-gradient-to-r from-blue-50 to-green-50 border-t border-b hidden">
            <div class="flex items-start space-x-3">
                <i class="fas fa-question-circle text-blue-600 mt-1 text-lg"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800 mb-2">Follow-up Question:</p>
                    <p id="next-question-text" class="text-sm md:text-base text-gray-700 bg-white rounded-lg px-3 py-2 border pulse-border"></p>
                    <button id="use-question-btn" class="mt-2 text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full">
                        <i class="fas fa-arrow-down mr-1"></i>Use This Question
                    </button>
                </div>
            </div>
        </div>

        <!-- Assessment Complete - Recommendations -->
        <div id="recommendations-panel" class="px-4 md:px-8 py-4 bg-green-50 border-t border-b hidden">
            <div class="flex items-start space-x-3">
                <i class="fas fa-check-circle text-green-600 text-xl mt-1"></i>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-green-800 mb-2">Assessment Complete</h3>
                    <p class="text-sm text-green-700 mb-3">Based on your symptoms, here are my recommendations:</p>
                    <div id="recommendations-list" class="space-y-2">
                        <!-- Recommendations will be inserted here -->
                    </div>
                    <button id="start-new-assessment" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-redo mr-2"></i>Start New Assessment
                    </button>
                </div>
            </div>
        </div>

        <!-- Triage Level -->
        <div id="triage-level" class="px-4 md:px-8 py-3 bg-white border-b hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <span class="text-xs md:text-sm font-medium text-gray-600">Current Assessment Level:</span>
                    <div id="triage-badge" class="px-3 py-1 rounded-full text-xs font-bold">
                        <!-- Triage level will be inserted here -->
                    </div>
                </div>
                <div id="triage-confidence" class="text-xs text-gray-500">
                    <!-- Confidence level will be shown here -->
                </div>
            </div>
        </div>

        <!-- Quick Symptoms (only shown at start) -->
        <div id="quick-symptoms" class="px-4 md:px-8 py-3 bg-gray-50 border-t">
            <p class="text-xs text-gray-600 mb-2">Quick symptom categories to get started:</p>
            <div class="flex flex-wrap gap-2">
                <button class="symptom-btn px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-full text-xs md:text-sm font-medium transition-colors" data-symptom="fever">🌡️ Fever</button>
                <button class="symptom-btn px-3 py-2 bg-red-100 hover:bg-red-200 text-red-800 rounded-full text-xs md:text-sm font-medium transition-colors" data-symptom="headache">🧠 Headache</button>
                <button class="symptom-btn px-3 py-2 bg-green-100 hover:bg-green-200 text-green-800 rounded-full text-xs md:text-sm font-medium transition-colors" data-symptom="stomach">🤢 Stomach Issues</button>
                <button class="symptom-btn px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-800 rounded-full text-xs md:text-sm font-medium transition-colors" data-symptom="respiratory">🫁 Breathing Issues</button>
                <button class="symptom-btn px-3 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-full text-xs md:text-sm font-medium transition-colors" data-symptom="injury">🩹 Injury/Pain</button>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="px-4 md:px-8 pb-2 hidden">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white text-sm md:text-base"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-sm px-4 py-2 shadow-sm border">
                    <div class="flex items-center space-x-1">
                        <span class="text-xs md:text-sm text-gray-600">AI is analyzing</span>
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t px-4 md:px-8 py-4 bg-white">
            <form id="chat-form" class="flex gap-3">
                <div class="flex-1">
                    <textarea id="message-input" rows="1" class="w-full resize-none border border-gray-300 rounded-xl px-4 py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Describe your symptoms in detail..." required></textarea>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 md:px-6 py-3 rounded-xl text-sm md:text-base font-semibold shadow-md transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-paper-plane"></i>
                    <span class="hidden sm:inline">Send</span>
                </button>
            </form>

            <!-- Emergency Button Disabled for now-->
            <div class="mt-3 text-center">
                <button id="emergency-btn" >
                    
                </button>
        
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentConversationId = null;
        let assessmentInProgress = false;
        let currentLanguage = 'en';
        
        // DOM elements
        const chatContainer = document.getElementById('chat-container');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const typingIndicator = document.getElementById('typing-indicator');
        const triageLevel = document.getElementById('triage-level');
        const triageBadge = document.getElementById('triage-badge');
        const triageConfidence = document.getElementById('triage-confidence');
        const emergencyAlert = document.getElementById('emergency-alert');
        const quickSymptoms = document.getElementById('quick-symptoms');
        const nextQuestionPrompt = document.getElementById('next-question-prompt');
        const nextQuestionText = document.getElementById('next-question-text');
        const useQuestionBtn = document.getElementById('use-question-btn');
        const recommendationsPanel = document.getElementById('recommendations-panel');
        const recommendationsList = document.getElementById('recommendations-list');
        const assessmentProgress = document.getElementById('assessment-progress');
        const progressStatus = document.getElementById('progress-status');
        const progressIndicator = document.getElementById('progress-indicator');
        const newConversationBtn = document.getElementById('new-conversation-btn');
        const startNewAssessmentBtn = document.getElementById('start-new-assessment');

        // Get CSRF token
        function getCSRFToken() {
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                return metaToken.getAttribute('content');
            }
            
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'XSRF-TOKEN') {
                    return decodeURIComponent(value);
                }
            }
            
            return '';
        }

        // Handle form submission
        chatForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;
            await sendMessage(message);
        });

        // Handle quick symptom buttons
        document.querySelectorAll('.symptom-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const symptom = this.dataset.symptom;
                const messages = {
                    'fever': 'I have a fever and feeling unwell',
                    'headache': 'I have a persistent headache', 
                    'stomach': 'I am experiencing stomach pain and nausea',
                    'respiratory': 'I am having trouble breathing',
                    'injury': 'I have an injury or experiencing pain'
                };
                messageInput.value = messages[symptom] || symptom;
                messageInput.focus();
            });
        });

        // Handle emergency button
        document.getElementById('emergency-btn').addEventListener('click', function() {
            showEmergencyAlert();
            sendMessage('This is an emergency situation - I need immediate help');
        });

        // Handle new conversation
        newConversationBtn.addEventListener('click', startNewConversation);
        startNewAssessmentBtn.addEventListener('click', startNewConversation);

        // Handle use question button
        useQuestionBtn.addEventListener('click', function() {
            const questionText = nextQuestionText.textContent;
            messageInput.value = questionText;
            hideNextQuestionPrompt();
            messageInput.focus();
        });

        async function startNewConversation() {
            try {
                const response = await fetch('/chatbot/new-conversation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    currentConversationId = data.conversation_id;
                }
            } catch (error) {
                console.warn('Failed to start new conversation:', error);
            }

            // Reset UI state
            resetChatInterface();
        }

        function resetChatInterface() {
            // Clear messages except welcome
            const messages = chatContainer.children;
            while (messages.length > 1) {
                messages[messages.length - 1].remove();
            }

            // Reset state
            assessmentInProgress = false;
            currentLanguage = 'en';

            // Hide all panels
            hideEmergencyAlert();
            hideNextQuestionPrompt();
            hideRecommendationsPanel();
            hideTriageLevel();
            hideAssessmentProgress();

            // Show quick symptoms
            showQuickSymptoms();

            // Reset input
            messageInput.value = '';
            messageInput.placeholder = 'Describe your symptoms in detail...';
        }

        async function sendMessage(message) {
            appendMessage('user', message);
            messageInput.value = '';
            scrollToBottom();
            hideQuickSymptoms();
            hideNextQuestionPrompt();
            showTypingIndicator();
            
            if (!assessmentInProgress) {
                showAssessmentProgress();
                assessmentInProgress = true;
            }

            try {
                const csrfToken = getCSRFToken();
                console.log('Sending message to:', '/chatbot/ask');

                const response = await fetch('/chatbot/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message })
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    } else if (response.status === 422) {
                        const errorData = await response.json();
                        throw new Error(`Validation error: ${JSON.stringify(errorData.errors || errorData.message)}`);
                    } else {
                        throw new Error(`Server error: ${response.status} ${response.statusText}`);
                    }
                }

                const data = await response.json();
                console.log('Received response:', data);
                
                // Store conversation ID
                if (data.conversation_id) {
                    currentConversationId = data.conversation_id;
                }

                // Store current language
                if (data.language) {
                    currentLanguage = data.language;
                }

                // Display AI response
                appendMessage('bot', data.reply, { language: data.language });
                
                // Update triage level
                if (data.triage) {
                    updateTriageLevel(data.triage);
                }

                // Handle emergency
                if (data.emergency) {
                    showEmergencyAlert();
                    updateProgressStatus('⚠️ Emergency Detected', 'text-red-600');
                }

                // Handle assessment completion
                if (data.assessment_complete) {
                    handleAssessmentComplete(data.recommendations || [], data.triage);
                } else if (data.next_question) {
                    // Show next question prompt
                    showNextQuestionPrompt(data.next_question);
                    updateProgressStatus('Gathering Information', 'text-blue-600');
                }

                // Update progress status
                if (data.triage && data.triage.level !== 'Assessing') {
                    updateProgressStatus(`Assessment: ${data.triage.level}`, getTriageColor(data.triage.level));
                }

            } catch (error) {
                console.error('Error:', error);
                let errorMessage = 'Sorry, there was an error processing your request.';
                
                if (error.message.includes('CSRF')) {
                    errorMessage += ' Please refresh the page and try again.';
                } else {
                    errorMessage += ' Please try again or contact support if the problem persists.';
                }
                
                appendMessage('bot', errorMessage);
                updateProgressStatus('Error Occurred', 'text-red-600');
            }

            hideTypingIndicator();
            scrollToBottom();
        }

        function appendMessage(sender, text, meta = {}) {
            const messageWrapper = document.createElement('div');
            messageWrapper.classList.add('flex', sender === 'user' ? 'justify-end' : 'justify-start', 'fade-in');

            if (sender === 'user') {
                const bubble = document.createElement('div');
                bubble.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-green-600', 'text-white', 'px-4', 'py-3', 'rounded-2xl', 'rounded-tr-sm', 'shadow-md', 'chat-bubble-user');
                bubble.innerHTML = formatMessage(text);
                messageWrapper.appendChild(bubble);
            } else {
                const avatarAndMessage = document.createElement('div');
                avatarAndMessage.classList.add('flex', 'items-start', 'space-x-3', 'chat-bubble-bot');
                
                const avatar = document.createElement('div');
                avatar.classList.add('w-8', 'h-8', 'md:w-10', 'md:h-10', 'bg-gradient-to-br', 'from-blue-500', 'to-green-500', 'rounded-full', 'flex', 'items-center', 'justify-center', 'flex-shrink-0');
                avatar.innerHTML = '<i class="fas fa-robot text-white text-sm md:text-base"></i>';
                
                const bubble = document.createElement('div');
                bubble.classList.add('px-4', 'py-3', 'rounded-2xl', 'rounded-tl-sm', 'shadow-sm', 'border', 'text-gray-800', 'text-sm', 'md:text-base');
                
                if (meta.language === 'tl') {
                    bubble.classList.add('bg-green-50', 'border-green-100');
                } else {
                    bubble.classList.add('bg-white', 'border-gray-100');
                }

                bubble.innerHTML = formatMessage(text);

                if (meta.language === 'tl') {
                    const badge = document.createElement('span');
                    badge.classList.add('inline-block','ml-2','text-xs','px-2','py-0.5','rounded-full','border','border-green-200','text-green-700','bg-green-50','align-middle');
                    badge.textContent = 'Tagalog';
                    bubble.appendChild(badge);
                }
                
                avatarAndMessage.appendChild(avatar);
                avatarAndMessage.appendChild(bubble);
                messageWrapper.appendChild(avatarAndMessage);
            }

            chatContainer.appendChild(messageWrapper);
        }

        function showNextQuestionPrompt(question) {
            nextQuestionText.textContent = question;
            nextQuestionPrompt.classList.remove('hidden');
        }

        function hideNextQuestionPrompt() {
            nextQuestionPrompt.classList.add('hidden');
        }

        function handleAssessmentComplete(recommendations, triage) {
            assessmentInProgress = false;
            hideNextQuestionPrompt();
            hideAssessmentProgress();
            
            // Update progress indicator to complete
            updateProgressStatus('✅ Assessment Complete', 'text-green-600');
            
            // Show recommendations if provided
            if (recommendations && recommendations.length > 0) {
                showRecommendationsPanel(recommendations);
            }
        }

        function showRecommendationsPanel(recommendations) {
            recommendationsList.innerHTML = '';
            
            recommendations.forEach((rec, index) => {
                const recItem = document.createElement('div');
                recItem.classList.add('flex', 'items-start', 'space-x-2', 'fade-in');
                recItem.style.animationDelay = `${index * 0.1}s`;
                
                const bullet = document.createElement('div');
                bullet.classList.add('w-2', 'h-2', 'bg-green-500', 'rounded-full', 'mt-2', 'flex-shrink-0');
                
                const text = document.createElement('p');
                text.classList.add('text-sm', 'text-gray-700');
                text.textContent = rec;
                
                recItem.appendChild(bullet);
                recItem.appendChild(text);
                recommendationsList.appendChild(recItem);
            });
            
            recommendationsPanel.classList.remove('hidden');
        }

        function hideRecommendationsPanel() {
            recommendationsPanel.classList.add('hidden');
        }

        function showAssessmentProgress() {
            assessmentProgress.classList.remove('hidden');
        }

        function hideAssessmentProgress() {
            assessmentProgress.classList.add('hidden');
        }

        function updateProgressStatus(status, colorClass = 'text-blue-600') {
            progressStatus.textContent = status;
            progressStatus.className = `text-sm ${colorClass}`;
        }

        function updateTriageLevel(triage) {
            const level = triage.level || 'Assessing';
            const confidence = triage.confidence || 0.5;
            const reason = triage.reason || 'Assessment in progress';
            
            const colors = getTriageColorClasses(level);
            const icons = {
                'Emergency': '🚨',
                'Urgent': '🟡',
                'Non-Urgent': '🟢',
                'Assessing': '🔍'
            };

            triageBadge.className = `px-3 py-1 rounded-full text-xs font-bold ${colors}`;
            triageBadge.textContent = `${icons[level] || icons['Assessing']} ${level}`;
            
            // Show confidence and reason
            const confidencePercent = Math.round(confidence * 100);
            triageConfidence.textContent = `Confidence: ${confidencePercent}% | ${reason}`;
            
            triageLevel.classList.remove('hidden');
        }

        function getTriageColor(level) {
            const colors = {
                'Emergency': 'text-red-600',
                'Urgent': 'text-yellow-600',
                'Non-Urgent': 'text-green-600',
                'Assessing': 'text-blue-600'
            };
            return colors[level] || colors['Assessing'];
        }

        function getTriageColorClasses(level) {
            const colors = {
                'Emergency': 'bg-red-200 text-red-900',
                'Urgent': 'bg-yellow-100 text-yellow-800',
                'Non-Urgent': 'bg-green-100 text-green-800',
                'Assessing': 'bg-blue-100 text-blue-800'
            };
            return colors[level] || colors['Assessing'];
        }

        function showEmergencyAlert() {
            emergencyAlert.classList.remove('hidden');
        }

        function hideEmergencyAlert() {
            emergencyAlert.classList.add('hidden');
        }

        function showQuickSymptoms() {
            quickSymptoms.style.display = 'block';
        }

        function hideQuickSymptoms() {
            quickSymptoms.style.display = 'none';
        }

        function hideTriageLevel() {
            triageLevel.classList.add('hidden');
        }

        function showTypingIndicator() {
            typingIndicator.classList.remove('hidden');
            scrollToBottom();
        }

        function hideTypingIndicator() {
            typingIndicator.classList.add('hidden');
        }

        function formatMessage(text) {
            return escapeHtml(text)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        function scrollToBottom() {
            setTimeout(() => {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }, 100);
        }

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Handle Enter key
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        // Handle call clinic button
        document.getElementById('call-clinic').addEventListener('click', function() {
            // You can customize this with actual clinic phone number
            if (confirm('This will attempt to call the clinic. Continue?')) {
                window.open('tel:+1234567890'); // Replace with actual clinic number
            }
        });

        // Initialize interface
        window.addEventListener('DOMContentLoaded', function() {
            console.log('Medical Chatbot Interface loaded');
            console.log('CSRF token available:', !!getCSRFToken());
            
            // Focus on message input
            messageInput.focus();
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && messageInput) {
                messageInput.focus();
            }
        });
    </script>
</body>
</html>