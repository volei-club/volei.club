<!DOCTYPE html>
<html lang="ro"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Volei.Club Verificare 2FA</title>
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
                        "primary": "#1e40af",
                        "primary-dark": "#1e3a8a",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "2xl": "2rem", "full": "9999px" },
                },
            },
        }
    </script>
<style type="text/tailwindcss">
        :root {
            --primary-color: #1e40af;
        }
        body {
            min-height: 100dvh;
        }
        .otp-input:focus {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--primary-color);
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 flex flex-col items-center justify-center p-4">
<div class="w-full max-w-[440px] bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-none p-8 md:p-10">
<div class="flex flex-col items-center mb-10">
<div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center text-white mb-4 shadow-lg shadow-primary/20">
<span class="material-symbols-outlined" style="font-size: 36px;">sports_volleyball</span>
</div>
<span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Volei.Club</span>
</div>
<div class="text-center mb-8">
<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Securitate cont</h1>
<p class="text-slate-500 dark:text-slate-400 text-[15px] leading-relaxed">
                Am trimis un cod de verificare prin email la adresa <span class="font-semibold text-slate-700 dark:text-slate-200 text-nowrap">{{ \App\Models\User::find(session('2fa_user_id'))->email ?? '' }}</span>. Te rugăm să introduci codul de 6 cifre mai jos.
            </p>
</div>
<form method="POST" action="{{ route('dash.2fa.verify') }}" class="space-y-8">
@csrf
<input name="code" required class="w-full h-14 text-center text-xl font-bold tracking-widest rounded-xl border {{ $errors->has('code') ? 'border-red-500' : 'border-slate-200 dark:border-slate-700' }} bg-slate-50 dark:bg-slate-800 focus:border-primary focus:ring-0 transition-all" maxlength="6" type="text" placeholder="000000" />
@error('code')
    <p class="text-red-500 text-center text-sm mt-2">{{ $message }}</p>
@enderror
<div class="text-center" x-data="resendTimer()">
<p class="text-sm text-slate-500 dark:text-slate-400">
                    Nu ai primit codul? 
                    <button @click="resend()" :disabled="timeLeft > 0" :class="timeLeft > 0 ? 'text-slate-400 cursor-not-allowed' : 'text-primary hover:text-primary-dark cursor-pointer'" class="ml-1 font-semibold transition-colors" type="button">
                        Retrimite codul <span x-show="timeLeft > 0" x-text="`(${formattedTime})`" class="text-primary/60 font-mono"></span>
</button>
</p>
</div>

<script>
function resendTimer() {
    return {
        timeLeft: 60,
        formattedTime: '01:00',
        timer: null,
        init() {
            this.startTimer();
        },
        startTimer() {
            this.timer = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                    const minutes = Math.floor(this.timeLeft / 60);
                    const seconds = this.timeLeft % 60;
                    this.formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                } else {
                    clearInterval(this.timer);
                }
            }, 1000);
        },
        async resend() {
            if (this.timeLeft === 0) {
                try {
                    const response = await fetch("{{ route('dash.2fa.resend') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.timeLeft = 60;
                        this.formattedTime = '01:00';
                        this.startTimer();
                        // Poți lăsa un alert finuț, dar nu mai e panica "simulare"
                        // alert('Noul cod a fost trimis pe email!');
                    } else {
                        alert('Eroare la retrimiterea codului: ' + (data.message || ''));
                    }
                } catch (error) {
                    alert('Eroare de conexiune la retrimitere.');
                    console.error('Error:', error);
                }
            }
        }
    }
}
</script>
<button type="submit" class="w-full h-14 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/25 active:scale-[0.98] flex items-center justify-center gap-2">
                Verifică și continuă
            </button>
</form>
</div>
<div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden pointer-events-none">
<div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px]"></div>
<div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-purple-500/5 rounded-full blur-[120px]"></div>
</div>

</body></html>