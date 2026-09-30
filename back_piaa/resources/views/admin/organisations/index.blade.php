@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Organisations')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.organisations.associations.index') }}" class="add-btn" style="background: var(--color-accent);">
                <i class="fas fa-link mr-2"></i>Associations
            </a>
            <a href="{{ route('admin.organisations.types-relation.index') }}" class="add-btn" style="background: var(--color-warning);">
                <i class="fas fa-tags mr-2"></i>Types de Relation
            </a>
            <a href="{{ route('admin.organisations.create') }}" class="add-btn">
                <i class="fas fa-plus mr-2"></i>Nouvelle Organisation
            </a>
        </div>
    </div>

    <!-- Tableau des organisations -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Logo</th>
                        <th class="table-header sortable {{ $sortBy == 'nom' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nom', 'sort_order' => $sortBy == 'nom' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Nom
                                @if($sortBy == 'nom')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Description</th>
                        <th class="table-header sortable {{ $sortBy == 'lien' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'lien', 'sort_order' => $sortBy == 'lien' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Lien
                                @if($sortBy == 'lien')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organisations as $organisation)
                    <tr class="table-row">
                        <td class="table-cell logo-cell">
                            @if($organisation->logo)
                                <img src="{{ asset($organisation->logo) }}" alt="{{ $organisation->nom }}" class="logo-image">
                            @else
                                <div class="logo-placeholder">
                                    <i class="fas fa-building"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $organisation->nom }}</div>
                        </td>
                        <td class="table-cell">
                            @if($organisation->description)
                                <div class="organisation-description">{{ Str::limit($organisation->description, 100) }}</div>
                            @else
                                <span class="no-description">Aucune description</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($organisation->lien)
                                <a href="{{ $organisation->lien }}" target="_blank" class="organisation-link">
                                    <i class="fas fa-external-link-alt mr-1"></i>Visiter
                                </a>
                            @else
                                <span class="no-link">Aucun lien</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.organisations.show', $organisation->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.organisations.edit', $organisation->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $organisation->id }}, '{{ addslashes($organisation->nom) }}')" 
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
                                <i class="fas fa-building"></i>
                                <p>Aucune organisation trouvée</p>
                                <a href="{{ route('admin.organisations.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer la première organisation
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
                    Affichage de <strong>{{ $organisations->firstItem() ?? 0 }}</strong> à <strong>{{ $organisations->lastItem() ?? 0 }}</strong> sur <strong>{{ $organisations->total() }}</strong> organisations
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $organisations->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
function confirmDelete(organisationId, organisationNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'organisation "${organisationNom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/organisations/${organisationId}`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    window.location.href = url.toString();
}
</script>
@endsection