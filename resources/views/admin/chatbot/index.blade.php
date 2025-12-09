<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kayan Group AI Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 800px;
            background: rgba(26, 26, 46, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .chat-header {
            text-align: center;
            padding: 60px 40px 40px;
            background: linear-gradient(135deg, rgba(103, 58, 183, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
            border-bottom: 1px solid rgba(147, 51, 234, 0.2);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #673ab7 0%, #9333ea 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 32px;
            font-weight: bold;
            color: white;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .subtitle {
            font-size: 16px;
            color: #a0aec0;
            font-weight: 400;
        }

        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 30px;
            background: rgba(15, 15, 30, 0.5);
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(147, 51, 234, 0.3);
            border-radius: 4px;
        }

        .message {
            margin-bottom: 24px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-content {
            display: inline-block;
            max-width: 80%;
            padding: 16px 20px;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.6;
        }

        .bot-message .message-content {
            background: rgba(147, 51, 234, 0.15);
            color: #e2e8f0;
            border: 1px solid rgba(147, 51, 234, 0.3);
        }

        .user-message {
            text-align: right;
        }

        .user-message .message-content {
            background: linear-gradient(135deg, #673ab7 0%, #9333ea 100%);
            color: white;
        }

        /* Formatted message styles */
        .category-header {
            font-weight: 700;
            font-size: 16px;
            color: #9333ea;
            margin: 12px 0 8px 0;
        }

        .bullet-item {
            padding-left: 16px;
            margin: 6px 0;
            line-height: 1.6;
        }

        .bot-message .message-content strong {
            color: #ffffff;
            font-weight: 600;
        }

        .spacer {
            height: 12px;
        }

        .typing-indicator {
            padding: 16px 20px;
            background: rgba(147, 51, 234, 0.15);
            border: 1px solid rgba(147, 51, 234, 0.3);
            border-radius: 16px;
            display: inline-block;
            margin-bottom: 24px;
        }

        .typing-indicator span {
            height: 10px;
            width: 10px;
            background: #9333ea;
            display: inline-block;
            border-radius: 50%;
            margin-right: 6px;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
            margin-right: 0;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-12px);
            }
        }

        .chat-input-area {
            padding: 30px;
            background: rgba(15, 15, 30, 0.8);
            border-top: 1px solid rgba(147, 51, 234, 0.2);
        }

        .input-wrapper {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        #chat-input {
            flex: 1;
            padding: 16px 24px;
            background: rgba(26, 26, 46, 0.8);
            border: 2px solid rgba(147, 51, 234, 0.3);
            border-radius: 50px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
        }

        #chat-input:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.1);
        }

        #chat-input::placeholder {
            color: #718096;
        }

        #send-button {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #673ab7 0%, #9333ea 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #send-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(147, 51, 234, 0.4);
        }

        #send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <a href="{{ url('/admin') }}" class="back-button">← Back to Dashboard</a>

    <div class="chat-container">
        <div class="chat-header">
            <div class="logo">KG</div>
            <h1>Welcome to Kayan Group AI</h1>
            <p class="subtitle">Start a conversation to get assistance with our products and services</p>
        </div>

        <div id="chat-messages" class="chat-messages">
            <div class="message bot-message">
                <div class="message-content">
                    Hello! I'm your AI assistant. I can help you with:
                    <br>• Answering questions
                    <br>• Creative writing
                    <br>• Problem solving
                    <br>• And much more!
                </div>
            </div>
        </div>

        <div class="chat-input-area">
            <form id="chat-form" class="input-wrapper">
                <input type="text" id="chat-input" placeholder="Message..." autocomplete="off" required />
                <button type="submit" id="send-button">→</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const sendButton = document.getElementById('send-button');

        let sessionId = localStorage.getItem('chat_session_id') || generateSessionId();

        function generateSessionId() {
            const id = 'chat-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chat_session_id', id);
            return id;
        }

        function addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}-message`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';

            if (type === 'bot') {
                // Format bot messages with markdown-like styling
                contentDiv.innerHTML = formatBotMessage(text);
            } else {
                contentDiv.textContent = text;
            }

            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        function formatBotMessage(text) {
            // Convert asterisks to bullet points and add proper formatting
            let formatted = text
                // Category headers (e.g., **Chocolate:**)
                .replace(/\*\*([^:]+):\*\*/g, '<div class="category-header">$1</div>')
                // Bold items (e.g., **Product Name**)
                .replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>')
                // Bullet points (e.g., * Item)
                .replace(/\* ([^\n]+)/g, '<div class="bullet-item">• $1</div>')
                // Line breaks
                .replace(/\n\n/g, '<div class="spacer"></div>')
                .replace(/\n/g, '<br>');

            return formatted;
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            chatMessages.appendChild(typingDiv);
            scrollToBottom();
        }

        function hideTyping() {
            const typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
        }

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = chatInput.value.trim();
            if (!message) return;

            addMessage(message, 'user');
            chatInput.value = '';
            sendButton.disabled = true;
            showTyping();

            try {
                const response = await fetch('{{ url("/api/chat/send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId
                    })
                });

                const data = await response.json();
                hideTyping();

                if (data.success) {
                    if (data.session_id) sessionId = data.session_id;
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            } catch (error) {
                console.error('Error:', error);
                hideTyping();
                addMessage('Sorry, I could not connect. Please try again.', 'bot');
            } finally {
                sendButton.disabled = false;
                chatInput.focus();
            }
        });
    </script>
</body>

</html>