@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Détails de l\'Association')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.organisations.associations.edit', $association->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.organisations.associations.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations principales -->
        <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Informations de l'association</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Organisation</label>
                    <div class="mt-1 flex items-center space-x-3">
                        @if($association->organisations->logo)
                            <img src="{{ asset($association->organisations->logo) }}" 
                                 alt="{{ $association->organisations->nom }}" 
                                 class="h-10 w-10 rounded-full object-cover">
                        @endif
                        <p class="text-sm text-gray-900">{{ $association->organisations->nom ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500">Projet</label>
                    <div class="mt-1 flex items-center space-x-3">
                        @if($association->projets->logo)
                            <img src="{{ asset($association->projets->logo) }}" 
                                 alt="{{ $association->projets->nom }}" 
                                 class="h-10 w-10 rounded-full object-cover">
                        @endif
                        <p class="text-sm text-gray-900">{{ $association->projets->nom ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Type de relation</label>
                    <p class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $association->types_relation->type ?? 'N/A' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Métadonnées et actions -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.organisations.show', $association->organisation_id) }}" 
                       class="create-btn w-full" style="background: var(--color-primary);">
                        <i class="fas fa-building mr-2"></i>Voir l'organisation
                    </a>
                    
                    <a href="{{ route('admin.projets.show', $association->projet_id) }}" 
                       class="create-btn w-full" style="background: var(--color-accent);">
                        <i class="fas fa-project-diagram mr-2"></i>Voir le projet
                    </a>
                    
                    <button type="button" 
                            onclick="confirmDelete({{ $association->id }})" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer l'association
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(associationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette association ?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/organisations/associations/${associationId}`;
        form.submit();
    }
}
</script>
@endsection