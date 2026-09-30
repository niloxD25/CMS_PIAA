@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Types de Relation')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.organisations.index') }}" class="add-btn" style="background: var(--color-primary);">
                <i class="fas fa-building mr-2"></i>Organisations
            </a>
            <a href="{{ route('admin.organisations.associations.index') }}" class="add-btn" style="background: var(--color-accent);">
                <i class="fas fa-link mr-2"></i>Associations
            </a>
            <a href="{{ route('admin.organisations.types-relation.create') }}" class="add-btn">
                <i class="fas fa-plus mr-2"></i>Nouveau Type
            </a>
        </div>
    </div>

    <!-- Tableau des types de relation -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="organisations-table">
                <thead>
                    <tr>
                        <th class="table-header">Type</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr class="table-row">
                        <td class="table-cell">
                            <span class="type-badge">
                                {{ $type->type }}
                            </span>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.organisations.types-relation.edit', $type->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $type->id }}, '{{ addslashes($type->type) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="3" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <p>Aucun type de relation trouvé</p>
                                <a href="{{ route('admin.organisations.types-relation.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer le premier type
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(typeId, typeName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type "${typeName}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/organisations/types-relation/${typeId}`;
        form.submit();
    }
}
</script>
@endsection