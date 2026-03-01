<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Volei.Club Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen" x-data="dashboard()">
    
    <!-- Loading Screen Overlay -->
    <div x-show="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-background-light dark:bg-background-dark">
        <span class="material-symbols-outlined animate-spin text-primary text-4xl">progress_activity</span>
    </div>

    <nav class="bg-white dark:bg-slate-900 shadow-sm border-b border-slate-100 dark:border-slate-800" x-show="!isLoading" style="display: none;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white">
                        <span class="material-symbols-outlined">sports_volleyball</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Volei.Club / Dash</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300" x-text="user?.name">
                        Se încarcă...
                    </span>
                    <button @click="logout()" type="button" class="text-sm text-red-500 hover:text-red-700 font-semibold px-4 py-2 rounded-lg bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                        Deconectare
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-show="!isLoading" style="display: none;">
        <div class="bg-white dark:bg-slate-900 shadow rounded-2xl p-8 border border-slate-100 dark:border-slate-800 text-center">
            <div class="w-20 h-20 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-6 text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined text-4xl">verified_user</span>
            </div>
            <h2 class="text-3xl font-bold mb-4">Bine ai venit în Dashboard!</h2>
            <p class="text-xl text-slate-600 dark:text-slate-400">
                Momentan logat ca <span class="font-bold text-slate-900 dark:text-white" x-text="user?.name"></span> 
                cu rolul <span class="font-bold text-primary px-3 py-1 bg-primary/10 rounded-full text-sm ml-2 uppercase" x-text="user?.role"></span>
            </p>
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                user: null,
                isLoading: true,
                token: null,

                async init() {
                    this.token = localStorage.getItem('auth_token');
                    
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
                        } else {
                            // Token Invalid sau Expirat
                            this.logout(false);
                        }
                    } catch (error) {
                        console.error('Error fetching user:', error);
                        // Ne ducem la afisare eroare sau login daca pica grav
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
                        } catch (e) {
                            // Ignoram eroarea pe logout, scopul e curatarea clientului
                        }
                    }
                    
                    localStorage.removeItem('auth_token');
                    window.location.href = '/dash/login';
                }
            }));
        });
    </script>

</body>
</html>
