@extends('admin.layouts.base')

@section('base_content')

@section('title', $evenement->titre)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.evenements.edit', $evenement->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.evenements.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Historique des états -->
            @if($evenement->changement_etat_evenements->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Historique des états</h2>
                </div>
                <div class="p-4 max-h-80 overflow-y-auto">
                    <div class="space-y-3">
                        @foreach($evenement->changement_etat_evenements->sortByDesc('date') as $changement)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $changement->etats_evenement->etat ?? 'État inconnu' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Le {{ $changement->date->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Sections de l'événement -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Sections</h2>
                    <button type="button" 
                            onclick="openCreateSectionModal()" 
                            class="add-btn">
                        <i class="fas fa-plus mr-2"></i>Ajouter
                    </button>
                </div>
                <div class="p-4 max-h-80 overflow-y-auto">
                    @if($evenement->sections->count() > 0)
                        <div class="space-y-3">
                            @foreach($evenement->sections as $section)
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors" style="border-radius: var(--border-radius);">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $section->titre }}</h3>
                                        @if($section->description)
                                            <p class="text-xs text-gray-600 mt-1">{{ Str::limit($section->description, 80) }}</p>
                                        @endif
                                    </div>
                                    <div class="actions-container">
                                        <button type="button" 
                                                onclick="editSection({{ $section->id }})" 
                                                class="action-btn edit-btn"
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmDeleteSection({{ $section->id }}, '{{ addslashes($section->titre) }}')" 
                                                class="action-btn delete-btn"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @if($section->image)
                                <div class="mt-2">
                                    <img src="{{ asset($section->image) }}" alt="{{ $section->titre }}" class="h-32 w-full object-cover rounded" style="border-radius: var(--border-radius);">
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-layer-group text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">Aucune section</p>
                            <button type="button" 
                                    onclick="openCreateSectionModal()" 
                                    class="add-btn empty-btn mt-2">
                                <i class="fas fa-plus mr-2"></i>Créer une section
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fichiers de l'événement -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Fichiers</h2>
                    <button type="button" 
                            onclick="openCreateFichierModal()" 
                            class="add-btn">
                        <i class="fas fa-plus mr-2"></i>Ajouter
                    </button>
                </div>
                <div class="p-4 max-h-80 overflow-y-auto">
                    @if($evenement->fichiers->count() > 0)
                        <div class="space-y-3">
                            @foreach($evenement->fichiers as $fichier)
                            <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors {{ $fichier->archived ? 'archived-row' : '' }}" style="border-radius: var(--border-radius);">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start space-x-3 flex-1">
                                        @php
                                            $fileIcon = App\Models\Fichiers::getFileIconStatic($fichier->type_fichier);
                                            $fileIconClass = App\Models\Fichiers::getFileIconClassStatic($fichier->type_fichier);
                                        @endphp
                                        <div class="file-icon {{ $fileIconClass }}">
                                            <i class="{{ $fileIcon }}"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">{{ $fichier->titre ?? 'Sans titre' }}</h3>
                                            @if($fichier->description)
                                                <p class="text-xs text-gray-600 mt-1">{{ Str::limit($fichier->description, 60) }}</p>
                                            @endif
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="text-xs text-gray-500">
                                                    @php
                                                        $fileSize = App\Models\Fichiers::getFileSizeStatic($fichier->chemin);
                                                    @endphp
                                                    {{ $fileSize }}
                                                </span>
                                                <span class="file-type-badge">{{ strtoupper($fichier->type_fichier) }}</span>
                                                @if($fichier->archived)
                                                    <span class="status-badge archived text-xs">Archivé</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="actions-container">
                                        <a href="{{ route('admin.fichiers.download', $fichier->id) }}" 
                                           class="action-btn download-btn" 
                                           title="Télécharger"
                                           target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" 
                                                onclick="editFichier({{ $fichier->id }})" 
                                                class="action-btn edit-btn"
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmArchiveFichier({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}', {{ $fichier->archived ? 'true' : 'false' }})" 
                                                class="action-btn archive-btn"
                                                title="{{ $fichier->archived ? 'Désarchiver' : 'Archiver' }}">
                                            <i class="fas fa-{{ $fichier->archived ? 'folder-open' : 'archive' }}"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmDeleteFichier({{ $fichier->id }}, '{{ addslashes($fichier->titre ?? 'Sans titre') }}')" 
                                                class="action-btn delete-btn"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-file text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">Aucun fichier</p>
                            <button type="button" 
                                    onclick="openCreateFichierModal()" 
                                    class="add-btn empty-btn mt-2">
                                <i class="fas fa-plus mr-2"></i>Ajouter un fichier
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations de l'événement</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Titre</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $evenement->titre }}</p>
                    </div>
                    
                    @if($evenement->description)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $evenement->description }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de début</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $evenement->date_debut ? $evenement->date_debut->format('d/m/Y') : 'Non définie' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de fin</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $evenement->date_fin ? $evenement->date_fin->format('d/m/Y') : 'Non définie' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Projet associé</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $evenement->projets->nom ?? 'Aucun projet' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Actualité ?</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($evenement->est_actualite)
                                <span class="actualite-badge actualite">Actualité</span>
                            @else
                                <span class="actualite-badge non-actualite">Standard</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">État actuel</label>
                        <p class="mt-5">
                            @if($evenement->etatCourant && $evenement->etatCourant->etats_evenement)
                                <span class="state-badge-large state-{{ strtolower(str_replace(' ', '-', $evenement->etatCourant->etats_evenement->etat)) }}">
                                    {{ $evenement->etatCourant->etats_evenement->etat }}
                                </span>
                            @else
                                <span class="state-badge-large state-indefini">Non défini</span>
                            @endif

                            @if($evenement->archived)
                                <span class="status-badge archived">Archivé</span>
                            @else
                                <span class="status-badge active">Actif</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Niveau d'accès</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $evenement->niveau_acces ?? '?' }}</p>
                    </div>
                </div>
            </div>

            <!-- Image principale -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Image de l'événement</h2>
                </div>
                <div class="p-6">
                    @if($evenement->image)
                        <img src="{{ asset($evenement->image) }}" alt="{{ $evenement->titre }}" class="w-full h-72 object-cover rounded-lg" style="border-radius: var(--border-radius);">
                    @else
                        <div class="h-64 w-full bg-gray-200 flex items-center justify-center rounded-lg" style="border-radius: var(--border-radius);">
                            <i class="fas fa-calendar-alt text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les sections -->
<!-- Modal Création Section -->
<div id="createSectionModal" class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Créer une section</h3>
            <button type="button" onclick="closeCreateSectionModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createSectionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="section_titre" class="form-label">Titre *</label>
                        <input type="text" name="titre" id="section_titre" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="section_description" class="form-label">Description</label>
                        <textarea name="description" id="section_description" rows="3" class="form-input"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="section_image" class="form-label">Image</label>
                        <input type="file" name="image" id="section_image" class="form-input" accept="image/jpeg,image/png">
                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeCreateSectionModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Créer la section</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Édition Section -->
<div id="editSectionModal" class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier la section</h3>
            <button type="button" onclick="closeEditSectionModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editSectionForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="edit_section_titre" class="form-label">Titre *</label>
                        <input type="text" name="titre" id="edit_section_titre" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_section_description" rows="3" class="form-input"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_image" class="form-label">Image</label>
                        <input type="file" name="image" id="edit_section_image" class="form-input" accept="image/jpeg,image/png">
                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG</p>
                        <div id="currentSectionImage" class="mt-2 hidden">
                            <p class="text-sm text-gray-700 mb-1">Image actuelle :</p>
                            <img id="sectionImagePreview" class="h-20 w-20 object-cover rounded">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditSectionModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="save-btn">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- Modals pour les fichiers -->
<!-- Modal Création Fichier -->
<div id="createFichierModal" class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter des fichiers</h3>
            <button type="button" onclick="closeCreateFichierModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createFichierForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="entite_type_id" value="{{ $entiteTypeEvenementId }}">
            <input type="hidden" name="entite_id" value="{{ $evenement->id }}">
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="fichier_titre" class="form-label">Titre (pour tous les fichiers)</label>
                        <input type="text" name="titre" id="fichier_titre" class="form-input" placeholder="Titre commun pour tous les fichiers (optionnel)">
                    </div>
                    <div class="form-group">
                        <label for="fichier_description" class="form-label">Description (pour tous les fichiers)</label>
                        <textarea name="description" id="fichier_description" rows="3" class="form-input" placeholder="Description commune pour tous les fichiers (optionnel)"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fichier_files" class="form-label">Fichiers *</label>
                        <input type="file" name="fichiers[]" id="fichier_files" class="form-input" required accept="*/*" multiple>
                        <p class="text-xs text-gray-500 mt-1">Taille maximale par fichier : 50MB. Vous pouvez sélectionner plusieurs fichiers.</p>
                        <div id="selectedFiles" class="mt-2 hidden">
                            <p class="text-sm text-gray-700 mb-1">Fichiers sélectionnés :</p>
                            <ul id="filesList" class="text-xs text-gray-600 space-y-1"></ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="fichier_niveau_acces" class="form-label">Niveau d'accès *</label>
                        <select name="niveau_acces" id="fichier_niveau_acces" class="form-input" required>
                            <option value="">Sélectionnez un niveau d'accès</option>
                            @foreach($typesCompte as $type)
                                <option value="{{ $type->niveau_acces }}">Niveau {{ $type->niveau_acces }} - {{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="archived" value="1" class="mr-2">
                            Archiver les fichiers
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeCreateFichierModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Ajouter les fichiers</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Édition Fichier -->
<div id="editFichierModal" class="modal-container">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Modifier le fichier</h3>
            <button type="button" onclick="closeEditFichierModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editFichierForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="edit_fichier_titre" class="form-label">Titre</label>
                        <input type="text" name="titre" id="edit_fichier_titre" class="form-input" placeholder="Titre du fichier (optionnel)">
                    </div>
                    <div class="form-group">
                        <label for="edit_fichier_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_fichier_description" rows="3" class="form-input" placeholder="Description du fichier (optionnel)"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_fichier_file" class="form-label">Fichier (remplacer)</label>
                        <input type="file" name="fichier" id="edit_fichier_file" class="form-input" accept="*/*">
                        <p class="text-xs text-gray-500 mt-1">Taille maximale : 50MB. Laissez vide pour conserver le fichier actuel.</p>
                        <div id="currentFichierInfo" class="mt-2 hidden">
                            <p class="text-sm text-gray-700 mb-1">Fichier actuel :</p>
                            <div class="flex items-center space-x-2">
                                <i id="currentFichierIcon" class="text-lg"></i>
                                <span id="currentFichierName" class="text-sm"></span>
                                <span id="currentFichierSize" class="text-xs text-gray-500"></span>
                            </div>
                            <div id="currentFichierPreview" class="mt-3 hidden">
                                <img id="currentFichierImage" src="" alt="Aperçu du fichier" class="w-32 h-32 object-cover border border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_fichier_niveau_acces" class="form-label">Niveau d'accès *</label>
                        <select name="niveau_acces" id="edit_fichier_niveau_acces" class="form-input" required>
                            <option value="">Sélectionnez un niveau d'accès</option>
                            @foreach($typesCompte as $type)
                                <option value="{{ $type->niveau_acces }}">Niveau {{ $type->niveau_acces }} - {{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="archived" id="edit_fichier_archived" value="1" class="mr-2">
                            Archiver le fichier
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditFichierModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="save-btn">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulaires cachés -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="deleteSectionForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="deleteFichierForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="archiveFichierForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
// Gestion des modals sections
function openCreateSectionModal() {
    document.getElementById('createSectionModal').classList.add('active');
    document.getElementById('createSectionForm').action = `/admin/evenements/{{ $evenement->id }}/sections`;
}

function closeCreateSectionModal() {
    document.getElementById('createSectionModal').classList.remove('active');
    document.getElementById('createSectionForm').reset();
}

function openEditSectionModal() {
    document.getElementById('editSectionModal').classList.add('active');
}

function closeEditSectionModal() {
    document.getElementById('editSectionModal').classList.remove('active');
}

// Gestion des modals fichiers
function openCreateFichierModal() {
    document.getElementById('createFichierModal').classList.add('active');
    document.getElementById('createFichierForm').action = `/admin/evenements/fichiers`;
}

function closeCreateFichierModal() {
    document.getElementById('createFichierModal').classList.remove('active');
    document.getElementById('createFichierForm').reset();
}

function openEditFichierModal() {
    document.getElementById('editFichierModal').classList.add('active');
}

function closeEditFichierModal() {
    document.getElementById('editFichierModal').classList.remove('active');
}

// Édition de section
function editSection(sectionId) {
    fetch(`/admin/evenements/{{ $evenement->id }}/sections/${sectionId}`)
        .then(response => response.json())
        .then(section => {
            document.getElementById('edit_section_titre').value = section.titre;
            document.getElementById('edit_section_description').value = section.description || '';
            document.getElementById('editSectionForm').action = `/admin/evenements/{{ $evenement->id }}/sections/${sectionId}`;
            
            const currentImageDiv = document.getElementById('currentSectionImage');
            const imagePreview = document.getElementById('sectionImagePreview');
            if (section.image) {
                imagePreview.src = section.image;
                currentImageDiv.classList.remove('hidden');
            } else {
                currentImageDiv.classList.add('hidden');
            }
            
            openEditSectionModal();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement de la section');
        });
}

// Édition de fichier
function editFichier(fichierId) {
    fetch(`/admin/evenements/fichiers/${fichierId}`)
        .then(response => response.json())
        .then(fichier => {
            // Remplir les champs du formulaire
            document.getElementById('edit_fichier_titre').value = fichier.titre || '';
            document.getElementById('edit_fichier_description').value = fichier.description || '';
            document.getElementById('edit_fichier_niveau_acces').value = fichier.niveau_acces;
            document.getElementById('edit_fichier_archived').checked = fichier.archived;
            document.getElementById('editFichierForm').action = `/admin/fichiers/${fichierId}`;

            // Références DOM
            const currentFichierDiv = document.getElementById('currentFichierInfo');
            const fileIcon = document.getElementById('currentFichierIcon');
            const fileName = document.getElementById('currentFichierName');
            const fileSize = document.getElementById('currentFichierSize');
            const previewDiv = document.getElementById('currentFichierPreview');
            const previewImg = document.getElementById('currentFichierImage');

            // Réinitialiser les éléments
            currentFichierDiv.classList.add('hidden');
            previewDiv.classList.add('hidden');
            fileIcon.className = '';
            fileName.textContent = '';
            fileSize.textContent = '';
            previewImg.src = '';

            if (fichier.chemin) {
                // Infos du fichier
                fileIcon.className = fichier.file_icon;
                fileName.textContent = fichier.titre || 'Fichier';
                fileSize.textContent = fichier.file_size;
                currentFichierDiv.classList.remove('hidden');

                // Vérifier si c’est une image
                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                const ext = (fichier.type_fichier || '').toLowerCase();

                if (imageExtensions.includes(ext)) {
                    previewImg.src = `${fichier.chemin}`;
                    previewDiv.classList.remove('hidden');
                }
            }

            openEditFichierModal();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du fichier');
        });
}

// Confirmations de suppression
function confirmDelete(evenementId, evenementTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'événement "${evenementTitre}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/evenements/${evenementId}`;
        form.submit();
    }
}

function confirmDeleteSection(sectionId, sectionTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la section "${sectionTitre}" ?`)) {
        const form = document.getElementById('deleteSectionForm');
        form.action = `/admin/evenements/{{ $evenement->id }}/sections/${sectionId}`;
        form.submit();
    }
}

function confirmDeleteFichier(fichierId, fichierTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le fichier "${fichierTitre}" ?`)) {
        const form = document.getElementById('deleteFichierForm');
        form.action = `/admin/fichiers/${fichierId}`;
        form.submit();
    }
}

function confirmArchive(evenementId, evenementTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} l'événement "${evenementTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/evenements/${evenementId}/toggle-archive`;
        form.submit();
    }
}

function confirmArchiveFichier(fichierId, fichierTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le fichier "${fichierTitre}" ?`)) {
        const form = document.getElementById('archiveFichierForm');
        form.action = `/admin/fichiers/${fichierId}/toggle-archive`;
        form.submit();
    }
}

// Fermer les modals en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const modals = ['createSectionModal', 'editSectionModal', 'createFichierModal', 'editFichierModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            if (modalId.includes('Section')) {
                if (modalId.includes('create')) closeCreateSectionModal();
                else closeEditSectionModal();
            } else {
                if (modalId.includes('create')) closeCreateFichierModal();
                else closeEditFichierModal();
            }
        }
    });
});

// Gestion de l'affichage des fichiers sélectionnés
document.getElementById('fichier_files').addEventListener('change', function(e) {
    const filesList = document.getElementById('filesList');
    const selectedFilesDiv = document.getElementById('selectedFiles');
    
    filesList.innerHTML = '';
    
    if (this.files.length > 0) {
        selectedFilesDiv.classList.remove('hidden');
        
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            const listItem = document.createElement('li');
            listItem.className = 'flex justify-between items-center';
            listItem.innerHTML = `
                <span>${file.name}</span>
                <span class="text-gray-500">${formatFileSize(file.size)}</span>
            `;
            filesList.appendChild(listItem);
        }
    } else {
        selectedFilesDiv.classList.add('hidden');
    }
});

// Fonction pour formater la taille des fichiers
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

.state-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
}

/* Couleurs prédéfinies pour les états */
.state-badge-large.state-a-venir { background: var(--bleu-ge); }
.state-badge-large.state-en-cours { background: var(--color-success); }
.state-badge-large.state-terminé { background: #6b7280; }
.state-badge-large.state-annulé { background: var(--color-error); }
.state-badge-large.state-indefini { background: #9ca3af; }

/* Styles pour les fichiers */
.file-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.file-icon.pdf { background: #fee2e2; color: #dc2626; }
.file-icon.doc { background: #dbeafe; color: #1d4ed8; }
.file-icon.xls { background: #dcfce7; color: #16a34a; }
.file-icon.img { background: #fef3c7; color: #d97706; }
.file-icon.zip { background: #f3e8ff; color: #7c3aed; }
.file-icon.audio { background: #f0f9ff; color: #0369a1; }
.file-icon.video { background: #fef7cd; color: #ca8a04; }
.file-icon.csv { background: #dcfce7; color: #16a34a; }
.file-icon.default { background: #f3f4f6; color: #6b7280; }

.file-type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f3e8ff;
    color: #6b21a8;
}

.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}

/* Styles pour les modals */
.modal-container {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-container.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 1rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>
@endsection