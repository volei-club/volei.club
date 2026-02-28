<!DOCTYPE html>

<html lang="ro">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Volei.Club - Aplicație Abonamente pentru Cluburi de Volei din Iași</title>
    <meta name="description"
        content="Gestionează-ți clubul de volei din Iași cu Volei.Club. Aplicație completă pentru abonamente, prezență, antrenamente și management sportiv eficient.">
    <meta name="keywords"
        content="club volei iasi, aplicatie abonamente volei, management club sportiv iasi, volei club, abonamente sportivi">
    <link rel="canonical" href="https://volei.club" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://volei.club">
    <meta property="og:title" content="Volei.Club - Aplicație Abonamente pentru Cluburi de Volei din Iași">
    <meta property="og:description"
        content="Platforma completă pentru managementul clubului tău de volei - Abonamente, Prezență, Scoruri.">
    <meta property="og:image" content="https://volei.club/og-image.jpg">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "Volei.Club",
      "operatingSystem": "Web, iOS, Android",
      "applicationCategory": "SportsApplication",
      "description": "Platforma de management sportiv dedicată cluburilor de volei, inclusiv gestionarea abonamentelor și prezenței.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "RON"
      },
      "areaServed": {
        "@type": "City",
        "name": "Iași"
      }
    }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#1E40AF", // Volleyball Blue
                        "primary-dark": "#1e3a8a",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                        "surface": "#ffffff",
                    },
                    fontFamily: {
                        display: ["Manrope", "sans-serif"],
                        sans: ["Manrope", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                        lg: "0.75rem",
                        xl: "1rem",
                        "2xl": "1.5rem",
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        html, body {
      overflow-x: hidden;
      position: relative;
    }
    
    body {
      min-height: max(884px, 100dvh);
    }
    
    /* Animation Styles */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .animate-float {
      animation: float 4s ease-in-out infinite;
    }
    
    nav.scrolled {
      background-color: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(226, 232, 240, 0.8);
      padding: 0.5rem 0;
    }
    
    .delay-100 { transition-delay: 100ms; }
    .delay-200 { transition-delay: 200ms; }
    .delay-300 { transition-delay: 300ms; }
    .delay-400 { transition-delay: 400ms; }
	 .mobile-screen {
            aspect-ratio: 9/19.5;
            max-width: 320px;
            width: 100%;
            margin: 0 auto;
            border: 8px solid #1e293b;
            border-radius: 40px;
            overflow: hidden;
            background-color: #f1f5f9;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .mobile-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 24px;
            background-color: #1e293b;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            z-index: 20;
        }
        .status-bar {
            height: 32px;
            width: 100%;
            background-color: transparent;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            font-size: 10px;
            font-weight: bold;
            color: #1e293b;
            padding-top: 5px;
            z-index: 10;
        }
        .step-number {
            font-size: 120px;
            line-height: 1;
            font-weight: 800;
            color: #f1f5f9;
            position: absolute;
            top: -40px;
            left: -20px;
            z-index: 0;
            user-select: none;
        }
        @media (min-width: 1024px) {
            .step-number {
                 font-size: 180px;
                 top: -60px;
                 left: -40px;
            }
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

  </style>
</head>

<body class="bg-background-light text-slate-900 font-display overflow-x-hidden" id="acasa">
    <!-- Navbar -->
    <nav
        class="fixed top-0 left-0 right-0 z-[100] bg-white/80 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <a href="#acasa"><span
                            class="material-symbols-outlined text-primary text-3xl">sports_volleyball</span></a>
                    <a href="#acasa"><span
                            class="text-xl font-extrabold tracking-tight text-slate-900">volei.club</span></a>
                    </a>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a class="text-slate-600 hover:text-primary font-medium transition-colors"
                        href="#despre-aplicatie">Despre aplicație</a>
                    <a class="text-slate-600 hover:text-primary font-medium transition-colors"
                        href="#cum-functioneaza">Cum funcționează ?</a>
                </div>
                <div class="flex items-center gap-4">

                    <a class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-full text-sm font-bold transition-all shadow-md shadow-primary/20"
                        href="#contact">
                        Ține-mă la curent
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="relative pt-24 pb-20 lg:pt-36 lg:pb-32 overflow-hidden reveal">
        <div class="absolute inset-0 z-0 opacity-10 pointer-events-none">
            <div
                class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary rounded-full blur-[120px] transform translate-x-1/3 -translate-y-1/4">
            </div>
            <div
                class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-sky-400 rounded-full blur-[100px] transform -translate-x-1/3 translate-y-1/4">
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-primary text-xs font-bold uppercase tracking-wide border border-blue-100 mb-6">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                Aplicația se află în stadiul de dezvoltare
            </span>
            <h1
                class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight">
                Platforma completă pentru <br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-sky-500">managementul
                    clubului tău</span> de volei
            </h1>
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                Simplifică administrarea, planificarea antrenamentelor și comunicarea cu părinții. Tot ce ai nevoie
                pentru a duce clubul tău la nivelul următor.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a class="w-full sm:w-auto px-8 py-4 bg-primary hover:bg-primary-dark text-white rounded-xl font-bold text-lg shadow-lg shadow-primary/25 transition-all transform hover:-translate-y-1"
                    href="#contact">
                    Ține-mă la curent
                </a>
                <a class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl font-bold text-lg shadow-sm transition-all"
                    href="{{ route('dash.login') }}">
                    Acces anticipat
                </a>
            </div>
        </div>
        <!-- Dashboard Preview / Mockup Hero Image -->
        <div class="mt-16 max-w-6xl mx-auto px-4 relative z-10 reveal">
            <div
                class="relative bg-surface rounded-2xl shadow-2xl border border-slate-200/60 overflow-hidden ring-1 ring-slate-900/5 animate-float">
                <!-- Browser Header Mockup -->
                <div class="h-10 bg-slate-50 border-b border-slate-200 flex items-center px-4 gap-2">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 text-center text-xs text-slate-400 font-medium">dashboard.volei.club</div>
                </div>
                <!-- Dashboard Content -->
                <div class="p-6 md:p-8 bg-slate-50/50">
                    <!-- Stats Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-sm font-medium">Jucători Activi</p>
                                <span
                                    class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">+5%</span>
                            </div>
                            <p class="text-slate-900 text-2xl font-bold counter" data-target="120">120</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-sm font-medium">Meciuri Viitoare</p>
                                <span
                                    class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">+5</span>
                            </div>
                            <p class="text-slate-900 text-2xl font-bold counter" data-target="8">8</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-sm font-medium">Venituri (RON)</p>
                                <span
                                    class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">+12%</span>
                            </div>
                            <p class="text-slate-900 text-2xl font-bold counter" data-target="15400" data-suffix=" RON">
                                15,400</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-sm font-medium">Prezență</p>
                                <span
                                    class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">Avg</span>
                            </div>
                            <p class="text-slate-900 text-2xl font-bold counter" data-target="92" data-suffix="%">
                                92%
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Chart Section -->
                        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-900">Performanță Jucători</h3>
                                <select
                                    class="text-sm border-slate-200 rounded-md py-1 px-2 text-slate-600 focus:ring-primary focus:border-primary">
                                    <option>Ultima lună</option>
                                    <option>Ultimul trimestru</option>
                                </select>
                            </div>
                            <div
                                class="h-64 w-full bg-slate-50 rounded-lg relative overflow-hidden flex items-end justify-between px-4 pb-0 pt-8 gap-2">
                                <!-- Mock Bars -->
                                <div
                                    class="w-full bg-primary/20 h-[40%] rounded-t-sm hover:bg-primary/30 transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        40%</div>
                                </div>
                                <div
                                    class="w-full bg-primary/30 h-[65%] rounded-t-sm hover:bg-primary/40 transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        65%</div>
                                </div>
                                <div
                                    class="w-full bg-primary/60 h-[55%] rounded-t-sm hover:bg-primary/70 transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        55%</div>
                                </div>
                                <div
                                    class="w-full bg-primary h-[85%] rounded-t-sm hover:bg-primary-dark transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        85%</div>
                                </div>
                                <div
                                    class="w-full bg-primary/80 h-[70%] rounded-t-sm hover:bg-primary/90 transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        70%</div>
                                </div>
                                <div
                                    class="w-full bg-primary/50 h-[60%] rounded-t-sm hover:bg-primary/60 transition-colors relative group">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        60%</div>
                                </div>
                            </div>
                            <div class="flex justify-between mt-2 text-xs text-slate-400 font-medium px-2">
                                <span>Ian</span><span>Feb</span><span>Mar</span><span>Apr</span><span>Mai</span><span>Iun</span>
                            </div>
                        </div>
                        <!-- Mini List Section -->
                        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">Antrenamente Azi</h3>
                            <div class="flex flex-col gap-3 flex-1 overflow-y-auto">
                                <div
                                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 cursor-pointer">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-xl">sports_volleyball</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900">Juniori U18</p>
                                        <p class="text-xs text-slate-500">16:00 - 18:00 • Sala Polivalentă</p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                                </div>
                                <div
                                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 cursor-pointer">
                                    <div
                                        class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                        <span class="material-symbols-outlined text-xl">fitness_center</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900">Pregătire Fizică</p>
                                        <p class="text-xs text-slate-500">18:30 - 19:30 • Gym</p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                                </div>
                                <div
                                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 cursor-pointer">
                                    <div
                                        class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                        <span class="material-symbols-outlined text-xl">groups</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900">Cadete Fete</p>
                                        <p class="text-xs text-slate-500">19:00 - 21:00 • Sala 2</p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Features Grid -->
    <section class="py-20 bg-white" id="despre-aplicatie">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Despre aplicație</h2>
                <p class="text-lg text-slate-600">Tot ce ai nevoie pentru a gestiona un club de succes, într-o
                    singură
                    aplicație intuitivă.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 reveal">
                <!-- Feature 1 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Administrare Club Iași</h3>
                    <p class="text-slate-600">Gestionează echipe, antrenori și membri din Iași dintr-un singur loc.
                        Roluri și
                        permisiuni personalizabile.</p>
                </div>
                <!-- Feature 2 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">sports_handball</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Conectare</h3>
                    <p class="text-slate-600">Definește staff-ul tehnic, lotul de jucători și conectează părinții
                        pentru
                        o comunicare transparentă și eficientă..</p>
                </div>
                <!-- Feature 3 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">calendar_month</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Planificare &amp; Calendar</h3>
                    <p class="text-slate-600">Calendar interactiv pentru antrenamente. Notificări automate pentru
                        modificări de program.</p>
                </div>
                <!-- Feature 4 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">scoreboard</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Programare Meciuri</h3>
                    <p class="text-slate-600">Organizează meciuri amicale și oficiale. Ține evidența scorurilor și a
                        locațiilor.</p>
                </div>
                <!-- Feature 5 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">fact_check</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Prezență Digitală</h3>
                    <p class="text-slate-600">Monitorizare prezență în timp real prin aplicația mobilă. Rapoarte
                        detaliate de participare.</p>
                </div>
                <!-- Feature 6 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Abonamente &amp; Plăți</h3>
                    <p class="text-slate-600">Urmărește abonamentele sportivilor din clubul tău de volei. Generează
                        facturi și trimite
                        remindere automate părinților.</p>
                </div>
                <!-- Feature 7 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">monitoring</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Statistici Jucători</h3>
                    <p class="text-slate-600">Analizează progresul sportivilor. Grafice de performanță și istoric
                        medical.</p>
                </div>
                <!-- Feature 8 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">family_restroom</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Portal Părinți</h3>
                    <p class="text-slate-600">Acces dedicat pentru părinți pentru a vedea programul, mesajele și a
                        efectua plăți.</p>
                </div>
                <!-- Feature 9 -->
                <div
                    class="group p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl hover:shadow-primary/5 border border-slate-100 transition-all duration-300">
                    <div
                        class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center text-cyan-600 mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl">dashboard</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Dashboards dedicate</h3>
                    <p class="text-slate-600">Interfețe specifice pentru antrenori, admini și jucători, adaptate
                        nevoilor fiecărui rol.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- App Preview / More Details Section -->
    <section class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
            <div class="flex flex-col lg:flex-row gap-12 items-center mb-24">
                <div class="lg:w-1/2">
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-slate-800">Management Meciuri</h4>
                            <button class="text-primary text-sm font-bold bg-blue-50 px-3 py-1 rounded-md">Adaugă
                                Meci</button>
                        </div>
                        <div class="space-y-3">
                            <!-- Header (Hidden on small mobile) -->
                            <div
                                class="hidden sm:grid sm:grid-cols-4 gap-4 px-4 py-3 bg-slate-50 rounded-lg text-xs text-slate-700 uppercase font-bold">
                                <div>Echipa</div>
                                <div>Data</div>
                                <div>Status</div>
                                <div class="text-right">Scor</div>
                            </div>

                            <!-- List / Rows -->
                            <div class="space-y-4 sm:space-y-0">
                                <!-- Row 1 -->
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 p-4 sm:px-4 sm:py-3 bg-white border border-slate-100 sm:border-0 sm:border-b rounded-2xl sm:rounded-none shadow-sm sm:shadow-none items-center">
                                    <div class="col-span-2 sm:col-span-1 font-bold text-slate-900 truncate">vs CSM
                                        București</div>
                                    <div class="text-slate-500 text-xs sm:text-sm">12 Oct, 18:00</div>
                                    <div class="flex items-center">
                                        <span
                                            class="bg-green-100 text-green-800 text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-full">Confirmat</span>
                                    </div>
                                    <div class="text-right font-black sm:font-normal text-slate-400 sm:text-slate-900">
                                        -
                                    </div>
                                </div>

                                <!-- Row 2 -->
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 p-4 sm:px-4 sm:py-3 bg-white border border-slate-100 sm:border-0 sm:border-b rounded-2xl sm:rounded-none shadow-sm sm:shadow-none items-center">
                                    <div class="col-span-2 sm:col-span-1 font-bold text-slate-900 truncate">vs Rapid
                                    </div>
                                    <div class="text-slate-500 text-xs sm:text-sm">19 Oct, 11:00</div>
                                    <div class="flex items-center">
                                        <span
                                            class="bg-yellow-100 text-yellow-800 text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-full">În
                                            așteptare</span>
                                    </div>
                                    <div class="text-right font-black sm:font-normal text-slate-400 sm:text-slate-900">
                                        -
                                    </div>
                                </div>

                                <!-- Row 3 -->
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 p-4 sm:px-4 sm:py-3 bg-white border border-slate-100 sm:border-0 rounded-2xl sm:rounded-none shadow-sm sm:shadow-none items-center">
                                    <div class="col-span-2 sm:col-span-1 font-bold text-slate-900 truncate">vs
                                        Dinamo
                                    </div>
                                    <div class="text-slate-500 text-xs sm:text-sm">05 Oct, 17:00</div>
                                    <div class="flex items-center">
                                        <span
                                            class="bg-slate-100 text-slate-800 text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-full">Finalizat</span>
                                    </div>
                                    <div class="text-right font-black text-primary">3 - 1</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <span class="text-primary font-bold tracking-wider uppercase text-sm">Organizare
                        Eficientă</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-2 mb-4">Nu pierde niciodată un detaliu
                        important</h2>
                    <p class="text-lg text-slate-600 mb-6">Fie că e vorba de programarea meciurilor, gestionarea
                        transportului sau evidența scorurilor, aplicația îți oferă un tabel de bord centralizat
                        pentru
                        toate activitățile competiționale.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Notificări automate pentru părinți și jucători
                        </li>
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Export rapid în format PDF sau Excel
                        </li>
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Istoric complet al rezultatelor
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row-reverse gap-12 items-center">
                <div class="lg:w-1/2 w-full">
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-100">
                        <div class="mb-4 pb-4 border-b border-slate-100">
                            <h4 class="font-bold text-slate-800">Configurare Club</h4>
                            <p class="text-xs text-slate-500">Setări generale și preferințe</p>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-1 text-sm font-medium text-slate-700">Nume Club</label>
                                <input
                                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                    type="text" value="Volei Club Junior" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-slate-700">Sport
                                        Principal</label>
                                    <select
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                        <option selected="">Volei</option>
                                        <option>Baschet</option>
                                        <option>Handbal</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-slate-700">Sezon</label>
                                    <input
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                        type="text" value="2023-2024" />
                                </div>
                            </div>
                            <div class="flex items-center p-3 text-sm text-slate-800 rounded-lg bg-blue-50 border border-blue-100"
                                role="alert">
                                <span class="material-symbols-outlined mr-2 text-primary">info</span>
                                <div>
                                    <span class="font-medium">Sfat:</span> Activează plățile online pentru a
                                    automatiza
                                    încasările lunare.
                                </div>
                            </div>
                            <button
                                class="w-full text-white bg-primary hover:bg-primary-dark focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Salvează
                                Modificările</button>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <span class="text-primary font-bold tracking-wider uppercase text-sm">Personalizare
                        Totală</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-2 mb-4">Adaptează platforma nevoilor tale
                    </h2>
                    <p class="text-lg text-slate-600 mb-6">Indiferent dacă gestionezi un mic club școlar sau o
                        academie
                        de performanță, aplicația se configurează ușor pentru a reflecta structura organizației
                        tale.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Definire categorii de vârstă
                        </li>
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Gestionare locații și săli
                        </li>
                        <li class="flex items-center gap-3 text-slate-700">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            Setare structură taxe și abonamente
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- How It Works -->
    <section class="py-20 bg-white" id="cum-functioneaza">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Cum funcționează?</h2>
                <p class="text-lg text-slate-600">Patru pași simpli pentru a digitaliza clubul tău.</p>
            </div>
            <div class="relative">
                <!-- Connecting Line (Desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative z-10 reveal">
                    <!-- Step 1 -->
                    <div class="bg-white p-6 rounded-xl border border-slate-100 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 ring-4 ring-white relative z-10">
                            1</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Înregistrează-te</h3>
                        <p class="text-slate-500 text-sm">Creează contul clubului și configurează detaliile de bază
                            în
                            câteva minute.</p>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-white p-6 rounded-xl border border-slate-100 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 ring-4 ring-white relative z-10">
                            2</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Adaugă Echipe</h3>
                        <p class="text-slate-500 text-sm">Importă lista de jucători, creează grupele și asignează
                            antrenorii.</p>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-white p-6 rounded-xl border border-slate-100 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 ring-4 ring-white relative z-10">
                            3</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Planifică</h3>
                        <p class="text-slate-500 text-sm">Setează orarul antrenamentelor și meciurilor în calendarul
                            interactiv.</p>
                    </div>
                    <!-- Step 4 -->
                    <div class="bg-white p-6 rounded-xl border border-slate-100 text-center shadow-sm">
                        <div
                            class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 ring-4 ring-white relative z-10">
                            4</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Monitorizează</h3>
                        <p class="text-slate-500 text-sm">Urmărește prezența, plățile și performanța în timp real.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- new -->

    <section class="py-16 overflow-hidden bg-white border-slate-100 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="lg:w-1/2 relative">
                    <span class="step-number">1</span>
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">Înregistrare și
                            Configurare
                        </h2>
                        <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                            Creează profilul clubului tău în câteva secunde. Platforma este optimizată exclusiv
                            pentru
                            volei, asigurând că toate setările sunt relevante pentru nevoile tale.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Identitate Digitală</strong>
                                    <span class="text-slate-500 text-sm">Încarcă logo-ul și definește culorile
                                        clubului.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="mobile-screen bg-white flex flex-col">
                        <div class="mobile-notch"></div>
                        <div class="status-bar">
                            <span>9:41</span>
                            <div class="flex gap-1">
                                <span class="material-symbols-outlined text-[14px]">signal_cellular_alt</span>
                                <span class="material-symbols-outlined text-[14px]">wifi</span>
                                <span class="material-symbols-outlined text-[14px]">battery_full</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 bg-white relative">
                            <div class="mb-8 mt-4">
                                <h3 class="text-2xl font-bold text-slate-900">Creează Club</h3>
                                <p class="text-slate-500 text-sm">Pasul inițial pentru administrare</p>
                            </div>
                            <div class="space-y-6">
                                <div class="flex justify-center mb-6">
                                    <div
                                        class="w-24 h-24 rounded-full bg-slate-50 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 cursor-pointer">
                                        <span class="material-symbols-outlined text-3xl">add_photo_alternate</span>
                                        <span class="text-[10px] mt-1 font-bold">Upload Logo</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-700 uppercase">Nume Club</label>
                                    <input
                                        class="w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 text-sm font-bold"
                                        type="text" value="CS Volei Elite" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-700 uppercase">Sport</label>
                                    <div
                                        class="border-2 border-primary bg-blue-50 rounded-xl p-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">sports_volleyball</span>
                                        <span class="text-sm font-bold text-primary">Volei</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-700 uppercase">Localitate</label>
                                    <input
                                        class="w-full px-4 py-3 rounded-xl border-slate-200 bg-slate-50 text-sm font-bold"
                                        type="text" value="Iași" />
                                </div>
                            </div>
                            <button
                                class="w-full bg-primary text-white py-3.5 rounded-xl font-bold mt-8 shadow-lg shadow-primary/20">
                                Continuă
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-16 lg:py-24 overflow-hidden bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal">
            <div class="flex flex-col-reverse lg:flex-row items-center gap-12 lg:gap-24">
                <div class="lg:w-1/2 flex justify-center">
                    <div class="mobile-screen bg-slate-50 flex flex-col">
                        <div class="mobile-notch"></div>
                        <div class="status-bar bg-white">
                            <span>9:42</span>
                            <div class="flex gap-1">
                                <span class="material-symbols-outlined text-[14px]">signal_cellular_alt</span>
                                <span class="material-symbols-outlined text-[14px]">wifi</span>
                                <span class="material-symbols-outlined text-[14px]">battery_full</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-hidden flex flex-col relative bg-white">
                            <div class="px-5 pt-6 pb-2">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-bold text-slate-900">Echipa și Staff</h3>
                                    <button class="bg-primary/10 text-primary p-2 rounded-lg"><span
                                            class="material-symbols-outlined">person_add</span></button>
                                </div>
                                <div class="flex gap-2 overflow-x-auto pb-4 scrollbar-hide">
                                    <div
                                        class="px-4 py-1.5 bg-primary text-white rounded-full text-xs font-bold whitespace-nowrap">
                                        Jucători</div>
                                    <div
                                        class="px-4 py-1.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold whitespace-nowrap">
                                        Antrenori</div>
                                    <div
                                        class="px-4 py-1.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold whitespace-nowrap">
                                        Părinți</div>
                                </div>
                            </div>
                            <div class="flex-1 overflow-y-auto px-5 pt-2 space-y-4 pb-20">
                                <div class="pt-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                        Staff
                                        Tehnic</p>
                                    <div class="flex items-center p-3 border border-slate-100 rounded-xl bg-blue-50/50">
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold mr-3">
                                            MR</div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-slate-900 text-sm">Marius Radu</h4>
                                            <span class="text-[10px] text-primary font-bold uppercase">Antrenor
                                                Principal</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                        Jucători Activi</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center p-3 border border-slate-100 rounded-xl">
                                            <div
                                                class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold mr-3">
                                                IC</div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-slate-900 text-sm">Ioana Costin</h4>
                                                <span class="text-xs text-slate-500">Libero • Parinte: Ion C.</span>
                                            </div>
                                            <span class="text-sm font-black text-slate-300">#5</span>
                                        </div>
                                        <div class="flex items-center p-3 border border-slate-100 rounded-xl">
                                            <div
                                                class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold mr-3">
                                                VM</div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-slate-900 text-sm">Vlad Marin</h4>
                                                <span class="text-xs text-slate-500">Ridicător • Parinte: Ana
                                                    M.</span>
                                            </div>
                                            <span class="text-sm font-black text-slate-300">#8</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 border-t border-slate-100 bg-white">
                                <button class="w-full py-3 bg-slate-900 text-white rounded-xl text-sm font-bold">Invită
                                    Membru Nou</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 relative">
                    <span class="step-number text-slate-200">2</span>
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">Adaugă Antrenori,
                            Jucători
                            și Părinți</h2>
                        <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                            Organizează întreaga comunitate a clubului tău. Definește staff-ul tehnic, lotul de
                            jucători
                            și conectează părinții pentru o comunicare transparentă și eficientă.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Roluri bine definite</strong>
                                    <span class="text-slate-500 text-sm">Fiecare utilizator are acces la
                                        funcționalitățile potrivite rolului său.</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Grupuri de Părinți</strong>
                                    <span class="text-slate-500 text-sm">Menține părinții informați despre program
                                        și
                                        progresul copiilor.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-16 lg:py-24 overflow-hidden bg-white border-y border-slate-100 relative reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="lg:w-1/2 relative">
                    <span class="step-number">3</span>
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">Programează și Marchează
                            Prezența</h2>
                        <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                            Transformă modul în care gestionezi antrenamentele. După crearea unui eveniment, poți
                            marca
                            prezența instant direct din aplicație. Fără liste pe hârtie, totul digital și gata
                            pentru
                            analiză.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Interfață Rapidă de Prezență</strong>
                                    <span class="text-slate-500 text-sm">Toggle simplu pentru prezent/absent/motivat
                                        pentru fiecare jucător.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="mobile-screen bg-slate-50 flex flex-col">
                        <div class="mobile-notch"></div>
                        <div class="status-bar">
                            <span>18:05</span>
                            <div class="flex gap-1">
                                <span class="material-symbols-outlined text-[14px]">signal_cellular_alt</span>
                                <span class="material-symbols-outlined text-[14px]">wifi</span>
                                <span class="material-symbols-outlined text-[14px]">battery_full</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto bg-white relative flex flex-col">
                            <div class="p-5 border-b border-slate-100">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="material-symbols-outlined text-primary">event_available</span>
                                    <h3 class="font-bold text-slate-900">Prezență Antrenament</h3>
                                </div>
                                <p class="text-xs text-slate-500 font-medium">Azi, 14 Oct • Sala Polivalentă</p>
                            </div>
                            <div class="flex-1 p-4 space-y-3">
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-300"></div>
                                        <span class="text-sm font-bold text-slate-700">Andrei Popescu</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">done</span></button>
                                        <button
                                            class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">close</span></button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-300"></div>
                                        <span class="text-sm font-bold text-slate-700">Maria Ionescu</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">done</span></button>
                                        <button
                                            class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">close</span></button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-300"></div>
                                        <span class="text-sm font-bold text-slate-700">Dan Voiculescu</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">done</span></button>
                                        <button
                                            class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">close</span></button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-300"></div>
                                        <span class="text-sm font-bold text-slate-700">Elena Luca</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">done</span></button>
                                        <button
                                            class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center"><span
                                                class="material-symbols-outlined text-sm">history</span></button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5">
                                <button
                                    class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md">Finalizează
                                    Raport</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-16 lg:py-24 overflow-hidden bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 reveal">
            <div class="flex flex-col-reverse lg:flex-row items-center gap-12 lg:gap-24">
                <div class="lg:w-1/2 flex justify-center">
                    <div class="mobile-screen bg-slate-900 flex flex-col text-white">
                        <div class="mobile-notch bg-black"></div>
                        <div class="status-bar bg-slate-900 text-white">
                            <span>9:44</span>
                            <div class="flex gap-1">
                                <span class="material-symbols-outlined text-[14px]">signal_cellular_alt</span>
                                <span class="material-symbols-outlined text-[14px]">wifi</span>
                                <span class="material-symbols-outlined text-[14px]">battery_full</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-5 relative bg-gradient-to-b from-slate-900 to-slate-800">
                            <div class="flex justify-between items-center mb-6 mt-2">
                                <h3 class="text-xl font-bold">Monitorizare</h3>
                                <span class="text-xs font-bold bg-white/10 px-2 py-1 rounded text-slate-300">Ultima
                                    lună</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="bg-primary rounded-2xl p-4 relative overflow-hidden shadow-lg">
                                    <p class="text-blue-100 text-xs font-bold mb-1">Prezență Lot</p>
                                    <p class="text-3xl font-black">92%</p>
                                    <div class="w-full bg-white/20 h-1 mt-2 rounded-full overflow-hidden">
                                        <div class="bg-white h-full w-[92%]"></div>
                                    </div>
                                </div>
                                <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700 shadow-lg">
                                    <p class="text-slate-400 text-xs font-bold mb-1">Evoluție</p>
                                    <p class="text-3xl font-black text-green-400">+5%</p>
                                    <p class="text-[10px] text-slate-500 mt-1">vs luna trecută</p>
                                </div>
                            </div>
                            <h4 class="text-sm font-bold text-slate-300 mb-3 uppercase tracking-wider">Top Prezență
                            </h4>
                            <div class="space-y-3">
                                <div
                                    class="bg-white/5 backdrop-blur-sm rounded-xl p-3 flex items-center border border-white/10">
                                    <div class="text-yellow-400 font-bold mr-3 text-lg">1</div>
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs mr-3">
                                        AP</div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm">Andrei P.</p>
                                        <p class="text-[10px] text-slate-400">100% Prezență</p>
                                    </div>
                                    <span class="material-symbols-outlined text-yellow-500">star</span>
                                </div>
                                <div
                                    class="bg-white/5 backdrop-blur-sm rounded-xl p-3 flex items-center border border-white/10">
                                    <div class="text-slate-400 font-bold mr-3 text-lg">2</div>
                                    <div
                                        class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-xs mr-3">
                                        MI</div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm">Maria I.</p>
                                        <p class="text-[10px] text-slate-400">98% Prezență</p>
                                    </div>
                                </div>
                                <div
                                    class="bg-white/5 backdrop-blur-sm rounded-xl p-3 flex items-center border border-white/10 opacity-70">
                                    <div class="text-slate-500 font-bold mr-3 text-lg">3</div>
                                    <div
                                        class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold text-xs mr-3">
                                        DV</div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm">Dan V.</p>
                                        <p class="text-[10px] text-slate-400">95% Prezență</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 relative">
                    <span class="step-number text-slate-200">4</span>
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">Monitorizează Evoluția
                            Clubului tău din Iași
                        </h2>
                        <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                            Ia decizii bazate pe date, nu pe presupuneri. aplicația analizează automat datele de
                            prezență și performanță, oferindu-ți rapoarte clare despre implicarea fiecărui membru.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Statistici Automate</strong>
                                    <span class="text-slate-500 text-sm">Vezi dintr-o privire cine sunt cei mai
                                        activi
                                        jucători din club.</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-1 text-xl">check_circle</span>
                                <div>
                                    <strong class="block text-slate-900">Rapoarte Exportabile</strong>
                                    <span class="text-slate-500 text-sm">Perfecte pentru contabilitate sau rapoarte
                                        către conducerea clubului.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Request Form -->
    <section class="py-20 bg-slate-900 relative overflow-hidden reveal" id="contact">
        <div class="absolute inset-0 z-0 opacity-20"
            style="background-size:cover; background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAZXrayFZuiFYPvPyjTWXgFO5qECN0w0WmjBENNabUWR0v4xyXOsbmRZtKWKRs4-PLwzEd8cGCug4GuL8CNABvFbrZrz3JTEMgj0FWZgMUwxV2-2l26AV6gYlmnH8ZFvGZ2k9qh4IpUfqb8BvunE95Br39FKaSDkm9QJXW1QKpaHO-qq-OgZpmlQ4yliTEGTVv1hh_ecYxsXY3tTIzelnVlVvIRBd6p6Eb78jkKIs_DRa31SfIIkGq4MP7D6ftBbhKZJK0opIM6vng');">
        </div>
        <div class="max-w-4xl mx-auto px-4 relative z-10">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
                <div class="md:w-5/12 bg-primary p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <!-- Decorative Circle -->
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Ține-mă la curent</h3>
                        <p class="text-blue-100 mb-6">Completează formularul și te vom contacta pentru a-ți prezenta
                            cum
                            aplicația poate transforma clubul tău.</p>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-blue-200">check</span>
                                <span>Acces beta la aplicație</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-blue-200">check</span>
                                <span>Suport la configurare</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-blue-200">check</span>
                                <span>Fără obligații</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8 text-sm text-blue-200">
                        Preferi email? <br />
                        <a class="text-white font-bold underline decoration-blue-300"
                            href="mailto:contact@volei.club">contact@volei.club</a>
                    </div>
                </div>
                <div class="md:w-7/12 p-8 md:p-12">
                    <form
                        action="https://club.us10.list-manage.com/subscribe/post?u=78b2a033aeb432efded5cfa4d&amp;id=de3bdaabda&amp;f_id=00bb84e2f0"
                        method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
                        target="_blank">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-900" for="name">Nume
                                    Complet</label>
                                <input
                                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-3"
                                    id="name" placeholder="Ion Popescu" name="FNAME" required type="text" />
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-900" for="email">Adresă
                                    Email</label>
                                <input
                                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-3"
                                    id="email" placeholder="ion@exemplu.ro" name="EMAIL" required type="email" />
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-slate-900" for="club">Nume
                                        Club</label>
                                    <input
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-3"
                                        id="club" placeholder="CS Volei" name="LNAME" required type="text" />
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-slate-900" for="role">Rolul
                                        Tău</label>
                                    <select name="RTAU"
                                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-3"
                                        id="role">
                                        <option>Antrenor</option>
                                        <option>Manager Club</option>
                                        <option>Admin</option>
                                        <option>Altul</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-900" for="message">Mesaj
                                    (Opțional)</label>
                                <textarea
                                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-3"
                                    id="message" name="MESAJ" placeholder="Spune-ne mai multe despre clubul tău..."
                                    rows="3"></textarea>
                            </div>
                            <div style="position: absolute; left: -5000px;" aria-hidden="true">
                                /* real people should not fill this in and expect good things - do not remove this or
                                risk form bot signups */
                                <input type="text" name="b_78b2a033aeb432efded5cfa4d_de3bdaabda" tabindex="-1" value="">
                                <input type="hidden" name="tags" value="10028308">
                            </div>
                            <button
                                class="w-full text-white bg-slate-900 hover:bg-slate-800 focus:ring-4 focus:ring-slate-300 font-bold rounded-lg text-sm px-5 py-3.5 focus:outline-none transition-colors"
                                type="submit">
                                Înregistrează-mi cererea
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gray">
        <div class="max-w-7xl mx-auto px-4 pb-4 sm:px-6 lg:px-8">
            <div class="border-slate-200 pt-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">© 2026 volei.club. Toate drepturile rezervate.</p>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Platformă în dezvoltare
                </span>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Navbar scroll effect
            const nav = document.querySelector('nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    nav.classList.add('scrolled', 'shadow-sm');
                } else {
                    nav.classList.remove('scrolled', 'shadow-sm');
                }
            });

            // Intersection Observer for scroll reveal and counters
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px 50px 0px' // Trigger early
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (entry.target.classList.contains('reveal')) {
                            entry.target.classList.add('active');
                        }
                        if (entry.target.classList.contains('counter')) {
                            animateCounter(entry.target);
                        }
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal, .counter').forEach(el => observer.observe(el));

            // Safety fallback: Only reveal things if they are NOT active after a much longer delay (5s)
            // or if they are already in viewport but missed by observer
            setTimeout(() => {
                document.querySelectorAll('.reveal:not(.active)').forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight) {
                        el.classList.add('active');
                    }
                });
            }, 5000);

            // Counter Animation Logic
            function animateCounter(el) {
                if (el.classList.contains('counted')) return;
                el.classList.add('counted');

                const targetAttr = el.getAttribute('data-target');
                const target = parseInt(targetAttr);
                const suffix = el.getAttribute('data-suffix') || '';
                let current = 0;
                const duration = 2000;
                const increment = Math.ceil(target / 50);
                const stepTime = 30;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        el.innerText = target.toLocaleString() + suffix;
                        clearInterval(timer);
                    } else {
                        el.innerText = current.toLocaleString() + suffix;
                    }
                }, stepTime);
            }
        });
    </script>
</body>

</html>