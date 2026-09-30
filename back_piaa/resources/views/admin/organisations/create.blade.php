@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Créer une Organisation')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.organisations.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.organisations.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.organisations.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.organisations.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="create-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Créer l'organisation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const nom = document.getElementById('nom').value;
    if (!nom.trim()) {
        alert('Veuillez saisir un nom pour l\'organisation.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir créer l'organisation "${nom}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection