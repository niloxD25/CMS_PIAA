@extends('admin.layouts.base')

@section('base_content')

@section('title', $fichier->titre ?? 'Fichier sans titre')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $fichier->titre ?? 'Fichier sans titre' }}</h1>
            @if($fichier->archived)
                <span class="status-badge archived">Archivé</span>
            @else
                <span class="status-badge active">Actif</span>
            @endif
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.fichiers.edit', $fichier->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.fichiers.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations du fichier</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nom du fichier</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $fichier->titre ?? basename($fichier->chemin) }}</p>
                    </div>
                    
                    @if($fichier->description)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $fichier->description }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Type de fichier</label>
                            <p class="mt-1 text-sm text-gray-900">{{ strtoupper($fichier->type_fichier) }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Taille</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @php
                                    $fileSize = App\Models\Fichiers::getFileSizeStatic($fichier->chemin);
                                @endphp
                                {{ $fileSize }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Type d'entité</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fichier->entites_fichier->nom ?? 'Non spécifié' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">ID d'entité</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fichier->entite_id ?? 'Non spécifié' }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Projet associé</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $fichier->projets->nom ?? 'Aucun projet' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Niveau d'accès</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @php
                                $typeCompte = \App\Models\TypesCompte::where('niveau_acces', $fichier->niveau_acces)->first();
                            @endphp
                            {{ $typeCompte ? $typeCompte->type . ' (Niveau ' . $fichier->niveau_acces . ')' : 'Niveau ' . $fichier->niveau_acces }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Chemin du fichier</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $fichier->chemin }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Aperçu du fichier -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Fichier</h2>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        @php
                            $fileIcon = App\Models\Fichiers::getFileIconStatic($fichier->type_fichier);
                            $fileIconClass = App\Models\Fichiers::getFileIconClassStatic($fichier->type_fichier);
                        @endphp

                        <div class="file-icon-large {{ $fileIconClass }} mx-auto mb-4">
                            <i class="{{ $fileIcon }}"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $fichier->titre ?? basename($fichier->chemin) }}</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ strtoupper($fichier->type_fichier) }} • {{ $fileSize }}
                        </p>
                        <a href="{{ route('admin.fichiers.download', $fichier->id) }}" 
                           class="download-btn-large" 
                           target="_blank">
                            <i class="fas fa-download mr-2"></i>Télécharger
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if($fichier->archived)
                    <button type="button" 
                            onclick="confirmArchive({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}', true)" 
                            class="create-btn w-full" 
                            style="background: var(--color-success);">
                        <i class="fas fa-folder-open mr-2"></i>Désarchiver
                    </button>
                    @else
                    <button type="button" 
                            onclick="confirmArchive({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}', false)" 
                            class="create-btn w-full" 
                            style="background: var(--color-warning);">
                        <i class="fas fa-archive mr-2"></i>Archiver
                    </button>
                    @endif
                    
                    <button type="button" 
                            onclick="confirmDelete({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer le fichier
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

<script>
function confirmDelete(fichierId, fichierTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le fichier "${fichierTitre}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/fichiers/${fichierId}`;
        form.submit();
    }
}

function confirmArchive(fichierId, fichierTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le fichier "${fichierTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/fichiers/${fichierId}/toggle-archive`;
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

.file-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.file-icon-large.pdf { background: #fee2e2; color: #dc2626; }
.file-icon-large.doc { background: #dbeafe; color: #1d4ed8; }
.file-icon-large.docx { background: #dbeafe; color: #1d4ed8; }
.file-icon-large.xls { background: #dcfce7; color: #16a34a; }
.file-icon-large.xlsx { background: #dcfce7; color: #16a34a; }
.file-icon-large.jpg { background: #fef3c7; color: #d97706; }
.file-icon-large.jpeg { background: #fef3c7; color: #d97706; }
.file-icon-large.png { background: #fef3c7; color: #d97706; }
.file-icon-large.gif { background: #fef3c7; color: #d97706; }
.file-icon-large.zip { background: #f3e8ff; color: #7c3aed; }
.file-icon-large.rar { background: #f3e8ff; color: #7c3aed; }
.file-icon-large.txt { background: #f3f4f6; color: #6b7280; }
.file-icon-large.default { background: #f3f4f6; color: #6b7280; }

.download-btn-large {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background: var(--color-success);
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.download-btn-large:hover {
    background: #059669;
}
</style>
@endsection