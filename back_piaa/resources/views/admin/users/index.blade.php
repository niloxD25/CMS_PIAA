@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Utilisateurs')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.users.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouvel Utilisateur
        </a>
        <a href="{{ route('admin.users.types-compte.index') }}" class="add-btn" style="background: var(--color-warning);">
            <i class="fas fa-tags mr-2"></i>Types de Compte
        </a>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <div>
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" 
                            id="search" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-input"
                            placeholder="Nom ou email...">
                    </div>

                    <div>
                        <label for="type_compte_id" class="form-label">Type de compte</label>
                        <select id="type_compte_id" name="type_compte_id" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($typesCompte as $type)
                                <option value="{{ $type->id }}" {{ request('type_compte_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header sortable {{ $sortBy == 'name' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Utilisateur
                                @if($sortBy == 'name')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'email' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'email', 'sort_order' => $sortBy == 'email' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Email
                                @if($sortBy == 'email')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Type de compte</th>
                        <th class="table-header sortable {{ $sortBy == 'created_at' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => $sortBy == 'created_at' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date de création
                                @if($sortBy == 'created_at')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Email vérifié</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div class="organisation-name">{{ $user->name }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">{{ $user->email }}</div>
                        </td>
                        <td class="table-cell">
                            @if($user->typeCompte)
                                <span class="type-compte-badge">{{ $user->typeCompte->type }}</span>
                            @else
                                <span class="no-link">Aucun</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $user->created_at->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($user->email_verified_at)
                                <span class="status-badge active">Oui</span>
                            @else
                                <span class="status-badge archived">Non</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.users.show', $user->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" 
                                   class="action-btn edit-btn" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <button type="button" 
                                        onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @else
                                <span class="action-btn disabled-btn" title="Vous ne pouvez pas supprimer votre propre compte">
                                    <i class="fas fa-trash"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="7" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Aucun utilisateur trouvé</p>
                                <a href="{{ route('admin.users.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Ajouter le premier utilisateur
                                </a>
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
                <!-- Sélection du nombre d'éléments par page -->
                <div class="per-page-selector">
                    <label for="per_page" class="per-page-label">Afficher :</label>
                    <select id="per_page" name="per_page" onchange="updatePerPage(this.value)" class="styled-select">
                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span class="per-page-text">éléments par page</span>
                </div>

                <!-- Informations de pagination -->
                <div class="pagination-info">
                    Affichage de <strong>{{ $users->firstItem() ?? 0 }}</strong> à <strong>{{ $users->lastItem() ?? 0 }}</strong> sur <strong>{{ $users->total() }}</strong> utilisateurs
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $users->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(userId, userName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/users/${userId}`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('type_compte_id', '{{ request('type_compte_id') }}');
    url.searchParams.set('search', '{{ request('search') }}');
    window.location.href = url.toString();
}
</script>

<style>
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.status-badge.active {
    background: var(--color-success);
}

.status-badge.archived {
    background: var(--color-warning);
}

.type-compte-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #e0f2fe;
    color: #0369a1;
}

.organisation-logo.placeholder.bg-blue-500 {
    background: var(--color-primary) !important;
}

.disabled-btn {
    background: #9ca3af !important;
    color: white !important;
    cursor: not-allowed !important;
    opacity: 0.5;
}

.disabled-btn:hover {
    background: #9ca3af !important;
    transform: none !important;
}
</style>
@endsection