<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Manager - @yield('title', 'Tableau de bord')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/content-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/items.css') }}">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar Content Manager -->
        <div class="content-sidebar" id="sidebar">
            <!-- Logo et bouton de toggle -->
            <div class="content-sidebar-header">
                <div class="content-sidebar-logo">
                    <div class="content-sidebar-logo-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h1 class="content-sidebar-title">Content Panel</h1>
                        <p class="content-sidebar-subtitle">Espace Content Manager</p>
                    </div>
                </div>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <div class="content-sidebar-nav-container">
                <!-- Navigation Content Manager -->
                <nav class="content-sidebar-nav">
                    <ul>
                        <li>
                            <a href="{{ route('admin.content.dashboard') }}" class="{{ request()->routeIs('admin.content.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chart-pie"></i>
                                <span class="nav-text">Tableau de bord</span>
                                <div class="nav-tooltip">Tableau de bord</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="fa-solid fa-users"></i>
                                <span class="nav-text">Gestion Utilisateur</span>
                                <div class="nav-tooltip">Gestion Utilisateur</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.personnes.index') }}" class="{{ request()->is('admin/personnes*') ? 'active' : '' }}">
                                <i class="fa-solid fa-sitemap"></i>
                                <span class="nav-text">Gestion Cellule</span>
                                <div class="nav-tooltip">Gestion Cellule</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.projets.index') }}" class="{{ request()->is('admin/projets*') ? 'active' : '' }}">
                                <i class="fa-solid fa-business-time"></i>
                                <span class="nav-text">Gestion Projets</span>
                                <div class="nav-tooltip">Gestion Projets</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.annonces.index') }}" class="{{ request()->is('admin/annonces*') ? 'active' : '' }}">
                                <i class="fa-duotone fa-solid fa-bullhorn"></i>
                                <span class="nav-text">Gestion Annonces</span>
                                <div class="nav-tooltip">Gestion Annonces</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.messages.index') }}" 
                            class="{{ request()->is('admin/messages*') ? 'active' : '' }} relative">
                                <i class="fa-solid fa-envelope-open"></i>
                                <span class="nav-text">Gestion Messages</span>
                                @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                                    <span class="absolute -top-0 -right-0 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                        {{ $unreadMessagesCount }}
                                    </span>
                                @endif
                                <div class="nav-tooltip">Gestion Messages</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.organisations.index') }}" class="{{ request()->is('admin/organisations*') ? 'active' : '' }}">
                                <i class="fas fa-building"></i>
                                <span class="nav-text">Gestion Organisations</span>
                                <div class="nav-tooltip">Gestion Organisations</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.temoignages.index') }}" class="{{ request()->is('admin/temoignages*') ? 'active' : '' }}">
                                <i class="fas fa-quote-left"></i>
                                <span class="nav-text">Gestion Témoignages</span>
                                <div class="nav-tooltip">Gestion Témoignages</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.evenements.index') }}" class="{{ request()->is('admin/evenements*') ? 'active' : '' }}">
                                <i class="fa-solid fa-calendar-day"></i>
                                <span class="nav-text">Gestion Evènements</span>
                                <div class="nav-tooltip">Gestion Evènements</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.fichiers.index') }}" class="{{ request()->is('admin/fichiers*') ? 'active' : '' }}">
                                <i class="fas fa-file"></i>
                                <span class="nav-text">Gestion Fichiers</span>
                                <div class="nav-tooltip">Gestion Fichiers</div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.realisations.index') }}" class="{{ request()->is('admin/realisations*') ? 'active' : '' }}">
                                <i class="fa-solid fa-check-double"></i>
                                <span class="nav-text">Gestion Réalisations</span>
                                <div class="nav-tooltip">Gestion Réalisations</div>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- User info & Logout -->
            <div class="content-sidebar-footer">
                <div class="content-user-info">
                    <div class="content-user-avatar">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="content-user-details">
                        <h4>{{ Auth::user()->name }}</h4>
                        <p>Content Manager</p>
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
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Tableau de bord content manager')</h1>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user-edit mr-2"></i>
                        Gestion de Contenu
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
    </script>

    @stack('scripts')
</body>
</html>