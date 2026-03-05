        <!-- Topbar -->
        <header class="fixed top-0 left-0 right-0 md:sticky md:relative md:left-auto md:right-auto z-40 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 md:px-6 justify-between shrink-0">
            <!-- Left side (Empty on mobile for centering, hamburger on desktop if needed, though sidebar is sticky) -->
            <div class="flex items-center w-1/3">
                <button @click="isMobileMenuOpen = true" class="hidden text-slate-500 hover:text-slate-900 dark:hover:text-white focus:outline-none">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
            
            <!-- Center: Title -->
            <div class="flex items-center justify-center w-1/3 text-center">
                <h1 class="text-xl md:text-xl font-bold text-slate-800 dark:text-white truncate" x-text="getPageTitle()"></h1>
            </div>

            <!-- Right side: Date (Desktop) or Profile Pic (Mobile) -->
            <div class="flex items-center justify-end w-1/3 gap-3">
                <div class="text-sm text-slate-500 hidden sm:block" x-text="new Date().toLocaleDateString(locale, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></div>
                <button @click="$dispatch('open-profile-modal')" class="md:hidden w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700">
                    <template x-if="user?.photo">
                        <img :src="'/storage/' + user.photo" @@error="user.photo = null" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!user?.photo">
                        <div class="w-full h-full flex items-center justify-center bg-primary/10 text-primary font-bold text-xs" x-text="user?.name ? user.name.charAt(0) : ''"></div>
                    </template>
                </button>
            </div>
        </header>

        <!-- Banner Impersonare Activa -->
        <template x-if="isImpersonating">
            <div class="bg-red-500 text-white px-4 py-3 flex items-center justify-between shrink-0 shadow-sm z-50 relative">
                <div class="flex items-center">
                    <span class="material-symbols-outlined mr-2">group_add</span>
                    <span class="font-semibold text-sm">{{ __('dash.impersonation.warning', ['name' => '']) }}<span x-text="user?.name"></span>.</span>
                </div>
                <button @click="leaveImpersonation()" class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold py-1.5 px-4 rounded-lg transition-colors flex items-center">
                    <span class="material-symbols-outlined text-[16px] mr-1">exit_to_app</span>
                    {{ __('dash.impersonation.leave') }}
                </button>
            </div>
        </template>
