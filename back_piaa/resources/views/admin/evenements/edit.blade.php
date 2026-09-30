@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier l\'Événement - ' . $evenement->titre)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.evenements.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.evenements.update', $evenement->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.evenements.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.evenements.index') }}" class="cancel-btn">
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
    const dateDebut = document.getElementById('date_debut').value;
    const projet = document.getElementById('projet_id').value;
    
    if (!titre.trim()) {
        alert('Veuillez saisir le titre de l\'événement.');
        return;
    }
    
    if (!dateDebut) {
        alert('Veuillez sélectionner une date de début.');
        return;
    }
    
    if (!projet) {
        alert('Veuillez sélectionner un projet.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour l'événement "${titre}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection