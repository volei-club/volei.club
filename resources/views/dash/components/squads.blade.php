            <!-- SQUADS (ECHIPE) VIEW -->
            <div x-show="currentPage.startsWith('/dash/echipe')" x-data="squadManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Echipe Formate</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">groups_2</span>
                        Adaugă Echipă
                    </button>
                </div>

                <template x-if="user?.role === 'administrator'">
                    <div class="mb-6 flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <select x-model="filters.club_id" @change="fetchSquads(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Echipele din Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="text-center py-12">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500">Se încarcă echipele...</p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="px-6 py-4 font-bold">Denumire Echipă</th>
                                <th class="px-6 py-4 font-bold">Aparține de Grupa</th>
                                <template x-if="user?.role === 'administrator'">
                                    <th class="px-6 py-4 font-bold">Club</th>
                                </template>
                                <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
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
                                            <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="squad.team?.club?.name || 'Necunoscut'"></span>
                                        </td>
                                    </template>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openModal(squad)" class="text-slate-400 hover:text-primary transition-colors p-2" title="Editează">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="deleteSquad(squad.id)" class="text-slate-400 hover:text-red-500 transition-colors p-2 ml-1" title="Șterge">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden flex flex-col gap-4">
                    <template x-for="squad in squads" :key="squad.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-sm">
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white" x-text="squad.name"></h4>
                            <div class="flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400 mt-3">
                                <span class="material-symbols-outlined text-[18px] mr-2 text-slate-400">groups</span>
                                <span class="px-2 py-1 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="squad.team?.name"></span>
                            </div>
                            <template x-if="user?.role === 'administrator'">
                                <div class="flex items-center text-sm font-semibold text-slate-500 mt-2">
                                    <span class="material-symbols-outlined text-[18px] mr-2 text-slate-400">domain</span>
                                    <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="squad.team?.club?.name || 'Necunoscut'"></span>
                                </div>
                            </template>
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                                <button @click="openModal(squad)" class="text-sm font-semibold text-primary hover:text-primary-dark px-3 py-1.5 bg-primary/10 rounded-lg">Editează</button>
                                <button @click="deleteSquad(squad.id)" class="text-sm font-semibold text-red-500 hover:text-red-700 px-3 py-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg">Șterge</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="squads.length === 0 && !loading" class="text-center py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">groups_2</span>
                    <p class="text-slate-500">Nu a fost găsită nicio echipă.</p>
                </div>

                <!-- Modal Echipe Formate -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Echipă' : 'Adaugă Echipă Nouă'"></h3>
                        </div>
                        <form @submit.prevent="saveSquad()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Denumire Echipă</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all" placeholder="ex: Echipa de Joi"/>
                            </div>
                            
                            <template x-if="user?.role === 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Selectează Mai Întâi Clubul</label>
                                    <select x-model="form.club_id" @change="fetchModalTeams()" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled selected>Alege clubul...</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Aparține de Grupa (Obligatoriu)</label>
                                <select x-model="form.team_id" required :disabled="!form.club_id && user?.role === 'administrator'" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer disabled:opacity-50">
                                    <option value="" disabled selected>Alege grupa...</option>
                                    <template x-for="t in availableModalTeams" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                <template x-if="user?.role === 'administrator' && !form.club_id">
                                    <p class="text-xs text-red-500 mt-1">Selectați mai întâi un club pentru a vedea grupele.</p>
                                </template>
                            </div>
                            
                            <template x-if="error">
                                <div class="p-3 mb-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100" x-text="error"></div>
                            </template>
                            
                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Anulare</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                    <span x-show="saving" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                    Salvează
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
