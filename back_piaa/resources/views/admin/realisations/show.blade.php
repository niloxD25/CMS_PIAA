@extends('admin.layouts.base')

@section('base_content')

@section('title', "Détails de la réalisation")

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $realisation->titre ?? 'Sans titre' }}</h1>
            @if($realisation->archived)
                <span class="status-badge archived">Archivée</span>
            @else
                <span class="status-badge active">Active</span>
            @endif
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.realisations.edit', $realisation->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.realisations.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Informations de base -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Niveau d'accès</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @php
                                $typeCompte = \App\Models\TypesCompte::where('niveau_acces', $realisation->niveau_acces)->first();
                            @endphp
                            {{ $typeCompte ? $typeCompte->type . ' (Niveau ' . $realisation->niveau_acces . ')' : 'Niveau ' . $realisation->niveau_acces }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date de réalisation</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $realisation->date_realisation ? $realisation->date_realisation->format('d/m/Y') : 'Non définie' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Projet associé</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $realisation->projets->nom ?? 'Aucun projet' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Détails de la réalisation</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Description -->
                    @if($realisation->description)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $realisation->description }}</p>
                    </div>
                    @endif

                    <!-- Bénéficiaires -->
                    @if($realisation->beneficiaires)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Bénéficiaires</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $realisation->beneficiaires }}</p>
                    </div>
                    @endif

                    <!-- Impacts potentiels -->
                    @if($realisation->impacts_potentiel)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Impacts potentiels</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $realisation->impacts_potentiel }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Image principale -->
            @if($realisation->image)
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Image de la réalisation</h2>
                </div>
                <div class="p-6">
                    <img src="{{ asset($realisation->image) }}" alt="{{ $realisation->titre }}" class="w-full h-72 object-cover rounded-lg" style="border-radius: var(--border-radius);">
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire d'archivage caché -->
<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(realisationId, realisationTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la réalisation "${realisationTitre}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/realisations/${realisationId}`;
        form.submit();
    }
}

function confirmArchive(realisationId, realisationTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} la réalisation "${realisationTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/realisations/${realisationId}/toggle-archive`;
        form.submit();
    }
}
</script>

<style>
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
}

.status-badge.active {
    background: var(--color-success);
}

.status-badge.archived {
    background: var(--color-warning);
}
</style>
@endsection