            <!-- USERS VIEW -->
            <div x-show="currentPage.startsWith('/dash/membri')" x-data="userManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Membri</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 outline-none flex items-center justify-center sm:justify-start gap-2">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        <span>Adaugă Membru</span>
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

                <!-- Tabel & Carduri -->
                <div class="bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                    <th class="px-6 py-4 font-bold">Nume & Email</th>
                                    <th class="px-6 py-4 font-bold">Rol / Statut</th>
                                    <th class="px-6 py-4 font-bold">Club</th>
                                    <th class="px-6 py-4 font-bold">Abonament</th>
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
                                        <td class="px-6 py-4">
                                            <template x-if="usr.role === 'sportiv'">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="usr.active_subscription">
                                                        <span :class="{
                                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800': usr.active_subscription.status === 'active_paid',
                                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800': usr.active_subscription.status === 'active_pending'
                                                        }" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold border" :title="statusLabels[usr.active_subscription.status]">
                                                            <span class="material-symbols-outlined text-[14px] mr-1" x-text="usr.active_subscription.status === 'active_paid' ? 'check_circle' : 'pending_actions'"></span>
                                                            <span x-text="statusLabels[usr.active_subscription.status]"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!usr.active_subscription && usr.upcoming_subscription">
                                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-xs font-bold border border-blue-200 dark:border-blue-800" :title="'Începe pe ' + formatDate(usr.upcoming_subscription.starts_at)">
                                                            <span class="material-symbols-outlined text-[14px] mr-1">schedule</span>
                                                            Programat
                                                        </span>
                                                    </template>
                                                    <template x-if="!usr.active_subscription && !usr.upcoming_subscription">
                                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold border border-red-200 dark:border-red-800" title="Fără Abonament">
                                                            <span class="material-symbols-outlined text-[14px] mr-1">cancel</span>
                                                            Inactiv
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="usr.role !== 'sportiv'">
                                                <span class="text-slate-400 text-xs italic">-</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-1">
                                                <template x-if="usr.role === 'sportiv'">
                                                    <div class="flex gap-1">
                                                        <button @click="openSubscriptionModal(usr)" class="p-2 text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/30 rounded-lg transition-colors" title="Gestionează Abonament">
                                                            <span class="material-symbols-outlined text-[20px]">loyalty</span>
                                                        </button>
                                                        <button @click="openSubscriptionHistory(usr)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Istoric Abonamente">
                                                            <span class="material-symbols-outlined text-[20px]">history</span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <button @click="openModal(usr)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Editează Membru">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                                    <button @click="impersonateUser(usr)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="Loghează-te ca">
                                                        <span class="material-symbols-outlined text-[20px]">login</span>
                                                    </button>
                                                </template>
                                                <button @click="deleteUser(usr.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Șterge Membru">
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
                    <div class="grid grid-cols-1 gap-4 md:hidden">
                        <template x-for="usr in users" :key="usr.id">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm relative group" :class="usr.role === 'sportiv' && !usr.active_subscription ? 'border-red-200 dark:border-red-900/50' : ''">
                                <div class="absolute top-4 right-4 flex gap-1">
                                    <template x-if="usr.role === 'sportiv'">
                                        <div class="flex gap-1">
                                            <button @click="openSubscriptionModal(usr)" class="p-2 text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/30 rounded-lg transition-colors" title="Gestionează Abonament">
                                                <span class="material-symbols-outlined text-[20px]">loyalty</span>
                                            </button>
                                            <button @click="openSubscriptionHistory(usr)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Istoric Abonamente">
                                                <span class="material-symbols-outlined text-[20px]">history</span>
                                            </button>
                                        </div>
                                    </template>
                                    <button @click="openModal(usr)" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Editează Membru">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <template x-if="user?.role === 'administrator' && usr.id !== user?.id">
                                        <button @click="impersonateUser(usr)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="Loghează-te ca">
                                            <span class="material-symbols-outlined text-[20px]">login</span>
                                        </button>
                                    </template>
                                    <button @click="deleteUser(usr.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Șterge Membru">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                                
                                <div class="font-bold text-lg text-slate-900 dark:text-white mb-1 pr-24" x-text="usr.name"></div>
                                <div class="text-slate-500 text-sm mb-4" x-text="usr.email"></div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-xs font-bold uppercase tracking-wide" x-text="usr.role"></span>
                                    <span x-show="usr.is_active" class="flex items-center px-2 py-1 bg-green-50 dark:bg-green-900/30 text-xs text-green-600 dark:text-green-400 font-semibold rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Activ</span>
                                    <span x-show="!usr.is_active" class="flex items-center px-2 py-1 bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-400 font-semibold rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Inactiv</span>
                                </div>
                                
                                <template x-if="usr.role === 'sportiv'">
                                    <div class="mb-4 flex flex-wrap gap-2">
                                        <template x-if="usr.active_subscription">
                                            <span :class="{
                                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': usr.active_subscription.status === 'active_paid',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': usr.active_subscription.status === 'active_pending'
                                            }" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold mt-1">
                                                <span class="material-symbols-outlined text-[14px] mr-1" x-text="usr.active_subscription.status === 'active_paid' ? 'check_circle' : 'pending_actions'"></span>
                                                <span x-text="statusLabels[usr.active_subscription.status]"></span>
                                            </span>
                                        </template>
                                        <template x-if="!usr.active_subscription">
                                            <span class="inline-flex items-center justify-center px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold mt-1">
                                                <span class="material-symbols-outlined text-[14px] mr-1">warning</span>
                                                Fără Abonament Activ
                                            </span>
                                        </template>
                                    </div>
                                </template>

                                <div class="flex items-center text-sm font-semibold text-slate-600 dark:text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-700">
                                    <span class="material-symbols-outlined text-[18px] mr-2">domain</span>
                                    <span x-text="usr.club ? usr.club.name : '-'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="users.length === 0 && !loading" class="text-center py-20 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 border-dashed">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-700 mb-4">group_off</span>
                        <p class="text-slate-500">Niciun Membru găsit.</p>
                    </div>
                </div>

                <!-- Modal Adăugare User -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Membru' : 'Adaugă Membru Nou'"></h3>
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
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nr. Telefon (opțional)</label>
                                <input x-model="form.phone" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all" placeholder="Ex: 0722 ..."/>
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
                                <p class="text-xs text-slate-500 mt-1">Dacă e lăsat gol la creare, se va genera o parolă temporară pe care Membruul și-o va reseta.</p>
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
                                         <p class="text-xs text-slate-500 mt-2">Bifează una sau mai multe grupe pentru a asocia Membruul.</p>
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
                                         <p class="text-xs text-slate-500 mt-2">Bifează una sau mai multe echipe pentru a asocia Membruul.</p>
                                    </template>
                                </div>
                            </template>

                            <!-- Student Association for Parents (Checkboxes) -->
                            <template x-if="form.role === 'parinte' && (user?.role === 'manager' || form.club_id)">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Asociază Jucători</label>
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4 rounded-xl max-h-48 overflow-y-auto">
                                        <template x-if="availableStudents.length === 0">
                                            <div class="text-slate-500 text-sm italic text-center py-4">Niciun jucător găsit în acest club.</div>
                                        </template>
                                        <template x-for="s in availableStudents" :key="s.id">
                                            <label class="flex items-center cursor-pointer hover:bg-white dark:hover:bg-slate-800 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                                <input type="checkbox" :value="s.id" x-model="form.child_ids" class="w-5 h-5 text-primary bg-white border-slate-300 rounded focus:ring-primary focus:ring-2 dark:bg-slate-800 dark:border-slate-600 transition-all cursor-pointer">
                                                <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300" x-text="s.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <template x-if="availableStudents.length > 0">
                                        <p class="text-xs text-slate-500 mt-2">Selectează unul sau mai mulți copii (jucători) ai acestui părinte.</p>
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

                <!-- Modal Abonament Sportiv -->
                <div x-show="showSubscriptionModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1" x-text="subscriptionForm.id ? 'Editează Abonament' : 'Abonament Sportiv'"></h3>
                            <p class="text-sm font-semibold text-slate-500" x-text="subscriptionForm.user_name"></p>
                        </div>
                        <form @submit.prevent="saveUserSubscription()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                                
                                <template x-if="subscriptionForm.current_subscription">
                                    <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Abonament Curent</span>
                                            <template x-if="subscriptionForm.current_subscription.status === 'active_paid'">
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-[10px] font-bold">Plătit</span>
                                            </template>
                                            <template x-if="subscriptionForm.current_subscription.status === 'active_pending'">
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded text-[10px] font-bold">Așteaptă Plată</span>
                                            </template>
                                        </div>
                                        <div class="font-bold text-slate-900 dark:text-white mb-1" x-text="subscriptionForm.current_subscription.subscription.name"></div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                                            Valabil până la: <strong x-text="new Date(subscriptionForm.current_subscription.expires_at).toLocaleDateString()"></strong>
                                        </div>

                                        <template x-if="subscriptionForm.current_subscription.status === 'active_pending'">
                                            <button type="button" @click="updateSubscriptionStatus(subscriptionForm.current_subscription.id, 'active_paid')" :disabled="savingSubscription" class="w-full py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold text-sm transition-colors flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[16px] mr-1.5">check_circle</span>
                                                Marchează ca Plătit
                                            </button>
                                        </template>

                                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 text-center">
                                            <button type="button" @click="updateSubscriptionStatus(subscriptionForm.current_subscription.id, 'cancelled')" :disabled="savingSubscription" class="text-red-500 hover:text-red-600 text-xs font-bold uppercase tracking-wider transition-colors inline-block pb-1">Anulează Abonamentul Curent</button>
                                        </div>
                                    </div>
                                </template>

                                <div class="mb-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-3" x-text="subscriptionForm.current_subscription ? 'Generează Perioadă Nouă' : 'Asociază Abonament Nou'"></h4>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Tip Abonament</label>
                                            <select x-model="subscriptionForm.subscription_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                                <option value="" disabled selected>Alege abonamentul...</option>
                                                <template x-for="sub in availableSubscriptions" :key="sub.id">
                                                    <option :value="sub.id" x-text="sub.name + ' (' + sub.price + ' lei / ' + sub.period.replace('_', ' ') + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Data Început</label>
                                            <input type="date" x-model="subscriptionForm.starts_at" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Statut</label>
                                            <select x-model="subscriptionForm.status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                                <option value="active_paid">Plătit (Activ)</option>
                                                <option value="active_pending">Așteaptă Plată</option>
                                                <option value="cancelled">Anulat</option>
                                                <option value="expired">Expirat</option>
                                            </select>
                                        </div>
                                    </div>
                                    <template x-if="availableSubscriptions.length === 0">
                                        <p class="text-xs text-red-500 mt-1">Acest club nu are niciun tip de abonament definit. Mergeți la meniul "Abonamente" pentru a crea unul.</p>
                                    </template>
                                </div>

                                <template x-if="subscriptionError">
                                    <div class="p-3 my-4 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100 font-medium" x-text="subscriptionError"></div>
                                </template>
                            </div>

                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                                <button type="button" @click="showSubscriptionModal = false" class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Închide</button>
                                <template x-if="subscriptionForm.subscription_id">
                                    <button type="submit" :disabled="savingSubscription" class="px-5 py-2.5 rounded-xl font-semibold bg-primary text-white hover:bg-primary-dark transition-colors flex items-center disabled:opacity-50">
                                        <span x-show="savingSubscription" class="material-symbols-outlined animate-spin mr-2 text-sm">progress_activity</span>
                                        <span x-text="subscriptionForm.id ? 'Actualizează Abonament' : 'Emite Abonament'"></span>
                                    </button>
                                </template>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Subscription History Modal -->
                <div x-show="showSubscriptionHistoryModal" 
                     class="fixed inset-0 z-[70] overflow-y-auto" 
                     x-cloak style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showSubscriptionHistoryModal" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
                             @click="showSubscriptionHistoryModal = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="showSubscriptionHistoryModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="inline-block w-full max-w-2xl p-0 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-3xl border border-slate-100 dark:border-slate-700">
                            
                            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl shadow-sm border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined">history</span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="historyUser ? 'Istoric Abonamente: ' + historyUser.name : 'Istoric Abonamente'"></h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Arhiva tuturor plăților și abonamentelor active</p>
                                    </div>
                                </div>
                                <button @click="showSubscriptionHistoryModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="px-6 py-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                <template x-if="historyUser && historyUser.subscriptions && historyUser.subscriptions.length > 0">
                                    <div class="space-y-3">
                                        <template x-for="sub in [...historyUser.subscriptions].sort((a,b) => new Date(b.created_at) - new Date(a.created_at))" :key="sub.id">
                                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-700 hover:shadow-md transition-all bg-white dark:bg-slate-800/50 group">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex gap-3">
                                                        <div :class="{
                                                            'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': sub.status === 'active_paid',
                                                            'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': sub.status === 'active_pending',
                                                            'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-500': sub.status === 'expired',
                                                            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': sub.status === 'cancelled'
                                                        }" class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-700 shadow-sm">
                                                            <span class="material-symbols-outlined" x-text="sub.status === 'active_paid' ? 'check_circle' : (sub.status === 'active_pending' ? 'pending_actions' : (sub.status === 'expired' ? 'history' : 'cancel'))"></span>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900 dark:text-white" x-text="sub.subscription ? sub.subscription.name : 'Abonament Șters'"></div>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="formatDate(sub.starts_at)"></span>
                                                                <span class="text-slate-300">→</span>
                                                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="formatDate(sub.expires_at)"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-right flex flex-col items-end">
                                                        <span :class="{
                                                            'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-400 border-green-100 dark:border-green-800': sub.status === 'active_paid',
                                                            'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border-amber-100 dark:border-amber-800': sub.status === 'active_pending',
                                                            'bg-slate-50 text-slate-600 dark:bg-slate-700/40 dark:text-slate-400 border-slate-100 dark:border-slate-700': sub.status === 'expired',
                                                            'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-400 border-red-100 dark:border-red-800': sub.status === 'cancelled'
                                                        }" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border shadow-sm" x-text="statusLabels[sub.status] || sub.status"></span>
                                                        <div class="text-[10px] text-slate-400 mt-2">Creat pe <span x-text="formatDate(sub.created_at)"></span></div>
                                                        
                                                        <div class="flex items-center gap-3 mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button @click="editUserSubscription(sub)" class="text-[10px] font-bold text-blue-500 hover:text-blue-600 uppercase tracking-wider flex items-center transition-colors">
                                                                <span class="material-symbols-outlined text-[14px] mr-1">edit</span> Editează
                                                            </button>
                                                            <button @click="deleteUserSubscription(sub.id)" class="text-[10px] font-bold text-red-500 hover:text-red-600 uppercase tracking-wider flex items-center transition-colors">
                                                                <span class="material-symbols-outlined text-[14px] mr-1">delete</span> Șterge
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!historyUser || !historyUser.subscriptions || historyUser.subscriptions.length === 0">
                                    <div class="text-center py-16">
                                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200 dark:border-slate-700">
                                            <span class="material-symbols-outlined text-4xl text-slate-300">subscriptions</span>
                                        </div>
                                        <h4 class="text-slate-900 dark:text-white font-bold mb-1">Niciun abonament</h4>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">Acest sportiv nu are niciun abonament înregistrat în istoric.</p>
                                    </div>
                                </template>
                            </div>

                            <div class="px-6 py-5 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/20 flex justify-end">
                                <button @click="showSubscriptionHistoryModal = false" class="px-8 py-3 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 rounded-2xl transition-all border border-slate-200 dark:border-slate-600 shadow-sm hover:shadow-md active:scale-95">
                                    Închide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
