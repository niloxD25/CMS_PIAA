<div class="space-y-6">
    <!-- Nom -->
    <div class="form-group">
        <label for="nom" class="form-label">
            <i class="fas fa-tag"></i>Nom du projet *
        </label>
        <input type="text" 
               name="nom" 
               id="nom" 
               value="{{ old('nom', $projet->nom ?? '') }}" 
               class="form-input @error('nom') error @enderror"
               placeholder="Entrez le nom du projet"
               required>
        @error('nom')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Acronymes -->
    <div class="form-group">
        <label for="acronymes" class="form-label">
            <i class="fas fa-abacus"></i>Acronymes
        </label>
        <input type="text" 
               name="acronymes" 
               id="acronymes" 
               value="{{ old('acronymes', $projet->acronymes ?? '') }}" 
               class="form-input @error('acronymes') error @enderror"
               placeholder="Ex: PIAA, CARE, UE, ..."
               maxlength="30">
        <p class="text-xs text-gray-500 mt-1">Maximum 30 caractères</p>
        @error('acronymes')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Devise -->
    <div class="form-group">
        <label for="devise" class="form-label">
            <i class="fas fa-quote-left"></i>Devise
        </label>
        <textarea name="devise" 
                  id="devise" 
                  rows="2"
                  class="form-input @error('devise') error @enderror"
                  placeholder="La devise ou slogan du projet...">{{ old('devise', $projet->devise ?? '') }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Maximum 255 caractères</p>
        @error('devise')
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
                  placeholder="Décrivez le projet...">{{ old('description', $projet->description ?? '') }}</textarea>
        @error('description')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Informations de Contact -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Email -->
        <div class="form-group">
            <label for="email" class="form-label">
                <i class="fas fa-envelope"></i>Email
            </label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="{{ old('email', $projet->email ?? '') }}" 
                   class="form-input @error('email') error @enderror"
                   placeholder="email@exemple.com"
                   maxlength="200">
            <p class="text-xs text-gray-500 mt-1">Adresse email valide</p>
            @error('email')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Téléphone -->
        <div class="form-group">
            <label for="telephone" class="form-label">
                <i class="fas fa-phone"></i>Téléphone
            </label>
            <input type="text" 
                   name="telephone" 
                   id="telephone" 
                   value="{{ old('telephone', $projet->telephone ?? '') }}" 
                   class="form-input @error('telephone') error @enderror"
                   placeholder="+33 1 23 45 67 89"
                   maxlength="20">
            <p class="text-xs text-gray-500 mt-1">Numéro de téléphone</p>
            @error('telephone')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <!-- Adresse -->
    <div class="form-group">
        <label for="adresse" class="form-label">
            <i class="fas fa-map-marker-alt"></i>Adresse
        </label>
        <textarea name="adresse" 
                  id="adresse" 
                  rows="2"
                  class="form-input @error('adresse') error @enderror"
                  placeholder="Adresse complète du projet..."
                  maxlength="200">{{ old('adresse', $projet->adresse ?? '') }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Maximum 200 caractères</p>
        @error('adresse')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Logo -->
    <div class="form-group">
        <label for="logo" class="form-label">
            <i class="fas fa-image"></i>Logo
        </label>
        @if(isset($projet) && $projet->logo)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Logo actuel :</p>
                <img src="{{ asset($projet->logo) }}" alt="Logo actuel" class="h-20 w-20 object-cover rounded-lg" style="border-radius: var(--border-radius);">
            </div>
        @endif
        <input type="file" 
               name="logo" 
               id="logo" 
               class="form-input @error('logo') error @enderror"
               accept="image/jpeg,image/png,image/gif,image/webp">
        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF, WebP (max 5MB)</p>
        @error('logo')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Date et Durée -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Début -->
        <div class="form-group">
            <label for="debut" class="form-label">
                <i class="fas fa-calendar-alt"></i>Date de début
            </label>
            <input type="date" 
                   name="debut" 
                   id="debut" 
                   value="{{ old('debut', isset($projet) && $projet->debut ? $projet->debut->format('Y-m-d') : '') }}"
                   class="form-input @error('debut') error @enderror">
            @error('debut')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Durée -->
        <div class="form-group">
            <label for="duree_annee" class="form-label">
                <i class="fas fa-clock"></i>Durée (années)
            </label>
            <input type="number" 
                   name="duree_annee" 
                   id="duree_annee" 
                   value="{{ old('duree_annee', $projet->duree_annee ?? '') }}" 
                   min="0"
                   class="form-input @error('duree_annee') error @enderror"
                   placeholder="Durée en années">
            @error('duree_annee')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>