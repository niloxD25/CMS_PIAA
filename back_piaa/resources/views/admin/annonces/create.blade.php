@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Créer une Annonce')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h1 class="admin-title">Créer une Annonce</h1>
        <a href="{{ route('admin.annonces.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.annonces.store') }}" method="POST" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.annonces.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.annonces.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="save-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Créer l'annonce
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const titre = document.getElementById('titre').value;
    if (!titre.trim()) {
        alert('Veuillez saisir un titre pour l\'annonce.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir créer l'annonce "${titre}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection