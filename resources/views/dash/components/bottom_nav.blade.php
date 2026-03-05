<!-- Bottom Navigation (Mobile Only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 z-50 pb-safe pb-4">
    <div class="flex justify-around items-center h-16 px-2">
        
        <!-- Acasa -->
        <a href="/dash" @click.prevent="navigate('/dash')"
           class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
           :class="currentPage === '/dash' ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
            <span class="material-symbols-outlined text-[24px]" :class="currentPage === '/dash' ? 'fill-1' : ''">dashboard</span>
            <span class="text-[10px] font-semibold tracking-wide">{{ __('dash.nav.home') }}</span>
        </a>

        <!-- Calendar (Coaches/Admin) or Meciuri (Athlete) -->
        <template x-if="['antrenor', 'manager', 'administrator'].includes(user?.role)">
            <a href="/dash/calendar" @click.prevent="navigate('/dash/calendar')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
               :class="currentPage.startsWith('/dash/calendar') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/calendar') ? 'fill-1' : ''">event</span>
                <span class="text-[10px] font-semibold tracking-wide">{{ __('dash.nav.calendar') }}</span>
            </a>
        </template>
        <template x-if="['sportiv', 'parinte'].includes(user?.role)">
            <a href="/dash/meciuri" @click.prevent="navigate('/dash/meciuri')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
               :class="currentPage.startsWith('/dash/meciuri') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/meciuri') ? 'fill-1' : ''">sports_volleyball</span>
                <span class="text-[10px] font-semibold tracking-wide">{{ __('dash.nav.matches') }}</span>
            </a>
        </template>

        <!-- Membri (Admin/Manager) or Performanta (Athletes) -->
        <template x-if="['manager', 'administrator'].includes(user?.role)">
            <a href="/dash/membri" @click.prevent="navigate('/dash/membri')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
               :class="currentPage.startsWith('/dash/membri') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/membri') ? 'fill-1' : ''">groups</span>
                <span class="text-[10px] font-semibold tracking-wide" x-text="window.innerWidth < 380 ? `{{ __('dash.nav.members') }}` : `{{ __('dash.nav.members') }}`"></span>
            </a>
        </template>
        <template x-if="['sportiv', 'parinte'].includes(user?.role)">
            <a href="/dash/performanta" @click.prevent="navigate('/dash/performanta')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
               :class="currentPage.startsWith('/dash/performanta') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/performanta') ? 'fill-1' : ''">trending_up</span>
                <span class="text-[10px] font-semibold tracking-wide truncate max-w-full px-1" x-text="window.innerWidth < 380 ? `{{ __('dash.nav.performance_short') }}` : `{{ __('dash.nav.performance') }}`"></span>
            </a>
        </template>

        <!-- Mesaje (Toate Rolurile) -->
        <a href="/dash/mesaje" @click.prevent="navigate('/dash/mesaje')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors relative"
               :class="currentPage.startsWith('/dash/mesaje') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/mesaje') ? 'fill-1' : ''">chat</span>
                <span class="text-[10px] font-semibold tracking-wide">{{ __('dash.nav.messages') }}</span>
                <template x-if="unreadMessagesCount > 0">
                    <span class="absolute top-1 right-2 lg:right-4 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                </template>
            </a>

        <!-- Abonament (Athletes) or Meniu (Admin) -->
        <template x-if="['antrenor', 'manager', 'administrator'].includes(user?.role)">
            <button @click="isMobileMenuOpen = true"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors text-slate-500 hover:text-slate-800 dark:hover:text-slate-300 focus:outline-none">
                <span class="material-symbols-outlined text-[24px]">menu</span>
                <span class="text-[10px] font-semibold tracking-wide">{{ __('dash.nav.menu') }}</span>
            </button>
        </template>
        <template x-if="['sportiv', 'parinte'].includes(user?.role)">
            <a href="/dash/abonamente" @click.prevent="navigate('/dash/abonamente')"
               class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
               :class="currentPage.startsWith('/dash/abonamente') ? 'text-primary' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-[24px]" :class="currentPage.startsWith('/dash/abonamente') ? 'fill-1' : ''">loyalty</span>
                <span class="text-[10px] font-semibold tracking-wide truncate max-w-full px-1" x-text="window.innerWidth < 380 ? `{{ __('dash.nav.subscription_short') }}` : `{{ __('dash.nav.subscription') }}`"></span>
            </a>
        </template>

    </div>
</nav>
