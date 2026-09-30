@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Créer un Type de Message')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.types-messages.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.types-messages.store') }}" method="POST" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.messages.types-form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.types-messages.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="save-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Créer le type
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const type = document.getElementById('type').value;
    if (!type.trim()) {
        alert('Veuillez saisir un nom pour le type de message.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir créer le type "${type}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection