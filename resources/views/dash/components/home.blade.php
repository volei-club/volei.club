            <!-- HOME VIEW -->
            <div x-show="currentPage === '/dash'">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-3xl">waving_hand</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-1">Salutare, <span x-text="user?.name.split(' ')[0]"></span>!</h2>
                        <p class="text-slate-500">Iată un rezumat al activității tale recente. Alege o opțiune din meniul principal pentru a începe administrarea.</p>
                    </div>
                </div>
            </div>
