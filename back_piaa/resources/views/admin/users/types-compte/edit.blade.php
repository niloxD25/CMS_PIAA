@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier le Type de Compte - ' . $typeCompte->type)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.users.types-compte.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.users.types-compte.update', $typeCompte->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.users.types-compte.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.users.types-compte.index') }}" class="cancel-btn">
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
    const niveauAcces = document.getElementById('niveau_acces').value;
    
    if (!type.trim()) {
        alert('Veuillez saisir le type de compte.');
        return;
    }
    
    if (!niveauAcces) {
        alert('Veuillez sélectionner un niveau d\'accès.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour le type de compte "${type}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection