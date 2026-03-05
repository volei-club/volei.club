            <!-- SQUADS (ECHIPE) VIEW -->
            <div x-show="currentPage.startsWith('/dash/echipe')" x-data="squadManager()" class="h-full flex flex-col relative">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __('admin.squads.title') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin.squads.subtitle') }}</p>
                    </div>
                    <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
                        <span class="material-symbols-outlined text-[20px]">groups_2</span>
                        <span>{{ __('admin.squads.add') }}</span>
                    </button>
                </div>

                <div class="mb-6 flex flex-col md:flex-row gap-4 items-center">
                    <div class="w-full md:flex-1 relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input 
                            type="text" 
                            x-model="search" 
                            @input.debounce.500ms="fetchSquads()" 
                            placeholder="{{ __('admin.squads.search_placeholder') }}" 
                            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"
                        >
                    </div>

                    <template x-if="user?.role === 'administrator'">
                        <div class="w-full md:w-64">
                            <select x-model="filters.club_id" @change="fetchSquads(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">{{ __('admin.squads.all_clubs_filter') }}</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>


                <!-- Loading Overlay -->
                <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500 font-medium">{{ __('admin.squads.loading') }}</p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="px-6 py-4 font-bold">{{ __('admin.squads.name_column') }}</th>
                                <th class="px-6 py-4 font-bold">{{ __('admin.squads.belongs_to_team') }}</th>
                                <template x-if="user?.role === 'administrator'">
                                    <th class="px-6 py-4 font-bold">{{ __('admin.squads.club_column') }}</th>
                                </template>
                                <th class="px-6 py-4 font-bold text-right">{{ __('admin.squads.actions_column') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                            <template x-for="squad in squads" :key="squad.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white text-base" x-text="squad.name"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="squad.team?.name"></span>
                                    </td>
                                    <template x-if="user?.role === 'administrator'">
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="squad.team?.club?.name || `{{ __('admin.unknown') }}`"></span>
                                        </td>
                                    </template>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openPreview(squad)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="{{ __('admin.squads.preview_title') }}">
                                                <span class="material-symbols-outlined text-[20px]">groups</span>
                                            </button>
                                            <button @click="openModal(squad)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="{{ __('admin.edit') }}">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button @click="deleteSquad(squad.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ __('admin.delete') }}">
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
                <div class="md:hidden flex flex-col gap-4">
                    <template x-for="squad in squads" :key="squad.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                            <div class="p-5 space-y-4">
                                <h4 class="font-bold text-lg text-slate-900 dark:text-white" x-text="squad.name"></h4>
                                
                                <div class="space-y-2.5">
                                    <div class="flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        <span class="material-symbols-outlined text-[18px] mr-2 text-slate-400">groups</span>
                                        <span class="px-2 py-0.5 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[10px] font-bold uppercase tracking-wide" x-text="squad.team?.name"></span>
                                    </div>
                                    <template x-if="user?.role === 'administrator'">
                                        <div class="flex items-center text-sm font-semibold text-slate-500">
                                            <span class="material-symbols-outlined text-[18px] mr-2 text-slate-400">domain</span>
                                            <span class="px-2 py-0.5 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[10px] font-bold uppercase tracking-wide" x-text="squad.team?.club?.name || `{{ __('admin.unknown') }}`"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                                </div>
                            </div>

                            <div class="px-5 py-4 bg-slate-50/50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2">
                                <button @click="openPreview(squad)" class="flex-1 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>
                                    {{ __('admin.squads.view_squad') }}
                                </button>
                                <button @click="openModal(squad)" class="flex-1 py-2 bg-primary/10 text-primary hover:bg-primary/20 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                    {{ __('admin.edit') }}
                                </button>
                                <button @click="deleteSquad(squad.id)" class="flex-1 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                    {{ __('admin.delete') }}
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="squads.length === 0 && !loading" class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 border-dashed">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">groups_2</span>
                    <p class="text-slate-500">{{ __('admin.squads.empty_state') }}</p>
                </div>

                <!-- Pagination -->
                <div x-show="pagination.last_page > 1" class="mt-4 px-6 py-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                        <span x-text="`{{ __('admin.pagination.page_of', ['current' => '', 'last' => '']) }}`.replace(':current', pagination.current_page).replace(':last', pagination.last_page)"></span>
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
                                :class="p === pagination.current_page ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="w-10 h-10 rounded-xl border font-bold text-sm transition-all shadow-sm"
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

                <!-- Squad Preview Modal -->
                <div x-show="showPreview" style="display:none" @keydown.escape.window="showPreview = false" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[85vh]">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="previewSquad?.name"></h3>
                                <p class="text-sm text-slate-500" x-text="`{{ __('admin.squads.members_count', ['count' => '']) }}`.replace(':count', (previewSquad?.users?.length || 0))"></p>
                            </div>
                            <button @click="showPreview = false" class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="overflow-y-auto flex-1">
                            <template x-if="previewSquad?.users?.length === 0">
                                <div class="py-16 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 mb-3">group_off</span>
                                    <p class="text-slate-500 text-sm">{{ __('admin.squads.no_members') }}</p>
                                </div>
                            </template>
                            <template x-for="member in previewSquad?.users" :key="member.id">
                                <button @click="goToMember(member.id)" class="w-full flex items-center gap-4 px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors border-b border-slate-100 dark:border-slate-700/50 last:border-0 text-left group">
                                    <template x-if="member.photo">
                                        <img :src="'/storage/' + member.photo" class="w-11 h-11 rounded-xl object-cover border-2 border-slate-100 dark:border-slate-700 shrink-0">
                                    </template>
                                    <template x-if="!member.photo">
                                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-primary/20">
                                            <span class="font-bold text-base" x-text="member.name.charAt(0)"></span>
                                        </div>
                                    </template>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate" x-text="member.name"></div>
                                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mt-0.5" x-text="roleLabels[member.role] || member.role"></div>
                                    </div>
                                    <span class="material-symbols-outlined text-[20px] text-slate-300 group-hover:text-primary transition-colors shrink-0">arrow_forward</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Modal Echipe Formate -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? `{{ __('admin.squads.edit_title') }}` : `{{ __('admin.squads.add_title') }}`"></h3>
                        </div>
                        <form @submit.prevent="saveSquad()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('admin.squads.name_label') }}</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all" placeholder="{{ __('admin.squads.name_placeholder') }}"/>
                            </div>
                            
                            <template x-if="user?.role === 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('admin.squads.select_club_first') }}</label>
                                    <select x-model="form.club_id" @change="fetchModalTeams()" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled selected>{{ __('admin.squads.choose_club') }}</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('admin.squads.belongs_to_team_label') }}</label>
                                <select x-model="form.team_id" required :disabled="!form.club_id && user?.role === 'administrator'" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer disabled:opacity-50">
                                    <option value="" disabled selected>{{ __('admin.squads.choose_team') }}</option>
                                    <template x-for="t in availableModalTeams" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                <template x-if="user?.role === 'administrator' && !form.club_id">
                                    <p class="text-xs text-red-500 mt-1">{{ __('admin.squads.select_club_notice') }}</p>
                                </template>
                            </div>
                            
                            <template x-if="error">
                                <div class="p-3 mb-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100" x-text="error"></div>
                            </template>
                            
                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">{{ __('admin.cancel') }}</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                    <span x-show="saving" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                    {{ __('admin.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
