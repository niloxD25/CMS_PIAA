<div class="space-y-6">
    <!-- Titre -->
    <div class="form-group">
        <label for="titre" class="form-label">
            <i class="fas fa-heading"></i>Titre de l'événement *
        </label>
        <input type="text" 
               name="titre" 
               id="titre" 
               value="{{ old('titre', $evenement->titre ?? '') }}" 
               class="form-input @error('titre') error @enderror"
               placeholder="Entrez le titre de l'événement"
               required>
        @error('titre')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Description -->
    <div class="form-group">
        <label for="description" class="form-label">
            <i class="fas fa-align-left"></i>Description
        </label>
        <textarea name="description" 
                  id="description" 
                  rows="4"
                  class="form-input @error('description') error @enderror"
                  placeholder="Description de l'événement...">{{ old('description', $evenement->description ?? '') }}</textarea>
        @error('description')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Date de début -->
        <div class="form-group">
            <label for="date_debut" class="form-label">
                <i class="fas fa-calendar-day"></i>Date de début *
            </label>
            <input type="date" 
                   name="date_debut" 
                   id="date_debut" 
                   value="{{ old('date_debut', isset($evenement) && $evenement->date_debut ? $evenement->date_debut->format('Y-m-d') : '') }}"
                   class="form-input @error('date_debut') error @enderror"
                   required>
            @error('date_debut')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Date de fin -->
        <div class="form-group">
            <label for="date_fin" class="form-label">
                <i class="fas fa-calendar-times"></i>Date de fin
            </label>
            <input type="date" 
                   name="date_fin" 
                   id="date_fin" 
                   value="{{ old('date_fin', isset($evenement) && $evenement->date_fin ? $evenement->date_fin->format('Y-m-d') : '') }}"
                   class="form-input @error('date_fin') error @enderror">
            @error('date_fin')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <!-- Image -->
    <div class="form-group">
        <label for="image" class="form-label">
            <i class="fas fa-image"></i>Image de l'événement
        </label>
        @if(isset($evenement) && $evenement->image)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Image actuelle :</p>
                <img src="{{ asset($evenement->image) }}" alt="Image actuelle" class="h-32 w-32 object-cover rounded-lg" style="border-radius: var(--border-radius);">
            </div>
        @endif
        <input type="file" 
               name="image" 
               id="image" 
               class="form-input @error('image') error @enderror"
               accept="image/jpeg,image/png">
        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG (max 5MB)</p>
        @error('image')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Projet -->
    <div class="form-group">
        <label for="projet_id" class="form-label">
            <i class="fas fa-project-diagram"></i>Projet associé *
        </label>
        <select name="projet_id" 
                id="projet_id" 
                class="form-input @error('projet_id') error @enderror"
                required>
            <option value="">Sélectionnez un projet</option>
            @foreach($projets as $projet)
                <option value="{{ $projet->id }}" 
                    {{ old('projet_id', $evenement->projet_id ?? '') == $projet->id ? 'selected' : '' }}>
                    {{ $projet->nom }}
                </option>
            @endforeach
        </select>
        @error('projet_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- État de l'événement -->
    <div class="form-group">
        <label for="etat_evenement_id" class="form-label">
            <i class="fas fa-tasks"></i>État de l'événement
        </label>
        <select name="etat_evenement_id" 
                id="etat_evenement_id" 
                class="form-input @error('etat_evenement_id') error @enderror">
            <option value="">Sélectionnez un état</option>
            @foreach($etatsEvenement as $etat)
                <option value="{{ $etat->id }}" 
                    {{ old('etat_evenement_id', $evenement->etatCourant->etat_evenement_id ?? '') == $etat->id ? 'selected' : '' }}>
                    {{ $etat->etat }}
                </option>
            @endforeach
        </select>
        @error('etat_evenement_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Niveau d'accès (TypesCompte) -->
    <div class="form-group">
        <label for="niveau_acces" class="form-label">
            <i class="fas fa-shield-alt"></i>Niveau d'accès *
        </label>
        <select name="niveau_acces" 
                id="niveau_acces" 
                class="form-input @error('niveau_acces') error @enderror"
                required>
            <option value="">Sélectionnez un niveau d'accès</option>
            @foreach($typesCompte as $typeCompte)
                <option value="{{ $typeCompte->niveau_acces }}" 
                    {{ old('niveau_acces', $evenement->niveau_acces ?? '') == $typeCompte->niveau_acces ? 'selected' : '' }}>
                    {{ $typeCompte->type }} (Niveau {{ $typeCompte->niveau_acces }})
                </option>
            @endforeach
        </select>
        @error('niveau_acces')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Définit qui peut voir cet événement selon le type de compte</p>
    </div>

    <!-- Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Est Actualité -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-star"></i>Actualité
            </label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="est_actualite" 
                           value="1" 
                           {{ old('est_actualite', $evenement->est_actualite ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Marquer comme actualité</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Les événements marqués comme actualité seront mis en avant</p>
        </div>

        <!-- Archivé -->
        @if(isset($evenement))
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-archive"></i>Statut
            </label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="archived" 
                           value="1" 
                           {{ old('archived', $evenement->archived) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Marquer comme archivé</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Les événements archivés ne sont pas affichés sur le site</p>
        </div>
        @endif
    </div>
</div>