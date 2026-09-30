<div class="space-y-6">
    <!-- Témoin -->
    <div class="form-group">
        <label for="temoin" class="form-label">
            <i class="fas fa-user"></i>Nom de la personne *
        </label>
        <input type="text" 
               name="temoin" 
               id="temoin" 
               value="{{ old('temoin', $temoignage->temoin ?? '') }}" 
               class="form-input @error('temoin') error @enderror"
               placeholder="Entrez le nom de la personne"
               required>
        @error('temoin')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Résidence -->
    <div class="form-group">
        <label for="residence" class="form-label">
            <i class="fas fa-home"></i>Résidence ou Statut
        </label>
        <input type="text" 
               name="residence" 
               id="residence" 
               value="{{ old('residence', $temoignage->residence ?? '') }}" 
               class="form-input @error('residence') error @enderror"
               placeholder="Lieu de résidence ou statut">
        @error('residence')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Contenu -->
    <div class="form-group">
        <label for="contenu" class="form-label">
            <i class="fas fa-comment"></i>Témoignage ou mots *
        </label>
        <textarea name="contenu" 
                  id="contenu" 
                  rows="6"
                  class="form-input @error('contenu') error @enderror"
                  placeholder="Témoignage ou mots de la personne..."
                  required>{{ old('contenu', $temoignage->contenu ?? '') }}</textarea>
        @error('contenu')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Image -->
    <div class="form-group">
        <label for="image" class="form-label">
            <i class="fas fa-image"></i>Photo de la personne
        </label>
        @if(isset($temoignage) && $temoignage->image)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Photo actuelle :</p>
                <img src="{{ asset($temoignage->image) }}" alt="Photo actuelle" class="h-20 w-20 object-cover rounded-full" style="border-radius: 50%;">
            </div>
        @endif
        <input type="file" 
               name="image" 
               id="image" 
               class="form-input @error('image') error @enderror"
               accept="image/jpeg,image/png">
        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG (max 10MB)</p>
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
            <option value="">Aucun projet</option>
            @foreach($projets as $projet)
                <option value="{{ $projet->id }}" 
                    {{ old('projet_id', $temoignage->projet_id ?? '') == $projet->id ? 'selected' : '' }}>
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

    <!-- Options -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Est Important -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-star"></i>Importance
            </label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="est_important" 
                           value="1" 
                           {{ old('est_important', $temoignage->est_important ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Marquer comme important</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Les témoignages importants seront mis en avant</p>
        </div>

        <!-- Archivé -->
        @if(isset($temoignage))
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-archive"></i>Statut
            </label>
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="archived" 
                           value="1" 
                           {{ old('archived', $temoignage->archived) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Marquer comme archivé</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Les témoignages archivés ne sont pas affichés sur le site</p>
        </div>
        @endif
    </div>
</div>