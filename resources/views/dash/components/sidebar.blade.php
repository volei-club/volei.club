    <!-- Sidebar Layout -->
    <aside :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
           class="w-64 bg-sidebar-light dark:bg-sidebar-dark border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 fixed md:relative z-40 inset-y-0 left-0 md:translate-x-0" 
           x-show="!isLoading" style="display: none;">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white mr-3">
                <span class="material-symbols-outlined text-sm">sports_volleyball</span>
            </div>
            <span class="text-lg font-bold tracking-tight">Volei.Club</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            
            <!-- Home -->
            <a href="/dash" @click.prevent="navigate('/dash'); isMobileMenuOpen = false;" 
               :class="currentPage === '/dash' ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
               class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-4">
                <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage === '/dash' ? 'fill-1' : ''">dashboard</span>
                Acasă
            </a>

            <!-- Admin Section -->
            <template x-if="user?.role === 'administrator'">
                <div>
                    <div class="px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Administrare</div>
                    
                    <a href="/dash/cluburi" @click.prevent="navigate('/dash/cluburi'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/cluburi') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/cluburi') ? 'fill-1' : ''">domain</span>
                        Cluburi
                    </a>

                    <a href="/dash/grupe" @click.prevent="navigate('/dash/grupe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/grupe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/grupe') ? 'fill-1' : ''">diversity_3</span>
                        Grupe
                    </a>

                    <a href="/dash/echipe" @click.prevent="navigate('/dash/echipe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/echipe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/echipe') ? 'fill-1' : ''">groups_2</span>
                        Echipe
                    </a>
                </div>
            </template>

            <!-- Management Section (Admins & Managers) -->
            <template x-if="user?.role === 'administrator' || user?.role === 'manager'">
                <div :class="user?.role === 'administrator' ? 'mt-4' : ''">
                    <div x-show="user?.role === 'manager'" class="px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Clubul Meu</div>
                    <a href="/dash/utilizatori" @click.prevent="navigate('/dash/utilizatori'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/utilizatori') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/utilizatori') ? 'fill-1' : ''">groups</span>
                        Membrii
                    </a>
                    
                    <a x-show="user?.role === 'manager'" href="/dash/grupe" @click.prevent="navigate('/dash/grupe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/grupe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/grupe') ? 'fill-1' : ''">diversity_3</span>
                        Grupele Mele
                    </a>

                    <a x-show="user?.role === 'manager'" href="/dash/echipe" @click.prevent="navigate('/dash/echipe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/echipe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/echipe') ? 'fill-1' : ''">groups_2</span>
                        Echipele Mele
                    </a>
                </div>
            </template>
            
        </nav>

        <!-- User Profile Area (Bottom of Sidebar) -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center w-full">
                <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold shrink-0">
                    <span x-text="user?.name.charAt(0)"></span>
                </div>
                <div class="ml-3 truncate flex-1">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate" x-text="user?.name"></p>
                    <p class="text-xs text-slate-500 capitalize truncate" x-text="user?.role"></p>
                </div>
                <button @click="logout()" class="ml-2 text-slate-400 hover:text-red-500 transition-colors bg-slate-100 dark:bg-slate-800 p-2 rounded-lg" title="Deconectare">
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </div>

    </aside>
