/**
 * Chat Widget JavaScript
 */
(function () {
    'use strict';

    const chatWidget = {
        config: window.chatConfig || {},
        sessionId: null,
        isOpen: false,

        init() {
            this.sessionId = this.config.sessionId || this.generateSessionId();
            this.cacheDom();
            this.bindEvents();
            this.loadSession();
        },

        cacheDom() {
            this.chatToggle = document.getElementById('chat-toggle');
            this.chatWindow = document.getElementById('chat-window');
            this.chatMessages = document.getElementById('chat-messages');
            this.chatForm = document.getElementById('chat-form');
            this.chatInput = document.getElementById('chat-input');
            this.typingIndicator = document.getElementById('typing-indicator');
            this.chatIcon = document.querySelector('.chat-icon');
            this.chatCloseIcon = document.querySelector('.chat-close-icon');
        },

        bindEvents() {
            this.chatToggle.addEventListener('click', () => {
                this.toggleChat();
            });

            this.chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            this.chatWindow.style.display = this.isOpen ? 'flex' : 'none';
            this.chatIcon.style.display = this.isOpen ? 'none' : 'block';
            this.chatCloseIcon.style.display = this.isOpen ? 'block' : 'none';

            if (this.isOpen) {
                this.chatInput.focus();
            }
        },

        async sendMessage() {
            const message = this.chatInput.value.trim();

            if (!message) return;

            // Add user message to chat
            this.addMessage(message, 'user');
            this.chatInput.value = '';

            // Show typing indicator
            this.showTyping();

            try {
                const response = await fetch(this.config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: this.sessionId
                    })
                });

                const data = await response.json();

                this.hideTyping();

                if (data.success) {
                    // Update session ID if new
                    if (data.session_id && data.session_id !== this.sessionId) {
                        this.sessionId = data.session_id;
                        this.saveSession();
                    }

                    // Add bot response
                    this.addMessage(data.response, 'bot');
                } else {
                    this.addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }

            } catch (error) {
                console.error('Chat error:', error);
                this.hideTyping();
                this.addMessage('Sorry, I could not connect to the server. Please try again.', 'bot');
            }
        },

        addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${type}-message`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';

            const textP = document.createElement('p');
            textP.textContent = text;
            textP.style.margin = '0';

            contentDiv.appendChild(textP);
            messageDiv.appendChild(contentDiv);

            this.chatMessages.appendChild(messageDiv);
            this.scrollToBottom();
        },

        showTyping() {
            this.typingIndicator.style.display = 'block';
            this.scrollToBottom();
        },

        hideTyping() {
            this.typingIndicator.style.display = 'none';
        },

        scrollToBottom() {
            setTimeout(() => {
                this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
            }, 100);
        },

        generateSessionId() {
            return 'chat-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        },

        saveSession() {
            try {
                localStorage.setItem('chat_session_id', this.sessionId);
            } catch (e) {
                console.warn('Could not save session:', e);
            }
        },

        loadSession() {
            try {
                const savedSessionId = localStorage.getItem('chat_session_id');
                if (savedSessionId) {
                    this.sessionId = savedSessionId;
                } else {
                    this.saveSession();
                }
            } catch (e) {
                console.warn('Could not load session:', e);
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => chatWidget.init());
    } else {
        chatWidget.init();
    }

})();
