@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Modifier l\'Association')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.organisations.associations.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.organisations.associations.update', $association->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="p-6">
                @include('admin.organisations.associations.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.organisations.associations.index') }}" class="cancel-btn">
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
    const organisation = document.getElementById('organisation_id').value;
    const projet = document.getElementById('projet_id').value;
    const typeRelation = document.getElementById('type_relation_id').value;

    if (!organisation) {
        alert('Veuillez sélectionner une organisation.');
        return;
    }
    
    if (!projet) {
        alert('Veuillez sélectionner un projet.');
        return;
    }
    
    if (!typeRelation) {
        alert('Veuillez sélectionner un type de relation.');
        return;
    }

    const organisationText = document.getElementById('organisation_id').options[document.getElementById('organisation_id').selectedIndex].text;
    const projetText = document.getElementById('projet_id').options[document.getElementById('projet_id').selectedIndex].text;
    const typeText = document.getElementById('type_relation_id').options[document.getElementById('type_relation_id').selectedIndex].text;

    if (confirm(`Êtes-vous sûr de vouloir mettre à jour cette association ?\n\nOrganisation : ${organisationText}\nProjet : ${projetText}\nType de relation : ${typeText}`)) {
        document.getElementById('editForm').submit();
    }
}
</script>
@endsection