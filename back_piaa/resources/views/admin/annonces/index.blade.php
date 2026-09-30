@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Annonces')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.annonces.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouvelle Annonce
        </a>
    </div>

    <!-- Tableau des annonces -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="annonces-table">
                <thead>
                    <tr>
                        <th class="table-header">Statut</th>
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
                        <th class="table-header sortable {{ $sortBy == 'date_affichage' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_affichage', 'sort_order' => $sortBy == 'date_affichage' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date d'affichage
                                @if($sortBy == 'date_affichage')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'nbr_jours_affichage' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nbr_jours_affichage', 'sort_order' => $sortBy == 'nbr_jours_affichage' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Durée affichage
                                @if($sortBy == 'nbr_jours_affichage')
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
                    @forelse($annonces as $annonce)
                    @php
                        $now = \Carbon\Carbon::now();
                        $dateFin = $annonce->date_affichage ? $annonce->date_affichage->copy()->addDays($annonce->nbr_jours_affichage ?? 0) : null;
                        $isActive = $annonce->date_affichage && $annonce->date_affichage <= $now && $dateFin && $dateFin >= $now;
                        $isFuture = $annonce->date_affichage && $annonce->date_affichage > $now;
                        $isExpired = $annonce->date_affichage && $dateFin && $dateFin < $now;
                    @endphp
                    <tr class="table-row">
                        <td class="table-cell status-cell">
                            @if($isActive)
                                <span class="status-badge status-active" title="Annonce active">
                                    <i class="fas fa-eye mr-1"></i>Active
                                </span>
                            @elseif($isFuture)
                                <span class="status-badge status-future" title="Annonce programmée">
                                    <i class="fas fa-clock mr-1"></i>Programmée
                                </span>
                            @elseif($isExpired)
                                <span class="status-badge status-expired" title="Annonce expirée">
                                    <i class="fas fa-ban mr-1"></i>Expirée
                                </span>
                            @else
                                <span class="status-badge status-inactive" title="Annonce inactive">
                                    <i class="fas fa-pause mr-1"></i>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="annonce-title">{{ $annonce->titre }}</div>
                            @if($annonce->contenu)
                                <div class="annonce-contenu">{{ Str::limit(strip_tags($annonce->contenu), 100) }}</div>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($annonce->date_affichage)
                                <span class="date-badge">
                                    {{ $annonce->date_affichage->format('d/m/Y') }}
                                </span>
                                <div class="date-info">
                                    @if($isActive && $dateFin)
                                        <small>Jusqu'au {{ $dateFin->format('d/m/Y') }}</small>
                                    @endif
                                </div>
                            @else
                                <span class="no-date">Non définie</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($annonce->nbr_jours_affichage)
                                <span class="duration-badge">
                                    {{ $annonce->nbr_jours_affichage }} jour{{ $annonce->nbr_jours_affichage > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="no-duration">Illimitée</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.annonces.show', $annonce->id) }}" class="action-btn view-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.annonces.edit', $annonce->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $annonce->id }}, '{{ addslashes($annonce->titre) }}')" 
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
                                <i class="fas fa-bullhorn"></i>
                                <p>Aucune annonce trouvée</p>
                                <a href="{{ route('admin.annonces.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer la première annonce
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
                    Affichage de <strong>{{ $annonces->firstItem() ?? 0 }}</strong> à <strong>{{ $annonces->lastItem() ?? 0 }}</strong> sur <strong>{{ $annonces->total() }}</strong> annonces
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $annonces->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
function confirmDelete(annonceId, annonceTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'annonce "${annonceTitre}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/annonces/${annonceId}`;
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

<style>
/* Cellules spécifiques */
.status-cell {
    width: 120px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
}

.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.status-future {
    background: rgba(59, 130, 246, 0.1);
    color: var(--bleu-ge);
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-expired {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.status-inactive {
    background: rgba(107, 114, 128, 0.1);
    color: rgb(107, 114, 128);
}

.annonce-title {
    font-weight: 600;
    color: var(--brun-fonce);
    margin-bottom: 0.25rem;
}

.annonce-contenu {
    font-size: 0.8rem;
    color: var(--brun-clair);
    line-height: 1.4;
}

.date-badge {
    background: rgba(59, 130, 246, 0.1);
    color: rgb(59, 130, 246);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.date-info {
    margin-top: 0.25rem;
}

.date-info small {
    font-size: 0.7rem;
    color: var(--brun-clair);
}

.no-date {
    color: var(--brun-clair);
    font-style: italic;
    font-size: 0.8rem;
}

.duration-badge {
    background: rgba(168, 85, 247, 0.1);
    color: rgb(168, 85, 247);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.no-duration {
    color: var(--brun-clair);
    font-style: italic;
    font-size: 0.8rem;
}
</style>
@endsection