<div class="space-y-6">
    <!-- Titre -->
    <div class="form-group">
        <label for="titre" class="form-label">
            <i class="fas fa-heading"></i>Titre de la réalisation
        </label>
        <input type="text" 
               name="titre" 
               id="titre" 
               value="{{ old('titre', $realisation->titre ?? '') }}" 
               class="form-input @error('titre') error @enderror"
               placeholder="Entrez le titre de la réalisation">
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
                  rows="3"
                  class="form-input @error('description') error @enderror"
                  placeholder="Description de cette réalisation...">{{ old('description', $realisation->description ?? '') }}</textarea>
        @error('description')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Bénéficiaires -->
    <div class="form-group">
        <label for="beneficiaires" class="form-label">
            <i class="fas fa-users"></i>Bénéficiaires
        </label>
        <textarea name="beneficiaires" 
                  id="beneficiaires" 
                  rows="3"
                  class="form-input @error('beneficiaires') error @enderror"
                  placeholder="Liste des bénéficiaires de cette réalisation...">{{ old('beneficiaires', $realisation->beneficiaires ?? '') }}</textarea>
        @error('beneficiaires')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Impacts potentiels -->
    <div class="form-group">
        <label for="impacts_potentiel" class="form-label">
            <i class="fas fa-chart-line"></i>Impacts potentiels
        </label>
        <textarea name="impacts_potentiel" 
                  id="impacts_potentiel" 
                  rows="4"
                  class="form-input @error('impacts_potentiel') error @enderror"
                  placeholder="Description des impacts potentiels de cette réalisation...">{{ old('impacts_potentiel', $realisation->impacts_potentiel ?? '') }}</textarea>
        @error('impacts_potentiel')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Date de réalisation -->
    <div class="form-group">
        <label for="date_realisation" class="form-label">
            <i class="fas fa-calendar-day"></i>Date de réalisation
        </label>
        <input type="date" 
               name="date_realisation" 
               id="date_realisation" 
               value="{{ old('date_realisation', isset($realisation) && $realisation->date_realisation ? $realisation->date_realisation->format('Y-m-d') : '') }}"
               class="form-input @error('date_realisation') error @enderror">
        @error('date_realisation')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Image -->
    <div class="form-group">
        <label for="image" class="form-label">
            <i class="fas fa-image"></i>Image représentative
        </label>
        @if(isset($realisation) && $realisation->image)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Image actuelle :</p>
                <img src="{{ asset($realisation->image) }}" alt="Image actuelle" class="h-32 w-32 object-cover rounded-lg" style="border-radius: var(--border-radius);">
            </div>
        @endif
        <input type="file" 
               name="image" 
               id="image" 
               class="form-input @error('image') error @enderror"
               accept="image/jpeg,image/png,image/jpg,image/gif">
        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF (max 10MB)</p>
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
            <i class="fas fa-project-diagram"></i>Projet associé
        </label>
        <select name="projet_id" 
                id="projet_id" 
                class="form-input @error('projet_id') error @enderror">
            <option value="">Sélectionnez un projet</option>
            @foreach($projets as $projet)
                <option value="{{ $projet->id }}" 
                    {{ old('projet_id', $realisation->projet_id ?? '') == $projet->id ? 'selected' : '' }}>
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
                    {{ old('niveau_acces', $realisation->niveau_acces ?? '') == $typeCompte->niveau_acces ? 'selected' : '' }}>
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
        <p class="text-xs text-gray-500 mt-1">Définit qui peut voir cette réalisation selon le type de compte</p>
    </div>

    <!-- Archivé -->
    @if(isset($realisation))
    <div class="form-group">
        <label class="form-label">
            <i class="fas fa-archive"></i>Statut
        </label>
        <div class="flex items-center space-x-4">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="archived" 
                       value="1" 
                       {{ old('archived', $realisation->archived) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Marquer comme archivée</span>
            </label>
        </div>
        <p class="text-xs text-gray-500 mt-1">Les réalisations archivées ne sont pas affichées sur le site</p>
    </div>
    @endif
</div>