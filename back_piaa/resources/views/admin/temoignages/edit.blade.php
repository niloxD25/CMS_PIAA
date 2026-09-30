@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier le Témoignage - ' . $temoignage->temoin)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.temoignages.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.temoignages.update', $temoignage->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.temoignages.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.temoignages.index') }}" class="cancel-btn">
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
    const temoin = document.getElementById('temoin').value;
    const contenu = document.getElementById('contenu').value;
    
    if (!temoin.trim()) {
        alert('Veuillez saisir le nom du témoin.');
        return;
    }
    
    if (!contenu.trim()) {
        alert('Veuillez saisir le contenu du témoignage.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir mettre à jour le témoignage de "${temoin}" ?`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection