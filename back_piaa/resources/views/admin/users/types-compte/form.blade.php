<div class="space-y-6">
    <!-- Type -->
    <div class="form-group">
        <label for="type" class="form-label">
            <i class="fas fa-user-tag"></i>Type de compte *
        </label>
        <input type="text" 
               name="type" 
               id="type" 
               value="{{ old('type', $typeCompte->type ?? '') }}" 
               class="form-input @error('type') error @enderror"
               placeholder="Ex: Administrateur, Éditeur, Lecteur..."
               required>
        @error('type')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Niveau d'accès -->
    <div class="form-group">
        <label for="niveau_acces" class="form-label">
            <i class="fas fa-shield-alt"></i> Niveau d'accès *
        </label>
        <input type="number"
            name="niveau_acces"
            id="niveau_acces"
            min="2"
            max="999"
            value="{{ old('niveau_acces', $typeCompte->niveau_acces ?? '') }}"
            class="form-input @error('niveau_acces') error @enderror"
            required
            placeholder="Entrez un niveau entre 2 et 999">
        @error('niveau_acces')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">
            Le niveau d'accès détermine les permissions des utilisateurs.
        </p>
    </div>

    @if(isset($typeCompte))
    <!-- Informations -->
    <div class="bg-gray-50 p-4 rounded-lg" style="border-radius: var(--border-radius);">
        <h3 class="text-sm font-medium text-gray-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>Informations
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="text-gray-600">Utilisateurs associés :</label>
                <p class="text-gray-900">{{ $typeCompte->users_count ?? 0 }} utilisateur(s)</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Guide des niveaux d'accès -->
    <div class="bg-blue-50 p-4 rounded-lg" style="border-radius: var(--border-radius);">
        <h3 class="text-sm font-medium text-blue-900 mb-2">
            <i class="fas fa-lightbulb mr-2"></i>Guide des niveaux d'accès
        </h3>
        <div class="text-xs text-blue-800 space-y-1">
            <p><strong>Niveaux 0 :</strong> Accès administrateur - Toutes les permissions</p>
            <p><strong>Niveaux 1 :</strong> Accès avancé - Content Manager</p>
            <p><strong>Niveaux 1 < 999 :</strong> Autres accès - Consultation en fonction des Gestions du content manager</p>
            <p><strong>Niveaux 999 :</strong> Accès basique - Consultation des données publiques</p>
        </div>
    </div>
</div>