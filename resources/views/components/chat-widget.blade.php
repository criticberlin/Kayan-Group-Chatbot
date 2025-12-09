{{-- Chat Widget - Place anywhere in your layout --}}
<div id="chat-widget" class="chat-widget">
    {{-- Chat Button --}}
    <button id="chat-toggle" class="chat-toggle" title="Chat with us">
        <svg class="chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
            </path>
        </svg>
        <svg class="chat-close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    {{-- Chat Window --}}
    <div id="chat-window" class="chat-window" style="display: none;">
        {{-- Header --}}
        <div class="chat-header">
            <div class="chat-header-content">
                <div class="chat-avatar">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                    </svg>
                </div>
                <div>
                    <h3 class="chat-title">Kayan Assistant</h3>
                    <p class="chat-subtitle">Ask me about our products</p>
                </div>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="chat-messages" class="chat-messages">
            <div class="chat-message bot-message">
                <div class="message-content">
                    <p>Hello! 👋 I'm here to help you find information about Kayan Group's products. How can I assist
                        you today?</p>
                </div>
            </div>
        </div>

        {{-- Typing Indicator --}}
        <div id="typing-indicator" class="typing-indicator" style="display: none;">
            <span></span>
            <span></span>
            <span></span>
        </div>

        {{-- Input Area --}}
        <div class="chat-input-container">
            <form id="chat-form">
                <input type="text" id="chat-input" class="chat-input" placeholder="Type your message..."
                    autocomplete="off" required />
                <button type="submit" class="chat-send-button" title="Send">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    <script>
        // Initialize chat widget with API endpoint
        window.chatConfig = {
            apiUrl: '{{ url('/api/chat/send') }}',
            sessionId: localStorage.getItem('chat_session_id') || null
        };
    </script>
@endpush