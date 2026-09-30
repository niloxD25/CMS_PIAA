@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Personnes')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.personnes.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouvelle Personne
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" id="nom" name="nom" value="{{ request('nom') }}" 
                               class="form-input" placeholder="Rechercher par nom...">
                    </div>

                    <div>
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="{{ request('prenom') }}" 
                               class="form-input" placeholder="Rechercher par prénom...">
                    </div>

                    <div>
                        <label for="poste" class="form-label">Poste</label>
                        <input type="text" id="poste" name="poste" value="{{ request('poste') }}" 
                               class="form-input" placeholder="Rechercher par poste...">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.personnes.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des personnes -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Photo</th>
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
                        <th class="table-header sortable {{ $sortBy == 'prenom' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'prenom', 'sort_order' => $sortBy == 'prenom' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Prénom
                                @if($sortBy == 'prenom')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'poste' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'poste', 'sort_order' => $sortBy == 'poste' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Poste
                                @if($sortBy == 'poste')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Projets</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personnes as $personne)
                    <tr class="table-row">
                        <td class="table-cell logo-cell">
                            @if($personne->photo)
                                <img src="{{ asset($personne->photo) }}" alt="{{ $personne->prenom }} {{ $personne->nom }}" class="logo-image">
                            @else
                                <div class="logo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $personne->nom }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $personne->prenom }}</div>
                        </td>
                        <td class="table-cell">
                            @if($personne->poste)
                                <div class="organisation-description">{{ $personne->poste }}</div>
                            @else
                                <span class="no-description">Non renseigné</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($personne->projets->count() > 0)
                                <div class="projects-list">
                                    @foreach($personne->projets->take(2) as $projet)
                                        <span class="project-badge" title="{{ $projet->nom }}">
                                            {{ Str::limit($projet->nom, 20) }}
                                        </span>
                                    @endforeach
                                    @if($personne->projets->count() > 2)
                                        <span class="project-count">
                                            +{{ $personne->projets->count() - 2 }} autres
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="no-link">Aucun projet</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.personnes.show', $personne->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.personnes.edit', $personne->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $personne->id }}, '{{ addslashes($personne->prenom . ' ' . $personne->nom) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="6" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Aucune personne trouvée</p>
                                <a href="{{ route('admin.personnes.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer la première personne
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
                    Affichage de <strong>{{ $personnes->firstItem() ?? 0 }}</strong> à <strong>{{ $personnes->lastItem() ?? 0 }}</strong> sur <strong>{{ $personnes->total() }}</strong> personnes
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $personnes->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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

<!-- Formulaire de suppression de photo caché -->
<form id="deletePhotoForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(personneId, personneNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${personneNom}" ?\n\nCette action supprimera également toutes ses participations aux projets.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/personnes/${personneId}`;
        form.submit();
    }
}

function confirmDeletePhoto(personneId, personneNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la photo de "${personneNom}" ?`)) {
        const form = document.getElementById('deletePhotoForm');
        form.action = `/admin/personnes/${personneId}/photo`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('nom', '{{ request('nom') }}');
    url.searchParams.set('prenom', '{{ request('prenom') }}');
    url.searchParams.set('poste', '{{ request('poste') }}');
    window.location.href = url.toString();
}
</script>

<style>
.projects-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.project-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #e0f2fe;
    color: #0369a1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.project-count {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f3f4f6;
    color: #6b7280;
}

.photo-btn {
    background: #f59e0b;
    color: white;
}

.photo-btn:hover {
    background: #d97706;
}

.logo-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.logo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

/* Ajustement pour la grille des filtres */
.grid-cols-1.md\:grid-cols-3 {
    grid-template-columns: repeat(1, 1fr);
}

@media (min-width: 768px) {
    .grid-cols-1.md\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }
}

.empty-state i.fa-users {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}
</style>
@endsection