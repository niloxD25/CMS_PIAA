@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Ajouter un Fichier')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.fichiers.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.fichiers.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.fichiers.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.fichiers.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="create-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Ajouter le fichier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const fichier = document.getElementById('fichier').files[0];
    const entiteType = document.getElementById('entite_type_id').value;
    const niveauAcces = document.getElementById('niveau_acces').value;
    
    if (!fichier) {
        alert('Veuillez sélectionner un fichier.');
        return;
    }
    
    if (!entiteType) {
        alert('Veuillez sélectionner un type d\'entité.');
        return;
    }
    
    if (!niveauAcces) {
        alert('Veuillez sélectionner un niveau d\'accès.');
        return;
    }
    
    const fileName = fichier.name;
    if (confirm(`Êtes-vous sûr de vouloir ajouter le fichier "${fileName}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection