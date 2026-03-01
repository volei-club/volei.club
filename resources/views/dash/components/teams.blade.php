            <!-- TEAMS (GRUPE) VIEW -->
            <div x-show="currentPage.startsWith('/dash/grupe')" x-data="teamManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Grupe</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">group_add</span>
                        Adaugă Grupă
                    </button>
                </div>

                <template x-if="user?.role === 'administrator'">
                    <div class="mb-6 flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <select id="teamFilterClub" x-model="filters.club_id" @change="fetchTeams(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Grupele din Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="text-center py-12">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500">Se încarcă grupele...</p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="px-6 py-4 font-bold">Denumire Grupă</th>
                                <template x-if="user?.role === 'administrator'">
                                    <th class="px-6 py-4 font-bold">Asociată Clubului</th>
                                </template>
                                <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                            <template x-for="team in teams" :key="team.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white text-base" x-text="team.name"></div>
                                    </td>
                                    <template x-if="user?.role === 'administrator'">
                                        <td class="px-6 py-4">
                                            <span class="text-slate-500" x-text="availableClubs.find(c => c.id === team.club_id)?.name || 'Necunoscut'"></span>
                                        </td>
                                    </template>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openModal(team)" class="text-slate-400 hover:text-primary transition-colors p-2" title="Editează">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="deleteTeam(team.id)" class="text-slate-400 hover:text-red-500 transition-colors p-2 ml-1" title="Șterge">
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
                    <template x-for="team in teams" :key="team.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-sm">
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white" x-text="team.name"></h4>
                            <template x-if="user?.role === 'administrator'">
                                <p class="text-sm text-slate-500 mt-1" x-text="'Club: ' + (availableClubs.find(c => c.id === team.club_id)?.name || 'Necunoscut')"></p>
                            </template>
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                                <button @click="openModal(team)" class="text-sm font-semibold text-primary hover:text-primary-dark px-3 py-1.5 bg-primary/10 rounded-lg">Editează</button>
                                <button @click="deleteTeam(team.id)" class="text-sm font-semibold text-red-500 hover:text-red-700 px-3 py-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg">Șterge</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="teams.length === 0 && !loading" class="text-center py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">diversity_3</span>
                    <p class="text-slate-500">Acest club nu are asocieri de grupe încă.</p>
                </div>

                <!-- Modal Grupe -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Grupă' : 'Adaugă Grupă Nouă'"></h3>
                        </div>
                        <form @submit.prevent="saveTeam()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Denumire Grupă</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all" placeholder="ex: U18 Fete"/>
                            </div>
                            
                            <template x-if="user?.role === 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Aparține de Clubul (Obligatoriu)</label>
                                    <select x-model="form.club_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled selected>Alege clubul...</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
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
