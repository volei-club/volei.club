<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Volei.Club / Dashboard</title>
    
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400..800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
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

    @include('dash.components.sidebar')


    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden" x-show="!isLoading" style="display: none;">
        
        @include('dash.components.topbar')


        <!-- Dynamic Working Canvas -->
        <main class="flex-1 overflow-y-auto px-6 pb-24 md:pb-6 pt-16 md:pt-6 relative">
            
            @include('dash.components.home')
            @include('dash.components.clubs')
            @include('dash.components.users')
            @include('dash.components.teams')
            @include('dash.components.squads')
            @include('dash.components.subscriptions')
            @include('dash.components.audit')
            @include('dash.components.locatii')
            @include('dash.components.antrenamente')
            @include('dash.components.calendar')
            @include('dash.components.meciuri')
            @include('dash.components.performanta')
            @include('dash.components.mesaje')
            @include('dash.components.system')
            @include('dash.components.profile')
            @include('dash.components.cropper')
            @include('dash.components.game-modal')

        </main>

    </div>

    <!-- Global Toast Notifications -->
    <div x-data="{ 
            notifications: [],
            add(msg, type = 'success') {
                const id = Date.now();
                this.notifications.push({ id, msg, type });
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 5000);
            }
         }"
         @notify.window="add($event.detail.message, $event.detail.type)"
         class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none">
        <template x-for="n in notifications" :key="n.id">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="pointer-events-auto flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl border min-w-[300px] max-w-md bg-white dark:bg-slate-800"
                 :class="n.type === 'success' ? 'border-green-100 dark:border-green-900/30' : 'border-red-100 dark:border-red-900/30'">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                     :class="n.type === 'success' ? 'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                    <span class="material-symbols-outlined text-[20px]" x-text="n.type === 'success' ? 'check_circle' : 'error'"></span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="n.type === 'success' ? '{{ __('admin.success') }}' : '{{ __('admin.error') }}'"></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="n.msg"></p>
                </div>
                <button @click="notifications = notifications.filter(notif => notif.id !== n.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        </template>
    </div>
    @include('dash.components.bottom_nav')
    @include('dash.components.scripts')
</body>
</html>
