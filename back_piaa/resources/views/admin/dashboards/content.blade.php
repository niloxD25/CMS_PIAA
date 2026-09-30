@extends('admin.layouts.content')

@section('title', 'Tableau de bord Content Manager')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l d F Y') }}</p>
        </div>
    </div>

    <!-- Bannière de bienvenue -->
    <div class="welcome-banner mb-6">
        <div class="flex items-center space-x-4">
            <div class="welcome-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h2 class="welcome-title">Bienvenue, {{ $user->name }} !</h2>
                <p class="welcome-subtitle">Vous êtes connecté en tant que <strong>Content Manager</strong></p>
            </div>
        </div>
    </div>

    <!-- Statistiques Content Manager -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stats-label">Mes Projets</p>
                    <p class="stats-value">{{ $stats['mes_projets'] }}</p>
                </div>
                <div class="stats-icon bg-green-100">
                    <i class="fas fa-project-diagram text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stats-label">Événements en cours</p>
                    <p class="stats-value">{{ $stats['evenements_actifs'] }}</p>
                </div>
                <div class="stats-icon bg-blue-100">
                    <i class="fas fa-calendar-check text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stats-label">Messages non lus</p>
                    <p class="stats-value">{{ $unreadMessagesCount }}</p>
                </div>
                <div class="stats-icon bg-purple-100">
                    <i class="fas fa-envelope-open text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stats-label">Annonces Actives</p>
                    <p class="stats-value">{{ $stats['annonces_actives'] }}</p>
                </div>
                <div class="stats-icon bg-purple-100">
                    <i class="fas fa-bullhorn text-pink-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions et Historique -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actions Rapides -->
        <div class="bg-white rounded-lg overflow-hidden" style="border-radius: var(--border-radius);">
            <div class="px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900">Actions Rapides</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.projets.create') }}" class="quick-action-btn bg-green-50 border-green-200 hover:bg-green-100">
                        <i class="fas fa-plus-circle text-green-600"></i>
                        <span>Nouveau Projet</span>
                    </a>
                    <a href="{{ route('admin.evenements.create') }}" class="quick-action-btn bg-blue-50 border-blue-200 hover:bg-blue-100">
                        <i class="fas fa-calendar-plus text-blue-600"></i>
                        <span>Créer Événement</span>
                    </a>
                    <a href="{{ route('admin.fichiers.create') }}" class="quick-action-btn bg-purple-50 border-purple-200 hover:bg-purple-100">
                        <i class="fas fa-file-upload text-purple-600"></i>
                        <span>Upload Fichier</span>
                    </a>
                    <a href="{{ route('admin.messages.index') }}" class="quick-action-btn bg-orange-50 border-orange-200 hover:bg-orange-100 relative">
                        <i class="fas fa-envelope-open text-orange-600"></i>
                        <span>Messages</span>
                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center shadow-md">
                                {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="bg-white rounded-lg overflow-hidden" style="border-radius: var(--border-radius);">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Activité Récente</h2>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @forelse ($audits as $audit)
                    <div class="activity-item">
                        <div class="activity-icon 
                            @if($audit->event === 'created') activity-created
                            @elseif($audit->event === 'updated') activity-updated
                            @elseif($audit->event === 'deleted') activity-deleted
                            @elseif($audit->event === 'restored') activity-restored
                            @else activity-default
                            @endif">
                            @if($audit->event === 'created')
                                <i class="fas fa-plus"></i>
                            @elseif($audit->event === 'updated')
                                <i class="fas fa-edit"></i>
                            @elseif($audit->event === 'deleted')
                                <i class="fas fa-trash"></i>
                            @elseif($audit->event === 'restored')
                                <i class="fas fa-undo"></i>
                            @else
                                <i class="fas fa-info-circle"></i>
                            @endif
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">
                                {{ ucfirst($audit->event) }}
                                @if ($audit->auditable_type)
                                    <span class="text-gray-600">
                                        sur {{ class_basename($audit->auditable_type) }}
                                    </span>
                                @endif
                            </p>
                            <p class="activity-time">
                                {{ $audit->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('admin.audits.show', $audit->id) }}" class="activity-action" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-history text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500">Aucune activité récente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Guide Content Manager -->
    <div class="bg-white rounded-lg overflow-hidden mt-6" style="border-radius: var(--border-radius);">
        <div class="px-6 py-4">
            <h2 class="text-lg font-medium text-gray-900">Guide du Content Manager</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="guide-item">
                    <div class="guide-icon bg-green-100">
                        <i class="fas fa-lightbulb text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Création de Projets</h3>
                        <p class="guide-description">Créez et gérez vos projets avec descriptions détaillées et images</p>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon bg-blue-100">
                        <i class="fas fa-calendar text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Gestion d'Événements</h3>
                        <p class="guide-description">Planifiez et organisez vos événements avec dates et états</p>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon bg-purple-100">
                        <i class="fas fa-upload text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="guide-title">Upload de Fichiers</h3>
                        <p class="guide-description">Partagez documents et médias avec contrôle d'accès</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Bannière de bienvenue */
.welcome-banner {
    background: var(--color-success);
    color: var(--color-text-light);
    padding: 2rem;
    border-radius: var(--border-radius);
}

.welcome-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.welcome-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.welcome-subtitle {
    opacity: 0.9;
    font-size: 1rem;
}

/* Badge de rôle */
.role-badge {
    background: var(--color-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-left: 1rem;
}

/* Cartes de statistiques */
.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
}

.stats-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stats-value {
    color: #111827;
    font-size: 2rem;
    font-weight: 600;
    line-height: 1;
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

/* Boutons d'action rapide */
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    border-radius: var(--border-radius);
    border: 1px solid;
    text-decoration: none;
    transition: all 0.2s ease;
    text-align: center;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.quick-action-btn span {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

/* Activité récente */
.activity-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-radius: var(--border-radius);
    margin-bottom: 0.5rem;
    transition: background-color 0.2s;
}

.activity-item:hover {
    background-color: #f9fafb;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.875rem;
    color: white;
}

.activity-created { background: var(--color-success); }
.activity-updated { background: var(--color-primary); }
.activity-deleted { background: var(--color-error); }
.activity-restored { background: var(--color-warning); }
.activity-default { background: #6b7280; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.75rem;
    color: #6b7280;
}

.activity-action {
    color: #6b7280;
    padding: 0.25rem;
    border-radius: 4px;
    transition: color 0.2s;
}

.activity-action:hover {
    color: var(--color-primary);
}

/* Guide items */
.guide-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.guide-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.guide-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.guide-description {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
}
</style>
@endsection