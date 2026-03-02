            <!-- CLUBS VIEW -->
            <div x-show="currentPage.startsWith('/dash/cluburi')" x-data="clubManager()" class="h-full flex flex-col relative">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Cluburi</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Administrează cluburile înregistrate în platformă</p>
                    </div>
                    <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Adaugă Club</span>
                    </button>
                </div>

                <!-- Loading Overlay -->
                <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500 font-medium">Se încarcă cluburile...</p>
                </div>

                <!-- Desktop Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="hidden md:block overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                    <th class="px-6 py-4 font-bold">Denumire Club</th>
                                    <th class="px-6 py-4 font-bold">Creat de</th>
                                    <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <template x-for="club in clubs" :key="club.id">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 bg-primary/10 text-primary rounded-xl flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-[20px]">domain</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="club.name"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="club.creator ? club.creator.name : 'Sistem'"></span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(club)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Editează">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <button @click="deleteClub(club.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Șterge">
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
                        <template x-for="club in clubs" :key="club.id">
                            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary/10 text-primary rounded-xl flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[22px]">domain</span>
                                    </div>
                                    <h4 class="font-bold text-lg text-slate-900 dark:text-white" x-text="club.name"></h4>
                                </div>

                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                    <span class="material-symbols-outlined text-[18px] text-slate-400">person</span>
                                    <span x-text="'Creat de: '"></span>
                                    <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="club.creator ? club.creator.name : 'Sistem'"></span>
                                </div>

                                <div class="pt-3 border-t border-slate-50 dark:border-slate-800 flex gap-2">
                                    <button @click="openModal(club)" class="flex-1 flex items-center justify-center gap-2 py-2 bg-primary/10 text-primary hover:bg-primary/20 rounded-xl font-bold text-sm transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                        Editează
                                    </button>
                                    <button @click="deleteClub(club.id)" class="flex-1 flex items-center justify-center gap-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-xl font-bold text-sm transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Șterge
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="clubs.length === 0 && !loading" class="py-20 text-center border-t border-dashed border-slate-100 dark:border-slate-700">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700 mb-4">domain_disabled</span>
                        <p class="text-slate-500">Nu am găsit cluburi. Creează tu primul!</p>
                    </div>
                </div>


                <!-- Modal Adăugare -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Club' : 'Adaugă Club Nou'"></h3>
                        </div>
                        <form @submit.prevent="saveClub()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Denumire Club</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all"/>
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
