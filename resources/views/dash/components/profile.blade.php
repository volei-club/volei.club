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
    
    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700"
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
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('profile.title') }}</h3>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">{{ __('profile.settings_label') }}</p>
                </div>
            </div>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="saveProfile" class="p-6 space-y-4">
            <!-- Photo Upload -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative group">
                    <template x-if="photoPreview || user?.photo">
                        <img :src="photoPreview || '/storage/' + user.photo" class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 dark:border-slate-900 shadow-xl">
                    </template>
                    <template x-if="!photoPreview && !user?.photo">
                        <div class="w-24 h-24 rounded-3xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center border-4 border-slate-50 dark:border-slate-900 shadow-xl">
                            <span class="material-symbols-outlined text-4xl text-slate-300">person</span>
                        </div>
                    </template>
                    <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-primary hover:bg-primary-dark text-white rounded-xl shadow-lg flex items-center justify-center cursor-pointer transition-all hover:scale-110 active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add_a_photo</span>
                        <input type="file" @change="handlePhotoSelect" class="hidden" accept="image/*">
                    </label>
                </div>
                <p class="text-[10px] font-bold text-slate-400 mt-3 uppercase tracking-wider">{{ __('profile.change_photo') }}</p>
            </div>

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">{{ __('profile.full_name') }}</label>
                <input type="text" x-model="formData.name" required
                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">{{ __('profile.email_address') }}</label>
                <input type="email" x-model="formData.email" required
                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-4">{{ __('profile.security_title') }}</p>
                
                <!-- Password -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">{{ __('profile.new_password') }}</label>
                        <input type="password" x-model="formData.password" placeholder="••••••••"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 px-1">{{ __('profile.confirm_password') }}</label>
                        <input type="password" x-model="formData.password_confirmation" placeholder="••••••••"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border-none focus:ring-2 focus:ring-primary/20 dark:text-white transition-all">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-3 pt-4">
                <button type="button" @click="showModal = false"
                        class="h-12 rounded-2xl font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all">
                    {{ __('admin.cancel') }}
                </button>
                <button type="submit" :disabled="saving"
                        class="h-12 rounded-2xl bg-primary hover:bg-primary-dark text-white font-bold shadow-lg shadow-primary/20 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="saving" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                    <span x-text="saving ? '{{ __('admin.saving') }}' : '{{ __('profile.save_profile') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
