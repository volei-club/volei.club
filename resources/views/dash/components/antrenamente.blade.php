<div x-show="currentPage === '/dash/antrenamente'" x-data="trainingManager()" class="h-full flex flex-col relative">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __('trainings.title') }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('trainings.subtitle') }}</p>
        </div>
        <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
            <span class="material-symbols-outlined text-[20px]">calendar_add_on</span>
            <span>{{ __('trainings.add') }}</span>
        </button>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
        <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
        <p class="text-slate-500 font-medium">{{ __('trainings.loading') }}</p>
    </div>

    <!-- Filtre -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm mb-6 flex flex-wrap gap-4 items-center">
        <div x-show="user?.role === 'administrator'" class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">{{ __('trainings.filters.club') }}</label>
            <select x-model="filters.club_id" @change="fetchTrainings()" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                <option value="">{{ __('trainings.filters.all_clubs') }}</option>
                <template x-for="club in allClubs" :key="club.id">
                    <option :value="club.id" x-text="club.name"></option>
                </template>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">{{ __('trainings.filters.team') }}</label>
            <select x-model="filters.squad_id" @change="fetchTrainings()" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                <option value="">{{ __('trainings.filters.all_teams') }}</option>
                <template x-for="squad in availableSquads" :key="squad.id">
                    <option :value="squad.id" x-text="squad.name"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Lista Antrenamente -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto flex-1 text-sm">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                        <th class="px-6 py-4 font-bold">{{ __('trainings.table.day') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('trainings.table.time') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('trainings.table.location') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('trainings.table.team') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('trainings.table.coach') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('trainings.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <template x-for="t in trainings" :key="t.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white capitalize" x-text="getDayLabel(t.day_of_week)"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400 font-medium">
                                    <span class="material-symbols-outlined text-[18px] text-primary">schedule</span>
                                    <span x-text="t.start_time.substring(0,5) + ' - ' + t.end_time.substring(0,5)"></span>
                                </div>
                            </td>
                             <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-slate-400">location_on</span>
                                    <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="t.location?.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="t.squad?.name"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center text-[10px] font-bold text-primary" x-text="t.coach?.name.charAt(0)"></div>
                                    <span class="text-slate-600 dark:text-slate-400" x-text="t.coach?.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <button @click="openModal(t)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="{{ __('admin.edit') }}">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button @click="deleteTraining(t.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="{{ __('admin.delete') }}">
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
            <template x-for="t in trainings" :key="t.id">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 -mx-5 -mt-5 p-4 rounded-t-2xl border-b border-slate-100 dark:border-slate-700">
                        <span class="font-bold text-slate-900 dark:text-white capitalize" x-text="getDayLabel(t.day_of_week)"></span>
                        <div class="flex items-center gap-2 text-primary font-bold text-sm">
                            <span class="material-symbols-outlined text-[18px]">schedule</span>
                            <span x-text="t.start_time.substring(0,5) + ' - ' + t.end_time.substring(0,5)"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">location_on</span>
                            <span class="font-semibold text-sm text-slate-700 dark:text-slate-300" x-text="t.location?.name"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400">groups</span>
                            <span class="px-2 py-0.5 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[10px] font-bold uppercase tracking-wide" x-text="t.squad?.name"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-[10px] font-bold text-primary" x-text="t.coach?.name.charAt(0)"></div>
                            <span class="text-sm text-slate-600 dark:text-slate-400" x-text="t.coach?.name"></span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-50 dark:border-slate-800 flex justify-end gap-2">
                        <button @click="openModal(t)" class="flex-1 py-2.5 bg-primary/10 text-primary rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            {{ __('admin.edit') }}
                        </button>
                        <button @click="deleteTraining(t.id)" class="flex-1 py-2.5 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            {{ __('admin.delete') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
            
        <template x-if="trainings.length === 0">
            <div class="text-center py-20 border-t border-slate-100 dark:border-slate-700 border-dashed">
                <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-5xl mb-4">event_busy</span>
                <p class="text-slate-500">{{ __('trainings.messages.empty_state') }}</p>
            </div>
        </template>
    </div>

    <!-- Modal Adaugare/Editare -->
    <div x-show="showModal" 
         @keydown.escape.window="showModal = false"
         style="display: none;" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden transition-all transform scale-100 flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/20 shrink-0">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white" x-text="editingId ? '{{ __('trainings.edit') }}' : '{{ __('trainings.add') }}'"></h3>
                <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form @submit.prevent="saveTraining()" class="flex flex-col overflow-hidden">
                <div class="p-6 space-y-4 overflow-y-auto">
                    <!-- Club Selector (Admin only) -->
                    <div x-show="user?.role === 'administrator'" class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.club') }}</label>
                        <select x-model="formData.club_id" @change="onClubChange()" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                            <option value="">{{ __('trainings.form.select_club') }}</option>
                            <template x-for="club in allClubs" :key="club.id">
                                <option :value="club.id" x-text="club.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.day') }}</label>
                            <select x-model="formData.day_of_week" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                                <option value="luni">{{ __('trainings.form.days.luni') }}</option>
                                <option value="marti">{{ __('trainings.form.days.marti') }}</option>
                                <option value="miercuri">{{ __('trainings.form.days.miercuri') }}</option>
                                <option value="joi">{{ __('trainings.form.days.joi') }}</option>
                                <option value="vineri">{{ __('trainings.form.days.vineri') }}</option>
                                <option value="sambata">{{ __('trainings.form.days.sambata') }}</option>
                                <option value="duminica">{{ __('trainings.form.days.duminica') }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                             <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.start') }}</label>
                                <input type="time" x-model="formData.start_time" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"/>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.end') }}</label>
                                <input type="time" x-model="formData.end_time" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"/>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.start_date') }}</label>
                            <input type="date" x-model="formData.start_date" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"/>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.end_date') }}</label>
                            <input type="date" x-model="formData.end_date" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm"/>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.location') }}</label>
                        <select x-model="formData.location_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                            <option value="">{{ __('trainings.form.select_location') }}</option>
                            <template x-for="loc in availableLocations" :key="loc.id">
                                <option :value="loc.id" x-text="loc.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.team') }}</label>
                            <select x-model="formData.squad_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                                <option value="">{{ __('trainings.form.select_team') }}</option>
                                <template x-for="squad in availableSquads" :key="squad.id">
                                    <option :value="squad.id" x-text="squad.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider ml-1">{{ __('trainings.form.coach') }}</label>
                            <select x-model="formData.coach_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                                <option value="">{{ __('trainings.form.select_coach') }}</option>
                                <template x-for="coach in availableCoaches" :key="coach.id">
                                    <option :value="coach.id" x-text="coach.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <template x-if="error">
                        <div class="p-3 bg-red-50 text-red-600 rounded-lg text-xs border border-red-100" x-text="error"></div>
                    </template>
                </div>

                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex gap-3 shrink-0 rounded-b-2xl">
                    <button type="button" @click="showModal = false" class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 transition-all">
                        {{ __('admin.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving" class="flex-1 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 disabled:opacity-50 flex items-center justify-center gap-2">
                        <span x-show="saving" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                        <span x-text="editingId ? '{{ __('admin.save') }}' : '{{ __('admin.add') }}'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
