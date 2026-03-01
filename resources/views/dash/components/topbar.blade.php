        <!-- Topbar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 md:px-6 justify-between shrink-0">
            <div class="flex items-center">
                <button @click="isMobileMenuOpen = true" class="md:hidden mr-4 text-slate-500 hover:text-slate-900 dark:hover:text-white focus:outline-none">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white" x-text="getPageTitle()"></h1>
            </div>
            <div class="text-sm text-slate-500 hidden sm:block" x-text="new Date().toLocaleDateString('ro-RO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></div>
        </header>

        <!-- Banner Impersonare Activa -->
        <template x-if="isImpersonating">
            <div class="bg-red-500 text-white px-4 py-3 flex items-center justify-between shrink-0 shadow-sm z-50 relative">
                <div class="flex items-center">
                    <span class="material-symbols-outlined mr-2">group_add</span>
                    <span class="font-semibold text-sm">Atenție! Acționați în contul unui alt utilizator (<span x-text="user?.name"></span>).</span>
                </div>
                <button @click="leaveImpersonation()" class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold py-1.5 px-4 rounded-lg transition-colors flex items-center">
                    <span class="material-symbols-outlined text-[16px] mr-1">exit_to_app</span>
                    Înapoi la contul tău
                </button>
            </div>
        </template>
