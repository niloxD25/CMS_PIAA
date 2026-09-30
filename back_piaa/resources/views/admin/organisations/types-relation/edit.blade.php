@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier le Type de Relation - ' . $type->type)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.organisations.types-relation.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.organisations.types-relation.update', $type->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.organisations.types-relation.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.organisations.types-relation.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmUpdate()" class="save-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmUpdate() {
    const type = document.getElementById('type').value;
    if (!type.trim()) {
        alert('Veuillez saisir un type de relation.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour le type de relation en "${type}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection