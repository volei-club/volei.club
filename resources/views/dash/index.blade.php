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

    @include('dash.components.sidebar')


    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden" x-show="!isLoading" style="display: none;">
        
        @include('dash.components.topbar')


        <!-- Dynamic Working Canvas -->
        <main class="flex-1 overflow-y-auto p-6 relative">
            
            @include('dash.components.home')
            @include('dash.components.clubs')
            @include('dash.components.users')
            @include('dash.components.teams')
            @include('dash.components.squads')
            @include('dash.components.subscriptions')
            @include('dash.components.audit')
            @include('dash.components.locatii')
            @include('dash.components.antrenamente')

        </main>

    </div>

    @include('dash.components.scripts')
    </script>
</body>
</html>
