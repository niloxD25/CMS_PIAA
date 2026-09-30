@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Types de Messages')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h1 class="admin-title">Types de Messages</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.types-messages.create') }}" class="add-btn">
                <i class="fas fa-plus mr-2"></i>Nouveau Type
            </a>
            <a href="{{ route('admin.messages.index') }}" class="cancel-btn">
                <i class="fas fa-envelope mr-2"></i>Messages
            </a>
        </div>
    </div>

    <!-- Tableau des types -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="types-table">
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
                            <div class="type-name">{{ $type->type }}</div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.types-messages.edit', $type->id) }}" class="action-btn edit-btn" title="Modifier">
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
                        <td colspan="2" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <p>Aucun type de message trouvé</p>
                                <a href="{{ route('admin.types-messages.create') }}" class="add-btn empty-btn">
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
function confirmDelete(typeId, typeNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type "${typeNom}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/types-messages/${typeId}`;
        form.submit();
    }
}
</script>
@endsection