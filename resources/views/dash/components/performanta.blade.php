<div x-show="currentPage === '/dash/performanta'" x-data="performanceManager()" class="h-full flex flex-col relative">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __('performance.title') }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('performance.subtitle') }}</p>
        </div>
        
        <template x-if="canAddEntry()">
            <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
                <span class="material-symbols-outlined text-[20px]">add_chart</span>
                <span>{{ __('performance.add_progress') }}</span>
            </button>
        </template>
    </div>

    <!-- Athlete Hierarchy Selector (for Coaches/Managers) -->
    <template x-if="['administrator', 'manager', 'antrenor'].includes(user?.role)">
        <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Club Selection (Admins only or info for managers) -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">{{ __('performance.filters.club') }}</label>
                    <template x-if="user.role === 'administrator'">
                        <select @change="onClubChange($event.target.value)" x-model="selectedClubId"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="">{{ __('performance.filters.all_clubs') }}</option>
                            <template x-for="club in clubs" :key="club.id">
                                <option :value="club.id" x-text="club.name"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="user.role !== 'administrator'">
                        <div class="px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300" 
                             x-text="clubs.find(c => c.id === user.club_id)?.name || 'Clubul Meu'"></div>
                    </template>
                </div>

                <!-- Team/Grupa Selection -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">{{ __('performance.filters.team') }}</label>
                    <select @change="onTeamChange($event.target.value)" x-model="selectedTeamId"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <option value="">{{ __('performance.filters.all_teams') }}</option>
                        <template x-for="team in teams" :key="team.id">
                            <option :value="team.id" x-text="team.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Squad/Echipa Selection -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">{{ __('performance.filters.squad') }}</label>
                    <select @change="onSquadChange($event.target.value)" x-model="selectedSquadId"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <option value="">{{ __('performance.filters.all_squads') }}</option>
                        <template x-for="squad in squads" :key="squad.id">
                            <option :value="squad.id" x-text="squad.name"></option>
                        </template>
                    </select>
                </div>
            </div>

                <!-- Athlete Selection -->
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">{{ __('performance.filters.athlete') }}</label>
                    <template x-if="availableAthletes.length > 0">
                        <select @change="selectAthleteById($event.target.value)" :value="selectedAthleteId"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold text-primary">
                            <option value="">{{ __('performance.filters.select_athlete') }}</option>
                            <template x-for="athlete in filteredAthletes" :key="athlete.id">
                                <option :value="athlete.id" x-text="athlete.name"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="availableAthletes.length === 0">
                         <div class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-400">
                             {{ __('performance.loading_athletes') }}
                         </div>
                    </template>
                </div>
            </div>

            <!-- Search Field -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input type="text" x-model="athleteSearch" placeholder="{{ __('performance.filters.athlete_search_placeholder') }}" 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary outline-none transition-all italic">
                </div>
            </div>
        </div>
    </template>

    <!-- Athlete Selector for Parents -->
    <template x-if="user?.role === 'parinte' && availableAthletes.length > 1">
        <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400">child_care</span>
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">{{ __('performance.filters.parent_view_label') }}</label>
                    <select @change="selectAthleteById($event.target.value)" :value="selectedAthleteId"
                            class="w-full bg-transparent font-bold text-primary outline-none appearance-none cursor-pointer">
                        <template x-for="athlete in availableAthletes" :key="athlete.id">
                            <option :value="athlete.id" x-text="athlete.name"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>
    </template>

    <!-- Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart/Stats Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Main Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
                    <span class="text-xs font-bold text-slate-400 uppercase">{{ __('performance.metrics.vertical_jump') }}</span>
                    <div class="text-2xl font-bold text-primary mt-1" x-text="(latestEntry?.vertical_jump || '-') + ' cm'"></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
                    <span class="text-xs font-bold text-slate-400 uppercase">{{ __('performance.metrics.serve_speed') }}</span>
                    <div class="text-2xl font-bold text-indigo-500 mt-1" x-text="(latestEntry?.serve_speed || '-') + ' km/h'"></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
                    <span class="text-xs font-bold text-slate-400 uppercase">{{ __('performance.metrics.weight') }}</span>
                    <div class="text-2xl font-bold text-slate-700 dark:text-slate-200 mt-1" x-text="(latestEntry?.weight || '-') + ' kg'"></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
                    <span class="text-xs font-bold text-slate-400 uppercase">{{ __('performance.metrics.latest_log') }}</span>
                    <div class="text-sm font-bold text-slate-500 mt-2" x-text="latestEntry ? formatDate(latestEntry.log_date) : '-'"></div>
                </div>
            </div>

            <!-- Chart Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm min-h-[350px] flex flex-col">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h4 class="font-bold text-slate-800 dark:text-white" x-text="'{{ __('performance.metrics.evolution_title', ['metric' => '']) }}' + activeMetricLabel"></h4>
                    <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 scrollbar-none -mr-2 sm:mr-0">
                        <button @click="setMetric('detenta')" 
                                :class="activeMetric === 'detenta' ? 'bg-primary/10 text-primary' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors whitespace-nowrap shrink-0">{{ __('performance.metrics.vertical_jump') }}</button>
                        <button @click="setMetric('viteza')" 
                                :class="activeMetric === 'viteza' ? 'bg-indigo-500/10 text-indigo-500' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors whitespace-nowrap shrink-0">{{ __('performance.metrics.serve_speed') }}</button>
                        <button @click="setMetric('greutate')" 
                                :class="activeMetric === 'greutate' ? 'bg-slate-500/10 text-slate-500' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-colors whitespace-nowrap shrink-0">{{ __('performance.metrics.weight') }}</button>
                    </div>
                </div>
                
                <div class="flex-1 relative flex items-center justify-center border-t border-slate-100 dark:border-slate-700 pt-8">
                    <template x-if="logs.length < 2">
                        <div class="text-slate-400 text-sm">{{ __('performance.metrics.min_logs_chart') }}</div>
                    </template>
                    <template x-if="logs.length >= 2">
                         <div class="w-full h-full relative">
                             <!-- Improved Dynamic SVG Chart -->
                             <svg viewBox="0 0 1000 380" class="w-full h-full overflow-visible">
                                 <!-- Y-Axis Mini Labels -->
                                 <text x="10" y="30" class="text-[10px] fill-slate-300 font-bold" x-text="chartMaxVal"></text>
                                 <text x="10" y="280" class="text-[10px] fill-slate-300 font-bold" x-text="chartMinVal"></text>
                                 
                                 <!-- Grid Lines -->
                                 <line x1="50" y1="30" x2="950" y2="30" stroke="currentColor" class="text-slate-100 dark:text-slate-700/50" stroke-dasharray="4" />
                                 <line x1="50" y1="280" x2="950" y2="280" stroke="currentColor" class="text-slate-100 dark:text-slate-700/50" stroke-dasharray="4" />

                                 <!-- Date Labels (X-Axis) moved inside SVG for perfect scaling -->
                                 <template x-for="p in chartPoints" :key="'lbl_' + p.raw.id">
                                     <text :x="p.x" y="340" text-anchor="middle" class="text-[24px] fill-slate-400 font-medium" x-text="p.label"></text>
                                 </template>

                                 <!-- Only the lines and axis in SVG -->
                                 <path :d="chartDataPath" fill="none" stroke="currentColor" class="text-primary" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                             </svg>

                             <!-- Points and Values (HTML Layer) -->
                             <div class="absolute inset-0 pointer-events-none">
                                 <template x-for="p in chartPoints" :key="'p_' + p.raw.id">
                                     <div class="absolute pointer-events-auto cursor-pointer group"
                                          :style="`left: ${(p.x / 1000) * 100}%; top: ${(p.y / 380) * 100}%; transform: translate(-50%, -50%)`"
                                          @mouseenter="activeTooltip = p"
                                          @mouseleave="activeTooltip = null">
                                         
                                         <!-- Point Dot -->
                                         <div class="w-3 h-3 bg-white border-2 border-primary rounded-full shadow-sm group-hover:scale-125 transition-transform"></div>
                                         
                                         <!-- Value & Unit (Always visible) -->
                                         <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 text-[10px] sm:text-[11px] font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap bg-white/80 dark:bg-slate-800/80 px-1 rounded shadow-sm"
                                              x-text="p.val + ' ' + p.unit"></div>
                                     </div>
                                 </template>
                             </div>

                             <!-- Absolute Tooltip Popup -->
                             <template x-if="activeTooltip">
                                 <div class="absolute p-3 bg-slate-900/90 backdrop-blur-md text-white rounded-xl shadow-xl border border-white/10 z-50 pointer-events-none transition-all duration-200"
                                      :style="`left: ${(activeTooltip.x / 1000) * 100}%; top: ${(activeTooltip.y / 380) * 100}%; transform: translate(-50%, -110%)`">
                                     <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 border-b border-white/10 pb-1" 
                                          x-text="formatDate(activeTooltip.raw.log_date)"></div>
                                     <div class="space-y-1 min-w-[120px]">
                                         <div class="flex justify-between gap-4 text-xs">
                                             <span class="text-slate-400">{{ __('performance.metrics.vertical_jump') }}:</span>
                                             <span class="font-bold text-primary" x-text="activeTooltip.raw.vertical_jump + ' ' + activeTooltip.unit"></span>
                                         </div>
                                         <div class="flex justify-between gap-4 text-xs">
                                             <span class="text-slate-400">{{ __('performance.metrics.serve_speed') }}:</span>
                                             <span class="font-bold text-indigo-400" x-text="activeTooltip.raw.serve_speed + ' km/h'"></span>
                                         </div>
                                         <div class="flex justify-between gap-4 text-xs">
                                             <span class="text-slate-400">{{ __('performance.metrics.weight') }}:</span>
                                             <span class="font-bold text-slate-200" x-text="activeTooltip.raw.weight + ' kg'"></span>
                                         </div>
                                         <div class="pt-1 mt-1 border-t border-white/10 space-y-1">
                                             <div class="flex justify-between gap-4 text-[10px]">
                                                 <span class="text-slate-500 uppercase">{{ __('performance.technical_abilities.reception') }}:</span>
                                                 <span class="font-bold text-primary" x-text="(activeTooltip.raw.reception_rating || '-') + '/5'"></span>
                                             </div>
                                             <div class="flex justify-between gap-4 text-[10px]">
                                                 <span class="text-slate-500 uppercase">{{ __('performance.technical_abilities.attack') }}:</span>
                                                 <span class="font-bold text-indigo-400" x-text="(activeTooltip.raw.attack_rating || '-') + '/5'"></span>
                                             </div>
                                             <div class="flex justify-between gap-4 text-[10px]">
                                                 <span class="text-slate-500 uppercase">{{ __('performance.technical_abilities.block') }}:</span>
                                                 <span class="font-bold text-emerald-400" x-text="(activeTooltip.raw.block_rating || '-') + '/5'"></span>
                                             </div>
                                         </div>
                                         <template x-if="activeTooltip.raw.notes">
                                             <div class="px-2 py-1 bg-white/5 rounded mt-2 text-[10px] italic text-slate-300 max-w-[150px] break-words" x-text="activeTooltip.raw.notes"></div>
                                         </template>
                                     </div>
                                 </div>
                             </template>
                         </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- History/Sidebar Column -->
        <div class="space-y-6">
            <!-- Ratings Card -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <h4 class="font-bold text-slate-800 dark:text-white mb-4">{{ __('performance.technical_abilities.title') }}</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-400 uppercase mb-1">
                            <span>{{ __('performance.technical_abilities.reception') }}</span>
                            <span class="text-primary" x-text="latestEntry?.reception_rating ? latestEntry.reception_rating + '/5' : '-'"></span>
                        </div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-primary transition-all duration-500" :style="`width: ${(latestEntry?.reception_rating || 0) * 20}%`"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-400 uppercase mb-1">
                            <span>{{ __('performance.technical_abilities.attack') }}</span>
                            <span class="text-indigo-500" x-text="latestEntry?.attack_rating ? latestEntry.attack_rating + '/5' : '-'"></span>
                        </div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 transition-all duration-500" :style="`width: ${(latestEntry?.attack_rating || 0) * 20}%`"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-bold text-slate-400 uppercase mb-1">
                            <span>{{ __('performance.technical_abilities.block') }}</span>
                            <span class="text-emerald-500" x-text="latestEntry?.block_rating ? latestEntry.block_rating + '/5' : '-'"></span>
                        </div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 transition-all duration-500" :style="`width: ${(latestEntry?.block_rating || 0) * 20}%`"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Entry List -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <h4 class="font-bold text-slate-800 dark:text-white">{{ __('performance.history.title') }}</h4>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-[400px] overflow-y-auto">
                    <template x-for="log in logs" :key="log.id">
                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-sm font-bold text-slate-800 dark:text-white" x-text="formatDate(log.log_date)"></span>
                                <template x-if="canDeleteEntry(log)">
                                    <button @click="deleteEntry(log.id)" class="text-red-400 hover:text-red-600">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </template>
                            </div>
                            <div class="text-xs text-slate-500" x-text="'{{ __('performance.history.recorded_by', ['name' => '']) }}' + (log.coach?.name || '{{ __('performance.history.system') }}')"></div>
                            <template x-if="log.notes">
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 italic" x-text="log.notes"></p>
                            </template>
                        </div>
                    </template>
                </div>
                <template x-if="logs.length === 0">
                    <div class="p-8 text-center text-slate-400 text-sm">{{ __('performance.history.empty') }}</div>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Adăugare Progres -->
    <div x-show="showModal" 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        
        <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden"
             @click.away="showModal = false">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ __('performance.modal.title') }}</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="saveEntry()" class="p-4 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
                <template x-if="canAddEntry()">
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.athlete') }}</label>
                        <select x-model="formData.user_id" required @change="onModalAthleteChange($event.target.value)"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                            <option value="">{{ __('performance.modal.select_athlete_placeholder') }}</option>
                            <template x-for="athlete in availableAthletes" :key="athlete.id">
                                <option :value="athlete.id" x-text="athlete.name" :selected="formData.user_id === athlete.id"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.log_date') }}</label>
                        <input type="date" x-model="formData.log_date" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.weight') }}</label>
                        <input type="number" step="0.1" x-model="formData.weight" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.vertical_jump') }}</label>
                        <input type="number" step="1" x-model="formData.vertical_jump" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.serve_speed') }}</label>
                        <input type="number" step="1" x-model="formData.serve_speed" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.reception') }}</label>
                        <select x-model="formData.reception_rating" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm appearance-none cursor-pointer">
                            <option value="">-</option>
                            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.attack') }}</label>
                        <select x-model="formData.attack_rating" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm appearance-none cursor-pointer">
                            <option value="">-</option>
                            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.block') }}</label>
                        <select x-model="formData.block_rating" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm appearance-none cursor-pointer">
                            <option value="">-</option>
                            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">{{ __('performance.modal.notes') }}</label>
                    <textarea x-model="formData.notes" rows="3" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all resize-none"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100 dark:border-slate-700 mt-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl font-bold transition-all order-2 sm:order-1">
                        {{ __('admin.cancel') }}
                    </button>
                    <button type="submit" 
                            :disabled="saving"
                            class="flex-1 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 disabled:opacity-50 flex items-center justify-center gap-2 order-1 sm:order-2">
                        <span x-show="saving" class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
                        <span x-text="saving ? '{{ __('admin.saving') }}' : '{{ __('admin.save') }}'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
