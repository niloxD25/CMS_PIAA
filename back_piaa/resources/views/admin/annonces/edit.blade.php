@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier l\'Annonce')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h1 class="admin-title">Modifier "{{ $annonce->titre }}"</h1>
        <a href="{{ route('admin.annonces.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.annonces.update', $annonce->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.annonces.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.annonces.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmUpdate()" class="save-btn">
                    <i class="fas fa-save mr-2"></i>Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmUpdate() {
    const titre = document.getElementById('titre').value;
    if (!titre.trim()) {
        alert('Veuillez saisir un titre pour l\'annonce.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour l'annonce "${titre}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection