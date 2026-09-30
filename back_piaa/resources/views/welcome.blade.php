<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Back-office PIAA</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/base.css') }}">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                .glass {
                    background: rgba(255, 255, 255, 0.03);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.05);
                }
                @keyframes fade-in-up {
                    0% {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .animate-fade-in-up {
                    animation: fade-in-up 0.8s ease-out forwards;
                }
                .gradient-text {
                    background: linear-gradient(135deg, #f53003 0%, #ff6b35 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }
            </style>
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] font-sans antialiased min-h-screen flex flex-col items-center">
        
        <header class="w-full max-w-7xl px-6 py-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img 
                    src="{{ asset('images/piaa-logo.png') }}" 
                    alt="PIAA Logo" 
                    class="h-10 w-auto"
                    onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iI2Y1MzAwMyIvPgo8dGV4dCB4PSIyMCIgeT0iMjUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZm9udC13ZWlnaHQ9ImJvbGQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5QPC90ZXh0Pgo8L3N2Zz4K'"
                >
            </div>

            @if (Route::has('admin.login'))
                <nav class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="px-5 py-2 bg-gradient-to-r from-[#f53003] to-[#ff6b35] text-white rounded-full text-sm font-medium transition-all hover:shadow-lg hover:shadow-[#f5300330] hover:scale-105">
                            Tableau de bord
                        </a>
                    @else
                        <a href="{{ route('admin.login') }}" class="px-5 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded-full text-sm font-medium hover:bg-black/5 dark:hover:bg-white/5 transition-all hover:border-[#f53003]/30">
                            Connexion
                        </a>
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-1 w-full max-w-7xl px-6 flex flex-col lg:flex-row items-center justify-center gap-12 pb-20">
            
            <div class="flex-1 space-y-8 text-center lg:text-left animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 text-xl font-semibold tracking-wider" style="color: var(--color-accent);>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#f53003] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#f53003]"></span>
                    </span>
                    Système de Gestion de contenu
                </div>
                
                <div class="space-y-6">
                    
                    <div class="space-y-4 max-w-2xl">
                        <p class="text-xl text-[#706f6c] dark:text-[#a1a09a] leading-relaxed">
                            Gérez l'ensemble des contenus, des publications et des données en temps réel du site web.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex-1 relative w-full max-w-[500px] lg:max-w-none">
                <div class="relative z-10 animate-fade-in duration-750">
                    <div class="glass p-4 rounded-3xl shadow-2xl">
                        <div class="bg-white dark:bg-[#161615] rounded-2xl overflow-hidden border border-[#e3e3e0] dark:border-[#3E3E3A] shadow-lg">
                            <div class="h-10 bg-gradient-to-r from-[#fdfdfc] to-[#f9f9f8] dark:from-[#1c1c1a] dark:to-[#191918] border-b border-[#e3e3e0] dark:border-[#3E3E3A] flex items-center px-4 gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-[#ff5f57]"></div>
                                <div class="w-3 h-3 rounded-full bg-[#febc2e]"></div>
                                <div class="w-3 h-3 rounded-full bg-[#28c840]"></div>
                                <div class="flex-1 text-center text-xs text-[#706f6c] font-medium">piaa.mg</div>
                            </div>
                            <div class="p-8">
                                <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <!-- Header du dashboard -->
                                    <rect x="20" y="20" width="360" height="60" rx="8" fill="url(#headerGradient)" fill-opacity="0.9"/>
                                    <rect x="40" y="40" width="120" height="8" rx="4" fill="white" fill-opacity="0.8"/>
                                    <rect x="40" y="52" width="80" height="5" rx="2.5" fill="white" fill-opacity="0.6"/>
                                    
                                    <!-- Cards -->
                                    <rect x="30" y="100" width="170" height="100" rx="10" fill="#f53003" fill-opacity="0.1" stroke="#f53003" stroke-opacity="0.2" stroke-width="1"/>
                                    <rect x="220" y="100" width="170" height="100" rx="10" fill="#706f6c" fill-opacity="0.05" stroke="#706f6c" stroke-opacity="0.1" stroke-width="1"/>
                                    
                                    <!-- Graphique -->
                                    <rect x="30" y="220" width="340" height="80" rx="8" fill="#706f6c" fill-opacity="0.03"/>
                                    <path d="M50 250 L90 240 L130 260 L170 230 L210 250 L250 220 L290 240 L330 250 L370 235" stroke="#f53003" stroke-width="2" stroke-linecap="round" fill="none"/>
                                    
                                    <!-- Points du graphique -->
                                    <circle cx="50" cy="250" r="3" fill="#f53003"/>
                                    <circle cx="90" cy="240" r="3" fill="#f53003"/>
                                    <circle cx="130" cy="260" r="3" fill="#f53003"/>
                                    <circle cx="170" cy="230" r="3" fill="#f53003"/>
                                    <circle cx="210" cy="250" r="3" fill="#f53003"/>
                                    <circle cx="250" cy="220" r="3" fill="#f53003"/>
                                    <circle cx="290" cy="240" r="3" fill="#f53003"/>
                                    <circle cx="330" cy="250" r="3" fill="#f53003"/>
                                    <circle cx="370" cy="235" r="3" fill="#f53003"/>
                                    
                                    <defs>
                                        <linearGradient id="headerGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#f53003" stop-opacity="0.8"/>
                                            <stop offset="100%" stop-color="#ff6b35" stop-opacity="0.8"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -top-10 -right-10 w-64 h-64 bg-gradient-to-br from-[#f53003] to-[#ff8c42] rounded-full mix-blend-multiply filter blur-[80px] opacity-20 animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-gradient-to-tr from-orange-400 to-amber-300 rounded-full mix-blend-multiply filter blur-[80px] opacity-20 animate-pulse delay-700"></div>
            </div>
        </main>

        <footer class="w-full max-w-7xl px-6 py-8 border-t border-[#e3e3e0] dark:border-[#3E3E3A] flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-[#706f6c]">
            <div class="flex items-center gap-3">
                <p>© 2025 PIAA • Tous droits réservés.</p>
            </div>
            <p class="text-xs text-[#a1a09a]">Version 1.0</p>
        </footer>

    </body>
</html>