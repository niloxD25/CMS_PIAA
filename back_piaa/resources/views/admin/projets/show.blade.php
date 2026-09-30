@extends('admin.layouts.base')

@section('base_content')

@section('title', $projet->nom)

@php
    $projetActuel = \App\Models\ProjetEnCours::latest('date_changement')->first();
    $isProjetActuel = $projetActuel && $projetActuel->projet_id == $projet->id;
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.projets.edit', $projet->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.projets.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations du projet</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nom</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $projet->nom }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $projet->description ?? 'Aucune description' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Devise</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $projet->devise ?? 'Aucune devise' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Acronymes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $projet->acronymes ?? 'Aucun acronymes' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de début</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $projet->debut ? $projet->debut->format('d/m/Y') : 'Non définie' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Durée</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $projet->duree_annee ? $projet->duree_annee . ' an(s)' : 'Non définie' }}</p>
                        </div>
                    </div>
                    
                    @if($isProjetActuel && $projetActuel)
                    <div class="pt-4 border-t border-gray-100">
                        <label class="text-sm font-medium text-gray-500">Défini comme actuel le</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $projetActuel->date_changement->format('d/m/Y à H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Section Personnes associées -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Personnes associées</h2>
                    <button type="button" 
                            onclick="openAddPersonneModal()" 
                            class="add-btn">
                        <i class="fas fa-plus mr-2"></i>Ajouter une personne
                    </button>
                </div>
                <div class="p-6">
                    @if($projet->projet_personnes->count() > 0)
                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2" style="scrollbar-width: thin;">
                            @foreach($projet->projet_personnes as $participation)
                            @php
                                $personne = $participation->personnes;
                            @endphp
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150" style="border-radius: var(--border-radius);">
                                <div class="flex items-center space-x-4 min-w-0 flex-1">
                                    @if($personne->photo)
                                        <img src="{{ asset($personne->photo) }}" 
                                            alt="{{ $personne->prenom }} {{ $personne->nom }}" 
                                            class="h-12 w-12 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate">{{ $personne->prenom }} {{ $personne->nom }}</p>
                                        <p class="text-sm text-gray-500 truncate">
                                            @if($personne->poste)
                                                {{ $personne->poste }}
                                            @endif
                                            @if($participation->date_debut_participation || $participation->date_fin_participation)
                                                • 
                                                @if($participation->date_debut_participation)
                                                    {{ \Carbon\Carbon::parse($participation->date_debut_participation)->format('d/m/Y') }}
                                                @endif
                                                @if($participation->date_fin_participation)
                                                    - {{ \Carbon\Carbon::parse($participation->date_fin_participation)->format('d/m/Y') }}
                                                @endif
                                            @endif
                                            @if(isset($participation->hierarchie))
                                                | hierarchie : {{ $participation->hierarchie }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="actions-container flex-shrink-0 ml-4">
                                    <a href="{{ route('admin.personnes.show', $personne->id) }}" 
                                    class="action-btn view-btn"
                                    title="Voir la personne">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            onclick="openEditPersonneModal({{ $personne->id }}, '{{ addslashes($personne->prenom . ' ' . $personne->nom) }}', '{{ $participation->date_debut_participation }}', '{{ $participation->date_fin_participation }}', '{{ $participation->hierarchie }}', {{ $participation->id }})"
                                            class="action-btn edit-btn"
                                            title="Modifier la participation">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="confirmRemovePersonne({{ $projet->id }}, {{ $participation->id }}, '{{ addslashes($personne->prenom . ' ' . $personne->nom) }}')"
                                            class="action-btn delete-btn"
                                            title="Retirer du projet">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500">Aucune personne associée à ce projet</p>
                            <button type="button" 
                                    onclick="openAddPersonneModal()" 
                                    class="add-btn mt-3">
                                <i class="fas fa-plus mr-2"></i>Ajouter la première personne
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section À propos -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Sections "À propos"</h2>
                    <a href="{{ route('admin.projets.apropos-sections.create', $projet->id) }}" 
                    class="add-btn">
                        <i class="fas fa-plus-circle mr-2"></i>Nouvelle section
                    </a>
                </div>
                <div class="p-6">
                    @if($projet->projet_apropos_sections && $projet->projet_apropos_sections->count() > 0)
                        <div class="space-y-4">
                            @foreach($projet->projet_apropos_sections->sortBy('ordre') as $section)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-150" style="border-radius: var(--border-radius);">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                <i class="fas fa-sort-numeric-down mr-1"></i>{{ $section->ordre }}
                                            </span>
                                            <h4 class="text-lg font-medium text-gray-900">{{ $section->titre }}</h4>
                                        </div>
                                        
                                        @if($section->sous_titre)
                                            <p class="text-sm text-gray-600 mb-2">{{ $section->sous_titre }}</p>
                                        @endif
                                        
                                        <p class="text-gray-700 mb-3">{{ Str::limit($section->description, 150) }}</p>
                                        
                                        @if($section->image)
                                        <div class="mt-2">
                                            <img src="{{ asset($section->image) }}" 
                                                alt="{{ $section->titre }}"
                                                class="h-24 w-24 object-cover rounded-lg"
                                                style="border-radius: var(--border-radius);">
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="actions-container flex-shrink-0 ml-4">
                                        <a href="{{ route('admin.projets.apropos-sections.show', [$projet->id, $section->id]) }}" 
                                        class="action-btn view-btn"
                                        title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.projets.apropos-sections.edit', [$projet->id, $section->id]) }}" 
                                        class="action-btn edit-btn"
                                        title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.projets.apropos-sections.destroy', [$projet->id, $section->id]) }}" 
                                            method="POST" 
                                            class="inline"
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette section ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="action-btn delete-btn"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-info-circle text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500">Aucune section "À propos" pour ce projet</p>
                            <a href="{{ route('admin.projets.apropos-sections.create', $projet->id) }}" 
                            class="add-btn mt-3">
                                <i class="fas fa-plus-circle mr-2"></i>Créer la première section
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section Réseaux Sociaux -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-6" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Réseaux Sociaux</h2>
                    <button type="button" 
                            onclick="openAddSocialModal()" 
                            class="add-btn">
                        <i class="fas fa-plus mr-2"></i>Ajouter un réseau
                    </button>
                </div>
                <div class="p-6">
                    @if($projet->reseaux_sociaux->count() > 0)
                        <div class="space-y-4 max-h-80 overflow-y-auto pr-2" style="scrollbar-width: thin;">
                            @foreach($projet->reseaux_sociaux as $reseau)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150" style="border-radius: var(--border-radius);">
                                <div class="flex items-center space-x-4 min-w-0 flex-1">
                                    @if($reseau->font_awesome_icon)
                                        <div class="social-icon flex-shrink-0">
                                            <i class="{{ $reseau->font_awesome_icon }} text-xl"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate">{{ $reseau->nom }}</p>
                                        <a href="{{ $reseau->pivot->url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 block truncate">
                                            {{ $reseau->pivot->url }}
                                        </a>
                                    </div>
                                </div>
                                <div class="actions-container flex-shrink-0 ml-4">
                                    <button type="button" 
                                            onclick="openEditSocialModal({{ $reseau->id }}, '{{ addslashes($reseau->nom) }}', '{{ addslashes($reseau->pivot->url) }}')"
                                            class="action-btn edit-btn"
                                            title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" 
                                            onclick="confirmRemoveSocial({{ $projet->id }}, {{ $reseau->id }}, '{{ addslashes($reseau->nom) }}')"
                                            class="action-btn delete-btn"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-share-alt text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500">Aucun réseau social associé à ce projet</p>
                            <button type="button" 
                                    onclick="openAddSocialModal()" 
                                    class="add-btn mt-3">
                                <i class="fas fa-plus mr-2"></i>Ajouter le premier réseau
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Logo et actions -->
        <div class="space-y-6">
            <!-- Logo -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Logo</h2>
                </div>
                <div class="p-6 flex justify-center relative">
                    @if($projet->logo)
                        <img src="{{ asset($projet->logo) }}" alt="{{ $projet->nom }}" class="h-32 w-32 rounded-full object-cover {{ $isProjetActuel ? 'logo-actuel-large' : '' }}" style="border-radius: 50%;">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center {{ $isProjetActuel ? 'logo-actuel-large' : '' }}" style="border-radius: 50%;">
                            <i class="fas fa-project-diagram text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                    @if($isProjetActuel)
                    <div class="actuel-indicator-large" title="Projet actuel">
                        <i class="fas fa-star"></i>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations de contact rapides -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Contact Rapide</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if($projet->email)
                    <div class="flex items-center space-x-3">
                        <div class="contact-icon bg-blue-100 text-blue-600">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Email</p>
                            <a href="mailto:{{ $projet->email }}" class="text-sm text-blue-600 hover:text-blue-800 truncate block">
                                {{ $projet->email }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($projet->telephone)
                    <div class="flex items-center space-x-3">
                        <div class="contact-icon bg-green-100 text-green-600">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Téléphone</p>
                            <a href="tel:{{ $projet->telephone }}" class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $projet->telephone }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($projet->adresse)
                    <div class="flex items-start space-x-3">
                        <div class="contact-icon bg-purple-100 text-purple-600 mt-1">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Adresse</p>
                            <p class="text-sm text-gray-600">{{ $projet->adresse }}</p>
                        </div>
                    </div>
                    @endif

                    @if(!$projet->email && !$projet->telephone && !$projet->adresse)
                    <div class="text-center py-4">
                        <i class="fas fa-address-book text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Aucune information de contact</p>
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
                    @if(!$isProjetActuel)
                        @if(!$projet->archived)
                            <!-- Bouton normal pour les projets non archivés -->
                            <button type="button" 
                                    onclick="confirmSetActuel({{ $projet->id }}, '{{ addslashes($projet->nom) }}')" 
                                    class="create-btn w-full" 
                                    style="background: var(--color-primary);">
                                <i class="fas fa-star mr-2"></i>Définir comme projet actuel
                            </button>
                        @else
                            <!-- Message d'information pour les projets archivés -->
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 text-gray-400 mt-0.5">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-gray-600 font-medium">
                                            Projet archivé
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Pour définir ce projet comme actuel, vous devez d'abord le désarchiver.
                                        </p>
                                        <button type="button" 
                                                onclick="confirmArchive({{ $projet->id }}, '{{ addslashes($projet->nom) }}', true)"
                                                class="mt-2 text-sm text-primary-600 hover:text-primary-800 font-medium">
                                            Désarchiver le projet
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="current-project-info">
                            <div class="current-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="current-text">
                                <p class="font-medium">Projet actuel</p>
                                <p class="text-sm opacity-75">Visible sur le site</p>
                            </div>
                        </div>
                    @endif
                    
                    <button type="button" 
                            onclick="confirmDelete({{ $projet->id }}, '{{ addslashes($projet->nom) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer le projet
                    </button>
                </div>
            </div>

            <!-- Statut du projet -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Statut</h2>
                </div>
                <div class="p-6">
                    <!-- Statut d'archivage -->
                    <div class="status-indicator {{ $projet->archived ? 'status-archived' : 'status-active' }}">
                        <div class="status-icon">
                            @if($projet->archived)
                                <i class="fas fa-archive"></i>
                            @else
                                <i class="fas fa-folder-open"></i>
                            @endif
                        </div>
                        <div class="status-content">
                            <p class="status-title">
                                @if($projet->archived)
                                    Projet archivé
                                @else
                                    Projet non archivé
                                @endif
                            </p>
                            <p class="status-description">
                                @if($projet->archived)
                                    Ce projet est archivé et n'est plus visible sur le site.
                                @else
                                    Ce projet est non archivé et visible sur le site.
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if(!$projet->archived)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="status-indicator {{ $isProjetActuel ? 'status-actuel' : 'status-normal' }}">
                            <div class="status-icon">
                                @if($isProjetActuel)
                                    <i class="fas fa-check-circle"></i>
                                @else
                                    <i class="fas fa-clock"></i>
                                @endif
                            </div>
                            <div class="status-content">
                                <p class="status-title">
                                    @if($isProjetActuel)
                                        Projet actuellement actif
                                    @else
                                        Projet inactif
                                    @endif
                                </p>
                                <p class="status-description">
                                    @if($isProjetActuel)
                                        Ce projet est actuellement visible sur le site comme le projet en cours.
                                    @else
                                        Ce projet n'est pas le projet actuel. Les visiteurs ne le verront pas comme projet principal.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier une personne -->
<div id="personneModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="personneModalTitle">Ajouter une personne</h3>
            <button type="button" onclick="closePersonneModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="personneForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="personne_id" class="form-label">Personne</label>
                    <select name="personne_id" id="personne_id" class="form-input" required>
                        <option value="">Sélectionnez une personne</option>
                        @foreach($personnes as $personne)
                            <option value="{{ $personne->id }}">
                                {{ $personne->prenom }} {{ $personne->nom }}
                                @if($personne->poste)
                                    - {{ $personne->poste }}
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
                           value="1"
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
                           value="{{ now()->format('Y-m-d') }}"
                           class="form-input"
                           required>
                </div>

                <!-- Date de fin -->
                <div class="form-group">
                    <label for="date_fin_participation" class="form-label">Date de fin de participation</label>
                    <input type="date" 
                           name="date_fin_participation" 
                           id="date_fin_participation"
                           class="form-input"
                           placeholder="Laissez vide si la participation est toujours en cours">
                    <p class="text-xs text-gray-500 mt-1">Laissez vide si la participation est toujours en cours</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closePersonneModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour modifier la participation d'une personne -->
<div id="editPersonneModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="editPersonneModalTitle">Modifier la participation</h3>
            <button type="button" onclick="closeEditPersonneModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editPersonneForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <input type="hidden" name="personne_id" id="edit_personne_id">
                
                <div class="form-group">
                    <label class="form-label">Personne</label>
                    <p class="form-static-value" id="edit_personne_nom"></p>
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
                <button type="button" onclick="closeEditPersonneModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour ajouter/modifier un réseau social -->
<div id="socialModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="socialModalTitle">Ajouter un réseau social</h3>
            <button type="button" onclick="closeSocialModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="socialForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="reseau_social_id" class="form-label">Réseau social</label>
                    <select name="reseau_social_id" id="reseau_social_id" class="form-input" required>
                        <option value="">Sélectionnez un réseau social</option>
                        @foreach($reseauxSociaux as $reseau)
                            <option value="{{ $reseau->id }}">{{ $reseau->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="url" class="form-label">URL</label>
                    <input type="url" 
                           name="url" 
                           id="url" 
                           class="form-input"
                           placeholder="https://..."
                           required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeSocialModal()" class="cancel-btn">Annuler</button>
                <button type="submit" class="create-btn">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire de projet actuel caché -->
<form id="setActuelForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Formulaire de suppression personne caché -->
<form id="removePersonneForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire de suppression réseau social caché -->
<form id="removeSocialForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Formulaire d'archivage caché -->
<form id="archiveForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmDelete(projetId, projetNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le projet "${projetNom}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/projets/${projetId}`;
        form.submit();
    }
}

function confirmSetActuel(projetId, projetNom) {
    if (confirm(`Êtes-vous sûr de vouloir définir "${projetNom}" comme projet actuel ?\n\nCe projet sera visible comme le projet en cours sur le site.`)) {
        const form = document.getElementById('setActuelForm');
        form.action = `/admin/projets/${projetId}/set-actuel`;
        form.submit();
    }
}

function confirmArchive(projetId, projetTitre, isArchived) {
    const action = isArchived ? 'désarchiver' : 'archiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} le projet "${projetTitre}" ?`)) {
        const form = document.getElementById('archiveForm');
        form.action = `/admin/projets/${projetId}/toggle-archive`;
        form.submit();
    }
}

// Gestion des personnes
function openAddPersonneModal() {
    document.getElementById('personneModalTitle').textContent = 'Ajouter une personne';
    document.getElementById('personneForm').action = `/admin/projets/{{ $projet->id }}/participants`;
    document.getElementById('personneForm').method = 'POST';
    document.getElementById('personne_id').value = '';
    document.getElementById('hierarchie').value = '1';
    document.getElementById('date_debut_participation').value = '{{ now()->format("Y-m-d") }}';
    document.getElementById('date_fin_participation').value = '';
    document.getElementById('personneModal').style.display = 'flex';
}

function openEditPersonneModal(personneId, personneNom, dateDebut, dateFin, hierarchie, participationId) {
    document.getElementById('editPersonneModalTitle').textContent = `Modifier la participation de ${personneNom}`;
    document.getElementById('editPersonneForm').action = `/admin/projets/{{ $projet->id }}/participants/${participationId}`;
    document.getElementById('edit_personne_id').value = personneId;
    document.getElementById('edit_personne_nom').textContent = personneNom;
    document.getElementById('edit_hierarchie').value = hierarchie || '1';
    document.getElementById('edit_date_debut_participation').value = dateDebut ? dateDebut.split(' ')[0] : '';
    document.getElementById('edit_date_fin_participation').value = dateFin ? dateFin.split(' ')[0] : '';
    document.getElementById('editPersonneModal').style.display = 'flex';
}

// Dans confirmRemovePersonne, utilisez l'ID de la participation
function confirmRemovePersonne(projetId, participationId, personneNom) {
    if (confirm(`Êtes-vous sûr de vouloir retirer "${personneNom}" de ce projet ?`)) {
        const form = document.getElementById('removePersonneForm');
        form.action = `/admin/projets/${projetId}/participants/${participationId}`;
        form.submit();
    }
}

function closePersonneModal() {
    document.getElementById('personneModal').style.display = 'none';
}

function closeEditPersonneModal() {
    document.getElementById('editPersonneModal').style.display = 'none';
}

// Gestion des réseaux sociaux
let currentEditingReseauId = null;

function openAddSocialModal() {
    document.getElementById('socialModalTitle').textContent = 'Ajouter un réseau social';
    document.getElementById('socialForm').action = `/admin/projets/{{ $projet->id }}/reseaux-sociaux`;
    document.getElementById('socialForm').method = 'POST';
    document.getElementById('reseau_social_id').value = '';
    document.getElementById('url').value = '';
    currentEditingReseauId = null;
    document.getElementById('socialModal').style.display = 'flex';
}

function openEditSocialModal(reseauId, reseauNom, url) {
    document.getElementById('socialModalTitle').textContent = `Modifier ${reseauNom}`;
    document.getElementById('socialForm').action = `/admin/projets/{{ $projet->id }}/reseaux-sociaux/${reseauId}`;
    document.getElementById('socialForm').method = 'POST';
    document.getElementById('reseau_social_id').disabled = true;
    document.getElementById('reseau_social_id').value = reseauId;
    document.getElementById('url').value = url;
    currentEditingReseauId = reseauId;
    document.getElementById('socialModal').style.display = 'flex';
}

function closeSocialModal() {
    document.getElementById('socialModal').style.display = 'none';
}

function confirmRemoveSocial(projetId, reseauId, reseauNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le réseau social "${reseauNom}" de ce projet ?`)) {
        const form = document.getElementById('removeSocialForm');
        form.action = `/admin/projets/${projetId}/reseaux-sociaux/${reseauId}`;
        form.submit();
    }
}

// Fermer les modals en cliquant à l'extérieur
function setupModalClose(modalId, closeFunction) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target.id === modalId) {
                closeFunction();
            }
        });
    }
}

setupModalClose('personneModal', closePersonneModal);
setupModalClose('editPersonneModal', closeEditPersonneModal);
setupModalClose('socialModal', closeSocialModal);
</script>

<style>
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
    justify-content: between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    flex: 1;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #6b7280;
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

.social-item, .personne-item {
    transition: all 0.2s ease;
}

.social-item:hover, .personne-item:hover {
    background: #f9fafb;
}

.social-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 50%;
}

.contact-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
}

.form-static-value {
    padding: 0.75rem;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    color: #374151;
}
</style>
@endsection