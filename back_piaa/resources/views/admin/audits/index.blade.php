@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Historique des Activités')

<div class="container-fluid">
    <!-- Filtres -->
    <div class="bg-white rounded-lg overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="event" class="form-label">Événement</label>
                        <select id="event" name="event" class="form-input">
                            <option value="">Tous les événements</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst($event) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="auditable_type" class="form-label">Type</label>
                        <select id="auditable_type" name="auditable_type" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($auditableTypes as $type)
                                <option value="{{ $type }}" {{ request('auditable_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="create-btn">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.audits.index') }}" class="cancel-btn">
                        <i class="fas fa-times mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des audits -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Action</th>
                        <th class="table-header">Type</th>
                        <th class="table-header sortable {{ $sortBy == 'user' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'user', 'sort_order' => $sortBy == 'user' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Utilisateur
                                @if($sortBy == 'user')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Anciennes valeurs</th>
                        <th class="table-header">Nouvelles valeurs</th>
                        <th class="table-header sortable {{ $sortBy == 'created_at' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => $sortBy == 'created_at' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date
                                @if($sortBy == 'created_at')
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
                    @forelse($audits as $audit)
                    <tr class="table-row">
                        <td class="table-cell">
                            <span class="audit-badge audit-{{ $audit->event }}">
                                <i class="fas fa-{{ $audit->event === 'created' ? 'plus' : ($audit->event === 'updated' ? 'edit' : ($audit->event === 'deleted' ? 'trash' : ($audit->event === 'restored' ? 'undo' : 'info'))) }} mr-1"></i>
                                {{ ucfirst($audit->event) }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="organisation-name">
                                {{ class_basename($audit->auditable_type) }}
                            </div>
                            <div class="text-xs text-gray-500">
                                ID: {{ $audit->auditable_id }}
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($audit->user)
                                <div class="flex items-center space-x-2">
                                    <span>{{ $audit->user->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">Non spécifié</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($audit->old_values && count($audit->old_values) > 0)
                                <span class="text-xs text-gray-600">
                                    {{ count($audit->old_values) }} champ(s) modifié(s)
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($audit->new_values && count($audit->new_values) > 0)
                                <span class="text-xs text-gray-600">
                                    {{ count($audit->new_values) }} champ(s) modifié(s)
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="organisation-description">
                                {{ $audit->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.audits.show', $audit->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="7" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <p>Aucune activité trouvée</p>
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
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="per-page-text">éléments par page</span>
                </div>

                <!-- Informations de pagination -->
                <div class="pagination-info">
                    Affichage de <strong>{{ $audits->firstItem() ?? 0 }}</strong> à <strong>{{ $audits->lastItem() ?? 0 }}</strong> sur <strong>{{ $audits->total() }}</strong> activités
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $audits->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('sort_by', '{{ $sortBy }}');
    url.searchParams.set('sort_order', '{{ $sortOrder }}');
    url.searchParams.set('search', '{{ request('search') }}');
    url.searchParams.set('event', '{{ request('event') }}');
    url.searchParams.set('auditable_type', '{{ request('auditable_type') }}');
    url.searchParams.set('user_id', '{{ request('user_id') }}');
    window.location.href = url.toString();
}
</script>

<style>
.audit-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
    display: inline-flex;
    align-items: center;
}

.audit-created { background: var(--color-success); }
.audit-updated { background: var(--color-primary); }
.audit-deleted { background: var(--color-error); }
.audit-restored { background: var(--color-warning); }
</style>
@endsection