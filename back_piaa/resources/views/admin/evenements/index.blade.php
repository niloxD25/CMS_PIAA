@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Événements')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.evenements.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouvel Événement
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
                        <label for="etat_id" class="form-label">État</label>
                        <select id="etat_id" name="etat_id" class="form-input">
                            <option value="">Tous les états</option>
                            @foreach($etatsEvenement as $etat)
                                <option value="{{ $etat->id }}" {{ request('etat_id') == $etat->id ? 'selected' : '' }}>
                                    {{ $etat->etat }}
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

                    <div>
                        <label for="est_actualite" class="form-label">Actualité</label>
                        <select id="est_actualite" name="est_actualite" class="form-input">
                            <option value="">Tous</option>
                            <option value="1" {{ request('est_actualite') === '1' ? 'selected' : '' }}>Actualités</option>
                            <option value="0" {{ request('est_actualite') === '0' ? 'selected' : '' }}>Non actualités</option>
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

    <!-- Tableau des événements -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Image</th>
                        <th class="table-header sortable {{ $sortBy == 'titre' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'titre', 'sort_order' => $sortBy == 'titre' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Titre
                                @if($sortBy == 'titre')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Description</th>
                        <th class="table-header sortable {{ $sortBy == 'date_debut' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_debut', 'sort_order' => $sortBy == 'date_debut' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date début
                                @if($sortBy == 'date_debut')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Date fin</th>
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
                        <th class="table-header">État</th>
                        <th class="table-header">Statut</th>
                        <th class="table-header">Actualité</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evenements as $evenement)
                    <tr class="table-row {{ $evenement->archived ? 'archived-row' : '' }}">
                        <td class="table-cell logo-cell">
                            @if($evenement->image)
                                <img src="{{ asset($evenement->image) }}" alt="{{ $evenement->titre }}" class="logo-image">
                            @else
                                <div class="logo-placeholder">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $evenement->titre }}</div>
                        </td>
                        <td class="table-cell">
                            @if($evenement->description)
                                <div class="organisation-description">{{ Str::limit($evenement->description, 80) }}</div>
                            @else
                                <span class="no-description">Aucune description</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $evenement->date_debut ? $evenement->date_debut->format('d/m/Y') : 'Non définie' }}
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $evenement->date_fin ? $evenement->date_fin->format('d/m/Y') : 'Non définie' }}
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($evenement->projet_id)
                                <span class="project-badge">{{ $evenement->projets->nom ?? 'Projet inconnu' }}</span>
                            @else
                                <span class="no-link">Aucun projet</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($evenement->etatCourant && $evenement->etatCourant->etats_evenement)
                                <span class="state-badge state-{{ strtolower(str_replace(' ', '-', $evenement->etatCourant->etats_evenement->etat)) }}">
                                    {{ $evenement->etatCourant->etats_evenement->etat }}
                                </span>
                            @else
                                <span class="state-badge state-indefini">Non défini</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($evenement->archived)
                                <span class="status-badge archived">Archivé</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($evenement->est_actualite)
                                <span class="actualite-badge actualite">Actualité</span>
                            @else
                                <span class="actualite-badge non-actualite">Standard</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.evenements.show', $evenement->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.evenements.edit', $evenement->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Bouton pour basculer l'état d'actualité -->
                                <button type="button" 
                                        onclick="confirmToggleActualite({{ $evenement->id }}, '{{ addslashes($evenement->titre) }}', {{ $evenement->est_actualite ? 'true' : 'false' }})" 
                                        class="action-btn {{ $evenement->est_actualite ? 'actualite-btn-active' : 'actualite-btn' }}"
                                        title="{{ $evenement->est_actualite ? 'Retirer des actualités' : 'Marquer comme actualité' }}">
                                    <i class="fas fa-{{ $evenement->est_actualite ? 'star' : 'star' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmArchive({{ $evenement->id }}, '{{ addslashes($evenement->titre) }}', {{ $evenement->archived ? 'true' : 'false' }})" 
                                        class="action-btn archive-btn"
                                        title="{{ $evenement->archived ? 'Désarchiver' : 'Archiver' }}">
                                    <i class="fas fa-{{ $evenement->archived ? 'folder-open' : 'archive' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmDelete({{ $evenement->id }}, '{{ addslashes($evenement->titre) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="10" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Aucun événement trouvé</p>
                                <a href="{{ route('admin.evenements.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer le premier événement
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
                    Affichage de <strong>{{ $evenements->firstItem() ?? 0 }}</strong> à <strong>{{ $evenements->lastItem() ?? 0 }}</strong> sur <strong>{{ $evenements->total() }}</strong> événements
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $evenements->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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

<!-- Formulaire de basculement actualité caché -->
<form id="actualiteForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(evenementId, evenementTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'événement "${evenementTitre}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/evenements/${evenementId}`;
        form.submit();
    }
}

function confirmArchive(evenementId, evenementTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} l'événement "${evenementTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/evenements/${evenementId}/toggle-archive`;
        form.submit();
    }
}

function confirmToggleActualite(evenementId, evenementTitre, isActualite) {
    const action = isActualite ? 'retirer des actualités' : 'marquer comme actualité';
    if (confirm(`Êtes-vous sûr de vouloir ${action} l'événement "${evenementTitre}" ?`)) {
        const form = document.getElementById('actualiteForm');
        form.action = `/admin/evenements/${evenementId}/toggle-actualite`;
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
    url.searchParams.set('est_actualite', '{{ request('est_actualite') }}');
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

.actualite-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.actualite-badge.actualite {
    background: var(--color-primary);
}

.actualite-badge.non-actualite {
    background: #6b7280;
}

.state-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

/* Couleurs prédéfinies pour les états */
.state-a-venir { background: var(--bleu-ge); }
.state-en-cours { background: var(--color-success); }
.state-terminé { background: #6b7280; }
.state-annulé { background: var(--color-error); }
.state-indefini { background: #9ca3af; }

.archive-btn {
    background: var(--color-warning);
    color: white;
}

.archive-btn:hover {
    background: #eab308;
}

.actualite-btn {
    background: #6b7280;
    color: white;
}

.actualite-btn:hover {
    background: #4b5563;
}

.actualite-btn-active {
    background: var(--color-primary);
    color: white;
}

.actualite-btn-active:hover {
    background: #2563eb;
}

.filters-container {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filters-form {
    width: 100%;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
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