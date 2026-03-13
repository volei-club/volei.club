<!DOCTYPE html>

<html lang="ro"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{{ __('frontend.auth.login.title') }}</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1e3fae",
                        "primary-dark": "#152c7a",
                        "background-light": "#f6f6f8",
                        "background-dark": "#121520",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "2xl": "2rem", "full": "9999px" },
                },
            },
        }
    </script>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center p-0 sm:p-4">
<!-- Main Container -->
<div class="w-full max-w-6xl h-screen sm:h-auto sm:min-h-[600px] flex overflow-hidden sm:rounded-2xl shadow-2xl bg-white dark:bg-slate-900">
<!-- Left Side: Login Form -->
<div class="w-full lg:w-1/2 flex flex-col justify-center p-6 sm:p-12 relative z-10">
<div class="max-w-md mx-auto w-full">
<!-- Logo Mobile/Desktop -->
<div class="mb-8 flex justify-center lg:justify-start">
<div class="w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white mr-3">
<span class="material-symbols-outlined" style="font-size: 32px;">sports_volleyball</span>
</div>
<span class="text-2xl font-bold tracking-tight self-center text-slate-900 dark:text-white">Volei.Club</span>
</div>
<div class="text-center lg:text-left mb-8">
<h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ __('frontend.auth.login.welcome_back') }}</h1>
<p class="text-slate-500 dark:text-slate-400">{{ __('frontend.auth.login.enter_details') }}</p>
</div>
<form @submit.prevent="submitLogin" class="space-y-6" x-data="loginForm()">
<!-- Global Error Message -->
<div x-show="errorMessage" class="p-4 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm font-medium" style="display: none;">
    <span class="material-symbols-outlined align-middle mr-1 text-[20px]">error</span>
    <span x-text="errorMessage" class="align-middle"></span>
</div>

<!-- Email Field -->
<div class="space-y-2">
<label class="text-sm font-medium text-slate-900 dark:text-slate-100" for="email">{{ __('frontend.auth.login.email_label') }}</label>
<div class="relative">
<input x-model="email" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none placeholder:text-slate-400" id="email" placeholder="{{ __('frontend.auth.login.email_placeholder') }}" type="email" :disabled="isLoading"/>
<div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
<span class="material-symbols-outlined">mail</span>
</div>
</div>
</div>
<!-- Password Field -->
<div class="space-y-2">
<label class="text-sm font-medium text-slate-900 dark:text-slate-100" for="password">{{ __('frontend.auth.login.password_label') }}</label>
<div class="relative">
<input x-model="password" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none placeholder:text-slate-400" id="password" placeholder="••••••••" :type="show ? 'text' : 'password'" :disabled="isLoading"/>
<button @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" type="button">
<span class="material-symbols-outlined" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
</button>
</div>
</div>
<!-- Remember & Forgot -->
<div class="flex items-center justify-between text-sm">
<label class="flex items-center gap-2 cursor-pointer">
<input x-model="remember" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox"/>
<span class="text-slate-600 dark:text-slate-300">{{ __('frontend.auth.login.remember_me') }}</span>
</label>
<a class="font-semibold text-primary hover:text-primary-dark transition-colors" href="{{ route('dash.recovery') }}">{{ __('frontend.auth.login.forgot_password') }}</a>
</div>
<!-- Submit Button -->
<button type="submit" :disabled="isLoading" class="w-full h-12 bg-primary hover:bg-primary-dark disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl hover:shadow-primary/30 active:scale-[0.98] flex items-center justify-center gap-2">
        <span x-show="!isLoading">{{ __('frontend.auth.login.login_button') }}</span>
        <span x-show="isLoading" class="material-symbols-outlined animate-spin" style="display: none;">progress_activity</span>
</button>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loginForm', () => ({
            email: '',
            password: '',
            remember: false,
            show: false,
            isLoading: false,
            errorMessage: '',
            
            async submitLogin() {
                this.isLoading = true;
                this.errorMessage = '';
                
                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: this.email,
                            password: this.password,
                            remember: this.remember
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.status === 'success') {
                        // Salvează ID-ul utilizatorului temporal pentru ecranul 2FA
                        sessionStorage.setItem('2fa_user_id', data.user_id);
                        window.location.href = "{{ route('dash.2fa.show', ['locale' => app()->getLocale()]) }}";
                    } else {
                        this.errorMessage = data.message || '{{ __('frontend.auth.login.invalid_credentials') }}';
                    }
                } catch (error) {
                    this.errorMessage = '{{ __('frontend.auth.login.connection_error') }}';
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
<!-- Divider -->
<div class="relative my-8">
<div class="absolute inset-0 flex items-center">
<div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
</div>
<div class="relative flex justify-center text-xs uppercase">
<span class="bg-white dark:bg-slate-900 px-2 text-slate-500">{{ __('frontend.auth.login.or_continue_with') }}</span>
</div>
</div>
<!-- Google Login -->
<a href="{{ route('dash.google.redirect') }}" class="w-full h-12 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium rounded-xl transition-all flex items-center justify-center gap-3">
<svg class="w-5 h-5" fill="none" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
</svg>
                    {{ __('frontend.auth.login.login_with_google') }}
                </a>
</div>
</div>
<!-- Right Side: Visual Rich Area (Hidden on Mobile usually, but let's make it responsive) -->
<div class="hidden lg:flex w-1/2 bg-gradient-to-br from-[#1e3fae] to-[#4c1d95] relative overflow-hidden flex-col items-center justify-center p-12 text-white">
<!-- Abstract background shapes -->
<div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
<div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-purple-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
<!-- Content Container -->
<div class="relative z-10 w-full max-w-md">
<!-- Floating Badge -->
<div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full border border-white/20 mb-8 shadow-xl animate-bounce" style="animation-duration: 3s;">
<span class="w-2 h-2 rounded-full bg-green-400"></span>
<span class="text-sm font-medium tracking-wide">{{ __('frontend.auth.login.badge') }}</span>
</div>
<!-- Mockup Card -->
<div class="relative group perspective-1000">
<!-- Main Dashboard Mockup -->
<div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-4 transform transition-all duration-500 hover:scale-[1.02] hover:rotate-1">
<!-- Mockup Header -->
<div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
<div class="flex items-center gap-2">
<div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-lg">dashboard</span>
</div>
<div class="h-2 w-24 bg-slate-200 rounded-full"></div>
</div>
<div class="flex gap-2">
<div class="w-6 h-6 rounded-full bg-slate-100"></div>
<div class="w-6 h-6 rounded-full bg-slate-100"></div>
</div>
</div>
<!-- Mockup Stats Grid -->
<div class="grid grid-cols-2 gap-3 mb-4">
<div class="bg-blue-50 p-3 rounded-xl">
<div class="h-2 w-12 bg-blue-200 rounded-full mb-2"></div>
<div class="h-6 w-16 bg-blue-500/20 rounded mb-1"></div>
</div>
<div class="bg-purple-50 p-3 rounded-xl">
<div class="h-2 w-12 bg-purple-200 rounded-full mb-2"></div>
<div class="h-6 w-16 bg-purple-500/20 rounded mb-1"></div>
</div>
</div>
<!-- Mockup Chart Area -->
<div class="bg-slate-50 rounded-xl p-3 h-24 flex items-end justify-between gap-2 px-4 mb-2">
<div class="w-full bg-slate-200 rounded-t-sm h-[40%]"></div>
<div class="w-full bg-primary/40 rounded-t-sm h-[70%]"></div>
<div class="w-full bg-slate-200 rounded-t-sm h-[50%]"></div>
<div class="w-full bg-primary rounded-t-sm h-[85%] shadow-lg shadow-primary/20"></div>
<div class="w-full bg-slate-200 rounded-t-sm h-[60%]"></div>
</div>
<!-- Mockup List -->
<div class="space-y-2 mt-4">
<div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg">
<div class="w-8 h-8 rounded-full bg-slate-200"></div>
<div class="flex-1">
<div class="h-2 w-20 bg-slate-200 rounded-full mb-1"></div>
<div class="h-2 w-12 bg-slate-100 rounded-full"></div>
</div>
</div>
<div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg">
<div class="w-8 h-8 rounded-full bg-slate-200"></div>
<div class="flex-1">
<div class="h-2 w-20 bg-slate-200 rounded-full mb-1"></div>
<div class="h-2 w-12 bg-slate-100 rounded-full"></div>
</div>
</div>
</div>
</div>
<!-- Decorative Elements Behind Mockup -->
<div class="absolute -z-10 -right-6 -bottom-6 w-full h-full bg-white/10 rounded-2xl transform rotate-3"></div>
<div class="absolute -z-20 -right-12 -bottom-12 w-full h-full bg-white/5 rounded-2xl transform rotate-6"></div>
</div>
<div class="mt-12 text-center">
<h2 class="text-2xl font-bold mb-2">{{ __('frontend.auth.login.manage_everything') }}</h2>
<p class="text-blue-100 text-sm leading-relaxed max-w-xs mx-auto">
                        {{ __('frontend.auth.login.manage_description') }}
                    </p>
</div>
</div>
<!-- Image for right side bg reference/fallback -->
<div class="hidden" data-alt="Abstract blue and purple gradient background representing modern technology"></div>
</div>
</div>
</body></html>