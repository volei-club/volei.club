            <!-- USERS VIEW -->
            <div x-show="currentPage.startsWith('/dash/membri')" x-data="userManager()" class="h-full flex flex-col relative">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __('members.title') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('members.subtitle') }}</p>
                    </div>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none flex items-center justify-center sm:justify-start gap-2">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        <span>{{ __('members.add') }}</span>
                    </button>
                </div>

                <!-- Loading Overlay -->
                <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500 font-medium">{{ __('members.loading') }}</p>
                </div>

                <!-- Filtre -->
                <div class="mb-6 space-y-4">
                    <!-- Visual Role Filter -->
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="filters.role = ''; fetchUsers(); updateHash()"
                            :class="filters.role === '' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]">group</span>
                            <span>{{ __('members.roles_filter.all') }}</span>
                        </button>

                        <template x-if="user?.role === 'administrator'">
                            <button 
                                @click="filters.role = 'administrator'; fetchUsers(); updateHash()"
                                :class="filters.role === 'administrator' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                                <span>{{ __('members.roles_filter.admin') }}</span>
                            </button>
                        </template>

                        <template x-if="user?.role === 'administrator'">
                            <button 
                                @click="filters.role = 'manager'; fetchUsers(); updateHash()"
                                :class="filters.role === 'manager' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                                <span>{{ __('members.roles_filter.manager') }}</span>
                            </button>
                        </template>

                        <button 
                            @click="filters.role = 'antrenor'; fetchUsers(); updateHash()"
                            :class="filters.role === 'antrenor' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]">psychology_alt</span>
                            <span>{{ __('members.roles_filter.coach') }}</span>
                        </button>

                        <button 
                            @click="filters.role = 'parinte'; fetchUsers(); updateHash()"
                            :class="filters.role === 'parinte' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]">family_restroom</span>
                            <span>{{ __('members.roles_filter.parent') }}</span>
                        </button>

                        <button 
                            @click="filters.role = 'sportiv'; fetchUsers(); updateHash()"
                            :class="filters.role === 'sportiv' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]">fitness_center</span>
                            <span>{{ __('members.roles_filter.student') }}</span>
                        </button>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:flex-1 relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input 
                                type="text" 
                                x-model="search" 
                                @input.debounce.500ms="fetchUsers()" 
                                placeholder="{{ __('members.search_placeholder') }}" 
                                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"
                            >
                        </div>

                        <template x-if="user?.role === 'administrator'">
                            <div class="w-full md:w-64">
                                <select id="userFilterClub" x-model="filters.club_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                    <option value="">{{ __('members.filters.all_clubs') }}</option>
                                    <template x-for="c in availableClubs" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <div class="w-full md:w-64" x-show="user?.role === 'manager' || (user?.role === 'administrator' && filters.club_id)">
                            <select id="userFilterTeam" x-model="filters.team_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">{{ __('members.filters.all_teams') }}</option>
                                <template x-for="t in availableFilterTeams" :key="t.id">
                                    <option :value="t.id" x-text="t.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="w-full md:w-64" x-show="filters.team_id">
                            <select id="userFilterSquad" x-model="filters.squad_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">{{ __('members.filters.all_squads') }}</option>
                                <template x-for="s in availableFilterSquads" :key="s.id">
                                    <option :value="s.id" x-text="s.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabel & Carduri -->
                <div class="bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                    <th class="px-6 py-4 font-bold">{{ __('members.table.name_email') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('members.table.role_status') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('members.table.club') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('members.table.subscription') }}</th>
                                    <th class="px-6 py-4 font-bold text-right">{{ __('members.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <template x-for="usr in users" :key="usr.id">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <template x-if="usr.photo">
                                                    <img :src="'/storage/' + usr.photo" class="w-10 h-10 rounded-xl object-cover border border-slate-100 dark:border-slate-700">
                                                </template>
                                                <template x-if="!usr.photo">
                                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 font-bold border border-slate-100 dark:border-slate-700">
                                                        <span x-text="usr.name.charAt(0)"></span>
                                                    </div>
                                                </template>
                                                <div>
                                                    <div class="font-bold text-slate-900 dark:text-white" x-text="usr.name"></div>
                                                    <div class="text-slate-500 text-xs mb-1" x-text="usr.email"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Relationships (Desktop) -->
                                            <div class="mt-2 space-y-1">
                                                <template x-if="usr.role === 'parinte' && usr.children?.length > 0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider w-12 shrink-0">{{ __('members.relations.children') }}:</span>
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="child in usr.children" :key="child.id">
                                                                <span class="text-[10px] px-1.5 py-0.5 bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-800/50 rounded-md font-bold uppercase tracking-tight" x-text="child.name"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                
                                                <template x-if="usr.role === 'sportiv' && usr.parents?.length > 0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider w-12 shrink-0">{{ __('members.relations.parents') }}:</span>
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="p in usr.parents" :key="p.id">
                                                                <span class="text-[10px] px-1.5 py-0.5 bg-teal-50/50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 border border-teal-100/50 dark:border-teal-800/50 rounded-md font-bold uppercase tracking-tight" x-text="p.name"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                
                                                <template x-if="(usr.role === 'sportiv' || usr.role === 'antrenor') && usr.squads?.length > 0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider w-12 shrink-0">{{ __('members.relations.squads') }}:</span>
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="s in usr.squads" :key="s.id">
                                                                <span class="text-[10px] px-1.5 py-0.5 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-md font-bold uppercase tracking-tight" x-text="s.name"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-600/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="roleLabels[usr.role] || usr.role"></span>
                                            <div class="mt-1">
                                                <span x-show="usr.is_active" class="flex items-center text-xs text-green-600 dark:text-green-400 font-semibold"><span class="w-2 h-2 rounded-full bg-green-500 mr-1.5"></span>{{ __('members.status.active') }}</span>
                                                <span x-show="!usr.is_active" class="flex items-center text-xs text-red-600 dark:text-red-400 font-semibold"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>{{ __('members.status.inactive') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <template x-if="usr.club">
                                                <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="usr.club.name"></span>
                                            </template>
                                            <template x-if="!usr.club">
                                                <span class="text-slate-400 text-xs italic">-</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4">
                                            <template x-if="usr.role === 'sportiv'">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="usr.active_subscription">
                                                        <span :class="{
                                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800': usr.active_subscription.status === 'active_paid',
                                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800': usr.active_subscription.status === 'active_pending'
                                                        }" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold border" :title="statusLabels[usr.active_subscription.status]">
                                                            <span class="material-symbols-outlined text-[14px] mr-1" x-text="usr.active_subscription.status === 'active_paid' ? 'check_circle' : 'pending_actions'"></span>
                                                            <span x-text="statusLabels[usr.active_subscription.status]"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!usr.active_subscription && usr.upcoming_subscription">
                                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-xs font-bold border border-blue-200 dark:border-blue-800" :title="'{{ __('members.status.scheduled') }} ' + formatDate(usr.upcoming_subscription.starts_at)">
                                                            <span class="material-symbols-outlined text-[14px] mr-1">schedule</span>
                                                            {{ __('members.status.scheduled') }}
                                                        </span>
                                                    </template>
                                                    <template x-if="!usr.active_subscription && !usr.upcoming_subscription">
                                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold border border-red-200 dark:border-red-800" title="{{ __('members.status.no_subscription') }}">
                                                            <span class="material-symbols-outlined text-[14px] mr-1">cancel</span>
                                                            {{ __('members.status.inactive') }}
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="usr.role !== 'sportiv'">
                                                <span class="text-slate-400 text-xs italic">-</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-1">
                                                <template x-if="usr.role === 'sportiv'">
                                                    <div class="flex gap-1">
                                                        <button @click="openSubscriptionModal(usr)" class="p-2 text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/30 rounded-lg transition-colors" title="{{ __('members.actions.manage_subscription') }}">
                                                            <span class="material-symbols-outlined text-[20px]">loyalty</span>
                                                        </button>
                                                        <button @click="openSubscriptionHistory(usr)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="{{ __('members.actions.subscription_history') }}">
                                                            <span class="material-symbols-outlined text-[20px]">history</span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <button @click="openModal(usr)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="{{ __('members.actions.edit_member') }}">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                                    <button @click="impersonateUser(usr)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="{{ __('members.actions.login_as') }}">
                                                        <span class="material-symbols-outlined text-[20px]">login</span>
                                                    </button>
                                                </template>
                                                <button @click="deleteUser(usr.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ __('members.actions.delete_member') }}">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="grid grid-cols-1 gap-4 md:hidden">
                        <template x-for="usr in users" :key="usr.id">
                            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm relative group flex flex-col" :class="usr.role === 'sportiv' && !usr.active_subscription ? 'ring-2 ring-red-500/10 border-red-100 dark:border-red-900/30' : ''">
                                
                                <!-- Card Header -->
                                <div class="flex items-start gap-4 mb-5">
                                    <div class="relative shrink-0">
                                        <template x-if="usr.photo">
                                            <img :src="'/storage/' + usr.photo" class="w-16 h-16 rounded-[1.25rem] object-cover border-2 border-slate-50 dark:border-slate-700 shadow-md">
                                        </template>
                                        <template x-if="!usr.photo">
                                            <div class="w-16 h-16 rounded-[1.25rem] bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400 font-bold border-2 border-slate-50 dark:border-slate-700 shadow-md">
                                                <span class="text-2xl" x-text="usr.name.charAt(0)"></span>
                                            </div>
                                        </template>
                                        <div x-show="usr.is_active" class="absolute -bottom-1 -right-1 w-5 h-5 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-sm">
                                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0 pt-1">
                                        <h4 class="font-extrabold text-xl text-slate-900 dark:text-white leading-tight truncate" x-text="usr.name"></h4>
                                        <p class="text-slate-500 text-xs font-medium truncate mb-3" x-text="usr.email"></p>
                                        
                                        <!-- Primary Badges Row -->
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 rounded-lg text-[10px] font-bold uppercase tracking-wider" x-text="roleLabels[usr.role] || usr.role"></span>
                                            
                                            <template x-if="usr.role === 'sportiv'">
                                                <div class="flex items-center">
                                                    <template x-if="usr.active_subscription">
                                                        <span :class="{
                                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200/50': usr.active_subscription.status === 'active_paid',
                                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200/50': usr.active_subscription.status === 'active_pending'
                                                        }" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold border">
                                                            <span class="material-symbols-outlined text-[12px] mr-1" x-text="usr.active_subscription.status === 'active_paid' ? 'check_circle' : 'pending_actions'"></span>
                                                            <span x-text="usr.active_subscription.status === 'active_paid' ? '{{ __('members.status.paid') }}' : '{{ __('members.status.pending') }}'"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!usr.active_subscription">
                                                        <span class="inline-flex items-center px-2 py-0.5 bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100 rounded-lg text-[10px] font-bold">
                                                            <span class="material-symbols-outlined text-[12px] mr-1">warning</span> {{ __('members.status.no_subscription') }}
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Contextual Info (Associations) -->
                                <div class="space-y-3 mb-5 p-4 bg-slate-50/50 dark:bg-slate-900/20 rounded-2xl border border-slate-100/50 dark:border-slate-700/50">
                                    <template x-if="(usr.role === 'sportiv' || usr.role === 'antrenor') && usr.squads?.length > 0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 bg-blue-100/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">groups</span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 min-w-0">
                                                <template x-for="s in usr.squads" :key="s.id">
                                                    <span class="text-[10px] px-2 py-0.5 bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900 rounded-md font-bold uppercase tracking-tight shadow-sm" x-text="s.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="usr.role === 'parinte' && usr.children?.length > 0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 bg-indigo-100/50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">family_restroom</span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 min-w-0">
                                                <template x-for="child in usr.children" :key="child.id">
                                                    <span class="text-[10px] px-2 py-0.5 bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 rounded-md font-bold uppercase tracking-tight shadow-sm" x-text="child.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <template x-if="usr.role === 'sportiv' && usr.parents?.length > 0">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 bg-teal-100/50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">child_care</span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 min-w-0">
                                                <template x-for="p in usr.parents" :key="p.id">
                                                    <span class="text-[10px] px-2 py-0.5 bg-white dark:bg-slate-800 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900 rounded-md font-bold uppercase tracking-tight shadow-sm" x-text="p.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 bg-slate-100/50 dark:bg-slate-800/50 text-slate-500 rounded-lg flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[16px]">apartment</span>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide" x-text="usr.club ? usr.club.name : '-'"></span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700/50 space-y-2">
                                    <!-- Sportiv-only actions -->
                                    <template x-if="usr.role === 'sportiv'">
                                        <div class="flex gap-2">
                                            <button @click="openSubscriptionModal(usr)" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-xl font-bold text-sm transition-colors hover:bg-orange-100 dark:hover:bg-orange-900/40 border border-orange-100/50 dark:border-orange-800/50">
                                                <span class="material-symbols-outlined text-[18px]">loyalty</span>
                                                {{ __('members.table.subscription') }}
                                            </button>
                                            <button @click="openSubscriptionHistory(usr)" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl font-bold text-sm transition-colors hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-100/50 dark:border-blue-800/50">
                                                <span class="material-symbols-outlined text-[18px]">history</span>
                                                {{ __('members.actions.subscription_history') }}
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Admin impersonate -->
                                    <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                        <button @click="impersonateUser(usr)" class="w-full flex items-center justify-center gap-2 py-2.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-xl font-bold text-sm transition-colors hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border border-indigo-100/50 dark:border-indigo-800/50">
                                            <span class="material-symbols-outlined text-[18px]">login</span>
                                            {{ __('members.actions.impersonate') }}
                                        </button>
                                    </template>

                                    <!-- Edit + Delete row -->
                                    <div class="flex gap-2">
                                        <button @click="openModal(usr)" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-primary/10 text-primary hover:bg-primary/20 rounded-xl font-bold text-sm transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                            {{ __('admin.edit') }}
                                        </button>
                                        <button @click="deleteUser(usr.id)" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-xl font-bold text-sm transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            {{ __('admin.delete') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="users.length === 0 && !loading" class="text-center py-20 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 border-dashed">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700 mb-4">group_off</span>
                        <p class="text-slate-500">{{ __('members.messages.empty_state') }}</p>
                    </div>

                    <!-- Pagination -->
                    <div x-show="pagination.last_page > 1" class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                            {{ __('admin.pagination.page_of', ['current' => '<span x-text="pagination.current_page"></span>', 'last' => '<span x-text="pagination.last_page"></span>']) }} 
                            <span class="mx-1">•</span> 
                            {{ __('members.messages.total_members', ['total' => '<span x-text="pagination.total"></span>']) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="changePage(pagination.current_page - 1)" 
                                :disabled="pagination.current_page === 1"
                                class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm"
                            >
                                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                            </button>
                            
                            <template x-for="p in Array.from({length: Math.min(5, pagination.last_page)}, (_, i) => {
                                if (pagination.last_page <= 5) return i + 1;
                                let start = Math.max(1, pagination.current_page - 2);
                                let end = Math.min(pagination.last_page, start + 4);
                                if (end === pagination.last_page) start = Math.max(1, end - 4);
                                return start + i;
                            })" :key="p">
                                <button 
                                    @click="changePage(p)" 
                                    :class="p === pagination.current_page ? 'bg-primary text-white border-primary shadow-md shadow-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm'"
                                    class="w-10 h-10 rounded-xl border font-bold text-sm transition-all"
                                    x-text="p"
                                ></button>
                            </template>

                            <button 
                                @click="changePage(pagination.current_page + 1)" 
                                :disabled="pagination.current_page === pagination.last_page"
                                class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm"
                            >
                                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Adăugare User -->
                <div x-show="showModal" 
                     @keydown.escape.window="showModal = false"
                     style="display: none;" 
                     class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? '{{ __('members.edit') }}' : '{{ __('members.add_new') }}'"></h3>
                        </div>
                        <form @submit.prevent="saveUser()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            
                            <!-- Photo Upload -->
                            <div class="flex flex-col items-center mb-6">
                                <div class="relative group">
                                    <template x-if="photoPreview || form.photo_url">
                                        <img :src="photoPreview || '/storage/' + form.photo_url" class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 dark:border-slate-900 shadow-xl">
                                    </template>
                                    <template x-if="!photoPreview && !form.photo_url">
                                        <div class="w-24 h-24 rounded-3xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center border-4 border-slate-50 dark:border-slate-900 shadow-xl">
                                            <span class="material-symbols-outlined text-4xl text-slate-300">person</span>
                                        </div>
                                    </template>
                                    <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-primary hover:bg-primary-dark text-white rounded-xl shadow-lg flex items-center justify-center cursor-pointer transition-all hover:scale-110 active:scale-95">
                                        <span class="material-symbols-outlined text-[20px]">add_a_photo</span>
                                        <input type="file" @change="handlePhotoSelect" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-[10px] font-bold text-slate-400 mt-3 uppercase tracking-wider">{{ __('members.form.photo') }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.form.name') }}</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.form.email') }}</label>
                                <input x-model="form.email" type="email" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.form.phone') }}</label>
                                <input x-model="form.phone" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all" placeholder="{{ __('members.form.phone_placeholder') }}"/>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.form.role') }}</label>
                                <select x-model="form.role" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected>{{ __('members.form.choose_role') }}</option>
                                    <template x-if="user?.role === 'administrator'">
                                        <option value="administrator">{{ __('members.roles_filter.admin') }} {{ __('members.roles_filter.global') }}</option>
                                    </template>
                                    <template x-if="user?.role === 'administrator'">
                                        <option value="manager">{{ __('members.roles_filter.manager') }}</option>
                                    </template>
                                    <option value="antrenor">{{ __('members.roles_filter.coach') }}</option>
                                    <option value="parinte">{{ __('members.roles_filter.parent') }}</option>
                                    <option value="sportiv">{{ __('members.roles_filter.student') }}</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    <span x-text="form.id ? '{{ __('members.form.password_new') }}' : '{{ __('members.form.password') }}'"></span>
                                </label>
                                <input x-model="form.password" type="password" placeholder="{{ __('members.form.password_placeholder') }}" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                                <p class="text-xs text-slate-500 mt-1">{{ __('members.form.password_hint') }}</p>
                            </div>

                            <div class="mb-5 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-green-500"></div>
                                    <span class="ml-3 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('members.form.is_active') }}</span>
                                </label>
                            </div>

                            <template x-if="user?.role === 'administrator' && form.role !== 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.form.assign_club') }}</label>
                                    <select x-model="form.club_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="">{{ __('members.form.no_club_selected') }}</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
                            <!-- Team Association (Checkboxes) -->
                            <template x-if="(form.role === 'antrenor' || form.role === 'sportiv') && (user?.role === 'manager' || form.club_id)">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('members.form.select_teams') }}</label>
                                    
                                    <template x-if="loadingTeams">
                                        <div class="text-xs text-slate-500 mb-2 flex items-center">
                                            <span class="material-symbols-outlined animate-spin text-sm mr-1">sync</span> {{ __('members.form.loading_teams') }}
                                        </div>
                                    </template>
                                    
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableTeams.length === 0 && !loadingTeams">
                                            <div class="text-slate-500 text-sm italic">{{ __('members.form.no_teams') }}</div>
                                        </template>
                                        
                                        <template x-for="t in availableTeams" :key="t.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="t.id" x-model="form.team_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300" x-text="t.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableTeams.length > 0 && !loadingTeams">
                                         <p class="text-xs text-slate-500 mt-2">{{ __('members.form.teams_hint') }}</p>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Squad Association (Checkboxes) -->
                            <template x-if="((form.role === 'antrenor' || form.role === 'sportiv') && form.team_ids.length > 0) || (form.role === 'antrenor' && (form.club_id || user?.role === 'manager'))">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('members.form.select_squads') }}</label>
                                    
                                    <template x-if="form.role === 'antrenor'">
                                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-tight mb-2 bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg border border-amber-100 dark:border-amber-800/50">
                                            <span class="material-symbols-outlined text-[14px] align-middle mr-1">info</span>
                                            {{ __('members.form.coach_squads_hint') }}
                                        </p>
                                    </template>
                                    
                                    <template x-if="loadingSquads">
                                        <div class="text-xs text-slate-500 mb-2 flex items-center">
                                            <span class="material-symbols-outlined animate-spin text-sm mr-1">sync</span> {{ __('members.form.loading_squads') }}
                                        </div>
                                    </template>
                                    
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableSquads.length === 0 && !loadingSquads">
                                            <div class="text-slate-500 text-sm italic">{{ __('members.form.no_squads') }}</div>
                                        </template>
                                        
                                        <template x-for="s in availableSquads" :key="s.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="s.id" x-model="form.squad_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <div class="ml-3">
                                                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300" x-text="s.name"></span>
                                                    <span class="block text-xs text-slate-500" x-text="s.team?.name"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableSquads.length > 0 && !loadingSquads">
                                         <p class="text-xs text-slate-500 mt-2">{{ __('members.form.squads_hint') }}</p>
                                    </template>
                                </div>
                            </template>

                            <!-- Student Association for Parents (Checkboxes) -->
                            <template x-if="form.role === 'parinte' && (user?.role === 'manager' || form.club_id)">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('members.form.associate_students') }}</label>
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableStudents.length === 0">
                                            <div class="text-slate-500 text-sm italic text-center py-4">{{ __('members.form.no_students') }}</div>
                                        </template>
                                        <template x-for="s in availableStudents" :key="s.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="s.id" x-model="form.child_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300" x-text="s.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableStudents.length > 0">
                                        <p class="text-xs text-slate-500 mt-2">{{ __('members.form.parent_students_hint') }}</p>
                                    </template>
                                </div>
                            </template>
                            
                            <template x-if="error">
                                <div class="p-3 mb-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100" x-text="error"></div>
                            </template>
                            
                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">{{ __('admin.cancel') }}</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                    <span x-show="saving" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                    {{ __('admin.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Abonament Sportiv -->
                <div x-show="showSubscriptionModal" 
                     @keydown.escape.window="showSubscriptionModal = false"
                     style="display: none;" 
                     class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1" x-text="subscriptionForm.id ? '{{ __('members.subscription.edit_title') }}' : '{{ __('members.subscription.title') }}'"></h3>
                            <p class="text-sm font-semibold text-slate-500" x-text="subscriptionForm.user_name"></p>
                        </div>
                        <form @submit.prevent="saveUserSubscription()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                                
                                <template x-if="subscriptionForm.current_subscription">
                                    <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('members.subscription.current') }}</span>
                                            <template x-if="subscriptionForm.current_subscription.status === 'active_paid'">
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-[10px] font-bold">{{ __('members.status.paid') }}</span>
                                            </template>
                                            <template x-if="subscriptionForm.current_subscription.status === 'active_pending'">
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded text-[10px] font-bold">{{ __('members.status.pending') }}</span>
                                            </template>
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white mb-1" x-text="subscriptionForm.current_subscription.subscription.name"></div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                                            {{ __('members.subscription.expires_at') }}: <strong x-text="new Date(subscriptionForm.current_subscription.expires_at).toLocaleDateString()"></strong>
                                        </div>

                                        <template x-if="subscriptionForm.current_subscription.status === 'active_pending'">
                                            <button type="button" @click="updateSubscriptionStatus(subscriptionForm.current_subscription.id, 'active_paid')" :disabled="savingSubscription" class="w-full py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold text-sm transition-colors flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[16px] mr-1.5">check_circle</span>
                                                {{ __('members.subscription.mark_paid') }}
                                            </button>
                                        </template>

                                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 text-center">
                                            <button type="button" @click="updateSubscriptionStatus(subscriptionForm.current_subscription.id, 'cancelled')" :disabled="savingSubscription" class="text-red-500 hover:text-red-600 text-xs font-bold uppercase tracking-wider transition-colors inline-block pb-1">{{ __('members.subscription.cancel_current') }}</button>
                                        </div>
                                    </div>
                                </template>

                                <div class="mb-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-3" x-text="subscriptionForm.current_subscription ? '{{ __('members.subscription.generate_new') }}' : '{{ __('members.subscription.associate_new') }}'"></h4>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.subscription.type') }}</label>
                                            <select x-model="subscriptionForm.subscription_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                                <option value="" disabled selected>{{ __('members.subscription.choose_type') }}</option>
                                                <template x-for="sub in availableSubscriptions" :key="sub.id">
                                                    <option :value="sub.id" x-text="sub.name + ' (' + sub.price + ' {{ __('dash.units.currency') }} / ' + sub.period.replace('_', ' ') + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.subscription.starts_at') }}</label>
                                            <input type="date" x-model="subscriptionForm.starts_at" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('members.subscription.status') }}</label>
                                            <select x-model="subscriptionForm.status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                                <option value="active_paid">{{ __('members.status.paid') }} ({{ __('members.status.active') }})</option>
                                                <option value="active_pending">{{ __('members.status.pending') }}</option>
                                                <option value="cancelled">{{ __('members.status.inactive') }} ({{ __('members.status.cancelled') }})</option>
                                                <option value="expired">{{ __('members.status.inactive') }} ({{ __('members.status.expired') }})</option>
                                            </select>
                                        </div>
                                    </div>
                                    <template x-if="availableSubscriptions.length === 0">
                                        <p class="text-xs text-red-500 mt-1">{{ __('members.subscription.no_types_defined') }}</p>
                                    </template>
                                </div>

                                <template x-if="subscriptionError">
                                    <div class="p-3 my-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100 font-medium" x-text="subscriptionError"></div>
                                </template>
                            </div>

                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showSubscriptionModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">{{ __('admin.close') }}</button>
                                <template x-if="subscriptionForm.subscription_id">
                                    <button type="submit" :disabled="savingSubscription" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                        <span x-show="savingSubscription" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                        <span x-text="subscriptionForm.id ? '{{ __('members.subscription.update_btn') }}' : '{{ __('members.subscription.issue_btn') }}'"></span>
                                    </button>
                                </template>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Subscription History Modal -->
                <div x-show="showSubscriptionHistoryModal" 
                     @keydown.escape.window="showSubscriptionHistoryModal = false"
                     class="fixed inset-0 z-[70] overflow-y-auto" 
                     x-cloak style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showSubscriptionHistoryModal" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="showSubscriptionHistoryModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="inline-block w-full max-w-2xl p-0 my-4 sm:my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-2xl sm:rounded-3xl border border-slate-100 dark:border-slate-700">
                            
                            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl shadow-sm border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined">history</span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="historyUser ? '{{ __('members.history.user_title', ['name' => "'+historyUser.name+'"]) }}' : '{{ __('members.history.title') }}'"></h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('members.history.subtitle') }}</p>
                                    </div>
                                </div>
                                <button @click="showSubscriptionHistoryModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="px-6 py-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                <template x-if="historyUser && historyUser.subscriptions && historyUser.subscriptions.length > 0">
                                    <div class="space-y-3">
                                        <template x-for="sub in [...historyUser.subscriptions].sort((a,b) => new Date(b.created_at) - new Date(a.created_at))" :key="sub.id">
                                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-700 hover:shadow-md transition-all bg-white dark:bg-slate-800/50 group">
                                                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                                                    <div class="flex gap-3">
                                                        <div :class="{
                                                            'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': sub.status === 'active_paid',
                                                            'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': sub.status === 'active_pending',
                                                            'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-500': sub.status === 'expired',
                                                            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': sub.status === 'cancelled'
                                                        }" class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-700 shadow-sm">
                                                            <span class="material-symbols-outlined" x-text="sub.status === 'active_paid' ? 'check_circle' : (sub.status === 'active_pending' ? 'pending_actions' : (sub.status === 'expired' ? 'history' : 'cancel'))"></span>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900 dark:text-white" x-text="sub.subscription ? sub.subscription.name : '{{ __('members.subscription.deleted') }}'"></div>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="formatDate(sub.starts_at)"></span>
                                                                <span class="text-slate-300">→</span>
                                                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="formatDate(sub.expires_at)"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-left sm:text-right flex flex-col items-start sm:items-end w-full sm:w-auto mt-2 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-0 border-slate-100 dark:border-slate-700/50">
                                                        <span :class="{
                                                            'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-400 border-green-100 dark:border-green-800': sub.status === 'active_paid',
                                                            'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border-amber-100 dark:border-amber-800': sub.status === 'active_pending',
                                                            'bg-slate-50 text-slate-600 dark:bg-slate-700/40 dark:text-slate-400 border-slate-100 dark:border-slate-700': sub.status === 'expired',
                                                            'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-400 border-red-100 dark:border-red-800': sub.status === 'cancelled'
                                                        }" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border shadow-sm" x-text="statusLabels[sub.status] || sub.status"></span>
                                                        <div class="text-[10px] text-slate-400 mt-2">{{ __('members.history.created_at', ['date' => "'+formatDate(sub.created_at)+'"]) }}</div>
                                                        
                                                        <div class="flex items-center gap-4 sm:gap-3 mt-3 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button @click="editUserSubscription(sub)" class="text-[11px] sm:text-[10px] font-bold text-blue-500 hover:text-blue-600 uppercase tracking-wider flex items-center transition-colors py-1">
                                                                <span class="material-symbols-outlined text-[16px] sm:text-[14px] mr-1">edit</span> {{ __('admin.edit') }}
                                                            </button>
                                                            <button @click="deleteUserSubscription(sub.id)" class="text-[11px] sm:text-[10px] font-bold text-red-500 hover:text-red-600 uppercase tracking-wider flex items-center transition-colors py-1">
                                                                <span class="material-symbols-outlined text-[16px] sm:text-[14px] mr-1">delete</span> {{ __('admin.delete') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!historyUser || !historyUser.subscriptions || historyUser.subscriptions.length === 0">
                                    <div class="text-center py-16">
                                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200 dark:border-slate-700">
                                            <span class="material-symbols-outlined text-4xl text-slate-300">subscriptions</span>
                                        </div>
                                        <h4 class="text-slate-900 dark:text-white font-bold mb-1">{{ __('members.history.no_subscriptions') }}</h4>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('members.history.empty_desc') }}</p>
                                    </div>
                                </template>
                            </div>

                            <div class="px-6 py-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/20 flex justify-end">
                                <button @click="showSubscriptionHistoryModal = false" class="px-8 py-3 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 rounded-2xl transition-all border border-slate-200 dark:border-slate-600 shadow-sm hover:shadow-md active:scale-95">
                                    {{ __('admin.close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>