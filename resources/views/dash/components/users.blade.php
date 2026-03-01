            <!-- USERS VIEW -->
            <div x-show="currentPage.startsWith('/dash/utilizatori')" x-data="userManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Membrii</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">person_add</span>
                        Adaugă Membru
                    </button>
                </div>

                <!-- Filtre -->
                <div class="mb-6 flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-64">
                        <select id="userFilterRole" x-model="filters.role" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                            <option value="">Toate Rolurile</option>
                            <template x-if="user?.role === 'administrator'">
                                <option value="administrator">Administrator</option>
                            </template>
                            <template x-if="user?.role === 'administrator'">
                                <option value="manager">Manager de Club</option>
                            </template>
                            <option value="antrenor">Antrenor</option>
                            <option value="parinte">Părinte</option>
                            <option value="sportiv">Sportiv</option>
                        </select>
                    </div>

                    <template x-if="user?.role === 'administrator'">
                        <div class="w-full md:w-64">
                            <select id="userFilterClub" x-model="filters.club_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div class="w-full md:w-64" x-show="user?.role === 'manager' || (user?.role === 'administrator' && filters.club_id)">
                        <select id="userFilterTeam" x-model="filters.team_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                            <option value="">Toate Grupele</option>
                            <template x-for="t in availableFilterTeams" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="w-full md:w-64" x-show="filters.team_id">
                        <select id="userFilterSquad" x-model="filters.squad_id" @change="fetchUsers(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                            <option value="">Toate Echipele</option>
                            <template x-for="s in availableFilterSquads" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Tabel & Carduri Utilizatori -->
                <div class="bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                    <th class="px-6 py-4 font-bold">Nume & Email</th>
                                    <th class="px-6 py-4 font-bold">Rol / Statut</th>
                                    <th class="px-6 py-4 font-bold">Club</th>
                                    <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                                <template x-for="usr in users" :key="usr.id">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white" x-text="usr.name"></div>
                                            <div class="text-slate-500" x-text="usr.email"></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-xs font-bold uppercase tracking-wide inline-block mb-1" x-text="usr.role"></span>
                                            <div class="mt-1">
                                                <span x-show="usr.is_active" class="flex items-center text-xs text-green-600 dark:text-green-400 font-semibold"><span class="w-2 h-2 rounded-full bg-green-500 mr-1.5"></span>Activ</span>
                                                <span x-show="!usr.is_active" class="flex items-center text-xs text-red-600 dark:text-red-400 font-semibold"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>Inactiv</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500" x-text="usr.club ? usr.club.name : '-'"></td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="openModal(usr)" class="text-slate-400 hover:text-primary transition-colors p-1" title="Editează Utilizator">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </button>
                                            <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                                <button @click="impersonateUser(usr)" class="text-slate-400 hover:text-blue-500 transition-colors p-1 ml-1" title="Loghează-te ca">
                                                    <span class="material-symbols-outlined text-sm">login</span>
                                                </button>
                                            </template>
                                            <button @click="deleteUser(usr.id)" class="text-slate-400 hover:text-red-500 transition-colors p-1 ml-1" title="Șterge Utilizator">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="grid grid-cols-1 gap-4 md:hidden">
                        <template x-for="usr in users" :key="usr.id">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm relative group">
                                <div class="absolute top-4 right-4 flex gap-1">
                                    <button @click="openModal(usr)" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-700 text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors flex items-center justify-center">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                        <button @click="impersonateUser(usr)" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-700 text-slate-400 hover:text-blue-500 hover:bg-blue-50 transition-colors flex items-center justify-center" title="Loghează-te ca">
                                            <span class="material-symbols-outlined text-sm">login</span>
                                        </button>
                                    </template>
                                    <button @click="deleteUser(usr.id)" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-700 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors flex items-center justify-center">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                                
                                <div class="font-bold text-lg text-slate-900 dark:text-white mb-1 pr-8" x-text="usr.name"></div>
                                <div class="text-slate-500 text-sm mb-4" x-text="usr.email"></div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-xs font-bold uppercase tracking-wide" x-text="usr.role"></span>
                                    <span x-show="usr.is_active" class="flex items-center px-2 py-1 bg-green-50 dark:bg-green-900/30 text-xs text-green-600 dark:text-green-400 font-semibold rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Activ</span>
                                    <span x-show="!usr.is_active" class="flex items-center px-2 py-1 bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-400 font-semibold rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Inactiv</span>
                                </div>
                                
                                <div class="flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400 pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <span class="material-symbols-outlined text-[18px] mr-2">domain</span>
                                    <span x-text="usr.club ? usr.club.name : '-'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="users.length === 0 && !loading" class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl md:rounded-none">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">group_off</span>
                        <p class="text-slate-500">Niciun utilizator găsit.</p>
                    </div>
                </div>

                <!-- Modal Adăugare User -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Utilizator' : 'Adaugă Utilizator Nou'"></h3>
                        </div>
                        <form @submit.prevent="saveUser()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nume Complet</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Adresă Email</label>
                                <input x-model="form.email" type="email" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Rol</label>
                                <select x-model="form.role" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected>Alege un rol...</option>
                                    <template x-if="user?.role === 'administrator'">
                                        <option value="administrator">Administrator (Global)</option>
                                    </template>
                                    <template x-if="user?.role === 'administrator'">
                                        <option value="manager">Manager de Club</option>
                                    </template>
                                    <option value="antrenor">Antrenor</option>
                                    <option value="parinte">Părinte</option>
                                    <option value="sportiv">Sportiv</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    <span x-text="form.id ? 'Parolă Nouă (opțional)' : 'Parolă (opțional)'"></span>
                                </label>
                                <input x-model="form.password" type="password" placeholder="Minim 6 caractere..." class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all"/>
                                <p class="text-xs text-slate-500 mt-1">Dacă e lăsat gol la creare, se va genera o parolă temporară pe care utilizatorul și-o va reseta.</p>
                            </div>

                            <div class="mb-5 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-green-500"></div>
                                    <span class="ml-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Cont Activ</span>
                                </label>
                            </div>

                            <template x-if="user?.role === 'administrator' && form.role !== 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Asignează la Clubul</label>
                                    <select x-model="form.club_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="">Niciun club selectat</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
                            <!-- Team Association (Checkboxes) -->
                            <template x-if="(form.role === 'antrenor' || form.role === 'sportiv') && (user?.role === 'manager' || form.club_id)">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Selectează Grupele</label>
                                    
                                    <template x-if="loadingTeams">
                                        <div class="text-xs text-slate-500 mb-2 flex items-center">
                                            <span class="material-symbols-outlined animate-spin text-sm mr-1">sync</span> Se încarcă grupele...
                                        </div>
                                    </template>
                                    
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableTeams.length === 0 && !loadingTeams">
                                            <div class="text-slate-500 text-sm italic">Acest club nu are asocieri de grupe încă.</div>
                                        </template>
                                        
                                        <template x-for="t in availableTeams" :key="t.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="t.id" x-model="form.team_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300" x-text="t.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableTeams.length > 0 && !loadingTeams">
                                         <p class="text-xs text-slate-500 mt-2">Bifează una sau mai multe grupe pentru a asocia utilizatorul.</p>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Squad Association (Checkboxes) -->
                            <template x-if="(form.role === 'antrenor' || form.role === 'sportiv') && form.team_ids.length > 0">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Selectează Echipele</label>
                                    
                                    <template x-if="loadingSquads">
                                        <div class="text-xs text-slate-500 mb-2 flex items-center">
                                            <span class="material-symbols-outlined animate-spin text-sm mr-1">sync</span> Se încarcă echipele...
                                        </div>
                                    </template>
                                    
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableSquads.length === 0 && !loadingSquads">
                                            <div class="text-slate-500 text-sm italic">Nu există echipe asociate formatiilor selectate.</div>
                                        </template>
                                        
                                        <template x-for="s in availableSquads" :key="s.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="s.id" x-model="form.squad_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <div class="ml-3">
                                                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300" x-text="s.name"></span>
                                                    <span class="block text-xs text-slate-500" x-text="s.team?.name"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableSquads.length > 0 && !loadingSquads">
                                         <p class="text-xs text-slate-500 mt-2">Bifează una sau mai multe echipe pentru a asocia utilizatorul.</p>
                                    </template>
                                </div>
                            </template>
                            
                            <template x-if="error">
                                <div class="p-3 mb-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100" x-text="error"></div>
                            </template>
                            
                            </div>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Anulare</button>
                                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                    <span x-show="saving" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                    Salvează
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
