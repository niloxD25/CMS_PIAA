@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier la Réalisation - ' . $realisation->titre)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.realisations.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.realisations.update', $realisation->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.realisations.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.realisations.index') }}" class="cancel-btn">
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
    const titre = document.getElementById('titre').value;
    const niveauAcces = document.getElementById('niveau_acces').value;
    
    if (!titre.trim()) {
        alert('Veuillez saisir le titre de la réalisation.');
        return;
    }
    
    if (!niveauAcces) {
        alert('Veuillez sélectionner un niveau d\'accès.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour la réalisation "${titre}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection