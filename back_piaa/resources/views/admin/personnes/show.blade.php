@extends('admin.layouts.base')

@section('base_content')

@section('title', $personne->prenom . ' ' . $personne->nom)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            @if($personne->poste)
                <span class="status-badge poste">{{ $personne->poste }}</span>
            @endif
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.personnes.edit', $personne->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.personnes.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations personnelles</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Nom et Prénom -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Prénom</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $personne->prenom }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nom</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $personne->nom }}</p>
                        </div>
                    </div>
                    
                    <!-- Poste -->
                    @if($personne->poste)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Poste/Fonction</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $personne->poste }}</p>
                    </div>
                    @endif

                    <!-- Projets associés -->
                    <div>
                        <label class="text-sm font-medium text-gray-500 mb-3 block">Projets associés</label>
                        @if($personne->projets->count() > 0)
                            <div class="space-y-3">
                                @foreach($personne->projets as $projet)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-project-diagram text-blue-500"></i>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $projet->nom }}</p>
                                            <p class="text-sm text-gray-500">
                                                Participation : 
                                                @if(isset($projet->pivot->date_debut_participation) && $projet->pivot->date_debut_participation)
                                                    {{ \Carbon\Carbon::parse($projet->pivot->date_debut_participation)->format('d/m/Y') }}
                                                @else
                                                    Non définie
                                                @endif
                                                @if(isset($projet->pivot->date_fin_participation) && $projet->pivot->date_fin_participation)
                                                    - {{ \Carbon\Carbon::parse($projet->pivot->date_fin_participation)->format('d/m/Y') }}
                                                @endif
                                                @if(isset($projet->pivot->hierarchie))
                                                    | hierarchie : {{ $projet->pivot->hierarchie }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="actions-container">
                                        <a href="{{ route('admin.projets.show', $projet->id) }}" 
                                        class="action-btn view-btn"
                                        title="Voir le projet">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                onclick="openEditParticipationModal({{ $projet->id }}, {{ $projet->pivot->id }}, '{{ addslashes($projet->nom) }}', '{{ $projet->pivot->date_debut_participation }}', '{{ $projet->pivot->date_fin_participation }}', '{{ $projet->pivot->hierarchie }}')"
                                                class="action-btn edit-btn"
                                                title="Modifier la participation">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmRemoveFromProject({{ $projet->id }}, {{ $projet->pivot->id }}, '{{ addslashes($projet->nom) }}')"
                                                class="action-btn delete-btn"
                                                title="Retirer du projet">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                                <i class="fas fa-folder-open text-gray-400 text-3xl mb-3"></i>
                                <p class="text-gray-500">Cette personne ne participe à aucun projet</p>
                                <a href="{{ route('admin.projets.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block">
                                    Gérer les projets
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo et actions -->
        <div class="space-y-6">
            <!-- Photo -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Photo de profil</h2>
                </div>
                <div class="p-6 flex flex-col items-center">
                    @if($personne->photo)
                        <img src="{{ asset($personne->photo) }}" 
                             alt="{{ $personne->prenom }} {{ $personne->nom }}" 
                             class="h-32 w-32 rounded-full object-cover mb-4" 
                             style="border-radius: 50%;">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4" style="border-radius: 50%;">
                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Statistiques</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Nombre de projets</span>
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-blue-600">{{ $personne->projets->count() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Projets en cours</span>
                            <div class="flex items-center">
                                @php
                                    $projetsEnCours = $personne->projets->filter(function($projet) {
                                        $now = now();
                                        $debut = $projet->debut ? \Carbon\Carbon::parse($projet->debut) : null;
                                        $fin = $debut && $projet->duree_annee ? $debut->copy()->addYears($projet->duree_annee) : null;
                                        
                                        if (!$debut || !$fin) return false;
                                        
                                        return $now->between($debut, $fin);
                                    })->count();
                                @endphp
                                <span class="text-lg font-bold text-green-600">{{ $projetsEnCours }}</span>
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
                    <!-- Lien vers l'édition -->
                    <a href="{{ route('admin.personnes.edit', $personne->id) }}" class="create-btn w-full text-center" style="background: var(--color-primary);">
                        <i class="fas fa-edit mr-2"></i>Modifier les informations
                    </a>

                    <!-- Ajouter à un projet - Bouton pour ouvrir le modal -->
                    <button type="button" 
                            onclick="openAddProjectModal()" 
                            class="create-btn w-full text-center" 
                            style="background: var(--color-success);">
                        <i class="fas fa-plus mr-2"></i>Ajouter à un projet
                    </button>
                    
                    <!-- Supprimer la personne -->
                    <button type="button" 
                            onclick="confirmDelete({{ $personne->id }}, '{{ addslashes($personne->prenom . ' ' . $personne->nom) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer la personne
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter à un projet -->
<div id="addProjectModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Ajouter à un projet</h3>
            <button type="button" onclick="closeAddProjectModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addProjectForm" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="personne_id" value="{{ $personne->id }}">

                <!-- Sélection du projet -->
                <div class="form-group">
                    <label for="projet_id" class="form-label">Projet *</label>
                    <select name="projet_id" id="projet_id" class="form-input" required>
                        <option value="">Sélectionnez un projet</option>
                        @foreach($projets as $projet)
                            <option value="{{ $projet->id }}">
                                {{ $projet->nom }}
                                @if($projet->debut)
                                    ({{ \Carbon\Carbon::parse($projet->debut)->format('Y') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Hiérarchie -->
                <div class="form-group">
                    <label for="hierarchie" class="form-label">Ordre d'affichage</label>
                    <input type="number" 
                           name="hierarchie" 
                           id="hierarchie"
                           value="{{ old('hierarchie', 1) }}"
                           class="form-input"
                           min="1"
                           placeholder="Ordre d'affichage (1 pour le premier)">
                    <p class="text-xs text-gray-500 mt-1">Définit l'ordre d'affichage dans les listes</p>
                </div>

                <!-- Date de début -->
                <div class="form-group">
                    <label for="date_debut_participation" class="form-label">Date de début de participation *</label>
                    <input type="date" 
                           name="date_debut_participation" 
                           id="date_debut_participation"
                           value="{{ old('date_debut_participation', now()->format('Y-m-d')) }}"
                           class="form-input"
                           required>
                </div>

                <!-- Date de fin -->
                <div class="form-group">
                    <label for="date_fin_participation" class="form-label">Date de fin de participation</label>
                    <input type="date" 
                           name="date_fin_participation" 
                           id="date_fin_participation"
                           value="{{ old('date_fin_participation') }}"
                           class="form-input"
                           placeholder="Laissez vide si la participation est toujours en cours">
                    <p class="text-xs text-gray-500 mt-1">Laissez vide si la participation est toujours en cours</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeAddProjectModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Ajouter au projet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour modifier la participation -->
<div id="editParticipationModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Modifier la participation</h3>
            <button type="button" onclick="closeEditParticipationModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editParticipationForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="hidden" name="personne_id" id="edit_personne_id" value="{{ $personne->id }}">
                <input type="hidden" name="projet_id" id="edit_projet_id">

                <div class="form-group">
                    <label class="form-label">Projet</label>
                    <p class="form-static-value" id="edit_projet_nom"></p>
                </div>

                <!-- Hiérarchie -->
                <div class="form-group">
                    <label for="edit_hierarchie" class="form-label">Ordre d'affichage</label>
                    <input type="number" 
                           name="hierarchie" 
                           id="edit_hierarchie"
                           class="form-input"
                           min="1"
                           placeholder="Ordre d'affichage (1 pour le premier)">
                    <p class="text-xs text-gray-500 mt-1">Définit l'ordre d'affichage dans les listes</p>
                </div>

                <!-- Date de début -->
                <div class="form-group">
                    <label for="edit_date_debut_participation" class="form-label">Date de début de participation *</label>
                    <input type="date" 
                           name="date_debut_participation" 
                           id="edit_date_debut_participation"
                           class="form-input"
                           required>
                </div>

                <!-- Date de fin -->
                <div class="form-group">
                    <label for="edit_date_fin_participation" class="form-label">Date de fin de participation</label>
                    <input type="date" 
                           name="date_fin_participation" 
                           id="edit_date_fin_participation"
                           class="form-input"
                           placeholder="Laissez vide si la participation est toujours en cours">
                    <p class="text-xs text-gray-500 mt-1">Laissez vide si la participation est toujours en cours</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditParticipationModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire de suppression de participation caché -->
<form id="removeProjectForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(personneId, personneNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${personneNom}" ?\n\nCette action supprimera également toutes ses participations aux projets.\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/personnes/${personneId}`;
        form.submit();
    }
}

function confirmRemoveFromProject(projetId, participationId, projetNom) {
    if (confirm(`Êtes-vous sûr de vouloir retirer cette personne du projet "${projetNom}" ?`)) {
        const form = document.getElementById('removeProjectForm');
        form.action = `/admin/projets/${projetId}/participants/${participationId}`;
        form.submit();
    }
}

function openAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'flex';
    document.body.classList.add('overflow-hidden');
}

function closeAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'none';
    document.body.classList.remove('overflow-hidden');
}

function openEditParticipationModal(projetId, participationId, projetNom, dateDebut, dateFin, hierarchie) {
    // Remplir les champs du formulaire
    document.getElementById('edit_projet_id').value = projetId;
    document.getElementById('edit_projet_nom').textContent = projetNom;
    document.getElementById('edit_date_debut_participation').value = dateDebut ? dateDebut.split(' ')[0] : '';
    document.getElementById('edit_date_fin_participation').value = dateFin ? dateFin.split(' ')[0] : '';
    document.getElementById('edit_hierarchie').value = hierarchie || '1';
    
    // Mettre à jour l'action du formulaire
    const form = document.getElementById('editParticipationForm');
    form.action = `/admin/projets/${projetId}/participants/${participationId}`;
    
    // Afficher le modal
    document.getElementById('editParticipationModal').style.display = 'flex';
    document.body.classList.add('overflow-hidden');
}

function closeEditParticipationModal() {
    document.getElementById('editParticipationModal').style.display = 'none';
    document.body.classList.remove('overflow-hidden');
}

// Mettre à jour l'action du formulaire d'ajout en fonction du projet sélectionné
document.getElementById('projet_id').addEventListener('change', function() {
    const projetId = this.value;
    const form = document.getElementById('addProjectForm');
    form.action = `/admin/projets/${projetId}/participants`;
});

// Fermer les modals en cliquant à l'extérieur
document.getElementById('addProjectModal').addEventListener('click', function(e) {
    if (e.target.id === 'addProjectModal') {
        closeAddProjectModal();
    }
});

document.getElementById('editParticipationModal').addEventListener('click', function(e) {
    if (e.target.id === 'editParticipationModal') {
        closeEditParticipationModal();
    }
});

// Gérer les erreurs de validation
@if($errors->any())
    @if(old('personne_id') == $personne->id)
        @if(request()->isMethod('post'))
            openAddProjectModal();
        @elseif(request()->isMethod('put'))
            openEditParticipationModal({{ old('projet_id') }}, {{ $personne->id }}, '{{ old('projet_nom') }}', '{{ old('date_debut_participation') }}', '{{ old('date_fin_participation') }}', '{{ old('hierarchie') }}');
        @endif
    @endif
@endif
</script>

<style>
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
}

.status-badge.poste {
    background: var(--color-primary);
}

.rounded-full {
    border-radius: 50%;
}

/* Styles pour les modals */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: white;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    flex: 1;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #6b7280;
    padding: 0.25rem;
}

.modal-close:hover {
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* Styles pour les formulaires */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-static-value {
    padding: 0.75rem;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    color: #374151;
}

/* Responsive */
@media (max-width: 1024px) {
    .grid-cols-1.lg\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
    
    .lg\:col-span-2 {
        grid-column: span 1;
    }
}

.overflow-hidden {
    overflow: hidden;
}
</style>
@endsection