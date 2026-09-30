@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Connexions Utilisateurs')

<div class="container-fluid">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.user-logins.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Filtre par utilisateur -->
                    <div>
                        <label for="user_id" class="form-label">Utilisateur</label>
                        <select id="user_id" name="user_id" class="form-input">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par type de connexion -->
                    <div>
                        <label for="connection_type" class="form-label">Type de connexion</label>
                        <select id="connection_type" name="connection_type" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($connectionTypes as $type)
                                @if($type)
                                    <option value="{{ $type }}" {{ request('connection_type') == $type ? 'selected' : '' }}>
                                        @switch($type)
                                            @case(\App\Models\UserLogins::TYPE_API)
                                                API
                                                @break
                                            @case(\App\Models\UserLogins::TYPE_ADMIN)
                                                Admin
                                                @break
                                            @case(\App\Models\UserLogins::TYPE_WEB)
                                                Web
                                                @break
                                            @default
                                                {{ ucfirst($type) }}
                                        @endswitch
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par statut -->
                    <div>
                        <label for="is_active" class="form-label">Statut</label>
                        <select id="is_active" name="is_active" class="form-input">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Sessions actives</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Sessions inactives</option>
                        </select>
                    </div>

                    <!-- Nombre d'éléments par page -->
                    <div>
                        <label for="per_page" class="form-label">Éléments par page</label>
                        <select id="per_page" name="per_page" class="form-input">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.user-logins.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>

            <div class="flex mt-4">
                <form
                    method="POST"
                    action="{{ route('admin.user-logins.cleanup') }}"
                    onsubmit="return confirm('Désactiver toutes les sessions inactives ?');"
                >
                    @csrf

                    <button type="submit" class="create-btn delete-btn w-full">
                        <i class="fas fa-broom mr-2"></i>
                        Désactiver les sessions obsolètes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tableau des connexions -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="projets-table">
                <thead>
                    <tr>
                        <th class="table-header sortable {{ $sortBy == 'user' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'user', 'sort_order' => $sortBy == 'user' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Utilisateur
                                @if($sortBy == 'user')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Adresse IP</th>
                        <th class="table-header sortable {{ $sortBy == 'logged_in_at' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'logged_in_at', 'sort_order' => $sortBy == 'logged_in_at' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Connexion
                                @if($sortBy == 'logged_in_at')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'logged_out_at' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'logged_out_at', 'sort_order' => $sortBy == 'logged_out_at' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Déconnexion
                                @if($sortBy == 'logged_out_at')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Statut</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logins as $login)
                    <tr class="table-row {{ $login->is_active ? 'active-session' : '' }}">
                        <td class="table-cell">
                            <div class="user-info">
                                @if($login->users)
                                    <div class="user-name">
                                        {{ $login->users->name }}
                                    </div>
                                    <div class="user-email">
                                        {{ $login->users->email }}
                                    </div>
                                @else
                                    <div class="user-deleted">
                                        <i class="fas fa-user-slash mr-1"></i>Utilisateur supprimé
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
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
                        </td>
                        <td class="table-cell">
                            @if($login->ip_address)
                                <span class="ip-address" title="{{ $login->ip_address }}">
                                    {{ $login->ip_address }}
                                </span>
                            @else
                                <span class="no-ip">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="date-badge" title="{{ $login->logged_in_at ? $login->logged_in_at->format('d/m/Y H:i:s') : '' }}">
                                {{ $login->logged_in_at ? $login->logged_in_at->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </td>
                        <td class="table-cell">
                            @if($login->logged_out_at)
                                <span class="date-badge" title="{{ $login->logged_out_at->format('d/m/Y H:i:s') }}">
                                    {{ $login->logged_out_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="no-logout">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($login->is_active)
                                <span class="status-badge active-badge">
                                    <i class="fas fa-circle mr-1"></i>Active
                                </span>
                            @else
                                <span class="status-badge inactive-badge">
                                    <i class="fas fa-circle mr-1"></i>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <!-- Voir les détails -->
                                <a href="{{ route('admin.user-logins.show', $login->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Déconnecter la session (uniquement si active) -->
                                @if($login->is_active)
                                    <button type="button" 
                                            onclick="confirmDisconnect({{ $login->id }}, '{{ addslashes($login->users ? $login->users->name : 'Utilisateur inconnu') }}')" 
                                            class="action-btn disconnect-btn"
                                            title="Déconnecter la session">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                @else
                                    <button type="button" 
                                            class="action-btn disconnect-btn disabled"
                                            title="Session déjà déconnectée"
                                            disabled>
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                @endif

                                <!-- Déconnecter toutes les sessions de l'utilisateur (uniquement si actives) -->
                                @if($login->users && $login->users->hasActiveSessions())
                                    <button type="button" 
                                            onclick="confirmDisconnectAll({{ $login->users->id }}, '{{ addslashes($login->users->name) }}')" 
                                            class="action-btn disconnect-all-btn"
                                            title="Déconnecter toutes les sessions">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                @else
                                    <button type="button" 
                                            class="action-btn disconnect-all-btn disabled"
                                            title="Aucune session active"
                                            disabled>
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="7" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <p>Aucune connexion trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="pagination-controls">
                <!-- Informations de pagination -->
                <div class="pagination-info">
                    Affichage de <strong>{{ $logins->firstItem() ?? 0 }}</strong> à <strong>{{ $logins->lastItem() ?? 0 }}</strong> sur <strong>{{ $logins->total() }}</strong> connexions
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $logins->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
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

// Mise à jour du nombre d'éléments par page
document.getElementById('per_page').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', this.value);
    window.location.href = url.toString();
});
</script>

<style>
.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 500;
    color: var(--color-text-primary);
}

.user-email {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.user-deleted {
    color: var(--color-error);
    font-size: 0.875rem;
}

.connection-type-badge {
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

.ip-address {
    font-family: monospace;
    font-size: 0.875rem;
    background-color: var(--color-background-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    color: var(--color-text-primary);
}

.no-ip, .no-logout {
    color: var(--color-text-tertiary);
    font-style: italic;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
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

.active-session {
    background-color: rgba(34, 197, 94, 0.03);
}

.active-session:hover {
    background-color: rgba(34, 197, 94, 0.05) !important;
}

.disconnect-btn {
    background-color: var(--color-warning);
    color: white;
}

.disconnect-btn:hover:not(:disabled) {
    background-color: #d97706;
}

.disconnect-all-btn {
    background-color: var(--color-error);
    color: white;
}

.disconnect-all-btn:hover:not(:disabled) {
    background-color: #dc2626;
}

.disconnect-btn:disabled,
.disconnect-all-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.date-badge {
    padding: 0.25rem 0.5rem;
    color: var(--text-color);
}
</style>
@endsection