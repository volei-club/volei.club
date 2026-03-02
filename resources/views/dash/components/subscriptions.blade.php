            <!-- ABONAMENTE VIEW -->
            <div x-show="currentPage.startsWith('/dash/abonamente')" x-data="subscriptionManager()" class="h-full flex flex-col relative">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Tipuri Abonamente</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Planuri de abonament disponibile pentru sportivi</p>
                    </div>
                    <template x-if="user?.role === 'manager' || user?.role === 'administrator'">
                        <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
                            <span class="material-symbols-outlined text-[20px]">add_card</span>
                            <span>Adaugă Abonament</span>
                        </button>
                    </template>
                </div>

                <!-- Loading Overlay -->
                <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500 font-medium">Se încarcă abonamentele...</p>
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

                <!-- Desktop Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="hidden md:block overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                    <th class="px-6 py-4 font-bold">Nume Abonament</th>
                                    <th class="px-6 py-4 font-bold">Preț</th>
                                    <th class="px-6 py-4 font-bold">Perioadă</th>
                                    <th x-show="user?.role === 'administrator'" class="px-6 py-4 font-bold">Club</th>
                                    <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <template x-for="sub in subscriptions" :key="sub.id">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-500 flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-[20px]">loyalty</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="sub.name"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-extrabold text-primary text-lg" x-text="sub.price"></span>
                                            <span class="text-slate-500 text-sm ml-1">lei</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-800/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="sub.period.replace(/_/g, ' ')"></span>
                                        </td>
                                        <td x-show="user?.role === 'administrator'" class="px-6 py-4">
                                            <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="sub.club?.name || 'N/A'"></span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openModal(sub)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Editează">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <button @click="deleteSubscription(sub.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Șterge">
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
                        <template x-for="sub in subscriptions" :key="sub.id">
                            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-500 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[22px]">loyalty</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white" x-text="sub.name"></h4>
                                        <div class="flex items-baseline gap-1">
                                            <span class="font-extrabold text-primary text-lg" x-text="sub.price"></span>
                                            <span class="text-slate-500 text-xs">lei / <span x-text="sub.period.replace(/_/g, ' ')"></span></span>
                                        </div>
                                    </div>
                                </div>

                                <template x-if="user?.role === 'administrator'">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[18px] text-slate-400">domain</span>
                                        <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide" x-text="sub.club?.name || 'N/A'"></span>
                                    </div>
                                </template>

                                <div class="pt-3 border-t border-slate-50 dark:border-slate-800 flex gap-2">
                                    <button @click="openModal(sub)" class="flex-1 flex items-center justify-center gap-2 py-2 bg-primary/10 text-primary hover:bg-primary/20 rounded-xl font-bold text-sm transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                        Editează
                                    </button>
                                    <button @click="deleteSubscription(sub.id)" class="flex-1 flex items-center justify-center gap-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-xl font-bold text-sm transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Șterge
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="subscriptions.length === 0 && !loading">
                        <div class="py-20 text-center border-t border-slate-100 dark:border-slate-700 border-dashed">
                            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700 mb-4">payments</span>
                            <p class="text-slate-500">Acest club nu are definit niciun plan de abonament.</p>
                        </div>
                    </template>
                </div>

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
