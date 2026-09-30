@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Détails de la Connexion')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <a href="{{ route('admin.user-logins.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <!-- Carte de détail -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="card-title">Informations de connexion</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Utilisateur -->
                    <div class="detail-item">
                        <span class="detail-label">Utilisateur :</span>
                        <div class="detail-value">
                            @if($login->users)
                                <div class="flex items-center space-x-3">
                                    <div>
                                        <div class="font-medium">{{ $login->users->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $login->users->email }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-red-500">
                                    <i class="fas fa-user-slash mr-1"></i>Utilisateur supprimé
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Type de connexion -->
                    <div class="detail-item">
                        <span class="detail-label">Type de connexion :</span>
                        <span class="detail-value">
                            <span class="connection-type-badge badge-{{ $login->connection_type }}">
                                @switch($login->connection_type)
                                    @case(\App\Models\UserLogins::TYPE_API)
                                        <i class="fas fa-code mr-1"></i>API
                                        @break
                                    @case(\App\Models\UserLogins::TYPE_ADMIN)
                                        <i class="fas fa-user-shield mr-1"></i>Admin
                                        @break
                                    @case(\App\Models\UserLogins::TYPE_WEB)
                                        <i class="fas fa-globe mr-1"></i>Web
                                        @break
                                    @default
                                        <i class="fas fa-plug mr-1"></i>{{ ucfirst($login->connection_type) }}
                                @endswitch
                            </span>
                        </span>
                    </div>

                    <!-- Statut -->
                    <div class="detail-item">
                        <span class="detail-label">Statut :</span>
                        <span class="detail-value">
                            @if($login->is_active)
                                <span class="status-badge active-badge">
                                    <i class="fas fa-circle mr-1"></i>Session active
                                </span>
                            @else
                                <span class="status-badge inactive-badge">
                                    <i class="fas fa-circle mr-1"></i>Session terminée
                                </span>
                            @endif
                        </span>
                    </div>

                    <!-- Adresse IP -->
                    <div class="detail-item">
                        <span class="detail-label">Adresse IP :</span>
                        <span class="detail-value">{{ $login->ip_address ?? 'Non disponible' }}</span>
                    </div>

                    <!-- Date de connexion -->
                    <div class="detail-item">
                        <span class="detail-label">Date de connexion :</span>
                        <span class="detail-value">
                            {{ $login->logged_in_at ? $login->logged_in_at->format('d/m/Y H:i:s') : 'Non disponible' }}
                        </span>
                    </div>

                    <!-- Date de déconnexion -->
                    <div class="detail-item">
                        <span class="detail-label">Date de déconnexion :</span>
                        <span class="detail-value">
                            {{ $login->logged_out_at ? $login->logged_out_at->format('d/m/Y H:i:s') : 'En cours...' }}
                        </span>
                    </div>

                    <!-- Dernière activité -->
                    @if($login->last_activity_at)
                    <div class="detail-item">
                        <span class="detail-label">Dernière activité :</span>
                        <span class="detail-value">
                            {{ $login->last_activity_at->format('d/m/Y H:i:s') }}
                            <span class="text-sm text-gray-500 ml-2">
                                ({{ $login->last_activity_at->diffForHumans() }})
                            </span>
                        </span>
                    </div>
                    @endif

                    <!-- Durée de la session -->
                    <div class="detail-item">
                        <span class="detail-label">Durée de la session :</span>
                        <span class="detail-value">
                            @if($login->logged_in_at && $login->logged_out_at)
                                {{ $login->logged_in_at->diff($login->logged_out_at)->format('%h heures %i minutes %s secondes') }}
                            @elseif($login->logged_in_at)
                                {{ $login->logged_in_at->diff(now())->format('%h heures %i minutes %s secondes') }}
                                <span class="text-sm text-gray-500 ml-2">(en cours)</span>
                            @else
                                Non disponible
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations techniques -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="card-title">Informations techniques</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- ID de session -->
                    <div class="detail-item">
                        <span class="detail-label">ID de session :</span>
                        <span class="detail-value">
                            <code class="session-id">{{ $login->session_id ?? 'Non disponible' }}</code>
                            @if($login->session_id)
                            <button onclick="copyToClipboard('{{ $login->session_id }}')" 
                                    class="copy-btn ml-2"
                                    title="Copier l'ID">
                                <i class="fas fa-copy"></i>
                            </button>
                            @endif
                        </span>
                    </div>

                    <!-- User Agent -->
                    <div class="detail-item">
                        <span class="detail-label">User Agent :</span>
                        <div class="detail-value">
                            @if($login->user_agent)
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $login->user_agent }}</pre>
                                </div>
                            @else
                                <span class="text-gray-500">Non disponible</span>
                            @endif
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="detail-item">
                        <span class="detail-label">Créé le :</span>
                        <span class="detail-value">
                            {{ $login->created_at->format('d/m/Y H:i:s') }}
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Mis à jour le :</span>
                        <span class="detail-value">
                            {{ $login->updated_at->format('d/m/Y H:i:s') }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex space-x-3">
                        @if($login->is_active)
                            <button type="button" 
                                    onclick="confirmDisconnect({{ $login->id }}, '{{ addslashes($login->users ? $login->users->name : 'Utilisateur inconnu') }}')" 
                                    class="create-btn"
                                    style="background: var(--color-warning);">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnecter la session
                            </button>
                        @endif

                        @if($login->users && $login->users->hasActiveSessions())
                            <button type="button" 
                                    onclick="confirmDisconnectAll({{ $login->users->id }}, '{{ addslashes($login->users->name) }}')" 
                                    class="create-btn"
                                    style="background: var(--color-error);">
                                <i class="fas fa-user-slash mr-2"></i>Déconnecter toutes les sessions
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de session -->
    @if($login->is_active && $login->users)
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-yellow-800">Session active</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Cette session est actuellement active. L'utilisateur est connecté depuis 
                       <strong>{{ $login->logged_in_at->diffForHumans() }}</strong>.</p>
                    <p class="mt-1">Vous pouvez forcer la déconnexion si nécessaire.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Formulaire de déconnexion caché -->
<form id="disconnectForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire de déconnexion de toutes les sessions caché -->
<form id="disconnectAllForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDisconnect(loginId, userName) {
    if (confirm(`Êtes-vous sûr de vouloir déconnecter la session de "${userName}" ?\n\nCette action forcera la déconnexion immédiate.`)) {
        const form = document.getElementById('disconnectForm');
        form.action = `/admin/user-logins/${loginId}`;
        form.submit();
    }
}

function confirmDisconnectAll(userId, userName) {
    if (confirm(`Êtes-vous sûr de vouloir déconnecter TOUTES les sessions actives de "${userName}" ?\n\nCette action forcera la déconnexion sur tous les appareils.`)) {
        const form = document.getElementById('disconnectAllForm');
        form.action = `/admin/user-logins/user/${userId}/sessions`;
        form.submit();
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Afficher une notification
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('bg-green-500');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-500');
        }, 2000);
    });
}
</script>

<style>

.detail-label {
    font-weight: 500;
    color: var(--text-color);
    min-width: 180px;
}

.session-id {
    font-family: monospace;
    background-color: var(--color-background-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: var(--color-text-primary);
    word-break: break-all;
}

.copy-btn {
    background-color: var(--color-background-secondary);
    border: 1px solid var(--color-border);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    transition: all 0.2s;
}

.copy-btn:hover {
    background-color: var(--color-background-primary);
    color: var(--color-text-primary);
}

.connection-type-badge,
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-api {
    background-color: rgba(59, 130, 246, 0.1);
    color: rgb(59, 130, 246);
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.badge-admin {
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.badge-web {
    background-color: rgba(16, 185, 129, 0.1);
    color: rgb(16, 185, 129);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.active-badge {
    background-color: rgba(34, 197, 94, 0.1);
    color: rgb(34, 197, 94);
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.inactive-badge {
    background-color: rgba(107, 114, 128, 0.1);
    color: rgb(107, 114, 128);
    border: 1px solid rgba(107, 114, 128, 0.2);
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-text-primary);
}
</style>
@endsection