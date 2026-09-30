@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Créer une Personne')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.personnes.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.personnes.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.personnes.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.personnes.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="create-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Créer la personne
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const nom = document.getElementById('nom').value;
    const prenom = document.getElementById('prenom').value;
    
    if (!nom.trim()) {
        alert('Veuillez saisir le nom de la personne.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir créer la personne "${prenom} ${nom}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection