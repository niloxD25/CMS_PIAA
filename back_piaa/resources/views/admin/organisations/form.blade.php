<div class="space-y-6">
    <!-- Nom -->
    <div class="form-group">
        <label for="nom" class="form-label">
            <i class="fas fa-building"></i>Nom de l'organisation *
        </label>
        <input type="text" 
               name="nom" 
               id="nom" 
               value="{{ old('nom', $organisation->nom ?? '') }}" 
               class="form-input @error('nom') error @enderror"
               placeholder="Entrez le nom de l'organisation"
               required>
        @error('nom')
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
                  placeholder="Décrivez l'organisation...">{{ old('description', $organisation->description ?? '') }}</textarea>
        @error('description')
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
        @if(isset($organisation) && $organisation->logo)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Logo actuel :</p>
                <img src="{{ asset($organisation->logo) }}" alt="Logo actuel" class="h-20 w-20 object-cover rounded-lg" style="border-radius: var(--border-radius);">
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

    <!-- Lien -->
    <div class="form-group">
        <label for="lien" class="form-label">
            <i class="fas fa-link"></i>Site web
        </label>
        <input type="url" 
               name="lien" 
               id="lien" 
               value="{{ old('lien', $organisation->lien ?? '') }}" 
               class="form-input @error('lien') error @enderror"
               placeholder="https://...">
        @error('lien')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>
</div>