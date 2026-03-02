<div x-show="currentPage === '/dash/locatii'" x-data="locationManager()" class="h-full flex flex-col">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Locații</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Gestionează locațiile de antrenament și joc</p>
        </div>
        <button @click="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none">
            <span class="material-symbols-outlined text-[20px]">add_location</span>
            <span>Adaugă Locație</span>
        </button>
    </div>

    <!-- Lista Locatii -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex-1 flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                        <th class="px-6 py-4 font-bold">Nume Locație</th>
                        <th class="px-6 py-4 font-bold">Adresă</th>
                        <th x-show="user?.role === 'administrator'" class="px-6 py-4 font-bold">Club</th>
                        <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm italic">
                    <template x-for="loc in locations" :key="loc.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors not-italic">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white" x-text="loc.name"></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400" x-text="loc.address"></td>
                            <td x-show="user?.role === 'administrator'" class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-100/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 rounded-lg text-[11px] font-bold uppercase tracking-wide inline-flex items-center" x-text="loc.club?.name || 'N/A'"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openModal(loc)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Editează">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button @click="deleteLocation(loc.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Șterge">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            
            <template x-if="locations.length === 0">
                <div class="text-center py-20 border-t border-slate-100 dark:border-slate-700 border-dashed">
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-5xl mb-4">location_off</span>
                    <p class="text-slate-500">Nu există locații definite încă.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal Adaugare/Editare -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div @click.away="showModal = false" class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden transition-all transform scale-100">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/20">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white" x-text="editingId ? 'Editează Locație' : 'Adaugă Locație'"></h3>
                <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form @submit.prevent="saveLocation()" class="p-6 space-y-4">
                <div x-show="user?.role === 'administrator' && !editingId" class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Club</label>
                    <select x-model="formData.club_id" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                        <option value="">Selectează Club</option>
                        <template x-for="club in allClubs" :key="club.id">
                            <option :value="club.id" x-text="club.name"></option>
                        </template>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nume Locație</label>
                    <input type="text" x-model="formData.name" required placeholder="Ex: Sala Polivalentă" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Adresă</label>
                    <textarea x-model="formData.address" required placeholder="Strada, Număr, Oraș..." rows="3" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showModal = false" class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                        Anulează
                    </button>
                    <button type="submit" :disabled="saving" class="flex-1 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 disabled:opacity-50 flex items-center justify-center gap-2">
                        <span x-show="saving" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                        <span x-text="editingId ? 'Salvează' : 'Adaugă'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
