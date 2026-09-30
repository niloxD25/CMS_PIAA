@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Projets')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.projets.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouveau Projet
        </a>

        <a href="{{ route('admin.reseaux-sociaux.index') }}" class="add-btn" style="background: var(--color-warning);">
            <i class="fas fa-tags mr-2"></i>Gestion des réseaux sociaux
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.projets.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nom" class="form-label">Rechercher</label>
                        <input type="text" id="nom" name="nom" value="{{ request('nom') }}" 
                               class="form-input" placeholder="Nom ou acronyme...">
                    </div>

                    <div>
                        <label for="archived" class="form-label">Statut</label>
                        <select id="archived" name="archived" class="form-input">
                            <option value="">Tous les statuts</option>
                            <option value="0" {{ request('archived') === '0' ? 'selected' : '' }}>Actifs uniquement</option>
                            <option value="1" {{ request('archived') === '1' ? 'selected' : '' }}>Archivés uniquement</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.projets.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des projets -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="projets-table">
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
                        <th class="table-header">Devise</th>
                        <th class="table-header">Acronymes</th>
                        <th class="table-header">Contact</th>
                        <th class="table-header sortable {{ $sortBy == 'debut' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'debut', 'sort_order' => $sortBy == 'debut' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Début
                                @if($sortBy == 'debut')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'duree_annee' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'duree_annee', 'sort_order' => $sortBy == 'duree_annee' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Durée
                                @if($sortBy == 'duree_annee')
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
                    @php
                        $projetActuel = \App\Models\ProjetEnCours::latest('date_changement')->first();
                        $projetActuelId = $projetActuel ? $projetActuel->projet_id : null;
                    @endphp
                    
                    @forelse($projets as $projet)
                    <tr class="table-row {{ $projet->archived ? 'archived-row' : '' }}">
                        <td class="table-cell logo-cell">
                            @if($projet->logo)
                                <img src="{{ asset($projet->logo) }}" alt="{{ $projet->nom }}" class="logo-image {{ $projet->id == $projetActuelId ? 'logo-actuel' : '' }}">
                            @else
                                <div class="logo-placeholder {{ $projet->id == $projetActuelId ? 'logo-actuel' : '' }}">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="project-name">
                                {{ $projet->nom }}
                                @if($projet->id == $projetActuelId)
                                    <span class="actuel-badge">Actuel</span>
                                @endif
                            </div>
                            @if($projet->description)
                                <div class="project-description">{{ Str::limit($projet->description, 80) }}</div>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($projet->devise)
                                <div class="devise-text">"{{ $projet->devise }}"</div>
                            @else
                                <span class="no-devise">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($projet->acronymes)
                                <span class="acronymes-badge">{{ $projet->acronymes }}</span>
                            @else
                                <span class="no-acronymes">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="contact-info">
                                @if($projet->email)
                                    <div class="contact-item">
                                        <i class="fas fa-envelope contact-icon"></i>
                                        <a href="mailto:{{ $projet->email }}" class="contact-link" title="Envoyer un email">
                                            {{ Str::limit($projet->email, 25) }}
                                        </a>
                                    </div>
                                @endif
                                @if($projet->telephone)
                                    <div class="contact-item">
                                        <i class="fas fa-phone contact-icon"></i>
                                        <a href="tel:{{ $projet->telephone }}" class="contact-link" title="Appeler">
                                            {{ $projet->telephone }}
                                        </a>
                                    </div>
                                @endif
                                @if($projet->adresse)
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt contact-icon"></i>
                                        <span class="contact-text" title="{{ $projet->adresse }}">
                                            {{ Str::limit($projet->adresse, 30) }}
                                        </span>
                                    </div>
                                @endif
                                @if(!$projet->email && !$projet->telephone && !$projet->adresse)
                                    <span class="no-contact">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="date-badge">
                                {{ $projet->debut ? $projet->debut->format('d/m/Y') : 'Non défini' }}
                            </span>
                        </td>
                        <td class="table-cell">
                            @if($projet->duree_annee)
                                <span class="duration-badge">
                                    {{ $projet->duree_annee }} an{{ $projet->duree_annee > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="no-duration">Non définie</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.projets.show', $projet->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.projets.edit', $projet->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($projet->id != $projetActuelId)
                                <button type="button" 
                                        onclick="confirmSetActuel({{ $projet->id }}, '{{ addslashes($projet->nom) }}')" 
                                        class="action-btn star-btn" 
                                        title="Définir comme projet actuel">
                                    <i class="fas fa-star"></i>
                                </button>
                                @else
                                <div class="action-btn current-star-btn" title="Projet actuel">
                                    <i class="fas fa-crown"></i>
                                </div>
                                @endif
                                @if($projet->id != $projetActuelId)
                                <button type="button" 
                                        onclick="confirmArchive({{ $projet->id }}, '{{ addslashes($projet->nom ?? 'Sans titre') }}', {{ $projet->archived ? 'true' : 'false' }})" 
                                        class="action-btn archive-btn"
                                        title="{{ $projet->archived ? 'Désarchiver' : 'Archiver' }}">
                                    <i class="fas fa-{{ $projet->archived ? 'folder-open' : 'archive' }}"></i>
                                </button>
                                @else
                                <button type="button" 
                                        class="action-btn archive-btn disabled"
                                        title="Impossible d'archiver le projet actuel"
                                        disabled>
                                    <i class="fas fa-archive"></i>
                                </button>
                                @endif
                                <button type="button" 
                                        onclick="confirmDelete({{ $projet->id }}, '{{ addslashes($projet->nom) }}')" 
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
                                <i class="fas fa-inbox"></i>
                                <p>Aucun projet trouvé</p>
                                <a href="{{ route('admin.projets.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer le premier projet
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination et sélection du nombre d'éléments -->
        <div class="pagination-container">
            <div class="pagination-controls">
                <!-- Sélection du nombre d'éléments par page -->
                <div class="per-page-selector">
                    <label for="per_page" class="per-page-label">Afficher :</label>
                    <select id="per_page" name="per_page" onchange="updatePerPage(this.value)" class="per-page-select">
                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="per-page-text">éléments par page</span>
                </div>

                <!-- Informations de pagination -->
                <div class="pagination-info">
                    Affichage de <strong>{{ $projets->firstItem() ?? 0 }}</strong> à <strong>{{ $projets->lastItem() ?? 0 }}</strong> sur <strong>{{ $projets->total() }}</strong> projets
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $projets->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="setActuelForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Formulaire d'archivage caché -->
<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(projetId, projetNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le projet "${projetNom}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/projets/${projetId}`;
        form.submit();
    }
}

function confirmSetActuel(projetId, projetNom) {
    if (confirm(`Êtes-vous sûr de vouloir définir "${projetNom}" comme projet actuel ?\n\nCe projet sera visible comme le projet en cours.`)) {
        const form = document.getElementById('setActuelForm');
        form.action = `/admin/projets/${projetId}/set-actuel`;
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

function confirmArchive(projetId, projetTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le projet "${projetTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/projets/${projetId}/toggle-archive`;
        form.submit();
    }
}
</script>

<style>
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.85rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.contact-icon {
    width: 12px;
    color: #6c757d;
    font-size: 0.75rem;
}

.contact-link {
    color: var(--color-primary);
    text-decoration: none;
    transition: color 0.2s;
}

.contact-link:hover {
    color: var(--color-primary-dark);
    text-decoration: underline;
}

.contact-text {
    color: #495057;
    word-break: break-word;
}

.no-contact {
    color: #6c757d;
    font-style: italic;
}

.action-btn.archive-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #ccc !important;
    color: #666 !important;
}

.action-btn.archive-btn.disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .contact-info {
        font-size: 0.8rem;
    }
    
    .contact-item {
        gap: 4px;
    }
}
</style>
@endsection