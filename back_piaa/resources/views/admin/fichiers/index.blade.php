@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Fichiers')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.fichiers.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouveau Fichier
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
                        <label for="entite_type_id" class="form-label">Type d'entité</label>
                        <select id="entite_type_id" name="entite_type_id" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($entitesFichier as $entite)
                                <option value="{{ $entite->id }}" {{ request('entite_type_id') == $entite->id ? 'selected' : '' }}>
                                    {{ $entite->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type_fichier" class="form-label">Type de fichier</label>
                        <select id="type_fichier" name="type_fichier" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($typesFichier as $type)
                                <option value="{{ $type }}" {{ request('type_fichier') == $type ? 'selected' : '' }}>
                                    {{ strtoupper($type) }}
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
                    <a href="{{ route('admin.fichiers.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des fichiers -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Icône</th>
                        <th class="table-header sortable {{ $sortBy == 'titre' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'titre', 'sort_order' => $sortBy == 'titre' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Fichier
                                @if($sortBy == 'titre')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Description</th>
                        <th class="table-header sortable {{ $sortBy == 'type_fichier' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'type_fichier', 'sort_order' => $sortBy == 'type_fichier' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Type
                                @if($sortBy == 'type_fichier')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Entité</th>
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
                        <th class="table-header sortable {{ $sortBy == 'niveau_acces' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'niveau_acces', 'sort_order' => $sortBy == 'niveau_acces' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Accès
                                @if($sortBy == 'niveau_acces')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Statut</th>
                        <th class="table-header sortable {{ $sortBy == 'date_upload' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_upload', 'sort_order' => $sortBy == 'date_upload' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date
                                @if($sortBy == 'date_upload')
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
                    @forelse($fichiers as $fichier)
                    <tr class="table-row {{ $fichier->archived ? 'archived-row' : '' }}">
                        <td class="table-cell logo-cell">
                            @php
                                $fileIcon = App\Models\Fichiers::getFileIconStatic($fichier->type_fichier);
                                $fileIconClass = App\Models\Fichiers::getFileIconClassStatic($fichier->type_fichier);
                            @endphp
                            <div class="file-icon {{ $fileIconClass }}">
                                <i class="{{ $fileIcon }}"></i>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">{{ $fichier->titre ?? 'Sans titre' }}</div>
                            <div class="file-size text-xs text-gray-500">
                                @php
                                    $fileSize = App\Models\Fichiers::getFileSizeStatic($fichier->chemin);
                                @endphp
                                {{ $fileSize }}
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($fichier->description)
                                <div class="organisation-description">{{ Str::limit($fichier->description, 60) }}</div>
                            @else
                                <span class="no-description">Aucune description</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="file-type-badge">{{ strtoupper($fichier->type_fichier) }}</span>
                        </td>
                        <td class="table-cell">
                            @if($fichier->entites_fichier)
                                <span class="entity-badge">{{ $fichier->entites_fichier->nom }}</span>
                            @else
                                <span class="no-link">Aucune</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($fichier->projet_id)
                                <span class="project-badge">{{ $fichier->projets->nom ?? 'Projet inconnu' }}</span>
                            @else
                                <span class="no-link">Aucun</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                                $typeCompte = \App\Models\TypesCompte::where('niveau_acces', $fichier->niveau_acces)->first();
                            @endphp
                            <span class="access-badge" title="{{ $typeCompte ? $typeCompte->type : 'Niveau ' . $fichier->niveau_acces }}">
                                {{ $typeCompte->type }}
                            </span>
                        </td>
                        <td class="table-cell">
                            @if($fichier->archived)
                                <span class="status-badge archived">Archivé</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $fichier->date_upload ? $fichier->date_upload->format('d/m/Y') : $fichier->created_at->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.fichiers.download', $fichier->id) }}" 
                                   class="action-btn download-btn" 
                                   title="Télécharger"
                                   target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="{{ route('admin.fichiers.show', $fichier->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.fichiers.edit', $fichier->id) }}" 
                                   class="action-btn edit-btn" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmArchive({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}', {{ $fichier->archived ? 'true' : 'false' }})" 
                                        class="action-btn archive-btn"
                                        title="{{ $fichier->archived ? 'Désarchiver' : 'Archiver' }}">
                                    <i class="fas fa-{{ $fichier->archived ? 'folder-open' : 'archive' }}"></i>
                                </button>
                                <button type="button" 
                                        onclick="confirmDelete({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}')" 
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
                                <i class="fas fa-file"></i>
                                <p>Aucun fichier trouvé</p>
                                <a href="{{ route('admin.fichiers.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Ajouter le premier fichier
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
                    Affichage de <strong>{{ $fichiers->firstItem() ?? 0 }}</strong> à <strong>{{ $fichiers->lastItem() ?? 0 }}</strong> sur <strong>{{ $fichiers->total() }}</strong> fichiers
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $fichiers->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
function confirmDelete(fichierId, fichierTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le fichier "${fichierTitre}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/fichiers/${fichierId}`;
        form.submit();
    }
}

function confirmArchive(fichierId, fichierTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le fichier "${fichierTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/fichiers/${fichierId}/toggle-archive`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('entite_type_id', '{{ request('entite_type_id') }}');
    url.searchParams.set('projet_id', '{{ request('projet_id') }}');
    url.searchParams.set('type_fichier', '{{ request('type_fichier') }}');
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

.access-badge {
    font-size: 0.75rem;
    font-weight: 500;
    color: #92400e;
}

.file-type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f3e8ff;
    color: #6b21a8;
}

.file-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.file-icon.pdf { background: #fee2e2; color: #dc2626; }
.file-icon.doc { background: #dbeafe; color: #1d4ed8; }
.file-icon.xls { background: #dcfce7; color: #16a34a; }
.file-icon.img { background: #fef3c7; color: #d97706; }
.file-icon.zip { background: #f3e8ff; color: #7c3aed; }
.file-icon.audio { background: #f0f9ff; color: #0369a1; }
.file-icon.video { background: #fef7cd; color: #ca8a04; }
.file-icon.csv { background: #dcfce7; color: #16a34a; }
.file-icon.default { background: #f3f4f6; color: #6b7280; }

.file-size {
    margin-top: 0.25rem;
}

</style>
@endsection