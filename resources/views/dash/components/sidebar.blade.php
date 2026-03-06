    <!-- Sidebar Layout -->
    <aside :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
           class="w-64 bg-sidebar-light dark:bg-sidebar-dark border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 fixed md:sticky md:top-0 md:h-screen shrink-0 z-40 inset-y-0 left-0 md:translate-x-0" 
           x-show="!isLoading" style="display: none;">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white mr-3">
                <span class="material-symbols-outlined text-[20px]">sports_volleyball</span>
            </div>
            <span class="text-lg font-bold tracking-tight">Volei.Club</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            
            <!-- Home -->
            <a href="{{ '/' . app()->getLocale() . '/dash' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash' }}'); isMobileMenuOpen = false;" 
               :class="currentPage === '{{ '/' . app()->getLocale() . '/dash' }}' ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
               class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage === '{{ '/' . app()->getLocale() . '/dash' }}' ? 'fill-1' : ''">dashboard</span>
                {{ __('dash.nav.home') }}
            </a>

            <!-- Main Navigation Items -->

            <!-- Admin Section -->
            <template x-if="user?.role === 'administrator'">
                <div>
                    <div class="px-3 mb-4 mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dash.nav.administration') }}</div>
                    
                    <a href="{{ '/' . app()->getLocale() . '/dash/cluburi' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/cluburi' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/cluburi' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/cluburi' }}') ? 'fill-1' : ''">domain</span>
                        {{ __('dash.nav.clubs') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/grupe' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/grupe' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/grupe' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/grupe' }}') ? 'fill-1' : ''">diversity_3</span>
                        {{ __('dash.nav.groups') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/echipe' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/echipe' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/echipe' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/echipe' }}') ? 'fill-1' : ''">groups_2</span>
                        {{ __('dash.nav.teams') }}
                    </a>

            </template>

            <!-- Management Section (Admins & Managers) -->
            <template x-if="user?.role === 'administrator' || user?.role === 'manager'">
                <div :class="user?.role === 'administrator' ? 'mt-4' : ''">
                    <div x-show="user?.role === 'manager'" class="px-3 mb-4 mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dash.nav.my_club') }}</div>
                    <a href="{{ '/' . app()->getLocale() . '/dash/membri' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/membri' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/membri' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/membri' }}') ? 'fill-1' : ''">groups</span>
                        {{ __('dash.nav.members') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/locatii' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/locatii' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/locatii' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/locatii' }}') ? 'fill-1' : ''">location_on</span>
                        {{ __('dash.nav.locations') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/antrenamente' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/antrenamente' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/antrenamente' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/antrenamente' }}') ? 'fill-1' : ''">calendar_month</span>
                        {{ __('dash.nav.trainings') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/meciuri' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/meciuri' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'fill-1' : ''">sports_volleyball</span>
                        {{ __('dash.nav.matches') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/abonamente' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/abonamente' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/abonamente' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/abonamente' }}') ? 'fill-1' : ''">loyalty</span>
                        {{ __('dash.nav.subscriptions') }}
                    </a>
                    
                    <a x-show="user?.role === 'manager'" href="{{ '/' . app()->getLocale() . '/dash/grupe' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/grupe' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/grupe' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/grupe' }}') ? 'fill-1' : ''">diversity_3</span>
                        {{ __('dash.nav.my_groups') }}
                    </a>

                    <a x-show="user?.role === 'manager'" href="{{ '/' . app()->getLocale() . '/dash/echipe' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/echipe' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/echipe' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/echipe' }}') ? 'fill-1' : ''">groups_2</span>
                        {{ __('dash.nav.my_teams') }}
                    </a>

            </template>

            <!-- Alte Opțiuni (Mesaje, Audit, Sistem) -->
            <div class="mt-8">
                <div class="px-3 mb-4 mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dash.nav.resources_system') }}</div>
                
                <a href="{{ '/' . app()->getLocale() . '/dash/mesaje' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/mesaje' }}'); isMobileMenuOpen = false;" 
                    :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/mesaje' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                    <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/mesaje' }}') ? 'fill-1' : ''">chat</span>
                    <span class="flex-1">{{ __('dash.nav.messages') }}</span>
                    <template x-if="unreadMessagesCount > 0">
                        <span class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full" x-text="unreadMessagesCount"></span>
                    </template>
                </a>

                <template x-if="user?.role === 'administrator' || user?.role === 'manager'">
                    <a href="{{ '/' . app()->getLocale() . '/dash/audit' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/audit' }}'); isMobileMenuOpen = false;"
                        :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/audit' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/audit' }}') ? 'fill-1' : ''">history_edu</span>
                        {{ __('dash.nav.audit') }}
                    </a>
                </template>

                <template x-if="user?.role === 'administrator'">
                    <a href="{{ '/' . app()->getLocale() . '/dash/sistem' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/sistem' }}'); isMobileMenuOpen = false;"
                        :class="currentPage === '{{ '/' . app()->getLocale() . '/dash/sistem' }}' ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage === '{{ '/' . app()->getLocale() . '/dash/sistem' }}' ? 'fill-1' : ''">settings_suggest</span>
                        {{ __('dash.nav.system') }}
                    </a>
                </template>
            </div>
            
            <div class="flex-1"></div>

            <!-- Athlete / Parent Section (Activitatea Mea) -->
            <template x-if="['sportiv', 'parinte'].includes(user?.role)">
                <div class="mt-4">
                    <div class="px-3 mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dash.nav.my_activity') }}</div>
                    
                    <a href="{{ '/' . app()->getLocale() . '/dash/calendar' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/calendar' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/calendar' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/calendar' }}') ? 'fill-1' : ''">calendar_month</span>
                        {{ __('dash.nav.calendar') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/performanta' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/performanta' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/performanta' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/performanta' }}') ? 'fill-1' : ''">monitoring</span>
                        {{ __('dash.nav.performance') }}
                    </a>
                    
                    <a href="{{ '/' . app()->getLocale() . '/dash/meciuri' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/meciuri' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'fill-1' : ''">sports_volleyball</span>
                        {{ __('dash.nav.matches') }}
                    </a>

                    <a href="{{ '/' . app()->getLocale() . '/dash/abonamente' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/abonamente' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/abonamente' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/abonamente' }}') ? 'fill-1' : ''">loyalty</span>
                        {{ __('dash.nav.subscriptions') }}
                    </a>
                </div>
            </template>

            <!-- Calendar Section (Antrenori, Manageri, Admini) -->
            <template x-if="['administrator', 'manager', 'antrenor'].includes(user?.role)">
                <div class="mt-4">
                    <div class="px-3 mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dash.nav.activity') }}</div>
                    <a href="{{ '/' . app()->getLocale() . '/dash/calendar' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/calendar' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/calendar' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/calendar' }}') ? 'fill-1' : ''">calendar_month</span>
                        {{ __('dash.nav.calendar') }}
                    </a>
                    <a href="{{ '/' . app()->getLocale() . '/dash/performanta' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/performanta' }}'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/performanta' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/performanta' }}') ? 'fill-1' : ''">monitoring</span>
                        {{ __('dash.nav.performance') }}
                    </a>
                    
                    <a href="{{ '/' . app()->getLocale() . '/dash/meciuri' }}" @click.prevent="navigate('{{ '/' . app()->getLocale() . '/dash/meciuri' }}'); isMobileMenuOpen = false;"
                       x-show="['antrenor'].includes(user?.role)"
                       :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('{{ '/' . app()->getLocale() . '/dash/meciuri' }}') ? 'fill-1' : ''">sports_volleyball</span>
                        {{ __('dash.nav.matches') }}
                    </a>
                   
                </div>
            </template>
        </nav>

        <!-- User Profile Area (Bottom of Sidebar) -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-sidebar-light dark:bg-sidebar-dark">
            <div class="flex items-center w-full">
                <template x-if="user?.photo">
                    <img :src="'/storage/' + user.photo" class="w-10 h-10 rounded-full object-cover shrink-0">
                </template>
                <template x-if="!user?.photo">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold shrink-0">
                        <span x-text="user?.name.charAt(0)"></span>
                    </div>
                </template>
                <div class="ml-3 truncate flex-1">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate" x-text="user?.name"></p>
                    <p class="text-xs text-slate-500 capitalize truncate" x-text="roleLabels[user?.role] || user?.role"></p>
                </div>
                <button @click="$dispatch('open-profile-modal')" class="ml-2 text-slate-400 hover:text-primary transition-colors bg-slate-100 dark:bg-slate-800 p-2 rounded-lg" :title="'{{ __('dash.profile.settings') }}'">
                    <span class="material-symbols-outlined text-[20px]">person_edit</span>
                </button>
                <button @click="logout()" class="ml-2 text-slate-400 hover:text-red-500 transition-colors bg-slate-100 dark:bg-slate-800 p-2 rounded-lg" :title="'{{ __('dash.profile.logout') }}'">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </div>
        </div>
    </aside>
