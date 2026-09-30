@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Types de Compte')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.users.types-compte.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouveau Type
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" 
                            id="search" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-input"
                            placeholder="Rechercher un type de compte...">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-search mr-2"></i>Rechercher
                    </button>
                    <a href="{{ route('admin.users.types-compte.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des types de compte -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header sortable {{ $sortBy == 'type' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'type', 'sort_order' => $sortBy == 'type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Type de compte
                                @if($sortBy == 'type')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'niveau_acces' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'niveau_acces', 'sort_order' => $sortBy == 'niveau_acces' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Niveau d'accès
                                @if($sortBy == 'niveau_acces')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Utilisateurs</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($typesCompte as $typeCompte)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div class="organisation-name">{{ $typeCompte->type }}</div>
                        </td>
                        <td class="table-cell">
                            <span class="niveau-badge }}">
                                Niveau {{ $typeCompte->niveau_acces }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                <span class="users-count">{{ $typeCompte->users_count }} utilisateur(s)</span>
                            </div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.users.types-compte.show', $typeCompte->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.types-compte.edit', $typeCompte->id) }}" 
                                   class="action-btn edit-btn" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($typeCompte->users_count == 0)
                                <button type="button" 
                                        onclick="confirmDelete({{ $typeCompte->id }}, '{{ addslashes($typeCompte->type) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @else
                                <span class="action-btn disabled-btn" title="Impossible de supprimer - utilisateurs associés">
                                    <i class="fas fa-trash"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="5" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-user-tag"></i>
                                <p>Aucun type de compte trouvé</p>
                                <a href="{{ route('admin.users.types-compte.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Ajouter le premier type
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
                    Affichage de <strong>{{ $typesCompte->firstItem() ?? 0 }}</strong> à <strong>{{ $typesCompte->lastItem() ?? 0 }}</strong> sur <strong>{{ $typesCompte->total() }}</strong> types de compte
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $typesCompte->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
function confirmDelete(typeCompteId, typeCompteType) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type de compte "${typeCompteType}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/types-compte/${typeCompteId}`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('search', '{{ request('search') }}');
    window.location.href = url.toString();
}
</script>

<style>
.organisation-logo.placeholder.bg-purple-500 {
    background: var(--color-primary) !important;
}

.niveau-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
    background-color: var(--color-warning);
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