<div x-data="profileManager()" 
     @open-profile-modal.window="openModal()"
     x-show="showModal" 
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">
    
    <div @click.away="showModal = false" 
         class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined">person_edit</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Editează Profilul</h3>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Personal Settings</p>
                </div>
            </div>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="saveProfile" class="p-6 space-y-4">
            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">Nume Complet</label>
                <input type="text" x-model="formData.name" required
                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">Adresă Email</label>
                <input type="email" x-model="formData.email" required
                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-4">Securitate (Opțional)</p>
                
                <!-- Password -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">Parolă Nouă</label>
                        <input type="password" x-model="formData.password" placeholder="••••••••"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">Confirmă Parola</label>
                        <input type="password" x-model="formData.password_confirmation" placeholder="••••••••"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-3 pt-4">
                <button type="button" @click="showModal = false"
                        class="h-12 rounded-2xl font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all">
                    Anulează
                </button>
                <button type="submit" :disabled="saving"
                        class="h-12 rounded-2xl bg-primary hover:bg-primary-dark text-white font-bold shadow-lg shadow-primary/20 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="saving" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                    <span x-text="saving ? 'Se salvează...' : 'Salvează Profilul'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
