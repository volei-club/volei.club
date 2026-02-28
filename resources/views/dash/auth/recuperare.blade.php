<!DOCTYPE html>
<html lang="ro"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Volei.Club Recuperare Parolă</title>
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
<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Recuperare parolă</h1>
<p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                    Introdu adresa de email asociată contului tău și îți vom trimite un link pentru resetarea parolei.
                </p>
</div>
<form method="POST" action="{{ route('dash.recovery') }}" class="space-y-6">
@csrf
<div class="space-y-2">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300 ml-1" for="email">Adresă de email</label>
<div class="relative">
<input name="email" value="{{ old('email') }}" required class="w-full h-12 pl-4 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none placeholder:text-slate-400" id="email" placeholder="ex: nume@email.ro" type="email"/>
<div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
<span class="material-symbols-outlined text-[20px]">mail</span>
</div>
</div>
</div>
<button type="submit" class="w-full h-12 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary/25 active:scale-[0.98] flex items-center justify-center gap-2">
                    Trimite link-ul de resetare
                </button>
</form>
@if (session('status'))
    <div class="mt-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 text-center" role="alert">
        {{ session('status') }}
    </div>
@endif
<div class="mt-10 flex justify-center">
<a class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-primary transition-colors group" href="{{ route('dash.login') }}">
<span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    Înapoi la autentificare
                </a>
</div>
</div>
</div>

</body></html>