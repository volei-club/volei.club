<div x-show="currentPage === '/dash/sistem'" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Sistem & Mentenanță</h1>
            <p class="text-slate-500 text-sm">Gestionare exporturi, importuri și date globale.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="systemManager()">
        <!-- Export Section -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-700/50">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <span class="material-symbols-outlined">download</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Export Date</h3>
                    <p class="text-xs text-slate-500">Descarcă datele sistemului în format JSON.</p>
                </div>
            </div>

            <div class="space-y-3">
                <button @click="exportData('all')" class="w-full flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 hover:bg-primary hover:text-white rounded-2xl transition-all group border border-transparent hover:border-primary/20">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-white">database</span>
                        <div class="text-left">
                            <div class="text-sm font-bold">Tot Sistemul</div>
                            <div class="text-[10px] opacity-60">Backup complet (toate tabelele)</div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <template x-for="type in types" :key="type.id">
                        <button @click="exportData(type.id)" class="flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all border border-slate-100 dark:border-slate-700/30">
                            <span class="material-symbols-outlined text-slate-400 mb-2" x-text="type.icon"></span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="type.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-700/50">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <span class="material-symbols-outlined">upload_file</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Import Date</h3>
                    <p class="text-xs text-slate-500">Încarcă un fișier JSON pentru actualizare/adăugare.</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Dropzone -->
                <div class="relative group">
                    <input type="file" @change="handleFileSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".json">
                    <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-8 flex flex-col items-center justify-center transition-colors group-hover:border-primary/50 bg-slate-50/50 dark:bg-slate-900/30">
                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-3 group-hover:scale-110 transition-transform">cloud_upload</span>
                        <div class="text-sm font-medium text-slate-600 dark:text-slate-400" x-text="selectedFile ? selectedFile.name : 'Alege un fișier JSON sau trage-l aici'"></div>
                        <div class="text-[10px] text-slate-400 mt-1" x-show="!selectedFile">Maxim 50MB per fișier</div>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-blue-50 dark:bg-blue-900/10 rounded-2xl border border-blue-100/50 dark:border-blue-800/20">
                    <span class="material-symbols-outlined text-blue-500 text-xl">info</span>
                    <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-relaxed italic">
                        <strong>Atenție:</strong> Elementele care au un <strong>ID</strong> identic în sistem vor fi <strong>suprascrise</strong>. Elementele noi (fără ID) vor fi adăugate automat.
                    </p>
                </div>

                <button @click="importData()" 
                        :disabled="!selectedFile || importing"
                        class="w-full h-12 bg-primary hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold flex items-center justify-center gap-2 transition-all shadow-lg shadow-primary/20">
                    <span x-show="!importing" class="material-symbols-outlined">publish</span>
                    <span x-show="importing" class="material-symbols-outlined animate-spin">progress_activity</span>
                    <span x-text="importing ? 'Se importă datele...' : 'Lansează Importul'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
