@extends('admin.layouts.base')

@section('base_content')

@section('title', $organisation->nom)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $organisation->nom }}</h1>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.organisations.edit', $organisation->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.organisations.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations de l'organisation</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nom</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $organisation->nom }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $organisation->description ?? 'Aucune description' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Site web</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($organisation->lien)
                                <a href="{{ $organisation->lien }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $organisation->lien }}
                                    <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                            @else
                                Aucun site web
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Associations avec les projets -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Projets associés</h2>
                </div>
                <div class="p-6">
                    @if($associations->count() > 0)
                        <div class="space-y-4">
                            @foreach($associations as $association)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                                <div class="flex items-center space-x-4">
                                    @if($association->projets->logo)
                                        <img src="{{ asset($association->projets->logo) }}" 
                                             alt="{{ $association->projets->nom }}" 
                                             class="h-12 w-12 rounded-full object-cover">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-project-diagram text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $association->projets->nom ?? 'Projet inconnu' }}</h3>
                                        <p class="text-sm text-gray-500">{{ $association->types_relation->type ?? 'Type inconnu' }}</p>
                                    </div>
                                </div>
                                <div class="actions-container">
                                    <a href="{{ route('admin.projets.show', $association->projet_id) }}" 
                                       class="action-btn view-btn" title="Voir le projet">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.organisations.associations.edit', $association->id) }}" 
                                       class="action-btn edit-btn" title="Modifier l'association">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            onclick="confirmAssociationDelete({{ $association->id }})" 
                                            class="action-btn delete-btn"
                                            title="Supprimer l'association">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-link text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500 mb-4">Aucun projet associé</p>
                            <a href="{{ route('admin.organisations.associations.create') }}?organisation_id={{ $organisation->id }}" 
                               class="create-btn" style="background: var(--color-primary);">
                                <i class="fas fa-plus mr-2"></i>Associer un projet
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Logo -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Logo</h2>
                </div>
                <div class="p-6 flex justify-center">
                    @if($organisation->logo)
                        <img src="{{ asset($organisation->logo) }}" alt="{{ $organisation->nom }}" class="h-32 object-cover">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center" style="border-radius: 50%;">
                            <i class="fas fa-building text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.organisations.associations.create') }}?organisation_id={{ $organisation->id }}" 
                       class="create-btn w-full" style="background: var(--color-accent);">
                        <i class="fas fa-link mr-2"></i>Associer à un projet
                    </a>
                    
                    <button type="button" 
                            onclick="confirmDelete({{ $organisation->id }}, '{{ addslashes($organisation->nom) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer l'organisation
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

<form id="deleteAssociationForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(organisationId, organisationNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'organisation "${organisationNom}" ?\n\nCette action supprimera également toutes ses associations avec des projets.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/organisations/${organisationId}`;
        form.submit();
    }
}

function confirmAssociationDelete(associationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette association ?')) {
        const form = document.getElementById('deleteAssociationForm');
        form.action = `/admin/organisations/associations/${associationId}`;
        form.submit();
    }
}
</script>
@endsection