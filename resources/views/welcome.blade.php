<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Kayan Group AI') }} - Your Intelligent Assistant</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'display': ['Space Grotesk', 'system-ui', 'sans-serif']
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'shimmer': 'shimmer 2s linear infinite',
                        'typing': 'typing 1.5s ease-in-out infinite',
                        'slideUp': 'slideUp 0.3s ease-out',
                        'scaleIn': 'scaleIn 0.2s ease-out',
                        'fadeIn': 'fadeIn 0.5s ease-out',
                        'bounce-slow': 'bounce 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s ease-in-out infinite'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(139, 92, 246, 0.3)' },
                            '100%': { boxShadow: '0 0 30px rgba(139, 92, 246, 0.6)' }
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-1000px 0' },
                            '100%': { backgroundPosition: '1000px 0' }
                        },
                        typing: {
                            '0%, 60%': { opacity: '1' },
                            '30%': { opacity: '0.5' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, 
                #0f0f23 0%, 
                #1a1a2e 25%, 
                #16213e 50%, 
                #0f0f23 75%, 
                #1a1a2e 100%
            );
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .glass-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }
        
        .glass-morphism {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(118, 75, 162, 0.3);
        }
        
        .chat-bubble {
            position: relative;
            animation: slideUp 0.6s ease-out;
        }
        
        .chat-bubble::before {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 20px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid rgba(255, 255, 255, 0.1);
        }
        
        .typing-indicator {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }
        
        .typing-indicator span {
            width: 4px;
            height: 4px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s ease-in-out infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        .ai-brain {
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb);
            background-size: 300% 300%;
            animation: gradientShift 3s ease infinite;
        }
        
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #667eea;
            border-radius: 50%;
            opacity: 0.6;
            animation: float 8s ease-in-out infinite;
        }
        

    </style>
</head>
<body class="min-h-screen overflow-x-hidden">
    <!-- Animated Background Particles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="particle" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="top: 60%; left: 80%; animation-delay: 2s;"></div>
        <div class="particle" style="top: 80%; left: 20%; animation-delay: 4s;"></div>
        <div class="particle" style="top: 30%; left: 70%; animation-delay: 6s;"></div>
        <div class="particle" style="top: 10%; left: 90%; animation-delay: 1s;"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-6xl mx-auto w-full">
            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left Column - Hero Content -->
                <div class="space-y-8 animate-fadeIn">
                    <!-- AI Brain Icon -->
                    <div class="flex justify-center lg:justify-start">
                        <div class="w-20 h-20 ai-brain rounded-2xl flex items-center justify-center animate-pulse-slow">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Main Heading -->
                    <div class="text-center lg:text-left">
                        <h1 class="text-5xl lg:text-6xl font-display font-bold text-white mb-6 leading-tight">
                            Welcome to
                            <span class="bg-gradient-to-r from-violet-400 via-purple-400 to-indigo-400 bg-clip-text text-transparent">
                                {{ config('app.name', 'Kayan AI') }}
                            </span>
                        </h1>
                        <p class="text-xl text-white/80 mb-8 leading-relaxed">
                            Experience the future of intelligent conversation. Our advanced AI assistant is here to help you achieve more, learn faster, and explore new possibilities.
                        </p>
                    </div>

                    <!-- Feature Pills -->
                    <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                        <span class="glass-morphism px-4 py-2 rounded-full text-white/90 text-sm font-medium">
                            🤖 Advanced AI
                        </span>
                        <span class="glass-morphism px-4 py-2 rounded-full text-white/90 text-sm font-medium">
                            ⚡ Real-time Responses
                        </span>
                        <span class="glass-morphism px-4 py-2 rounded-full text-white/90 text-sm font-medium">
                            🌍 Multi-language
                        </span>
                        <span class="glass-morphism px-4 py-2 rounded-full text-white/90 text-sm font-medium">
                            🔒 Secure & Private
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    @if (Route::has('login'))
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            @auth
                                @if(Auth::user()->hasRole('Admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="btn-primary px-8 py-4 rounded-xl text-white font-semibold text-lg hover:scale-105 transition-all duration-300 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                                        </svg>
                                        Dashboard
                                    </a>
                                @endif
                                <a href="{{ route('chat') }}" class="btn-primary px-8 py-4 rounded-xl text-white font-semibold text-lg hover:scale-105 transition-all duration-300 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Start Chatting
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-primary px-8 py-4 rounded-xl text-white font-semibold text-lg hover:scale-105 transition-all duration-300 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Login
                                </a>
                                
                                <!-- Guest Chat Button -->
                                <button onclick="openGuestChat()" class="glass-card px-8 py-4 rounded-xl text-white font-semibold text-lg hover:scale-105 transition-all duration-300 flex items-center justify-center border border-violet-400/30 hover:border-violet-400/50">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Try Kayan AI 
                                </button>
                                
                            @endauth
                        </div>
                    @endif
                </div>

                <!-- Right Column - Chat Demo -->
                <div class="space-y-6 animate-scaleIn">
                    <!-- Chat Interface Demo -->
                    <div class="glass-card rounded-2xl p-6 max-w-md mx-auto">
                        <div class="flex items-center mb-6">
                            <div class="w-3 h-3 bg-red-400 rounded-full mr-2"></div>
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></div>
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                            <span class="text-white/70 text-sm ml-auto">Kayan AI Chat</span>
                        </div>
                        
                        <!-- Chat Messages -->
                        <div class="space-y-4 mb-4">
                            <!-- User message -->
                            <div class="flex justify-end">
                                <div class="bg-gradient-to-r from-violet-500 to-purple-500 rounded-2xl rounded-br-sm px-4 py-3 max-w-xs">
                                    <p class="text-white text-sm">Hello! How can you help me today?</p>
                                </div>
                            </div>
                            
                            <!-- AI response -->
                            <div class="chat-bubble glass-morphism rounded-2xl rounded-bl-sm px-4 py-3 max-w-xs">
                                <p class="text-white/90 text-sm mb-2">
                                    Hello! I'm your AI assistant. I can help you with:
                                </p>
                                <ul class="text-white/80 text-sm space-y-1">
                                    <li>• Answering questions</li>
                                    <li>• Creative writing</li>
                                    <li>• Problem solving</li>
                                    <li>• And much more!</li>
                                </ul>
                            </div>
                            
                            <!-- Typing indicator -->
                            <div class="glass-morphism rounded-2xl rounded-bl-sm px-4 py-3 w-16">
                                <div class="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat input -->
                        <div class="flex items-center space-x-2 p-3 glass-morphism rounded-xl">
                            <input type="text" placeholder="Type your message..." 
                                   class="flex-1 bg-transparent text-white placeholder-white/50 outline-none text-sm"
                                   readonly>
                            <button class="p-2 bg-gradient-to-r from-violet-500 to-purple-500 rounded-lg hover:scale-105 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Feature Cards -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="glass-card rounded-xl p-4 text-center animate-float">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-violet-400 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <p class="text-white/90 text-sm font-medium">Lightning Fast</p>
                        </div>
                        
                        <div class="glass-card rounded-xl p-4 text-center animate-float" style="animation-delay: 1s;">
                            <div class="w-8 h-8 bg-gradient-to-r from-emerald-400 to-cyan-400 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <p class="text-white/90 text-sm font-medium">Secure</p>
                        </div>
                    </div>
                </div>
            </div>
            

            
            <!-- Footer -->
            <div class="mt-12 text-center">
                <p class="text-white/50 text-sm">
                    Powered by {{ config('app.name') }} • 
                    Version 1.0 • 
                    Built with ❤️ for the future
                </p>
            </div>
        </div>
    </div>

    <!-- Guest Chat Modal -->
    <div id="guestChatModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-card rounded-2xl max-w-2xl w-full max-h-[80vh] flex flex-col overflow-hidden">
            <!-- Chat Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 ai-brain rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">Chat with Kayan AI</h3>
                        <p class="text-white/60 text-sm" id="sessionInfo">Loading session...</p>
                    </div>
                </div>
                <button onclick="closeGuestChat()" class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Chat Messages -->
            <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Welcome message -->
                <div class="glass-morphism rounded-2xl rounded-bl-sm px-4 py-3 max-w-xs">
                    <p class="text-white/90 text-sm">
                        👋 Hello! I'm Kayan AI. I can help you learn about our company and products. What would you like to know?
                    </p>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-6 border-t border-white/10">
                <div class="flex items-center space-x-3 p-3 glass-morphism rounded-xl">
                    <input type="text" id="guestMessageInput" placeholder="Ask me about Kayan Group products..." 
                           class="flex-1 bg-transparent text-white placeholder-white/50 outline-none"
                           onkeypress="handleGuestInputKeypress(event)"
                           maxlength="1000">
                    <button id="guestSendButton" onclick="sendGuestMessage()" 
                            class="p-2 bg-gradient-to-r from-violet-500 to-purple-500 rounded-lg hover:scale-105 transition-transform">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js for interactions -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Guest Chat JavaScript -->
    <script>
        let guestSession = null;
        let isGuestChatLoading = false;

        // Open guest chat modal
        function openGuestChat() {
            document.getElementById('guestChatModal').classList.remove('hidden');
            loadGuestSession();
            document.getElementById('guestMessageInput').focus();
        }

        // Close guest chat modal
        function closeGuestChat() {
            document.getElementById('guestChatModal').classList.add('hidden');
        }

        // Load guest session info
        async function loadGuestSession() {
            try {
                const response = await fetch('/Kayan-AI-Chatbot/Kayan-Group/public/guest/session', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    guestSession = data.data;
                    updateSessionInfo();
                } else {
                    console.error('Failed to load guest session');
                    document.getElementById('sessionInfo').textContent = 'Session unavailable';
                }
            } catch (error) {
                console.error('Error loading guest session:', error);
                document.getElementById('sessionInfo').textContent = 'Connection error';
            }
        }

        // Update session info display
        function updateSessionInfo() {
            // DEVELOPMENT: Show unlimited questions message
            document.getElementById('sessionInfo').textContent = 'Development Mode - Unlimited Questions';
            
            /* PRODUCTION: Show remaining questions
            if (guestSession) {
                const remaining = guestSession.remaining_queries;
                document.getElementById('sessionInfo').textContent = 
                    `${remaining} question${remaining !== 1 ? 's' : ''} remaining today`;
            }
            */
        }

        // Handle input keypress
        function handleGuestInputKeypress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendGuestMessage();
            }
        }

        // Send guest message
        async function sendGuestMessage() {
            const input = document.getElementById('guestMessageInput');
            const sendButton = document.getElementById('guestSendButton');
            const message = input.value.trim();

            if (!message || isGuestChatLoading) return;

            // Add user message to chat
            addMessageToChat(message, 'user');
            input.value = '';

            // Show loading state
            isGuestChatLoading = true;
            sendButton.disabled = true;
            sendButton.innerHTML = `
                <div class="w-4 h-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            `;

            // Add typing indicator
            const typingId = addTypingIndicator();
            
            // Update message after 10 seconds to let user know it's still processing
            const progressTimeout = setTimeout(() => {
                const typingElement = document.getElementById(typingId);
                if (typingElement) {
                    const messageElement = typingElement.querySelector('p');
                    if (messageElement) {
                        messageElement.textContent = 'Still processing... This may take up to 30 seconds for detailed responses.';
                    }
                }
            }, 10000);

            try {
                const response = await fetch('/Kayan-AI-Chatbot/Kayan-Group/public/guest/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ query: message })
                });

                const data = await response.json();

                // Remove typing indicator and clear progress timeout
                clearTimeout(progressTimeout);
                removeTypingIndicator(typingId);

                if (data.success) {
                    // Handle different response scenarios
                    let aiMessage;
                    
                    // Check for nested data structure first (new RAG service format)
                    const responseData = data.data || data;
                    
                    if (responseData.answer || responseData.response || responseData.message || data.answer || data.response || data.message) {
                        // RAG service returned an answer
                        aiMessage = responseData.answer || responseData.response || responseData.message || data.answer || data.response || data.message;
                    } else if (data.has_answer === false || responseData.has_answer === false) {
                        // RAG service connected but no knowledge base content
                        aiMessage = 'I\'m currently learning and don\'t have specific information about that topic yet. However, I\'m here to help! Could you try asking something else, or would you like to contact our team directly for more detailed assistance?';
                    } else {
                        // Fallback message
                        aiMessage = 'I received your message. How can I assist you further?';
                    }
                    
                    addMessageToChat(aiMessage, 'ai');
                    
                    // DEVELOPMENT: Skip session updates
                    // updateSessionInfo(); // Disabled for development

                    // Show contact form if needed
                    if (data.show_contact_form && data.contact_form_html) {
                        addContactFormToChat(data.contact_form_html);
                    }

                } else {
                    const errorMessage = data.error || data.message || 'Sorry, I encountered an error. Please try again.';
                    addMessageToChat(errorMessage, 'ai');
                    
                    // DEVELOPMENT: Disable rate limit messages
                    /*
                    if (data.show_contact_form) {
                        addMessageToChat('You\'ve reached your daily limit. Please contact us directly for more assistance.', 'ai');
                    }
                    */
                }

            } catch (error) {
                clearTimeout(progressTimeout);
                removeTypingIndicator(typingId);
                console.error('Error sending message:', error);
                addMessageToChat('Sorry, I\'m having trouble connecting. Please try again later.', 'ai');
            } finally {
                // Reset loading state
                isGuestChatLoading = false;
                sendButton.disabled = false;
                sendButton.innerHTML = `
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                `;
            }
        }

        // Add message to chat
        function addMessageToChat(message, sender) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            
            if (sender === 'user') {
                messageDiv.className = 'flex justify-end';
                messageDiv.innerHTML = `
                    <div class="bg-gradient-to-r from-violet-500 to-purple-500 rounded-2xl rounded-br-sm px-4 py-3 max-w-xs">
                        <p class="text-white text-sm">${escapeHtml(message)}</p>
                    </div>
                `;
            } else {
                messageDiv.className = 'chat-bubble glass-morphism rounded-2xl rounded-bl-sm px-4 py-3 max-w-sm';
                messageDiv.innerHTML = `<p class="text-white/90 text-sm">${formatAIMessage(message)}</p>`;
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Add typing indicator
        function addTypingIndicator() {
            const messagesContainer = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            const typingId = 'typing-' + Date.now();
            
            typingDiv.id = typingId;
            typingDiv.className = 'glass-morphism rounded-2xl rounded-bl-sm px-4 py-3 max-w-sm';
            typingDiv.innerHTML = `
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <p class="text-white/70 text-xs mt-2">Processing your question...</p>
            `;
            
            messagesContainer.appendChild(typingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            return typingId;
        }

        // Remove typing indicator
        function removeTypingIndicator(typingId) {
            const typingElement = document.getElementById(typingId);
            if (typingElement) {
                typingElement.remove();
            }
        }

        // Add contact form to chat
        function addContactFormToChat(formHtml) {
            const messagesContainer = document.getElementById('chatMessages');
            const formDiv = document.createElement('div');
            formDiv.innerHTML = formHtml;
            messagesContainer.appendChild(formDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Submit guest contact form
        async function submitGuestContact(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/Kayan-AI-Chatbot/Kayan-Group/public/guest/contact/submit', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    addMessageToChat('✅ ' + data.message, 'ai');
                    form.style.display = 'none';
                } else {
                    addMessageToChat('❌ Failed to submit contact form. Please try again.', 'ai');
                }
            } catch (error) {
                console.error('Error submitting contact form:', error);
                addMessageToChat('❌ Error submitting contact form. Please try again.', 'ai');
            }
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatAIMessage(message) {
            // Safety check for undefined/null message
            if (!message) {
                return 'I apologize, but I encountered an issue processing your request.';
            }
            
            // Convert markdown-like formatting to HTML
            return message
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/•/g, '•');
        }

        // Close modal when clicking outside
        document.getElementById('guestChatModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeGuestChat();
            }
        });
    </script>
    
    <!-- Custom animations script -->
    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-slideUp');
                    }
                });
            }, observerOptions);
            
            // Observe all glass cards
            document.querySelectorAll('.glass-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>