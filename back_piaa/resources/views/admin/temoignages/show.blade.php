@extends('admin.layouts.base')

@section('base_content')

@section('title', $temoignage->temoin)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $temoignage->temoin }}</h1>
            <div class="flex space-x-2">
                @if($temoignage->archived)
                    <span class="status-badge archived">Archivé</span>
                @else
                    <span class="status-badge active">Actif</span>
                @endif
                @if($temoignage->est_important)
                    <span class="status-badge important">Important</span>
                @endif
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.temoignages.edit', $temoignage->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.temoignages.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations du témoignage</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Témoin</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $temoignage->temoin }}</p>
                    </div>
                    
                    @if($temoignage->residence)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Résidence</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $temoignage->residence }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-gray-500">Témoignage</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $temoignage->contenu }}</p>
                    </div>

                    @if($temoignage->projet_id)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Projet associé</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $temoignage->projets->nom ?? 'Projet inconnu' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Photo et actions -->
        <div class="space-y-6">
            <!-- Photo -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Photo</h2>
                </div>
                <div class="p-6 flex justify-center">
                    @if($temoignage->image)
                        <img src="{{ asset($temoignage->image) }}" alt="{{ $temoignage->temoin }}" class="h-32 w-32 rounded-full object-cover" style="border-radius: 50%;">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center" style="border-radius: 50%;">
                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statut -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Statut</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Archivé</span>
                            <div class="flex items-center">
                                @if($temoignage->archived)
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    <span class="ml-2 text-sm text-gray-600">Oui</span>
                                @else
                                    <i class="fas fa-times-circle text-gray-400"></i>
                                    <span class="ml-2 text-sm text-gray-600">Non</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Important</span>
                            <div class="flex items-center">
                                @if($temoignage->est_important)
                                    <i class="fas fa-check-circle text-blue-500"></i>
                                    <span class="ml-2 text-sm text-gray-600">Oui</span>
                                @else
                                    <i class="fas fa-times-circle text-gray-400"></i>
                                    <span class="ml-2 text-sm text-gray-600">Non</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <!-- Bouton pour basculer l'importance -->
                    @if($temoignage->est_important)
                    <button type="button" 
                            onclick="confirmToggleImportant({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', true)" 
                            class="create-btn w-full" 
                            style="background: #6b7280;">
                        <i class="fas fa-star mr-2"></i>Retirer des importants
                    </button>
                    @else
                    <button type="button" 
                            onclick="confirmToggleImportant({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', false)" 
                            class="create-btn w-full" 
                            style="background: var(--color-primary);">
                        <i class="fas fa-star mr-2"></i>Marquer comme important
                    </button>
                    @endif

                    <!-- Bouton pour archiver/désarchiver -->
                    @if($temoignage->archived)
                    <button type="button" 
                            onclick="confirmArchive({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', true)" 
                            class="create-btn w-full" 
                            style="background: var(--color-success);">
                        <i class="fas fa-folder-open mr-2"></i>Désarchiver
                    </button>
                    @else
                    <button type="button" 
                            onclick="confirmArchive({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}', false)" 
                            class="create-btn w-full" 
                            style="background: var(--color-warning);">
                        <i class="fas fa-archive mr-2"></i>Archiver
                    </button>
                    @endif
                    
                    <button type="button" 
                            onclick="confirmDelete({{ $temoignage->id }}, '{{ addslashes($temoignage->temoin) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer le témoignage
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

<!-- Formulaire d'archivage caché -->
<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Formulaire de basculement important caché -->
<form id="importantForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(temoignageId, temoinNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le témoignage de "${temoinNom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/temoignages/${temoignageId}`;
        form.submit();
    }
}

function confirmArchive(temoignageId, temoinNom, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le témoignage de "${temoinNom}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/temoignages/${temoignageId}/toggle-archive`;
        form.submit();
    }
}

function confirmToggleImportant(temoignageId, temoinNom, isImportant) {
    const action = isImportant ? 'retirer des importants' : 'marquer comme important';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le témoignage de "${temoinNom}" ?`)) {
        const form = document.getElementById('importantForm');
        form.action = `/admin/temoignages/${temoignageId}/toggle-important`;
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

.status-badge.important {
    background: var(--color-primary);
}
</style>
@endsection