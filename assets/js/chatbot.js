/**
 * Prime EMS Studios - Modern Chatbot Widget
 * Advanced chatbot with N8N integration and smooth animations
 */

class PrimeChatbot {
    constructor(options = {}) {
        this.options = {
            apiEndpoint: '/api/chatbot-simple.php',
            brandColor: '#FFD700',
            position: 'bottom-right',
            autoOpen: false,
            welcomeMessage: true,
            typingSpeed: 50,
            ...options
        };
        
        this.isOpen = false;
        this.isTyping = false;
        this.sessionId = this.generateSessionId();
        this.chatHistory = [];
        
        this.init();
    }
    
    init() {
        this.createChatWidget();
        this.attachEventListeners();
        this.loadChatHistory();
        
        if (this.options.welcomeMessage) {
            setTimeout(() => {
                this.showWelcomeMessage();
            }, 2000);
        }
    }
    
    generateSessionId() {
        return 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    }
    
    createChatWidget() {
        // Chat widget HTML structure
        const widgetHTML = `
            <div id="prime-chatbot" class="prime-chatbot ${this.options.position}">
                <!-- Chat Toggle Button -->
                <div class="chat-toggle-wrapper">
                    <div class="chat-label">Ücretsiz Deneme Ders Randevusu Al!</div>
                    <div class="chat-toggle" id="chatToggle">
                        <div class="chat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H6L4 18V4H20V16Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="close-icon" style="display: none;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="notification-badge" id="notificationBadge">1</div>
                    </div>
                </div>
                
                <!-- Chat Window -->
                <div class="chat-window" id="chatWindow">
                    <div class="chat-header">
                        <div class="agent-info">
                            <div class="agent-avatar">
                                <div class="status-indicator"></div>
                                ⚡
                            </div>
                            <div class="agent-details">
                                <h4>Prime Assistant</h4>
                                <span class="status">Çevrimiçi</span>
                            </div>
                        </div>
                        <button class="minimize-btn" id="minimizeBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 13H5V11H19V13Z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages will be inserted here -->
                    </div>
                    
                    <div class="typing-indicator" id="typingIndicator" style="display: none;">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="typing-text">Prime Assistant yazıyor...</span>
                    </div>
                    
                    <div class="quick-actions" id="quickActions">
                        <button class="quick-btn" data-action="appointment">📅 Randevu Al</button>
                        <button class="quick-btn" data-action="pricing">💰 Fiyatlar</button>
                        <button class="quick-btn" data-action="services">🏋️ Hizmetler</button>
                        <button class="quick-btn" data-action="faq">❓ S.S.S.</button>
                    </div>
                    
                    <div class="chat-input-container">
                        <div class="chat-input">
                            <textarea 
                                id="messageInput" 
                                placeholder="Mesajınızı yazın..." 
                                rows="1"
                                maxlength="500"
                            ></textarea>
                            <button class="send-btn" id="sendBtn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.01 21L23 12 2.01 3 2 10L17 12 2 14L2.01 21Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                        <div class="input-footer">
                            <span class="powered-by">Powered by Prime AI</span>
                            <span class="char-count" id="charCount">0/500</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
        this.injectStyles();
    }
    
    injectStyles() {
        const styles = `
            <style>
                .prime-chatbot {
                    position: fixed;
                    z-index: 10000;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                }
                
                .prime-chatbot.bottom-right {
                    bottom: 20px;
                    right: 20px;
                }
                
                .prime-chatbot.bottom-left {
                    bottom: 20px;
                    left: 20px;
                }
                
                .chat-toggle-wrapper {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    position: relative;
                    justify-content: flex-end;
                    flex-direction: row-reverse;
                }
                
                .chat-label {
                    background: linear-gradient(135deg, ${this.options.brandColor} 0%, #f0c420 100%);
                    padding: 10px 20px;
                    border-radius: 25px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    font-weight: 600;
                    color: #1a1a1a;
                    font-size: 14px;
                    white-space: nowrap;
                    animation: fadeInRight 0.5s ease-out;
                    position: relative;
                    margin-right: 15px;
                }
                
                @keyframes fadeInRight {
                    from {
                        opacity: 0;
                        transform: translateX(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
                
                .chat-toggle {
                    width: 60px;
                    height: 60px;
                    background: linear-gradient(135deg, ${this.options.brandColor} 0%, #f0c420 100%);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                    overflow: hidden;
                }
                
                .chat-toggle::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: radial-gradient(circle at center, rgba(255,255,255,0.2) 0%, transparent 70%);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                
                .chat-toggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 25px rgba(255, 215, 0, 0.4);
                }
                
                .chat-toggle:hover::before {
                    opacity: 1;
                }
                
                .chat-toggle svg {
                    color: #1a1a1a;
                    transition: transform 0.3s ease;
                }
                
                .notification-badge {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: #ff4757;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: 600;
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
                
                .chat-window {
                    position: absolute;
                    bottom: 70px;
                    right: 0;
                    width: 380px;
                    max-width: calc(100vw - 40px);
                    height: 500px;
                    max-height: calc(100vh - 120px);
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                    display: flex;
                    flex-direction: column;
                    transform: scale(0.8) translateY(20px);
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    overflow: hidden;
                    border: 1px solid rgba(0, 0, 0, 0.1);
                }
                
                .chat-window.open {
                    transform: scale(1) translateY(0);
                    opacity: 1;
                    visibility: visible;
                }
                
                .chat-header {
                    background: linear-gradient(135deg, ${this.options.brandColor} 0%, #f0c420 100%);
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    color: #1a1a1a;
                }
                
                .agent-info {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                
                .agent-avatar {
                    width: 40px;
                    height: 40px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    position: relative;
                }
                
                .status-indicator {
                    position: absolute;
                    bottom: 2px;
                    right: 2px;
                    width: 10px;
                    height: 10px;
                    background: #2ed573;
                    border-radius: 50%;
                    border: 2px solid white;
                }
                
                .agent-details h4 {
                    margin: 0;
                    font-size: 14px;
                    font-weight: 600;
                }
                
                .agent-details .status {
                    font-size: 12px;
                    opacity: 0.8;
                }
                
                .minimize-btn {
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    border-radius: 50%;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: background 0.2s ease;
                }
                
                .minimize-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                .chat-messages {
                    flex: 1;
                    padding: 16px;
                    overflow-y: auto;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                
                .message {
                    display: flex;
                    gap: 8px;
                    opacity: 0;
                    animation: slideIn 0.4s ease forwards;
                }
                
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .message.user {
                    flex-direction: row-reverse;
                }
                
                .message-content {
                    max-width: 80%;
                    padding: 12px 16px;
                    border-radius: 18px;
                    font-size: 14px;
                    line-height: 1.4;
                    word-wrap: break-word;
                }
                
                .message.bot .message-content {
                    background: #f8f9fa;
                    color: #2c3e50;
                    border-bottom-left-radius: 4px;
                }
                
                .message.user .message-content {
                    background: ${this.options.brandColor};
                    color: #1a1a1a;
                    border-bottom-right-radius: 4px;
                }
                
                .message-avatar {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: ${this.options.brandColor};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    flex-shrink: 0;
                }
                
                .message.user .message-avatar {
                    background: #6c757d;
                    color: white;
                }
                
                .message-actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin-top: 8px;
                }
                
                .action-btn {
                    background: ${this.options.brandColor};
                    color: #1a1a1a;
                    border: none;
                    padding: 8px 12px;
                    border-radius: 12px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .action-btn:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
                }
                
                .typing-indicator {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 16px;
                    border-top: 1px solid #eee;
                }
                
                .typing-dots {
                    display: flex;
                    gap: 4px;
                }
                
                .typing-dots span {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: ${this.options.brandColor};
                    animation: typing 1.4s infinite ease-in-out;
                }
                
                .typing-dots span:nth-child(2) {
                    animation-delay: 0.2s;
                }
                
                .typing-dots span:nth-child(3) {
                    animation-delay: 0.4s;
                }
                
                @keyframes typing {
                    0%, 60%, 100% {
                        transform: translateY(0);
                        opacity: 0.4;
                    }
                    30% {
                        transform: translateY(-10px);
                        opacity: 1;
                    }
                }
                
                .typing-text {
                    font-size: 12px;
                    color: #6c757d;
                }
                
                .quick-actions {
                    padding: 12px 16px;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    border-top: 1px solid #eee;
                    background: #fafbfc;
                }
                
                .quick-btn {
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 12px;
                    padding: 6px 12px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .quick-btn:hover {
                    background: ${this.options.brandColor};
                    border-color: ${this.options.brandColor};
                    color: #1a1a1a;
                }
                
                .chat-input-container {
                    border-top: 1px solid #eee;
                    background: white;
                }
                
                .chat-input {
                    display: flex;
                    align-items: flex-end;
                    padding: 16px;
                    gap: 12px;
                }
                
                .chat-input textarea {
                    flex: 1;
                    border: 1px solid #ddd;
                    border-radius: 12px;
                    padding: 12px 16px;
                    resize: none;
                    outline: none;
                    font-family: inherit;
                    font-size: 14px;
                    line-height: 1.4;
                    transition: border-color 0.2s ease;
                    max-height: 100px;
                }
                
                .chat-input textarea:focus {
                    border-color: ${this.options.brandColor};
                }
                
                .send-btn {
                    width: 40px;
                    height: 40px;
                    background: ${this.options.brandColor};
                    border: none;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                }
                
                .send-btn:hover {
                    transform: scale(1.05);
                    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
                }
                
                .send-btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                    transform: none;
                }
                
                .input-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0 16px 12px;
                    font-size: 11px;
                    color: #6c757d;
                }
                
                .powered-by {
                    font-weight: 500;
                }
                
                /* Mobile Responsive */
                @media (max-width: 480px) {
                    .prime-chatbot.bottom-right {
                        right: 10px;
                        bottom: 10px;
                    }
                    
                    .chat-window {
                        width: calc(100vw - 20px);
                        height: calc(100vh - 80px);
                        bottom: 70px;
                        right: -10px;
                    }
                    
                    .chat-toggle {
                        width: 50px;
                        height: 50px;
                    }
                }
                
                /* Scrollbar Styling */
                .chat-messages::-webkit-scrollbar {
                    width: 4px;
                }
                
                .chat-messages::-webkit-scrollbar-track {
                    background: transparent;
                }
                
                .chat-messages::-webkit-scrollbar-thumb {
                    background: #ddd;
                    border-radius: 2px;
                }
                
                .chat-messages::-webkit-scrollbar-thumb:hover {
                    background: #bbb;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }
    
    attachEventListeners() {
        // Wait for DOM elements to be ready
        setTimeout(() => {
            const chatToggle = document.getElementById('chatToggle');
            const chatWindow = document.getElementById('chatWindow');
            const minimizeBtn = document.getElementById('minimizeBtn');
            const sendBtn = document.getElementById('sendBtn');
            const messageInput = document.getElementById('messageInput');
            const quickActions = document.getElementById('quickActions');
            const charCount = document.getElementById('charCount');
            
            if (chatToggle) {
                // Toggle chat window
                chatToggle.addEventListener('click', () => this.toggleChat());
            }
            
            if (minimizeBtn) {
                minimizeBtn.addEventListener('click', () => this.toggleChat());
            }
            
            if (sendBtn) {
                // Send message
                sendBtn.addEventListener('click', () => this.sendMessage());
            }
            
            if (messageInput) {
                messageInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
                
                // Auto-resize textarea
                messageInput.addEventListener('input', (e) => {
                    this.autoResizeTextarea(e.target);
                    this.updateCharCount();
                });
            }
            
            if (quickActions) {
                // Quick actions
                quickActions.addEventListener('click', (e) => {
                    if (e.target.classList.contains('quick-btn')) {
                        const action = e.target.getAttribute('data-action');
                        this.handleQuickAction(action);
                    }
                });
            }
        }, 100);
    }
    
    toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        const chatIcon = document.querySelector('.chat-icon');
        const closeIcon = document.querySelector('.close-icon');
        const notificationBadge = document.getElementById('notificationBadge');
        
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            chatWindow.classList.add('open');
            chatIcon.style.display = 'none';
            closeIcon.style.display = 'block';
            notificationBadge.style.display = 'none';
            
            // Focus input
            setTimeout(() => {
                document.getElementById('messageInput').focus();
            }, 300);
        } else {
            chatWindow.classList.remove('open');
            chatIcon.style.display = 'block';
            closeIcon.style.display = 'none';
        }
    }
    
    async sendMessage(message = null) {
        const messageInput = document.getElementById('messageInput');
        const messageText = message || messageInput.value.trim();
        
        if (!messageText) return;
        
        // Clear input
        messageInput.value = '';
        this.autoResizeTextarea(messageInput);
        this.updateCharCount();
        
        // Add user message to chat
        this.addMessage(messageText, 'user');
        
        // Show typing indicator
        this.showTypingIndicator();
        
        try {
            const response = await this.callAPI(messageText);
            
            if (response.success) {
                // Hide typing indicator
                this.hideTypingIndicator();
                
                // Add bot response with typing effect
                await this.addMessageWithTyping(response.data.message, 'bot', response.data.actions);
                
                // Update session ID
                this.sessionId = response.data.session_id;
                
            } else {
                this.hideTypingIndicator();
                this.addMessage('Üzgünüm, bir hata oluştu. Lütfen tekrar deneyin.', 'bot');
            }
        } catch (error) {
            console.error('Chatbot Error:', error);
            this.hideTypingIndicator();
            this.addMessage('Bağlantı hatası. Lütfen internet bağlantınızı kontrol edin.', 'bot');
        }
    }
    
    async callAPI(message) {
        const response = await fetch(this.options.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                session_id: this.sessionId
            })
        });
        
        return await response.json();
    }
    
    addMessage(content, role, actions = null) {
        const chatMessages = document.getElementById('chatMessages');
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${role}`;
        
        const avatar = role === 'bot' ? '⚡' : '👤';
        
        let messageHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">
                ${content.replace(/\n/g, '<br>')}
            </div>
        `;
        
        messageElement.innerHTML = messageHTML;
        chatMessages.appendChild(messageElement);
        
        // Add actions if provided
        if (actions && actions.buttons) {
            const actionsElement = document.createElement('div');
            actionsElement.className = 'message-actions';
            
            actions.buttons.forEach(button => {
                const buttonElement = document.createElement('button');
                buttonElement.className = 'action-btn';
                buttonElement.textContent = button.text;
                buttonElement.addEventListener('click', () => {
                    this.handleActionButton(button);
                });
                actionsElement.appendChild(buttonElement);
            });
            
            const messageContent = messageElement.querySelector('.message-content');
            messageContent.appendChild(actionsElement);
        }
        
        // Scroll to bottom
        this.scrollToBottom();
        
        // Store in history
        this.chatHistory.push({ content, role, timestamp: new Date() });
    }
    
    async addMessageWithTyping(content, role, actions = null) {
        const chatMessages = document.getElementById('chatMessages');
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${role}`;
        
        const avatar = role === 'bot' ? '⚡' : '👤';
        
        messageElement.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">
                <span class="typing-text-content"></span>
            </div>
        `;
        
        chatMessages.appendChild(messageElement);
        this.scrollToBottom();
        
        // Typing effect
        const typingElement = messageElement.querySelector('.typing-text-content');
        await this.typeText(typingElement, content);
        
        // Add actions after typing is complete
        if (actions && actions.buttons) {
            const actionsElement = document.createElement('div');
            actionsElement.className = 'message-actions';
            
            actions.buttons.forEach(button => {
                const buttonElement = document.createElement('button');
                buttonElement.className = 'action-btn';
                buttonElement.textContent = button.text;
                buttonElement.addEventListener('click', () => {
                    this.handleActionButton(button);
                });
                actionsElement.appendChild(buttonElement);
            });
            
            const messageContent = messageElement.querySelector('.message-content');
            messageContent.appendChild(actionsElement);
        }
        
        // Store in history
        this.chatHistory.push({ content, role, timestamp: new Date() });
    }
    
    async typeText(element, text, speed = 50) {
        const lines = text.split('\n');
        element.innerHTML = '';
        
        for (let lineIndex = 0; lineIndex < lines.length; lineIndex++) {
            if (lineIndex > 0) {
                element.innerHTML += '<br>';
            }
            
            const line = lines[lineIndex];
            for (let i = 0; i < line.length; i++) {
                element.innerHTML += line[i];
                this.scrollToBottom();
                await this.sleep(speed);
            }
        }
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    handleActionButton(button) {
        switch (button.action) {
            case 'call':
                window.open(`tel:${button.value}`);
                break;
            case 'whatsapp':
                window.open(`https://wa.me/${button.value}`);
                break;
            case 'link':
                window.open(button.value, '_blank');
                break;
            case 'appointment':
                this.sendMessage('Randevu almak istiyorum');
                break;
            case 'contact':
                this.sendMessage('İletişim bilgilerinizi alabilir miyim?');
                break;
            case 'faq':
                window.open('/sss.php', '_blank');
                break;
        }
    }
    
    handleQuickAction(action) {
        const actionMessages = {
            appointment: 'Randevu almak istiyorum',
            pricing: 'Paket fiyatlarınızı öğrenebilir miyim?',
            services: 'Hangi hizmetleri sunuyorsunuz?',
            faq: 'Sık sorulan soruları görmek istiyorum'
        };
        
        if (actionMessages[action]) {
            this.sendMessage(actionMessages[action]);
        }
    }
    
    showTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        typingIndicator.style.display = 'flex';
        this.scrollToBottom();
    }
    
    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        typingIndicator.style.display = 'none';
    }
    
    showWelcomeMessage() {
        if (!this.isOpen) {
            const notificationBadge = document.getElementById('notificationBadge');
            notificationBadge.style.display = 'flex';
            
            // Add welcome message to chat
            this.addMessage('Merhaba! 👋 Prime EMS Studios\'a hoş geldiniz! Size nasıl yardımcı olabilirim?', 'bot', {
                buttons: [
                    { text: '📅 Randevu Al', action: 'appointment' },
                    { text: '💰 Fiyatlar', action: 'pricing' },
                    { text: '🏋️ Hizmetler', action: 'services' }
                ]
            });
        }
    }
    
    autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
    }
    
    updateCharCount() {
        const messageInput = document.getElementById('messageInput');
        const charCount = document.getElementById('charCount');
        const currentLength = messageInput.value.length;
        
        charCount.textContent = `${currentLength}/500`;
        
        if (currentLength > 450) {
            charCount.style.color = '#ff4757';
        } else {
            charCount.style.color = '#6c757d';
        }
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    async loadChatHistory() {
        try {
            const response = await fetch(`${this.options.apiEndpoint}?session_id=${this.sessionId}`);
            const data = await response.json();
            
            if (data.success && data.data.messages.length > 0) {
                data.data.messages.forEach(message => {
                    this.addMessage(message.message, message.role);
                });
            }
        } catch (error) {
            console.log('No previous chat history found');
        }
    }
}

// Auto-initialize chatbot when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chatbot with N8N integration
    window.primeChatbot = new PrimeChatbot({
        apiEndpoint: '/api/chatbot-simple.php',
        brandColor: '#FFD700',
        position: 'bottom-right',
        autoOpen: false,
        welcomeMessage: true,
        typingSpeed: 30
    });
});
