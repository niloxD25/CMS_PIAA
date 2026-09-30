@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Réalisations')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.realisations.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouvelle Réalisation
        </a>
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
                        <label for="archived" class="form-label">Statut</label>
                        <select id="archived" name="archived" class="form-input">
                            <option value="">Tous les statuts</option>
                            <option value="0" {{ request('archived') === '0' ? 'selected' : '' }}>Actifs</option>
                            <option value="1" {{ request('archived') === '1' ? 'selected' : '' }}>Archivés</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.evenements.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des réalisations -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Image</th>
                        <th class="table-header sortable {{ $sortBy == 'titre' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'titre', 'sort_order' => $sortBy == 'titre' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Réalisation
                                @if($sortBy == 'titre')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Bénéficiaires</th>
                        <th class="table-header">Impacts</th>
                        <th class="table-header sortable {{ $sortBy == 'projet' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'projet', 'sort_order' => $sortBy == 'projet' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Projet
                                @if($sortBy == 'projet')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Accès</th>
                        <th class="table-header">Statut</th>
                        <th class="table-header sortable {{ $sortBy == 'date_realisation' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_realisation', 'sort_order' => $sortBy == 'date_realisation' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date
                                @if($sortBy == 'date_realisation')
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
                    @forelse($realisations as $realisation)
                    <tr class="table-row {{ $realisation->archived ? 'archived-row' : '' }}">
                        <td class="table-cell logo-cell">
                            @if($realisation->image)
                                <img src="{{ asset($realisation->image) }}" alt="{{ $realisation->titre }}" class="logo-image">
                            @else
                                <div class="logo-image placeholder">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $realisation->titre ?? 'Sans titre' }}</div>
                        </td>
                        <td class="table-cell">
                            @if($realisation->description)
                                <div class="organisation-description">{{ Str::limit($realisation->description, 60) }}</div>
                            @else
                                <span class="no-description">Aucune description</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($realisation->beneficiaires)
                                <div class="organisation-description">{{ Str::limit($realisation->beneficiaires, 60) }}</div>
                            @else
                                <span class="no-description">Aucun bénéficiaire</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($realisation->impacts_potentiel)
                                <div class="organisation-description">{{ Str::limit($realisation->impacts_potentiel, 60) }}</div>
                            @else
                                <span class="no-description">Aucun impact</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($realisation->projet_id)
                                <span class="project-badge">{{ $realisation->projets->nom ?? 'Projet inconnu' }}</span>
                            @else
                                <span class="no-link">Aucun</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                                $typeCompte = \App\Models\TypesCompte::where('niveau_acces', $realisation->niveau_acces)->first();
                            @endphp
                            <span class="access-badge" title="{{ $typeCompte ? $typeCompte->type : 'Niveau ' . $realisation->niveau_acces }}">
                                {{ $typeCompte->type }}
                            </span>
                        </td>
                        <td class="table-cell">
                            @if($realisation->archived)
                                <span class="status-badge archived">Archivée</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $realisation->date_realisation ? $realisation->date_realisation->format('d/m/Y') : 'Non définie' }}
                            </div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.realisations.show', $realisation->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.realisations.edit', $realisation->id) }}" 
                                   class="action-btn edit-btn" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmArchive({{ $realisation->id }}, '{{ addslashes($realisation->titre ?? 'Sans titre') }}', {{ $realisation->archived ? 'true' : 'false' }})" 
                                        class="action-btn archive-btn"
                                        title="{{ $realisation->archived ? 'Désarchiver' : 'Archiver' }}">
                                    <i class="fas fa-{{ $realisation->archived ? 'folder-open' : 'archive' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmDelete({{ $realisation->id }}, '{{ addslashes($realisation->titre ?? 'Sans titre') }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="9" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <p>Aucune réalisation trouvée</p>
                                <a href="{{ route('admin.realisations.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Ajouter la première réalisation
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
                    Affichage de <strong>{{ $realisations->firstItem() ?? 0 }}</strong> à <strong>{{ $realisations->lastItem() ?? 0 }}</strong> sur <strong>{{ $realisations->total() }}</strong> réalisations
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $realisations->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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

<!-- Formulaire d'archivage caché -->
<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(realisationId, realisationTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la réalisation "${realisationTitre}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/realisations/${realisationId}`;
        form.submit();
    }
}

function confirmArchive(realisationId, realisationTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} la réalisation "${realisationTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/realisations/${realisationId}/toggle-archive`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('projet_id', '{{ request('projet_id') }}');
    url.searchParams.set('archived', '{{ request('archived') }}');
    window.location.href = url.toString();
}
</script>

<style>
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}

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

.state-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

</style>

@endsection