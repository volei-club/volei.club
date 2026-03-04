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

                <!-- ADMIN / MANAGER VIEW -->
                <template x-if="user?.role === 'administrator' || user?.role === 'manager'">
                    <div class="flex-1 flex flex-col">
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
                </div>
                </template>

                <!-- ATHLETE / PARENT VIEW -->
                <template x-if="user?.role && user.role !== 'administrator' && user.role !== 'manager'">
                    <div class="flex-1 flex flex-col gap-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Abonament Activ -->
                            <div class="bg-gradient-to-br from-primary to-primary-dark rounded-[20px] md:rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl text-white/10 select-none pointer-events-none">card_membership</span>
                                
                                <h3 class="text-white/80 font-bold uppercase tracking-wider text-xs mb-4">Abonament Curent</h3>
                                
                                <template x-if="mySubscriptions.length > 0 && (mySubscriptions[0].status === 'active_paid' || mySubscriptions[0].status === 'active_pending')">
                                    <div>
                                        <div class="text-3xl font-extrabold mb-1" x-text="mySubscriptions[0].subscription?.name || 'Abonament Activ'"></div>
                                        <div class="text-white/80 flex items-center gap-2 mb-6">
                                            <span class="material-symbols-outlined text-sm">event</span>
                                            Valabil până la <span class="font-bold text-white ml-1" x-text="new Date(mySubscriptions[0].expires_at).toLocaleDateString('ro-RO')"></span>
                                        </div>
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-lg text-sm font-semibold">
                                            <span class="w-2 h-2 rounded-full" :class="mySubscriptions[0].status === 'active_paid' ? 'bg-green-400' : 'bg-amber-400'"></span>
                                            <span x-text="mySubscriptions[0].status === 'active_paid' ? 'Abonament Plătit' : 'Plată în Așteptare'"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!mySubscriptions.length || (mySubscriptions[0].status !== 'active_paid' && mySubscriptions[0].status !== 'active_pending')">
                                    <div>
                                        <div class="text-2xl font-bold mb-2">Niciun abonament activ</div>
                                        <p class="text-white/70 text-sm mb-6">Nu ai un plan de abonament valabil în acest moment. Pentru informații suplimentare, te rugăm să contactezi antrenorul sau administrația clubului.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Istoric Abonamente -->
                        <div class="bg-white dark:bg-slate-800 rounded-[20px] md:rounded-2xl border border-slate-50 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden flex-1 flex flex-col">
                            <div class="p-5 md:p-6 border-b border-slate-50 dark:border-slate-800/60">
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">history</span>
                                    Istoric Abonamente
                                </h3>
                            </div>
                            
                            <div class="hidden md:block flex-1 overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[500px]">
                                    <thead>
                                        <tr class="bg-slate-50/50 dark:bg-slate-900/20 border-b border-slate-50 dark:border-slate-800/60 text-slate-500 uppercase text-[11px] font-bold tracking-wider">
                                            <th class="px-6 py-4">Plan Abonament</th>
                                            <th class="px-6 py-4">Status</th>
                                            <th class="px-6 py-4">Data Activării</th>
                                            <th class="px-6 py-4">Data Expirării</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60 text-sm">
                                        <template x-for="ms in mySubscriptions" :key="ms.id">
                                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/30 transition-colors">
                                                <td class="px-6 py-4">
                                                    <span class="font-bold text-slate-900 dark:text-white" x-text="ms.subscription?.name || 'Abonament'"></span>
                                                    <div class="text-xs text-slate-500 mt-0.5">
                                                        <span x-text="ms.subscription?.price || '-'"></span> lei / <span x-text="ms.subscription?.period ? ms.subscription.period.replace(/_/g, ' ') : '-'"></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center gap-1"
                                                        :class="{
                                                            'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400': ms.status === 'active_paid',
                                                            'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400': ms.status === 'active_pending',
                                                            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400': ms.status === 'expired',
                                                            'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400': ms.status === 'cancelled'
                                                        }">
                                                        <span class="material-symbols-outlined text-[14px]" x-text="ms.status === 'active_paid' ? 'check_circle' : (ms.status === 'active_pending' ? 'pending' : (ms.status === 'expired' ? 'history' : 'cancel'))"></span>
                                                        <span x-text="ms.status.replace('_', ' ')"></span>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400" x-text="new Date(ms.starts_at).toLocaleDateString('ro-RO')"></td>
                                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium" x-text="new Date(ms.expires_at).toLocaleDateString('ro-RO')"></td>
                                            </tr>
                                        </template>
                                        <template x-if="mySubscriptions.length === 0 && !loading">
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-700 mb-2">inbox</span>
                                                        <p>Nu există abonamente înregistrate pe acest cont.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards -->
                            <div class="md:hidden flex-1 overflow-y-auto p-4 space-y-4">
                                <template x-for="ms in mySubscriptions" :key="ms.id">
                                    <div class="bg-slate-50/50 dark:bg-slate-900/20 border border-slate-100 dark:border-slate-800/60 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                                        
                                        <!-- Decorative Strip -->
                                        <div class="absolute left-0 top-0 bottom-0 w-1"
                                             :class="{
                                                 'bg-green-400': ms.status === 'active_paid',
                                                 'bg-amber-400': ms.status === 'active_pending',
                                                 'bg-slate-400': ms.status === 'expired',
                                                 'bg-red-400': ms.status === 'cancelled'
                                             }"></div>

                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h4 class="font-bold text-slate-900 dark:text-white text-base" x-text="ms.subscription?.name || 'Abonament'"></h4>
                                                <div class="text-sm text-slate-500 mt-0.5">
                                                    <span class="font-bold text-primary" x-text="ms.subscription?.price || '-'"></span> lei / <span x-text="ms.subscription?.period ? ms.subscription.period.replace(/_/g, ' ') : '-'"></span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] sm:text-[11px] font-bold uppercase tracking-wide inline-flex items-center gap-1 shrink-0"
                                                :class="{
                                                    'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400': ms.status === 'active_paid',
                                                    'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400': ms.status === 'active_pending',
                                                    'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400': ms.status === 'expired',
                                                    'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400': ms.status === 'cancelled'
                                                }">
                                                <span class="material-symbols-outlined text-[13px]" x-text="ms.status === 'active_paid' ? 'check_circle' : (ms.status === 'active_pending' ? 'pending' : (ms.status === 'expired' ? 'history' : 'cancel'))"></span>
                                                <span x-text="ms.status.replace('_', ' ')"></span>
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/60 text-sm">
                                            <div>
                                                <p class="text-xs text-slate-400 uppercase tracking-wide font-bold mb-1">Activare</p>
                                                <p class="text-slate-700 dark:text-slate-300 font-medium flex items-center gap-1.5 flex-wrap">
                                                    <span class="material-symbols-outlined text-[16px] text-slate-400">event_available</span>
                                                    <span x-text="new Date(ms.starts_at).toLocaleDateString('ro-RO')"></span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-400 uppercase tracking-wide font-bold mb-1">Expirare</p>
                                                <p class="text-slate-700 dark:text-slate-300 font-medium flex items-center gap-1.5 flex-wrap">
                                                    <span class="material-symbols-outlined text-[16px] text-slate-400">event_busy</span>
                                                    <span x-text="new Date(ms.expires_at).toLocaleDateString('ro-RO')"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="mySubscriptions.length === 0 && !loading">
                                    <div class="py-12 text-center border border-slate-100 dark:border-slate-800/60 border-dashed rounded-2xl bg-slate-50/30 dark:bg-slate-900/10">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-700 mb-2">inbox</span>
                                        <p class="text-slate-500 text-sm">Nu există abonamente.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

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
