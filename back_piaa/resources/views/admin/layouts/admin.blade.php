<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Tableau de bord')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/content-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/items.css') }}">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
    <style>
        /* Style pour le bouton du dropdown */
        .nav-dropdown-toggle {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
            border: none;
            background: transparent;
            color: inherit;
            cursor: pointer;
        }

        .nav-dropdown-toggle.active .arrow-icon {
            transform: rotate(180deg);
        }

        /* Cache le texte et la flèche quand la sidebar est repliée */
        .collapsed .nav-dropdown-toggle .arrow-icon,
        .collapsed .nav-dropdown-toggle .nav-text {
            display: none;
        }

        /* Style du sous-menu */
        .nav-submenu.show {
            display: block !important;
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar Admin -->
        <div class="content-sidebar" id="sidebar">
            <!-- Logo et bouton de toggle -->
            <div class="content-sidebar-header">
                <div class="content-sidebar-logo">
                    <div class="content-sidebar-logo-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h1 class="content-sidebar-title">Admin Panel</h1>
                        <p class="content-sidebar-subtitle">Espace Administrateur</p>
                    </div>
                </div>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <!-- Conteneur pour la navigation scrollable -->
            <div class="content-sidebar-nav-container">
                <!-- Navigation Admin -->
                <nav class="content-sidebar-nav">
                    <ul>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="nav-text">Tableau de bord</span>
                                <div class="nav-tooltip">Tableau de bord</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}"
                            class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span class="nav-text">Gestion Utilisateurs</span>
                                <div class="nav-tooltip">Gestion Utilisateurs</div>
                            </a>
                        </li>
                        <li class="nav-dropdown">
                            <button class="nav-dropdown-toggle {{ request()->is('admin/audits*') || request()->is('admin/user-logins*') ? 'active' : '' }}" 
                                    onclick="toggleSubmenu(this)">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="nav-text text-left flex-1">Historiques</span>
                                <i class="fas fa-chevron-down arrow-icon ml-auto text-xs transition-transform duration-200"></i>
                                <div class="nav-tooltip">Historiques</div>
                            </button>
                            
                            <ul class="nav-submenu hidden">
                                <li>
                                    <a href="{{ route('admin.audits.index') }}" 
                                    class="pl-12 py-2 flex items-center text-xs {{ request()->routeIs('admin.audits.index') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-list mr-2 text-xs"></i>
                                        <span class="nav-text">Audits système</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.user-logins.index') }}" 
                                    class="pl-12 py-2 flex items-center text-xs {{ request()->routeIs('admin.user-logins.index') ? 'active' : '' }}">
                                        <i class="fas fa-user-clock mr-2 text-xs"></i>
                                        <span class="nav-text">Connexions</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- User info & Logout -->
            <div class="content-sidebar-footer">
                <div class="content-user-info">
                    <div class="content-user-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="content-user-details">
                        <h4>{{ Auth::user()->name }}</h4>
                        <p>Administrateur</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="content-logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="btn-text">Déconnexion</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="content-header">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="content-title text-2xl">@yield('title', 'Tableau de bord Admin')</h1>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Accès Administrateur Complet
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="content-main flex-1 overflow-x-hidden overflow-y-auto p-6">
                <!-- Messages de session -->
                @if(session('success'))
                    <div class="alert-success px-4 py-3 rounded mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-error px-4 py-3 rounded mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert-warning px-4 py-3 rounded mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('warning') }}
                        </div>
                    </div>
                @endif

                <!-- Contenu principal -->
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('toggleSidebar');
            const toggleIcon = toggleButton.querySelector('i');

            // Vérifier l'état sauvegardé
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }

            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            });

            // Fermer la sidebar en cliquant à l'extérieur (sur mobile)
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(event.target) && 
                    !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    localStorage.setItem('sidebarCollapsed', 'true');
                }
            });
        });

        function toggleSubmenu(button) {
            const submenu = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-icon');

            submenu.classList.toggle('hidden');
            submenu.classList.toggle('show');
            arrow.classList.toggle('rotate-180');
        }

        // Optionnel : Ouvrir automatiquement si on est sur une des pages du menu
        document.addEventListener('DOMContentLoaded', function() {
            const activeSubLink = document.querySelector('.nav-submenu .active');
            if (activeSubLink) {
                const submenu = activeSubLink.closest('.nav-submenu');
                submenu.classList.remove('hidden');
                submenu.classList.add('show');
                submenu.previousElementSibling.querySelector('.arrow-icon').classList.add('rotate-180');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>