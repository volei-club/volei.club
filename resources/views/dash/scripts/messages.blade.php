window.messagesManager = () => {
    return {
        conversations: [],
        messages: [],
        activeConversationId: null,
        activePartner: null,
        contacts: [],
        newMessage: '',
        
        loadingConversations: false,
        loadingMessages: false,
        loadingContacts: false,
        sendingMessage: false,
        
        showNewChatModal: false,
        contactSearchQuery: '',
        
        pollingInterval: null,
        roleLabels: {
            'administrator': 'Administrator',
            'manager': 'Manager',
            'antrenor': 'Antrenor',
            'sportiv': 'Sportiv',
            'parinte': 'Părinte'
        },

        init() {
            this.$watch('currentPage', (val) => {
                if (val === '/dash/mesaje') {
                    this.fetchConversations();
                    this.startPolling();
                } else {
                    this.stopPolling();
                }
            });
            
            // Wait for user to be loaded from parent dashboard scope if needed
            this.$watch('user', (newUser) => {
                if (newUser && this.currentPage === '/dash/mesaje' && this.conversations.length === 0) {
                    this.fetchConversations();
                }
            });

            if (this.currentPage === '/dash/mesaje') {
                this.fetchConversations();
                this.startPolling();
            }
        },

        async fetchConversations() {
            if (!this.user) return;
            // Only show loader on initial fetch
            if (this.conversations.length === 0) {
                this.loadingConversations = true;
            }
            try {
                const response = await axios.get('/api/chat/conversations');
                if (response.data.status === 'success') {
                    this.conversations = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching conversations:', error);
            } finally {
                this.loadingConversations = false;
            }
        },

        async selectConversation(conv) {
            this.activeConversationId = conv.id;
            this.activePartner = this.getConversationPartner(conv);
            this.messages = [];
            await this.fetchMessages(conv.id);
            this.markAsRead(conv.id);
        },

        async fetchMessages(conversationId) {
            if (this.loadingMessages || !conversationId) return;
            this.loadingMessages = true;
            try {
                const response = await axios.get(`/api/chat/conversations/${conversationId}`);
                if (response.data.status === 'success') {
                    this.messages = response.data.data.messages;
                    this.scrollToBottom();
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
            } finally {
                this.loadingMessages = false;
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sendingMessage || !this.user) return;
            
            this.sendingMessage = true;
            const content = this.newMessage;
            this.newMessage = '';

            try {
                const payload = {
                    content: content
                };
                
                if (this.activeConversationId && String(this.activeConversationId).startsWith('temp-')) {
                    payload.recipient_id = this.activePartner.id;
                } else {
                    payload.conversation_id = this.activeConversationId;
                }
                
                const response = await axios.post('/api/chat/messages', payload);
                if (response.data.status === 'success') {
                    const newMsg = response.data.data;
                    
                    if (this.activeConversationId && String(this.activeConversationId).startsWith('temp-')) {
                        this.activeConversationId = newMsg.conversation_id;
                    }
                    
                    this.messages.push(newMsg);
                    this.scrollToBottom();
                    this.fetchConversations();
                }
            } catch (error) {
                this.$dispatch('notify', { message: 'Eroare la trimiterea mesajului.', type: 'error' });
                this.newMessage = content;
            } finally {
                this.sendingMessage = false;
            }
        },

        async markAsRead(conversationId) {
            if (!this.user || !conversationId) return;
            try {
                await axios.post(`/api/chat/conversations/${conversationId}/read`);
                const conv = this.conversations.find(c => c.id === conversationId);
                if (conv) {
                    conv.is_unread = false;
                    const myId = this.user.id;
                    const pivot = conv.users.find(u => u.id === myId)?.pivot;
                    if (pivot) pivot.last_read_at = new Date().toISOString();
                }
                window.dispatchEvent(new CustomEvent('refresh-unread-count'));
            } catch (error) {
                console.error('Error marking as read:', error);
            }
        },

        async openNewChat() {
            this.showNewChatModal = true;
            this.loadingContacts = true;
            try {
                const response = await axios.get('/api/chat/contacts');
                if (response.data.status === 'success') {
                    this.contacts = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching contacts:', error);
            } finally {
                this.loadingContacts = false;
            }
        },

        async startChatWith(contact) {
            if (!this.user) return;
            const existing = this.conversations.find(c => 
                c.type === 'direct' && c.users.some(u => u.id === contact.id)
            );

            if (existing) {
                this.selectConversation(existing);
            } else {
                this.activeConversationId = 'temp-' + contact.id; 
                this.activePartner = contact;
                this.messages = [];
            }
            
            this.showNewChatModal = false;
        },

        getConversationPartner(conv) {
            if (!conv || !conv.users || !this.user) return null;
            const myId = this.user.id;
            return conv.users.find(u => u.id !== myId);
        },

        hasUnreadMessages(conv) {
            if (!conv.last_message || !this.user) return false;
            
            // If I am currently looking at this conversation, it's NOT unread
            if (this.activeConversationId === conv.id) return false;

            // If server explicitly told us it's unread, believe it
            if (conv.is_unread !== undefined) return conv.is_unread;

            // Fallback to local calculation logic if flag is not present
            if (conv.last_message.sender_id === this.user.id) return false;

            const myId = this.user.id;
            const myPivot = conv.users.find(u => u.id === myId)?.pivot;
            if (!myPivot || !myPivot.last_read_at) return true;
            
            return new Date(conv.last_message.created_at).getTime() > new Date(myPivot.last_read_at).getTime();
        },

        formatMessageTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            
            if (date.toDateString() === now.toDateString()) {
                return date.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString('ro-RO', { day: '2-digit', month: '2-digit' });
        },

        get filteredContacts() {
            if (!this.contactSearchQuery) return this.contacts;
            const q = this.contactSearchQuery.toLowerCase();
            return this.contacts.filter(c => c.name.toLowerCase().includes(q));
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.msgArea;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        startPolling() {
            this.stopPolling();
            this.pollingInterval = setInterval(() => {
                if (this.currentPage === '/dash/mesaje') {
                    this.fetchConversations();
                    if (this.activeConversationId && !String(this.activeConversationId).startsWith('temp-')) {
                        this.pollMessages();
                    }
                }
            }, 5000); 
        },

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },

        async pollMessages() {
             if (!this.activeConversationId || String(this.activeConversationId).startsWith('temp-')) return;
             try {
                const response = await axios.get(`/api/chat/conversations/${this.activeConversationId}`);
                if (response.data.status === 'success') {
                    const newMessages = response.data.data.messages;
                    if (newMessages.length > this.messages.length) {
                        this.messages = newMessages;
                        this.scrollToBottom();
                        this.markAsRead(this.activeConversationId);
                    }
                }
            } catch (error) {
                console.error('Error polling messages:', error);
            }
        }
    };
};
