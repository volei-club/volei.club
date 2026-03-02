<div x-show="currentPage === '/dash/audit'" x-data="auditManager()" class="h-full flex flex-col relative">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Audit Sistem</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Jurnalul activităților și modificărilor din platformă</p>
        </div>
        <div class="flex gap-2">
            <button @click="fetchLogs()" class="p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm outline-none flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-primary active:scale-95">
                <span class="material-symbols-outlined text-[20px]">refresh</span>
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading" style="display:none" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm rounded-2xl">
        <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
        <p class="text-slate-500 font-medium">Se încarcă jurnalul...</p>
    </div>

    <!-- Filtre Audit -->
    <div class="mb-6 flex flex-wrap gap-4">
        <div class="w-full md:w-48">
            <select x-model="filters.event" @change="fetchLogs()" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm appearance-none cursor-pointer">
                <option value="">Toate Evenimentele</option>
                <option value="created">Creat</option>
                <option value="updated">Actualizat</option>
                <option value="deleted">Șters</option>
            </select>
        </div>
        <div class="w-full md:w-48">
            <select x-model="filters.type" @change="fetchLogs()" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm appearance-none cursor-pointer">
                <option value="">Toate Tipurile</option>
                <option value="User">Utilizatori</option>
                <option value="Subscription">Abonamente</option>
                <option value="Club">Cluburi</option>
                <option value="Team">Grupe</option>
                <option value="Squad">Echipe</option>
            </select>
        </div>
    </div>

    <!-- Tabel Audit -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                        <th class="px-6 py-4 font-bold">Utilizator</th>
                        <th class="px-6 py-4 font-bold">Acțiune</th>
                        <th class="px-6 py-4 font-bold">Entitate</th>
                        <th class="px-6 py-4 font-bold">Modificări</th>
                        <th class="px-6 py-4 font-bold">Dată & IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <template x-for="log in logs" :key="log.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <template x-if="log.user">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="log.user.name"></div>
                                        <div class="text-[11px] text-slate-500" x-text="log.user.email"></div>
                                    </div>
                                </template>
                                <template x-if="!log.user">
                                    <span class="text-slate-400 italic">Sistem / Anonim</span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': log.event === 'created',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': log.event === 'updated',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': log.event === 'deleted'
                                }" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm" x-text="log.event === 'created' ? 'Creat' : (log.event === 'updated' ? 'Editat' : 'Șters')"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700 dark:text-slate-300" x-text="log.auditable_type.split('\\').pop()"></div>
                                <div class="text-[11px] text-slate-500 font-mono" x-text="log.auditable_id.substring(0,8) + '...'"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs overflow-hidden">
                                    <template x-if="log.event === 'updated'">
                                        <div class="space-y-1">
                                            <template x-for="(val, key) in log.new_values" :key="key">
                                                <div class="text-[11px] truncate">
                                                    <span class="font-bold text-slate-600 dark:text-slate-400" x-text="translateAuditKey(key) + ': '"></span>
                                                    <span class="text-red-500 line-through" x-text="translateAuditValue(key, log.old_values[key])"></span>
                                                    <span class="text-slate-400 mx-1">→</span>
                                                    <span class="text-green-600 dark:text-green-400 font-medium" x-text="translateAuditValue(key, val)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="log.event === 'created'">
                                        <button @click="openLogDetails(log)" class="text-xs text-primary hover:text-primary-dark font-bold flex items-center gap-1.5 transition-colors group/link">
                                            <span class="material-symbols-outlined text-[18px] transition-transform group-hover/link:scale-110">visibility</span>
                                            <span>Vezi date inițiale</span>
                                        </button>
                                    </template>
                                    <template x-if="log.event === 'deleted'">
                                        <button @click="openLogDetails(log)" class="text-xs text-red-500 hover:text-red-600 font-bold flex items-center gap-1.5 transition-colors group/link">
                                            <span class="material-symbols-outlined text-[18px] transition-transform group-hover/link:scale-110">history</span>
                                            <span>Vezi date șterse</span>
                                        </button>
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-900 dark:text-white font-medium" x-text="new Date(log.created_at).toLocaleString('ro-RO')"></div>
                                <div class="text-[10px] text-slate-500 flex items-center mt-1 opacity-70">
                                    <span class="material-symbols-outlined text-[14px] mr-1">lan</span>
                                    <span x-text="log.ip_address"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/30 dark:bg-slate-900/20">
            <template x-for="log in logs" :key="log.id">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex justify-between items-start border-b border-slate-50 dark:border-slate-800 pb-3">
                        <div class="flex-1">
                           <template x-if="log.user">
                               <div>
                                   <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="log.user.name"></div>
                                   <div class="text-[10px] text-slate-500" x-text="log.user.email"></div>
                               </div>
                           </template>
                           <template x-if="!log.user">
                               <span class="text-slate-400 italic text-xs">Sistem / Anonim</span>
                           </template>
                        </div>
                        <span :class="{
                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': log.event === 'created',
                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': log.event === 'updated',
                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': log.event === 'deleted'
                        }" class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider shrink-0" x-text="log.event === 'created' ? 'Creat' : (log.event === 'updated' ? 'Editat' : 'Șters')"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Entitate</p>
                            <div class="font-semibold text-slate-700 dark:text-slate-300" x-text="log.auditable_type.split('\\').pop()"></div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Dată</p>
                            <div class="text-slate-600 dark:text-slate-400 font-medium" x-text="new Date(log.created_at).toLocaleString('ro-RO')"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Modificări</p>
                        <template x-if="log.event === 'updated'">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                <template x-for="(val, key) in log.new_values" :key="key">
                                    <div class="text-[11px] mb-1 last:mb-0 break-all">
                                        <span class="font-bold text-slate-600 dark:text-slate-400" x-text="translateAuditKey(key) + ': '"></span>
                                        <span class="text-green-600 dark:text-green-400 font-medium" x-text="translateAuditValue(key, val)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="log.event === 'created' || log.event === 'deleted'">
                            <button @click="openLogDetails(log)" class="w-full py-2 bg-slate-50 dark:bg-slate-800 text-xs font-bold text-primary rounded-xl flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]" x-text="log.event === 'created' ? 'visibility' : 'history'"></span>
                                <span x-text="log.event === 'created' ? 'Vezi date inițiale' : 'Vezi date șterse'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination Audit -->
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
            <span class="text-xs text-slate-500">
                Pagina <span class="font-bold" x-text="pagination.current_page"></span> din <span class="font-bold" x-text="pagination.last_page"></span>
            </span>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold hover:bg-slate-50 disabled:opacity-50 transition-all">Anterior</button>
                <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold hover:bg-slate-50 disabled:opacity-50 transition-all">Următor</button>
            </div>
        </div>
    </div>

    <!-- Modal Detalii Log -->
    <div x-show="showDetailsModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[85vh]">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-xl font-bold">Detalii Obiect</h3>
                <button @click="showDetailsModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="p-6 overflow-y-auto bg-slate-50 dark:bg-slate-900 font-mono text-xs">
                <pre class="whitespace-pre-wrap break-all" x-text="JSON.stringify(selectedLogData, null, 2)"></pre>
            </div>
            <div class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <button @click="showDetailsModal = false" class="px-6 py-2 bg-primary text-white rounded-xl font-bold">Închide</button>
            </div>
        </div>
    </div>
</div>
