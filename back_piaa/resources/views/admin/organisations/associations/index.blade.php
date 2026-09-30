@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Associations Organisations-Projets')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.organisations.index') }}" class="add-btn" style="background: var(--color-primary);">
                <i class="fas fa-building mr-2"></i>Organisations
            </a>
            <a href="{{ route('admin.organisations.types-relation.index') }}" class="add-btn" style="background: var(--color-warning);">
                <i class="fas fa-tags mr-2"></i>Types de Relation
            </a>
            <a href="{{ route('admin.organisations.associations.create') }}" class="add-btn">
                <i class="fas fa-plus mr-2"></i>Nouvelle Association
            </a>
        </div>
    </div>

        <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="projet_id" class="form-label">Projet</label>
                        <select id="projet_id" name="projet_id" class="form-input">
                            <option value="">Tous les projets</option>
                            @foreach($projets as $projet)
                                <option value="{{ $projet->id }}" {{ request('projet_id') == $projet->id ? 'selected' : '' }}>
                                    {{ $projet->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="organisation_id" class="form-label">Organisation</label>
                        <select id="organisation_id" name="organisation_id" class="form-input">
                            <option value="">Toutes les organisations</option>
                            @foreach($organisations as $organisation)
                                <option value="{{ $organisation->id }}" {{ request('organisation_id') == $organisation->id ? 'selected' : '' }}>
                                    {{ $organisation->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type_relation_id" class="form-label">Type de relation</label>
                        <select id="type_relation_id" name="type_relation_id" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($typesRelation as $type)
                                <option value="{{ $type->id }}" {{ request('type_relation_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn" style="background: var(--color-primary);">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.organisations.associations.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des associations -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Organisation</th>
                        <th class="table-header">Projet</th>
                        <th class="table-header">Type de relation</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($associations as $association)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div class="flex items-center">
                                @if($association->organisations->logo)
                                    <img src="{{ asset($association->organisations->logo) }}" 
                                         alt="{{ $association->organisations->nom }}" 
                                         class="logo-image mr-3">
                                @else
                                    <div class="logo-placeholder mr-3">
                                        <i class="fas fa-building"></i>
                                    </div>
                                @endif
                                <div class="organisation-name">{{ $association->organisations->nom ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center">
                                @if($association->projets->logo)
                                    <img src="{{ asset($association->projets->logo) }}" 
                                         alt="{{ $association->projets->nom }}" 
                                         class="logo-image mr-3">
                                @else
                                    <div class="logo-placeholder mr-3">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                @endif
                                <div class="organisation-name">{{ $association->projets->nom ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="relation-badge">
                                {{ $association->types_relation->type ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.organisations.associations.show', $association->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.organisations.associations.edit', $association->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $association->id }})" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="5" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-link"></i>
                                <p>Aucune association trouvée</p>
                                <a href="{{ route('admin.organisations.associations.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer la première association
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
                    Affichage de <strong>{{ $associations->firstItem() ?? 0 }}</strong> à <strong>{{ $associations->lastItem() ?? 0 }}</strong> sur <strong>{{ $associations->total() }}</strong> associations
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $associations->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
function confirmDelete(associationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette association ?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/organisations/associations/${associationId}`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}
</script>

<style>

</style>
@endsection