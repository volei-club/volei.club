<!DOCTYPE html>
<html lang="ro"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{{ __('frontend.auth.reset.title') }}</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1E40AF",
                        "primary-dark": "#1e3fae",
                        "background-light": "#f6f6f8",
                        "background-dark": "#121520",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "2xl": "2rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style type="text/tailwindcss">
        body {
            min-height: 100dvh;
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 flex items-center justify-center p-4">
<div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-100 dark:border-slate-800">
<div class="p-8 sm:p-10 flex flex-col">
<div class="mb-10 flex flex-col items-center">
<div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center text-white mb-3 shadow-lg shadow-primary/20">
<span class="material-symbols-outlined" style="font-size: 36px;">sports_volleyball</span>
</div>
<span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Volei.Club</span>
</div>
<div class="text-center mb-8">
<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">{{ __('frontend.auth.reset.set_new_password') }}</h1>
<p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                    {{ __('frontend.auth.reset.instructions') }}
                </p>
</div>
<form @submit.prevent="submitReset" class="space-y-6" x-data="resetForm('{{ $token }}')">
<!-- Global Error or Success Message -->
<div x-show="message" class="p-4 rounded-xl text-sm font-medium border" :class="isSuccess ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-600 border-red-200'" style="display: none;">
    <span class="material-symbols-outlined align-middle mr-1 text-[20px]" x-text="isSuccess ? 'check_circle' : 'error'"></span>
    <span x-text="message" class="align-middle"></span>
</div>

<div class="space-y-2">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1" for="email">{{ __('frontend.auth.reset.email_label') }}</label>
<div class="relative">
<input x-model="email" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none placeholder:text-slate-400" id="email" placeholder="{{ __('frontend.auth.reset.email_placeholder') }}" type="email" :disabled="isLoading"/>
<div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
<span class="material-symbols-outlined text-[20px]">mail</span>
</div>
</div>
</div>

<div class="space-y-2">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1" for="password">{{ __('frontend.auth.reset.new_password_label') }}</label>
<div class="relative">
<input x-model="password" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none" id="password" :type="show ? 'text' : 'password'" :disabled="isLoading"/>
<button @click="show = !show" type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
<span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
</button>
</div>
</div>

<div class="space-y-2">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1" for="password_confirmation">{{ __('frontend.auth.reset.confirm_password_label') }}</label>
<div class="relative">
<input x-model="password_confirmation" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none" id="password_confirmation" :type="showConf ? 'text' : 'password'" :disabled="isLoading"/>
<button @click="showConf = !showConf" type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
<span class="material-symbols-outlined text-[20px]" x-text="showConf ? 'visibility_off' : 'visibility'">visibility</span>
</button>
</div>
</div>

<button type="submit" :disabled="isLoading" class="w-full h-12 bg-primary hover:bg-primary-dark disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary/25 active:scale-[0.98] flex items-center justify-center gap-2">
    <span x-show="!isLoading">{{ __('frontend.auth.reset.reset_password_button') }}</span>
    <span x-show="isLoading" class="material-symbols-outlined animate-spin" style="display: none;">progress_activity</span>
</button>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('resetForm', (token) => ({
            token: token,
            email: '',
            password: '',
            password_confirmation: '',
            show: false,
            showConf: false,
            isLoading: false,
            message: '',
            isSuccess: false,
            
            async submitReset() {
                this.isLoading = true;
                this.message = '';
                this.isSuccess = false;
                
                try {
                    const response = await fetch('/api/resetare-parola', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            token: this.token,
                            email: this.email,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.status === 'success') {
                        this.isSuccess = true;
                        this.message = data.message || '{{ __('frontend.auth.reset.success_message') }}';
                        setTimeout(() => {
                            window.location.href = "{{ route('dash.login', ['locale' => app()->getLocale()]) }}";
                        }, 2000);
                    } else {
                        this.isSuccess = false;
                        this.message = data.message || (data.errors ? Object.values(data.errors)[0][0] : '{{ __('frontend.auth.reset.check_data') }}');
                    }
                } catch (error) {
                    this.isSuccess = false;
                    this.message = '{{ __('frontend.auth.reset.connection_error') }}';
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
<div class="mt-10 flex justify-center">
<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-primary transition-colors group" href="{{ route('dash.login') }}">
<span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    {{ __('frontend.auth.reset.back_to_login') }}
                </a>
</div>
</div>
</div>

</body></html>
