            <!-- ABONAMENTE VIEW -->
            <div x-show="currentPage.startsWith('/dash/abonamente')" x-data="subscriptionManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Tipuri Abonamente</h3>
                    <template x-if="user?.role === 'manager' || user?.role === 'administrator'">
                        <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none flex items-center justify-center sm:justify-start gap-2">
                            <span class="material-symbols-outlined text-[20px]">add_card</span>
                            <span>Adaugă Abonament</span>
                        </button>
                    </template>
                </div>

                <template x-if="user?.role === 'administrator'">
                    <div class="mb-6 flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <select id="subFilterClub" x-model="filters.club_id" @change="fetchSubscriptions(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="sub in subscriptions" :key="sub.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all relative group flex flex-col">
                            
                            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openModal(sub)" class="p-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-primary hover:text-white transition-colors" title="Editează">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button @click="deleteSubscription(sub.id)" class="p-2 bg-slate-100 dark:bg-slate-700 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-colors" title="Șterge">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>

                            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-500 flex items-center justify-center mb-4 shrink-0">
                                <span class="material-symbols-outlined text-2xl">loyalty</span>
                            </div>

                            <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2" x-text="sub.name"></h4>
                            
                            <div class="flex items-baseline mb-4">
                                <span class="text-3xl font-extrabold text-primary" x-text="sub.price"></span>
                                <span class="text-sm font-bold text-slate-500 ml-1">lei / 
                                    <span x-text="sub.period.replace('_', ' ')"></span>
                                </span>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700 text-[11px] font-bold text-slate-500 uppercase flex items-center">
                                <span class="material-symbols-outlined text-[18px] mr-1.5 opacity-70">domain</span>
                                <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="sub.club ? sub.club.name : '-'"></span>
                            </div>

                        </div>
                    </template>
                </div>

                <template x-if="subscriptions.length === 0 && !loading">
                    <div class="col-span-full w-full py-20 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 border-dashed">
                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-700/50 mx-auto flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700">payments</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Niciun abonament</h4>
                        <p class="text-slate-500 mb-6 max-w-sm mx-auto">Acest club nu are definit niciun plan de abonament pentru sportivi.</p>
                    </div>
                </template>

                <!-- Modal Abonament -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Abonament' : 'Adaugă Abonament Nou'"></h3>
                        </div>
                        <form @submit.prevent="saveSubscription()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto max-h-[70vh]">
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nume Abonament</label>
                                    <input x-model="form.name" type="text" placeholder="Ex: Abonament Standard" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Preț (LEI)</label>
                                    <input x-model="form.price" type="number" step="0.01" min="0" placeholder="Ex: 250" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Perioadă Recurență</label>
                                    <select x-model="form.period" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="1_saptamana">O Săptămână</option>
                                        <option value="2_saptamani">2 Săptămâni</option>
                                        <option value="1_luna">O Lună</option>
                                        <option value="3_luni">3 Luni</option>
                                        <option value="6_luni">6 Luni</option>
                                        <option value="1_an">Un An</option>
                                    </select>
                                </div>

                                <template x-if="user?.role === 'administrator'">
                                    <div class="mb-4">
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Club Aparținător</label>
                                        <select x-model="form.club_id" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                            <option value="" disabled selected>Selectează clubul</option>
                                            <template x-for="c in availableClubs" :key="c.id">
                                                <option :value="c.id" x-text="c.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <template x-if="error">
                                    <div class="p-3 mt-2 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100 font-medium" x-text="error"></div>
                                </template>

                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Anulare</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50 shadow-sm hover:shadow">
                                    <span x-show="saving" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                    Salvează
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
