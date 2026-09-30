<div class="space-y-6">
    <!-- Titre -->
    <div class="form-group">
        <label for="titre" class="form-label">
            <i class="fas fa-heading"></i>Titre de l'annonce *
        </label>
        <input type="text" 
               name="titre" 
               id="titre" 
               value="{{ old('titre', $annonce->titre ?? '') }}" 
               class="form-input @error('titre') error @enderror"
               placeholder="Entrez le titre de l'annonce"
               required>
        @error('titre')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Contenu -->
    <div class="form-group">
        <label for="contenu" class="form-label">
            <i class="fas fa-align-left"></i>Contenu
        </label>
        <textarea name="contenu" 
                  id="contenu" 
                  rows="6"
                  class="form-input @error('contenu') error @enderror"
                  placeholder="Contenu de l'annonce...">{{ old('contenu', $annonce->contenu ?? '') }}</textarea>
        @error('contenu')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Date d'affichage -->
    <div class="form-group">
        <label for="date_affichage" class="form-label">
            <i class="fas fa-calendar-alt"></i>Date d'affichage
        </label>
        <input type="datetime-local" 
               name="date_affichage" 
               id="date_affichage" 
               value="{{ old('date_affichage', isset($annonce) && $annonce->date_affichage ? $annonce->date_affichage->format('Y-m-d\TH:i') : '') }}"
               class="form-input @error('date_affichage') error @enderror">
        <p class="text-xs text-gray-500 mt-1">Si non définie, l'annonce ne sera pas affichée automatiquement</p>
        @error('date_affichage')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Nombre de jours d'affichage -->
    <div class="form-group">
        <label for="nbr_jours_affichage" class="form-label">
            <i class="fas fa-clock"></i>Nombre de jours d'affichage
        </label>
        <input type="number" 
               name="nbr_jours_affichage" 
               id="nbr_jours_affichage" 
               value="{{ old('nbr_jours_affichage', $annonce->nbr_jours_affichage ?? '') }}" 
               min="1"
               class="form-input @error('nbr_jours_affichage') error @enderror"
               placeholder="Nombre de jours">
        <p class="text-xs text-gray-500 mt-1">Durée pendant laquelle l'annonce sera affichée (minimum 1 jour)</p>
        @error('nbr_jours_affichage')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>
</div>