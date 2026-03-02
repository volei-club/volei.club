            <!-- CLUBS VIEW -->
            <div x-show="currentPage.startsWith('/dash/cluburi')" x-data="clubManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Toate Cluburile</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none flex items-center justify-center sm:justify-start gap-2">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Adaugă Club</span>
                    </button>
                </div>

                <!-- Lista de Cluburi -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="club in clubs" :key="club.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm relative group">
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                <button @click="openModal(club)" class="p-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-primary hover:text-white transition-colors" title="Editează">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button @click="deleteClub(club.id)" class="p-2 bg-slate-100 dark:bg-slate-700 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-colors" title="Șterge">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-2xl">domain</span>
                            </div>
                            <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2" x-text="club.name"></h4>
                            <div class="flex items-center text-sm text-slate-500 mb-4">
                                <span>Creat de:</span>
                                <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide ml-2" x-text="club.creator ? club.creator.name : 'Sistem'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="clubs.length === 0 && !loading" class="text-center py-20 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 border-dashed">
                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700 mb-4">domain_disabled</span>
                    <p class="text-slate-500">Nu am găsit cluburi. Creează tu primul!</p>
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
