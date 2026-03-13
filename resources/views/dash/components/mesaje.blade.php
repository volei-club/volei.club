<div x-show="currentPage === '/dash/mesaje'" class="h-full flex flex-col" x-data="messagesManager()" x-init="init()" style="display: none;">
    <!-- Main Messaging Layout -->
    <div class="flex-1 flex overflow-hidden bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
        
        <!-- Sidebar: Conversations List -->
        <div class="w-full md:w-80 flex flex-col border-r border-slate-100 dark:border-slate-700 h-full" :class="activeConversationId ? 'hidden md:flex' : 'flex'">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('chat.title') }}</h2>
                <button @click="openNewChat()" class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">add_comment</span>
                </button>
            </div>
            
            <!-- Conversation List -->
            <div class="flex-1 overflow-y-auto">
                <template x-if="conversations.length === 0 && !loadingConversations">
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300 mx-auto mb-4">
                            <span class="material-symbols-outlined text-3xl">chat_bubble_outline</span>
                        </div>
                        <p class="text-slate-400 text-sm italic">{{ __('chat.no_conversations') }}</p>
                    </div>
                </template>
                
                <template x-if="loadingConversations">
                    <div class="p-4 space-y-4">
                        <template x-for="i in 5">
                            <div class="flex gap-3 animate-pulse">
                                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 bg-slate-100 dark:bg-slate-700 rounded w-1/2"></div>
                                    <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded w-full"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                
                <div class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    <template x-for="conv in conversations" :key="conv.id">
                        <button @click="selectConversation(conv)" 
                                class="w-full text-left p-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors flex gap-3 items-center group"
                                :class="activeConversationId === conv.id ? 'bg-primary/5 dark:bg-primary/10' : ''">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <template x-if="getConversationPartner(conv)?.photo">
                                    <img :src="'/storage/' + getConversationPartner(conv).photo" class="w-12 h-12 rounded-full object-cover">
                                </template>
                                <template x-if="!getConversationPartner(conv)?.photo">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 font-bold border border-slate-100 dark:border-slate-700">
                                        <span x-text="getConversationPartner(conv)?.name.charAt(0)"></span>
                                    </div>
                                </template>
                                <!-- Unread dot could be here -->
                                <template x-if="hasUnreadMessages(conv)">
                                    <span class="absolute top-0 right-0 w-3 h-3 bg-primary border-2 border-white dark:border-slate-800 rounded-full"></span>
                                </template>
                            </div>
                            
                            <!-- Preview Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <span class="font-bold text-slate-900 dark:text-white truncate" x-text="getConversationPartner(conv)?.name"></span>
                                    <span class="text-[10px] text-slate-400 shrink-0" x-text="formatMessageTime(conv.last_message?.created_at)"></span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="conv.last_message?.content || `{{ __('chat.start_conversation') }}`"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Main Area: Active Chat Window -->
        <div class="flex-1 flex flex-col h-full bg-slate-50/50 dark:bg-slate-900/10 overflow-hidden" :class="activeConversationId ? 'flex' : 'hidden md:flex'">
            
            <template x-if="!activeConversationId">
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <div class="w-20 h-20 rounded-3xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-center text-primary/30 mb-6">
                        <span class="material-symbols-outlined text-5xl">forum</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('chat.choose_conversation') }}</h3>
                    <p class="text-sm max-w-xs">{{ __('chat.select_contact') }}</p>
                </div>
            </template>
            
            <template x-if="activeConversationId">
                <div class="flex flex-col h-full bg-white dark:bg-slate-800">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 md:rounded-tr-3xl flex items-center shrink-0">
                        <button @click="activeConversationId = null" class="md:hidden mr-3 w-8 h-8 flex items-center justify-center text-slate-500">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                        
                        <div class="flex items-center gap-3">
                            <template x-if="activePartner?.photo">
                                <img :src="'/storage/' + activePartner.photo" class="w-10 h-10 rounded-full object-cover">
                            </template>
                            <template x-if="!activePartner?.photo">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 font-bold border border-slate-100 dark:border-slate-700">
                                    <span x-text="activePartner?.name.charAt(0)"></span>
                                </div>
                            </template>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white leading-tight" x-text="activePartner?.name"></h3>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold" x-text="roleLabels[activePartner?.role] || activePartner?.role"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Area -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messagesScrollArea" x-ref="msgArea">
                        <template x-for="(msg, index) in messages" :key="msg.id">
                            <div class="flex w-full" :class="msg.sender_id === user.id ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[85%] md:max-w-[70%] lg:max-w-[60%] flex flex-col" :class="msg.sender_id === user.id ? 'items-end' : 'items-start'">
                                    <!-- Message Bubble -->
                                    <div class="px-4 py-3 rounded-2xl text-sm break-words relative shadow-sm"
                                         :class="msg.sender_id === user.id 
                                            ? 'bg-primary text-white rounded-tr-none' 
                                            : 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white rounded-tl-none border border-slate-100 dark:border-slate-600'">
                                        <p x-text="msg.content"></p>
                                        <div class="flex justify-end mt-1">
                                            <span class="text-[9px] font-medium opacity-60" x-text="formatMessageTime(msg.created_at)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Chat Input -->
                    <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700 md:rounded-br-3xl shrink-0">
                        <form @submit.prevent="sendMessage()" class="flex gap-2 p-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl shadow-lg focus-within:border-primary transition-all">
                            <input type="text" 
                                   x-model="newMessage" 
                                   placeholder="{{ __('chat.type_message') }}" 
                                   class="flex-1 bg-transparent border-none focus:ring-0 text-sm py-2 px-2 text-slate-900 dark:text-white"
                                   :disabled="sendingMessage">
                            <button type="submit" 
                                    :disabled="!newMessage.trim() || sendingMessage"
                                    class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center hover:bg-primary-dark transition-colors disabled:opacity-50">
                                <span class="material-symbols-outlined text-[20px]" x-show="!sendingMessage">send</span>
                                <span class="material-symbols-outlined animate-spin text-[20px]" x-show="sendingMessage">sync</span>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- New Chat Modal (Contact List) -->
    <div x-show="showNewChatModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showNewChatModal = false"></div>
        <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden relative"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('chat.new_contact') }}</h3>
                <button @click="showNewChatModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-2 overflow-y-auto max-h-[60vh]">
                <div class="p-4">
                    <div class="relative mb-4">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input type="text" x-model="contactSearchQuery" placeholder="{{ __('chat.search_contact') }}" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-xl text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    
                    <div class="space-y-1">
                        <template x-if="loadingContacts">
                            <div class="space-y-4 py-4">
                                <template x-for="i in 3">
                                    <div class="flex items-center gap-3 animate-pulse">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700"></div>
                                        <div class="h-3 bg-slate-100 dark:bg-slate-700 rounded w-1/2"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-for="contact in filteredContacts" :key="contact.id">
                            <button @click="startChatWith(contact)" 
                                    class="w-full text-left p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors flex items-center gap-3">
                                <template x-if="contact.photo">
                                    <img :src="'/storage/' + contact.photo" class="w-10 h-10 rounded-full object-cover">
                                </template>
                                <template x-if="!contact.photo">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 font-bold border border-slate-100 dark:border-slate-700">
                                        <span x-text="contact.name.charAt(0)"></span>
                                    </div>
                                </template>
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white" x-text="contact.name"></div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold" x-text="roleLabels[contact.role] || contact.role"></div>
                                </div>
                            </button>
                        </template>
                        
                        <template x-if="!loadingContacts && filteredContacts.length === 0">
                            <div class="py-8 text-center text-slate-400 italic text-sm">{{ __('chat.no_contacts_found') }}</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
