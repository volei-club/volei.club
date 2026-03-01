<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Volei.Club / Dashboard</title>
    
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/router@1.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400..800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1e40af",
                        "primary-dark": "#1e3a8a",
                        "background-light": "#f1f5f9",
                        "background-dark": "#0f172a",
                        "sidebar-light": "#ffffff",
                        "sidebar-dark": "#1e293b",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen flex" x-data="dashboard()">
    
    <!-- Global Loader -->
    <div x-show="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-slate-900 transition-opacity">
        <span class="material-symbols-outlined animate-spin text-primary text-5xl">progress_activity</span>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="isMobileMenuOpen && !isLoading" 
         @click="isMobileMenuOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm md:hidden" 
         style="display: none;">
    </div>

    <!-- Sidebar Layout -->
    <aside :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
           class="w-64 bg-sidebar-light dark:bg-sidebar-dark border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 fixed md:relative z-40 inset-y-0 left-0 md:translate-x-0" 
           x-show="!isLoading" style="display: none;">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white mr-3">
                <span class="material-symbols-outlined text-sm">sports_volleyball</span>
            </div>
            <span class="text-lg font-bold tracking-tight">Volei.Club</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            
            <!-- Home -->
            <a href="/dash" @click.prevent="navigate('/dash'); isMobileMenuOpen = false;" 
               :class="currentPage === '/dash' ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
               class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-4">
                <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage === '/dash' ? 'fill-1' : ''">dashboard</span>
                Acasă
            </a>

            <!-- Admin Section -->
            <template x-if="user?.role === 'administrator'">
                <div>
                    <div class="px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Administrare</div>
                    
                    <a href="/dash/cluburi" @click.prevent="navigate('/dash/cluburi'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/cluburi') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/cluburi') ? 'fill-1' : ''">domain</span>
                        Cluburi
                    </a>

                    <a href="/dash/grupe" @click.prevent="navigate('/dash/grupe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/grupe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/grupe') ? 'fill-1' : ''">diversity_3</span>
                        Grupe
                    </a>

                    <a href="/dash/echipe" @click.prevent="navigate('/dash/echipe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/echipe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/echipe') ? 'fill-1' : ''">groups_2</span>
                        Echipe
                    </a>
                </div>
            </template>

            <!-- Management Section (Admins & Managers) -->
            <template x-if="user?.role === 'administrator' || user?.role === 'manager'">
                <div :class="user?.role === 'administrator' ? 'mt-4' : ''">
                    <div x-show="user?.role === 'manager'" class="px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Clubul Meu</div>
                    <a href="/dash/utilizatori" @click.prevent="navigate('/dash/utilizatori'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/utilizatori') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/utilizatori') ? 'fill-1' : ''">groups</span>
                        Utilizatori
                    </a>
                    
                    <a x-show="user?.role === 'manager'" href="/dash/grupe" @click.prevent="navigate('/dash/grupe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/grupe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors mb-1">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/grupe') ? 'fill-1' : ''">diversity_3</span>
                        Grupele Mele
                    </a>

                    <a x-show="user?.role === 'manager'" href="/dash/echipe" @click.prevent="navigate('/dash/echipe'); isMobileMenuOpen = false;"
                       :class="currentPage.startsWith('/dash/echipe') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                       class="flex items-center px-3 py-2.5 rounded-xl transition-colors">
                        <span class="material-symbols-outlined mr-3 text-xl" :class="currentPage.startsWith('/dash/echipe') ? 'fill-1' : ''">groups_2</span>
                        Echipele Mele
                    </a>
                </div>
            </template>
            
        </nav>

        <!-- User Profile Area (Bottom of Sidebar) -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center w-full">
                <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold shrink-0">
                    <span x-text="user?.name.charAt(0)"></span>
                </div>
                <div class="ml-3 truncate flex-1">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate" x-text="user?.name"></p>
                    <p class="text-xs text-slate-500 capitalize truncate" x-text="user?.role"></p>
                </div>
                <button @click="logout()" class="ml-2 text-slate-400 hover:text-red-500 transition-colors bg-slate-100 dark:bg-slate-800 p-2 rounded-lg" title="Deconectare">
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden" x-show="!isLoading" style="display: none;">
        
        <!-- Topbar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 md:px-6 justify-between shrink-0">
            <div class="flex items-center">
                <button @click="isMobileMenuOpen = true" class="md:hidden mr-4 text-slate-500 hover:text-slate-900 dark:hover:text-white focus:outline-none">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white" x-text="getPageTitle()"></h1>
            </div>
            <div class="text-sm text-slate-500 hidden sm:block" x-text="new Date().toLocaleDateString('ro-RO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></div>
        </header>

        <!-- Banner Impersonare Activa -->
        <template x-if="isImpersonating">
            <div class="bg-red-500 text-white px-4 py-3 flex items-center justify-between shrink-0 shadow-sm z-50 relative">
                <div class="flex items-center">
                    <span class="material-symbols-outlined mr-2">group_add</span>
                    <span class="font-semibold text-sm">Atenție! Acționați în contul unui alt utilizator (<span x-text="user?.name"></span>).</span>
                </div>
                <button @click="leaveImpersonation()" class="bg-white/20 hover:bg-white/30 text-white text-sm font-bold py-1.5 px-4 rounded-lg transition-colors flex items-center">
                    <span class="material-symbols-outlined text-[16px] mr-1">exit_to_app</span>
                    Înapoi la contul tău
                </button>
            </div>
        </template>

        <!-- Dynamic Working Canvas -->
        <main class="flex-1 overflow-y-auto p-6 relative">
            
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

            <!-- CLUBS VIEW -->
            <div x-show="currentPage.startsWith('/dash/cluburi')" x-data="clubManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Toate Cluburile</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">add</span>
                        Adaugă Club
                    </button>
                </div>

                <!-- Lista de Cluburi -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="club in clubs" :key="club.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm relative group">
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-2">
                                <button @click="openModal(club)" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="deleteClub(club.id)" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined">domain</span>
                            </div>
                            <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2" x-text="club.name"></h4>
                            <p class="text-sm text-slate-500 mb-4">
                                Creat de: <span class="font-medium" x-text="club.creator ? club.creator.name : 'Sistem'"></span>
                            </p>
                        </div>
                    </template>
                </div>

                <div x-show="clubs.length === 0 && !loading" class="text-center py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">domain_disabled</span>
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

            <!-- USERS VIEW -->
            <div x-show="currentPage.startsWith('/dash/utilizatori')" x-data="userManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Membri & Utilizatori</h3>
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

            <!-- TEAMS (GRUPE) VIEW -->
            <div x-show="currentPage.startsWith('/dash/grupe')" x-data="teamManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Grupe</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">group_add</span>
                        Adaugă Grupă
                    </button>
                </div>

                <template x-if="user?.role === 'administrator'">
                    <div class="mb-6 flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <select id="teamFilterClub" x-model="filters.club_id" @change="fetchTeams(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Grupele din Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="text-center py-12">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500">Se încarcă grupele...</p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="px-6 py-4 font-bold">Denumire Grupă</th>
                                <template x-if="user?.role === 'administrator'">
                                    <th class="px-6 py-4 font-bold">Asociată Clubului</th>
                                </template>
                                <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                            <template x-for="team in teams" :key="team.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white text-base" x-text="team.name"></div>
                                    </td>
                                    <template x-if="user?.role === 'administrator'">
                                        <td class="px-6 py-4">
                                            <span class="text-slate-500" x-text="availableClubs.find(c => c.id === team.club_id)?.name || 'Necunoscut'"></span>
                                        </td>
                                    </template>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openModal(team)" class="text-slate-400 hover:text-primary transition-colors p-2" title="Editează">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="deleteTeam(team.id)" class="text-slate-400 hover:text-red-500 transition-colors p-2 ml-1" title="Șterge">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden flex flex-col gap-4">
                    <template x-for="team in teams" :key="team.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-sm">
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white" x-text="team.name"></h4>
                            <template x-if="user?.role === 'administrator'">
                                <p class="text-sm text-slate-500 mt-1" x-text="'Club: ' + (availableClubs.find(c => c.id === team.club_id)?.name || 'Necunoscut')"></p>
                            </template>
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                                <button @click="openModal(team)" class="text-sm font-semibold text-primary hover:text-primary-dark px-3 py-1.5 bg-primary/10 rounded-lg">Editează</button>
                                <button @click="deleteTeam(team.id)" class="text-sm font-semibold text-red-500 hover:text-red-700 px-3 py-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg">Șterge</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="teams.length === 0 && !loading" class="text-center py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">diversity_3</span>
                    <p class="text-slate-500">Acest club nu are asocieri de grupe încă.</p>
                </div>

                <!-- Modal Grupe -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Grupă' : 'Adaugă Grupă Nouă'"></h3>
                        </div>
                        <form @submit.prevent="saveTeam()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Denumire Grupă</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all" placeholder="ex: U18 Fete"/>
                            </div>
                            
                            <template x-if="user?.role === 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Aparține de Clubul (Obligatoriu)</label>
                                    <select x-model="form.club_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled selected>Alege clubul...</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
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

            <!-- SQUADS (ECHIPE) VIEW -->
            <div x-show="currentPage.startsWith('/dash/echipe')" x-data="squadManager()" class="h-full flex flex-col">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Echipe Formate</h3>
                    <button @click="openModal()" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center sm:justify-start">
                        <span class="material-symbols-outlined mr-2">groups_2</span>
                        Adaugă Echipă
                    </button>
                </div>

                <template x-if="user?.role === 'administrator'">
                    <div class="mb-6 flex flex-col md:flex-row gap-4">
                        <div class="w-full md:w-64">
                            <select x-model="filters.club_id" @change="fetchSquads(); updateHash()" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer text-sm">
                                <option value="">Toate Echipele din Toate Cluburile</option>
                                <template x-for="c in availableClubs" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="text-center py-12">
                    <span class="material-symbols-outlined animate-spin text-4xl text-primary mb-2">sync</span>
                    <p class="text-slate-500">Se încarcă echipele...</p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block bg-transparent md:bg-white md:dark:bg-slate-800 rounded-2xl md:border md:border-slate-100 dark:md:border-slate-700 md:shadow-sm md:overflow-hidden">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="px-6 py-4 font-bold">Denumire Echipă</th>
                                <th class="px-6 py-4 font-bold">Aparține de Grupa</th>
                                <template x-if="user?.role === 'administrator'">
                                    <th class="px-6 py-4 font-bold">Club</th>
                                </template>
                                <th class="px-6 py-4 font-bold text-right">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                            <template x-for="squad in squads" :key="squad.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white text-base" x-text="squad.name"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-700 dark:text-slate-300 font-semibold" x-text="squad.team?.name"></div>
                                    </td>
                                    <template x-if="user?.role === 'administrator'">
                                        <td class="px-6 py-4">
                                            <span class="text-slate-500" x-text="squad.team?.club?.name || 'Necunoscut'"></span>
                                        </td>
                                    </template>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openModal(squad)" class="text-slate-400 hover:text-primary transition-colors p-2" title="Editează">
                                            <span class="material-symbols-outlined">edit</span>
                                        </button>
                                        <button @click="deleteSquad(squad.id)" class="text-slate-400 hover:text-red-500 transition-colors p-2 ml-1" title="Șterge">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden flex flex-col gap-4">
                    <template x-for="squad in squads" :key="squad.id">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-sm">
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white" x-text="squad.name"></h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 font-semibold" x-text="'Grupă: ' + squad.team?.name"></p>
                            <template x-if="user?.role === 'administrator'">
                                <p class="text-sm text-slate-500 mt-1" x-text="'Club: ' + (squad.team?.club?.name || 'Necunoscut')"></p>
                            </template>
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                                <button @click="openModal(squad)" class="text-sm font-semibold text-primary hover:text-primary-dark px-3 py-1.5 bg-primary/10 rounded-lg">Editează</button>
                                <button @click="deleteSquad(squad.id)" class="text-sm font-semibold text-red-500 hover:text-red-700 px-3 py-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg">Șterge</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="squads.length === 0 && !loading" class="text-center py-12">
                    <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">groups_2</span>
                    <p class="text-slate-500">Nu a fost găsită nicio echipă.</p>
                </div>

                <!-- Modal Echipe Formate -->
                <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                            <h3 class="text-xl font-bold" x-text="form.id ? 'Editează Echipă' : 'Adaugă Echipă Nouă'"></h3>
                        </div>
                        <form @submit.prevent="saveSquad()" class="flex flex-col overflow-hidden">
                            <div class="p-6 overflow-y-auto">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Denumire Echipă</label>
                                <input x-model="form.name" type="text" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-slate-900 dark:text-white transition-all" placeholder="ex: Echipa de Joi"/>
                            </div>
                            
                            <template x-if="user?.role === 'administrator'">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Selectează Mai Întâi Clubul</label>
                                    <select x-model="form.club_id" @change="fetchModalTeams()" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled selected>Alege clubul...</option>
                                        <template x-for="c in availableClubs" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Aparține de Grupa (Obligatoriu)</label>
                                <select x-model="form.team_id" required :disabled="!form.club_id && user?.role === 'administrator'" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none cursor-pointer disabled:opacity-50">
                                    <option value="" disabled selected>Alege grupa...</option>
                                    <template x-for="t in availableModalTeams" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                <template x-if="user?.role === 'administrator' && !form.club_id">
                                    <p class="text-xs text-red-500 mt-1">Selectați mai întâi un club pentru a vedea grupele.</p>
                                </template>
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

        </main>

    </div>

    <!-- Alpine App Logic -->
    <script>
        document.addEventListener('alpine:init', () => {

            // ------- Gestiune Cluburi -------
            Alpine.data('clubManager', () => ({
                clubs: [],
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '' },

                init() {
                    if (this.currentPage.startsWith('/dash/cluburi')) {
                        this.fetchClubs();
                    }
                    this.$watch('currentPage', value => {
                        if (value === '/dash/cluburi' && this.clubs.length === 0) {
                            this.fetchClubs();
                        }
                    });
                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/cluburi')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.clubs.find(c => c.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.clubs.find(c => c.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteClub(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(club = null) {
                    this.error = null;
                    if(club) {
                        this.form.id = club.id;
                        this.form.name = club.name;
                        this.updateHash('edit', club.id);
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    this.loading = true;
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.clubs = payload.data;
                            this.processHashActions();
                        }
                    } catch (e) { console.error(e); }
                    this.loading = false;
                },

                async saveClub() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/clubs/${this.form.id}` : '/api/clubs';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ name: this.form.name })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            if (isEdit) {
                                const idx = this.clubs.findIndex(c => c.id === this.form.id);
                                if (idx !== -1) this.clubs[idx].name = payload.data.name;
                            } else {
                                this.clubs.unshift(payload.data);
                            }
                            window.dispatchEvent(new CustomEvent('clubs-updated'));
                            this.showModal = false;
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                        }
                    } catch (e) { this.error = "Eroare de rețea."; }
                    this.saving = false;
                },

                async deleteClub(id) {
                    if(!confirm('Sigur dorești ștergerea acestui club? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/clubs/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.clubs = this.clubs.filter(c => c.id !== id);
                        } else {
                            const data = await res.json();
                            alert(data.message || 'Eroare la ștergere.');
                        }
                    } catch (e) { alert('A apărut o eroare de rețea.'); }
                }
            }));

            // ------- Gestiune Utilizatori -------
            Alpine.data('userManager', () => ({
                users: [],
                availableClubs: [],
                availableTeams: [],
                availableFilterTeams: [],
                availableFilterSquads: [],
                availableSquads: [],
                loading: false,
                loadingTeams: false,
                loadingSquads: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', email: '', role: '', club_id: '', password: '', is_active: true, team_ids: [], squad_ids: [] },
                filters: { role: '', club_id: '', team_id: '', squad_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashRole = '';
                        let hashClub = '';
                        let hashTeam = '';
                        let hashSquad = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/utilizatori')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashRole = hp.get('role') || '';
                                hashClub = hp.get('club_id') || '';
                                hashTeam = hp.get('team_id') || '';
                                hashSquad = hp.get('squad_id') || '';
                            } catch(e) {}
                        }
                        return { role: hashRole, club: hashClub, team: hashTeam, squad: hashSquad };
                    };

                    const applyFiltersAndFetch = async (h) => {
                        this.filters.role = h.role;
                        this.filters.club_id = h.club;
                        this.filters.team_id = h.team;
                        this.filters.squad_id = h.squad;
                        
                        if (h.club || this.user?.role === 'manager') {
                             await this.fetchFilterTeams(h.club || this.user?.club_id);
                        }
                        if (h.team) {
                             await this.fetchFilterSquads(h.team);
                        }

                        // Break Alpine cache by forcefully mutating the native DOM element.
                        setTimeout(() => {
                            const rSelect = document.getElementById('userFilterRole');
                            const cSelect = document.getElementById('userFilterClub');
                            const tSelect = document.getElementById('userFilterTeam');
                            const sSelect = document.getElementById('userFilterSquad');
                            if (rSelect) rSelect.value = h.role;
                            if (cSelect) cSelect.value = h.club;
                            if (tSelect) tSelect.value = h.team;
                            if (sSelect) sSelect.value = h.squad;
                            this.fetchUsers();
                        }, 50);
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/utilizatori') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchDependentData();
                            }
                        } else if (!value.startsWith('/dash/utilizatori')) {
                            this.filters.role = '';
                            this.filters.club_id = '';
                            this.filters.team_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && this.currentPage.startsWith('/dash/utilizatori')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (usr.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchDependentData();
                            }
                        }
                    });

                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/utilizatori')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/utilizatori')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchDependentData();
                    }
                    
                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchDependentData();
                    });
                    
                    this.$watch('filters.club_id', async (val) => {
                        if (this.currentPage.startsWith('/dash/utilizatori')) {
                            if (val) {
                                await this.fetchFilterTeams(val);
                            } else {
                                this.availableFilterTeams = [];
                                this.filters.team_id = '';
                            }
                        }
                    });

                    this.$watch('filters.team_id', async (val) => {
                        if (this.currentPage.startsWith('/dash/utilizatori')) {
                            if (val) {
                                await this.fetchFilterSquads(val);
                            } else {
                                this.availableFilterSquads = [];
                                this.filters.squad_id = '';
                            }
                        }
                    });

                    this.$watch('form.club_id', async (val) => {
                        if (this.showModal) await this.fetchTeamsBasedOnClub();
                    });
                    this.$watch('form.role', async (val) => {
                        if (this.showModal && (val === 'sportiv' || val === 'antrenor')) await this.fetchTeamsBasedOnClub();
                    });
                    this.$watch('form.team_ids', async (val) => {
                        if (this.showModal) await this.fetchSquadsBasedOnTeams();
                    });
                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/utilizatori')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.users.find(u => u.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.users.find(u => u.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteUser(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.role) params.append('role', this.filters.role);
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (this.filters.team_id) params.append('team_id', this.filters.team_id);
                    if (this.filters.squad_id) params.append('squad_id', this.filters.squad_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(userToEdit = null) {
                    this.error = null;
                    if(userToEdit) {
                        this.form.id = userToEdit.id;
                        this.form.name = userToEdit.name;
                        this.form.email = userToEdit.email;
                        this.form.role = userToEdit.role;
                        this.form.club_id = userToEdit.club_id || '';
                        this.form.is_active = !!userToEdit.is_active;
                        this.form.team_ids = userToEdit.teams ? userToEdit.teams.map(t => t.id) : [];
                        this.form.squad_ids = userToEdit.squads ? userToEdit.squads.map(s => s.id) : [];
                        this.form.password = ''; // empty default, typed only to override
                        this.updateHash('edit', userToEdit.id);
                        this.fetchTeamsBasedOnClub().then(() => {
                            if (this.form.team_ids.length > 0) this.fetchSquadsBasedOnTeams();
                        });
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.email = '';
                        this.form.role = '';
                        this.form.club_id = '';
                        this.form.password = '';
                        this.form.team_ids = [];
                        this.form.squad_ids = [];
                        this.availableSquads = [];
                        this.form.is_active = true;
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchUsers() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.role) params.append('role', this.filters.role);
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                        if (this.filters.team_id) params.append('team_id', this.filters.team_id);
                        if (this.filters.squad_id) params.append('squad_id', this.filters.squad_id);

                        const res = await fetch(`/api/users?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.users = payload.data;
                            this.processHashActions();
                        }
                    } catch (e) {}
                    this.loading = false;
                },

                async fetchDependentData() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchFilterTeams(clubIdStr) {
                    if(!clubIdStr) return;
                    try {
                        const res = await fetch(`/api/teams?club_id=${clubIdStr}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableFilterTeams = payload.data;
                        }
                    } catch (e) {}
                },

                async fetchFilterSquads(teamIdStr) {
                    if(!teamIdStr) return;
                    try {
                        const res = await fetch(`/api/squads?team_id=${teamIdStr}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableFilterSquads = payload.data;
                        }
                    } catch (e) {}
                },

                async fetchTeamsBasedOnClub() {
                    const cid = this.form.club_id || (this.user?.role === 'manager' ? this.user.club_id : null);
                    if (!cid) {
                        this.availableTeams = [];
                        return;
                    }
                    this.loadingTeams = true;
                    try {
                        const res = await fetch(`/api/teams?club_id=${cid}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableTeams = payload.data;
                        }
                    } catch (e) {}
                    this.loadingTeams = false;
                },

                async fetchSquadsBasedOnTeams() {
                    if (this.form.team_ids.length === 0) {
                        this.availableSquads = [];
                        return;
                    }
                    this.loadingSquads = true;
                    try {
                        let squadsRaw = [];
                        for(let tid of this.form.team_ids) {
                            const res = await fetch(`/api/squads?team_id=${tid}`, {
                                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                            });
                            if(res.ok) {
                                const payload = await res.json();
                                squadsRaw.push(...payload.data);
                            }
                        }
                        this.availableSquads = squadsRaw;
                    } catch (e) {}
                    this.loadingSquads = false;
                },

                async saveUser() {
                    this.saving = true;
                    this.error = null;
                    
                    if (this.form.role === 'administrator') this.form.club_id = '';
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/users/${this.form.id}` : '/api/users';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({
                                name: this.form.name,
                                email: this.form.email,
                                role: this.form.role,
                                password: this.form.password,
                                is_active: this.form.is_active,
                                club_id: this.form.club_id || null,
                                team_ids: this.form.team_ids,
                                squad_ids: this.form.squad_ids
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            if(isEdit) {
                                const idx = this.users.findIndex(u => u.id === this.form.id);
                                if(idx !== -1) this.users[idx] = payload.data;
                            } else {
                                this.fetchUsers(); // Refresh pt a aduce pe noua pozitie + relatii noi in caz extrem
                            }
                            this.showModal = false;
                            this.form = { id: null, name: '', email: '', role: '', club_id: '', password: '', is_active: true, team_ids: [], squad_ids: [] };
                            this.availableSquads = [];
                        } else {
                            this.error = payload.message || 'Eroare la salvare. Verificați datele (ex: email duplicat).';
                        }
                    } catch (e) { this.error = "Eroare rețea."; }
                    
                    this.saving = false;
                },

                async deleteUser(id) {
                    if(!confirm('Sigur dorești să ștergi acest utilizator? Această acțiune este ireversibilă.')) return;
                    try {
                        const res = await fetch(`/api/users/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        const payload = await res.json();
                        if(!res.ok) {
                            alert(payload.message || 'Nu poți șterge acest utilizator.');
                            return;
                        }
                        this.fetchUsers();
                    } catch(e) {
                        alert('A apărut o eroare la ștergere.');
                    }
                },

                async impersonateUser(user) {
                    if(!confirm(`Ești sigur că vrei să te loghezi ca ${user.name}?`)) return;
                    
                    try {
                        const res = await fetch(`/api/impersonate/${user.id}`, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        const payload = await res.json();
                        if(res.ok) {
                            // Salvam tokenul de admin original pentru restabilire
                            localStorage.setItem('original_admin_token', localStorage.getItem('auth_token'));
                            
                            // Inlocuim tokenul activ cu cel al utilizatorului
                            localStorage.setItem('auth_token', payload.token);
                            
                            // Reincarcam aplicatia complet
                            window.location.reload();
                        } else {
                            alert(payload.message || 'Eroare la impersonare.');
                        }
                    } catch(e) {
                        alert('Eroare de rețea la impersonare.');
                    }
                }
            }));

            // ------- Gestiune Grupe (Teams) -------
            Alpine.data('teamManager', () => ({
                teams: [],
                availableClubs: [],
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', club_id: '' },
                filters: { club_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashClub = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/grupe')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashClub = hp.get('club_id') || '';
                            } catch(e) {}
                        }
                        return hashClub;
                    };

                    const applyFiltersAndFetch = (h) => {
                        this.filters.club_id = h;
                        
                        setTimeout(() => {
                            const cSelect = document.getElementById('teamFilterClub');
                            if (cSelect) cSelect.value = h;
                            this.fetchTeams();
                        }, 50);
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/grupe') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchClubs();
                            }
                        } else if (!value.startsWith('/dash/grupe')) {
                            this.filters.club_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/grupe')) {
                            if (this.availableClubs.length === 0) this.fetchClubs();
                        }
                    });

                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/grupe')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/grupe')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    }

                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    });
                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/grupe')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.teams.find(t => t.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.teams.find(t => t.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteTeam(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(team = null) {
                    this.error = null;
                    if(team) {
                        this.form.id = team.id;
                        this.form.name = team.name;
                        this.form.club_id = team.club_id;
                        this.updateHash('edit', team.id);
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.club_id = '';
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchTeams() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);

                        const res = await fetch(`/api/teams?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.teams = payload.data;
                            this.processHashActions();
                        }
                    } catch (e) { console.error(e); }
                    this.loading = false;
                },

                async saveTeam() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/teams/${this.form.id}` : '/api/teams';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ 
                                name: this.form.name,
                                club_id: this.form.club_id || null
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchTeams();
                            this.showModal = false;
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                        }
                    } catch (e) { this.error = "Eroare de rețea."; }
                    this.saving = false;
                },

                async deleteTeam(id) {
                    if(!confirm('Sigur dorești ștergerea acestei grupe? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/teams/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.teams = this.teams.filter(t => t.id !== id);
                        } else {
                            const data = await res.json();
                            alert(data.message || 'Eroare la ștergere. Posibil grupa are membri asociați.');
                        }
                    } catch (e) { alert('A apărut o eroare de rețea.'); }
                }
            }));

            // ------- Gestiune Echipe (Squads) -------
            Alpine.data('squadManager', () => ({
                squads: [],
                availableClubs: [],
                availableModalTeams: [], // Grupele încărcate pentru dropdown-ul din modal de creare
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', club_id: '', team_id: '' },
                filters: { club_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashClub = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/echipe')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashClub = hp.get('club_id') || '';
                            } catch(e) {}
                        }
                        return hashClub;
                    };

                    const applyFiltersAndFetch = (h) => {
                        this.filters.club_id = h;
                        this.fetchSquads();
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/echipe') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchClubs();
                            }
                        } else if (!value.startsWith('/dash/echipe')) {
                            this.filters.club_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/echipe')) {
                            if (this.availableClubs.length === 0) this.fetchClubs();
                        }
                    });
                    
                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/echipe')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/echipe')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    }

                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    });

                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/echipe')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.squads.find(s => s.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.squads.find(s => s.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteSquad(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                async fetchModalTeams() {
                    // Când selectezi un club in modal, vrem să arătăm doar grupele acelui club
                    this.availableModalTeams = [];
                    this.form.team_id = ''; // resetare selecție
                    if (this.user?.role === 'administrator' && !this.form.club_id) return;
                    
                    try {
                        let url = '/api/teams';
                        if (this.form.club_id) {
                            url += `?club_id=${this.form.club_id}`;
                        }

                        const res = await fetch(url, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if (res.ok) {
                            const payload = await res.json();
                            this.availableModalTeams = payload.data;
                        }
                    } catch(e) {}
                },

                openModal(squad = null) {
                    this.error = null;
                    if(squad) {
                        this.form.id = squad.id;
                        this.form.name = squad.name;
                        this.form.club_id = squad.team?.club_id || '';
                        this.updateHash('edit', squad.id);
                        
                        // Populăm echipele pentru acel club + selectăm grupa
                        if (this.form.club_id || this.user?.role === 'manager') {
                            // Dacă e manager, știm sigur că tragem toate echipele din clubul lui implicit (via empty club_id query for teams sau backend filter).
                            // Pentru admin, o chemăm explicit.
                            this.fetchModalTeams().then(() => {
                                this.form.team_id = squad.team_id;
                            });
                        } else {
                            this.form.team_id = squad.team_id;
                        }
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.club_id = '';
                        this.form.team_id = '';
                        this.availableModalTeams = [];
                        
                        // Dacă e manager, încarcă direct grupele lui (fără să trebuiască selecteze club)
                        if (this.user?.role === 'manager') {
                            this.fetchModalTeams();
                        }
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchSquads() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);

                        const res = await fetch(`/api/squads?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.squads = payload.data;
                            this.processHashActions();
                        }
                    } catch(e) {}
                    this.loading = false;
                },

                async saveSquad() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/squads/${this.form.id}` : '/api/squads';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ 
                                name: this.form.name,
                                team_id: this.form.team_id
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchSquads();
                            this.showModal = false;
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                        }
                    } catch (e) { this.error = "Eroare de rețea."; }
                    this.saving = false;
                },

                async deleteSquad(id) {
                    if(!confirm('Sigur dorești ștergerea acestei echipe? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/squads/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.squads = this.squads.filter(s => s.id !== id);
                        } else {
                            const data = await res.json();
                            alert(data.message || 'Eroare la ștergere. Posibil echipa are membri asociați.');
                        }
                    } catch (e) { alert('A apărut o eroare de rețea.'); }
                }
            }));

            // ------- Kernel SPA Dashboard -------
            Alpine.data('dashboard', () => ({
                user: null,
                isLoading: true,
                token: null,
                isMobileMenuOpen: false,
                isImpersonating: false,
                currentPage: window.location.pathname, // Route Tracker Simplu

                    getPageTitle() {
                        if(this.currentPage === '/dash') return 'Acasă';
                        if(this.currentPage.startsWith('/dash/cluburi')) return 'Management Cluburi';
                        if(this.currentPage.startsWith('/dash/utilizatori')) return 'Echipă & Utilizatori';
                        if(this.currentPage.startsWith('/dash/echipe')) return 'Echipe Formate';
                        return 'Dashboard';
                    },

                navigate(path) {
                    if (this.user) {
                        if (!['administrator', 'manager'].includes(this.user.role) && path !== '/dash') {
                            path = '/dash';
                        }
                        if (this.user.role === 'manager' && path.startsWith('/dash/cluburi')) {
                            path = '/dash';
                        }
                    }
                    this.currentPage = path;
                    
                    // Clear Hash State gracefully on programmatic navigation
                    if (window.location.hash) {
                        window.history.pushState({}, '', path); // Set without hash
                    } else {
                        window.history.pushState({}, '', path);
                    }
                },

                async init() {
                    // Ascultăm schimbările de istoric din browser (Butonul Back/Forward)
                    window.addEventListener('popstate', () => {
                        this.currentPage = window.location.pathname;
                    });
                    
                    // Suport fallback SPA imediat după încărcarea paginii dacă URL-ul este pe vreo subrută
                    const currentPath = window.location.pathname;
                    if (currentPath !== '/dash' && currentPath.startsWith('/dash/')) {
                         this.currentPage = currentPath;
                    }

                    this.token = localStorage.getItem('auth_token');
                    this.isImpersonating = !!localStorage.getItem('original_admin_token');
                    
                    if (!this.token) {
                        window.location.href = '/dash/login';
                        return;
                    }

                    try {
                        const response = await fetch('/api/user', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });
                        
                        if (response.ok) {
                            this.user = await response.json();
                            this.isLoading = false;
                            
                            // Security check fallback for deep links
                            if (!['administrator', 'manager'].includes(this.user.role) && this.currentPage !== '/dash') {
                                this.navigate('/dash');
                            } else if (this.user.role === 'manager' && this.currentPage.startsWith('/dash/cluburi')) {
                                this.navigate('/dash');
                            }
                        } else {
                            this.logout(false);
                        }
                    } catch (error) {
                        this.logout(false);
                    }
                },

                async logout(callApi = true) {
                    if (callApi && this.token) {
                        try {
                            await fetch('/api/logout', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': `Bearer ${this.token}`
                                }
                            });
                        } catch (e) {}
                    }
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('original_admin_token'); // Clear in caz ca era impersonat si da logout manual
                    window.location.href = '/dash/login';
                },

                async leaveImpersonation() {
                    try {
                        const res = await fetch('/api/impersonate-leave', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${this.token}` }
                        });
                        
                        // Oricum distrugem starea locala chiar dac API da timeout, sa nu ramana blocat
                        const adminToken = localStorage.getItem('original_admin_token');
                        localStorage.setItem('auth_token', adminToken);
                        localStorage.removeItem('original_admin_token');
                        window.location.reload();
                    } catch (e) {
                        alert('Eroare la delogare din impersonare.');
                    }
                }
            }));
        });
    </script>
</body>
</html>
