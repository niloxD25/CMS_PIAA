@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Témoignages')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.temoignages.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouveau Témoignage
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                    <!-- Nouveau filtre pour est_important -->
                    <div>
                        <label for="est_important" class="form-label">Importance</label>
                        <select id="est_important" name="est_important" class="form-input">
                            <option value="">Tous</option>
                            <option value="1" {{ request('est_important') === '1' ? 'selected' : '' }}>Importants</option>
                            <option value="0" {{ request('est_important') === '0' ? 'selected' : '' }}>Non importants</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.temoignages.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des témoignages -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Photo</th>
                        <th class="table-header sortable {{ $sortBy == 'temoin' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'temoin', 'sort_order' => $sortBy == 'temoin' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Témoin
                                @if($sortBy == 'temoin')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Résidence</th>
                        <th class="table-header">Témoignage</th>
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
                        <th class="table-header">Statut</th>
                        <th class="table-header">Importance</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($temoignages as $temoignage)
                    <tr class="table-row {{ $temoignage->archived ? 'archived-row' : '' }}">
                        <td class="table-cell logo-cell">
                            @if($temoignage->image)
                                <img src="{{ asset($temoignage->image) }}" alt="{{ $temoignage->temoin }}" class="logo-image">
                            @else
                                <div class="logo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $temoignage->temoin }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">{{ $temoignage->residence ?? 'Non renseignée' }}</div>
                        </td>
                        <td class="table-cell">
                            @if($temoignage->contenu)
                                <div class="organisation-description">{{ Str::limit($temoignage->contenu, 80) }}</div>
                            @else
                                <span class="no-description">Aucun contenu</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($temoignage->projet_id)
                                <span class="project-badge">{{ $temoignage->projets->nom ?? 'Projet inconnu' }}</span>
                            @else
                                <span class="no-link">Aucun projet</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($temoignage->archived)
                                <span class="status-badge archived">Archivé</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($temoignage->est_important)
                                <span class="important-badge important">Important</span>
                            @else
                                <span class="important-badge non-important">Standard</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.temoignages.show', $temoignage->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.temoignages.edit', $temoignage->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Bouton pour basculer l'état important -->
                                <button type="button" 
                                        onclick="confirmToggleImportant({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', {{ $temoignage->est_important ? 'true' : 'false' }})" 
                                        class="action-btn {{ $temoignage->est_important ? 'important-btn-active' : 'important-btn' }}"
                                        title="{{ $temoignage->est_important ? 'Retirer des importants' : 'Marquer comme important' }}">
                                    <i class="fas fa-{{ $temoignage->est_important ? 'star' : 'star' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmArchive({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', {{ $temoignage->archived ? 'true' : 'false' }})" 
                                        class="action-btn archive-btn"
                                        title="{{ $temoignage->archived ? 'Désarchiver' : 'Archiver' }}">
                                    <i class="fas fa-{{ $temoignage->archived ? 'folder-open' : 'archive' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmDelete({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="8" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <p>Aucun témoignage trouvé</p>
                                <a href="{{ route('admin.temoignages.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer le premier témoignage
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
                    Affichage de <strong>{{ $temoignages->firstItem() ?? 0 }}</strong> à <strong>{{ $temoignages->lastItem() ?? 0 }}</strong> sur <strong>{{ $temoignages->total() }}</strong> témoignages
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $temoignages->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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

<!-- Formulaire de basculement important caché -->
<form id="importantForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(temoignageId, temoinNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le témoignage de "${temoinNom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/temoignages/${temoignageId}`;
        form.submit();
    }
}

function confirmArchive(temoignageId, temoinNom, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le témoignage de "${temoinNom}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/temoignages/${temoignageId}/toggle-archive`;
        form.submit();
    }
}

function confirmToggleImportant(temoignageId, temoinNom, isImportant) {
    const action = isImportant ? 'retirer des importants' : 'marquer comme important';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le témoignage de "${temoinNom}" ?`)) {
        const form = document.getElementById('importantForm');
        form.action = `/admin/temoignages/${temoignageId}/toggle-important`;
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
    url.searchParams.set('est_important', '{{ request('est_important') }}');
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

.important-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.important-badge.important {
    background: var(--color-primary);
}

.important-badge.non-important {
    background: #6b7280;
}

.project-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #e0f2fe;
    color: #0369a1;
}

.archive-btn {
    background: var(--color-warning);
    color: white;
}

.archive-btn:hover {
    background: #eab308;
}

.important-btn {
    background: #6b7280;
    color: white;
}

.important-btn:hover {
    background: #4b5563;
}

.important-btn-active {
    background: var(--color-primary);
    color: white;
}

.important-btn-active:hover {
    background: #2563eb;
}

/* Ajustement pour la grille des filtres */
.grid-cols-1.md\:grid-cols-4 {
    grid-template-columns: repeat(1, 1fr);
}

@media (min-width: 768px) {
    .grid-cols-1.md\:grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
@endsection