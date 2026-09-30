@extends('admin.layouts.base')

@section('base_content')
@section('title', 'Modifier ' . $reseauSocial->nom)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.reseaux-sociaux.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.reseaux-sociaux.update', $reseauSocial->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Modifier le réseau social</h2>
            </div>
            <div class="p-6">
                @include('admin.reseaux-sociaux.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.reseaux-sociaux.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmUpdate()" class="save-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmUpdate() {
    const nom = document.getElementById('nom').value;
    const icone = document.getElementById('icone').value;
    
    if (!nom.trim()) {
        alert('Veuillez saisir un nom pour le réseau social.');
        return;
    }
    
    if (!icone.trim()) {
        alert('Veuillez saisir une icône pour le réseau social.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour le réseau social "${nom}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection