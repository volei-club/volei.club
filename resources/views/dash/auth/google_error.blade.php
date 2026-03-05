<!DOCTYPE html>
<html lang="ro"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{{ __('frontend.auth.google_error.title') }}</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
      min-height: max(600px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 flex items-center justify-center p-4">
<div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-100 dark:border-slate-800">
<div class="p-8 sm:p-10 flex flex-col items-center text-center">
<div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 mb-6 shadow-sm">
<span class="material-symbols-outlined" style="font-size: 36px;">error</span>
</div>

<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ __('frontend.auth.google_error.account_not_found') }}</h1>
<p class="text-slate-500 dark:text-slate-400 text-[15px] leading-relaxed mb-8">
    {{ __('frontend.auth.google_error.message_p1') }} <br/><strong class="text-slate-700 dark:text-slate-200">{{ $email }}</strong><br/> {{ __('frontend.auth.google_error.message_p2') }} <span class="font-semibold text-slate-800 dark:text-slate-100">Volei.Club</span>.<br><br>{{ __('frontend.auth.google_error.message_p3') }}
</p>

<a href="{{ route('dash.login') }}" class="w-full h-12 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary/25 active:scale-[0.98] flex items-center justify-center gap-2 mb-4">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    {{ __('frontend.auth.google_error.back_to_login') }}
</a>

</div>
</div>

</body></html>
