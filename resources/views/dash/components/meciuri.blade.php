<div x-show="currentPage === '/dash/meciuri'" x-data="gameManager()" class="h-full flex flex-col relative" style="display:none">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __('matches.title') }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('matches.subtitle') }}</p>
        </div>
        <template x-if="canAddGame()">
            <button @click="openGameModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 dark:shadow-indigo-900/20 outline-none">
                <span class="material-symbols-outlined text-[20px]">sports_volleyball</span>
                <span>{{ __('matches.add') }}</span>
            </button>
        </template>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
        <span class="material-symbols-outlined animate-spin text-4xl text-indigo-500 mb-2">sync</span>
        <p class="text-slate-500 font-medium">{{ __('matches.loading') }}</p>
    </div>

    <!-- Filtre -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm mb-6 flex flex-wrap gap-4 items-center">
        <div x-show="user?.role === 'administrator'" class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">{{ __('matches.filters.club') }}</label>
            <select x-model="filters.club_id" @change="fetchGames()" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                <option value="">{{ __('matches.filters.all_clubs') }}</option>
                <template x-for="club in allClubs" :key="club.id">
                    <option :value="club.id" x-text="club.name"></option>
                </template>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">{{ __('matches.filters.team') }}</label>
            <select x-model="filters.squad_id" @change="fetchGames()" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                <option value="">{{ __('matches.filters.all_teams') }}</option>
                <template x-for="squad in availableSquads" :key="squad.id">
                    <option :value="squad.id" x-text="squad.name"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Lista Meciuri -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto flex-1 text-sm">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                        <th class="px-6 py-4 font-bold">{{ __('matches.table.date') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('matches.table.opponent') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('matches.table.score') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('matches.table.location') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('matches.table.team') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('matches.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <template x-for="g in games" :key="g.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white" x-text="formatDate(g.match_date)"></div>
                                <div class="text-[10px] text-slate-500 font-medium" x-text="formatTime(g.match_date)"></div>
                            </td>
                            <td class="px-6 py-4 font-black text-indigo-700 dark:text-indigo-400 uppercase" x-text="g.opponent_name"></td>
                            <td class="px-6 py-4">
                                <template x-if="getFinalScore(g)">
                                    <span class="px-2 py-1 bg-indigo-500 text-white rounded font-black" x-text="getFinalScore(g)"></span>
                                </template>
                                <template x-if="!getFinalScore(g)">
                                    <span class="text-slate-300 font-medium">—</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-600 dark:text-slate-400" x-text="g.location"></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="g.squad?.name"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <button @click="openGameModal(g)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" :title="canModifyMatches() ? '{{ __('admin.edit') }}' : '{{ __('matches.details') }}'">
                                        <span class="material-symbols-outlined text-[20px]" x-text="canModifyMatches() ? 'edit' : 'visibility'"></span>
                                    </button>
                                    <button x-show="canModifyMatches()" @click="deleteGame(g.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ __('admin.delete') }}">
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
        <div class="md:hidden flex-1 overflow-y-auto p-4 space-y-4">
            <template x-for="g in games" :key="g.id">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 -mx-5 -mt-5 p-4 rounded-t-2xl border-b border-slate-100 dark:border-slate-700">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-900 dark:text-white" x-text="formatDate(g.match_date)"></span>
                            <span class="text-[10px] text-slate-500" x-text="formatTime(g.match_date)"></span>
                        </div>
                        <template x-if="getFinalScore(g)">
                           <span class="px-2 py-1 bg-indigo-500 text-white rounded-lg font-black text-sm" x-text="getFinalScore(g)"></span>
                        </template>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-indigo-500">sports_volleyball</span>
                            <span class="font-black text-indigo-900 dark:text-indigo-200 uppercase" x-text="g.opponent_name"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">location_on</span>
                            <span class="font-semibold text-sm text-slate-700 dark:text-slate-300" x-text="g.location"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">groups</span>
                            <span class="px-2 py-0.5 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[10px] font-bold uppercase tracking-wide" x-text="g.squad?.name"></span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-50 dark:border-slate-800 flex justify-end gap-2">
                        <button @click="openGameModal(g)" class="flex-1 py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]" x-text="canModifyMatches() ? 'edit' : 'visibility'"></span>
                            <span x-text="canModifyMatches() ? '{{ __('admin.edit') }}' : '{{ __('matches.details') }}'"></span>
                        </button>
                        <button x-show="canModifyMatches()" @click="deleteGame(g.id)" class="flex-1 py-2.5 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            {{ __('admin.delete') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
            
        <template x-if="games.length === 0">
            <div class="text-center py-20 border-t border-slate-100 dark:border-slate-700 border-dashed">
                <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-5xl mb-4">sports_volleyball</span>
                <p class="text-slate-500">{{ __('matches.messages.empty_state') }}</p>
            </div>
        </template>
    </div>
</div>
