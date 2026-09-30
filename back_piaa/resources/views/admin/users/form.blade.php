<div class="space-y-6">
    <!-- Nom -->
    <div class="form-group">
        <label for="name" class="form-label">
            <i class="fas fa-user"></i>Nom complet *
        </label>
        <input type="text" 
               name="name" 
               id="name" 
               value="{{ old('name', $user->name ?? '') }}" 
               class="form-input @error('name') error @enderror"
               placeholder="Entrez le nom complet de l'utilisateur"
               required>
        @error('name')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Email -->
    <div class="form-group">
        <label for="email" class="form-label">
            <i class="fas fa-envelope"></i>Adresse email *
        </label>
        <input type="email" 
               name="email" 
               id="email" 
               value="{{ old('email', $user->email ?? '') }}" 
               class="form-input @error('email') error @enderror"
               placeholder="Entrez l'adresse email"
               required>
        @error('email')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Mot de passe -->
    <div class="form-group">
        <label for="password" class="form-label">
            <i class="fas fa-lock"></i>Mot de passe {{ !isset($user) ? '*' : '' }}
        </label>
        <input type="password" 
               name="password" 
               id="password" 
               class="form-input @error('password') error @enderror"
               placeholder="{{ isset($user) ? 'Laissez vide pour ne pas modifier' : 'Entrez le mot de passe' }}"
               {{ !isset($user) ? 'required' : '' }}>
        @error('password')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Confirmation du mot de passe -->
    <div class="form-group">
        <label for="password_confirmation" class="form-label">
            <i class="fas fa-lock"></i>Confirmation du mot de passe {{ !isset($user) ? '*' : '' }}
        </label>
        <input type="password" 
               name="password_confirmation" 
               id="password_confirmation" 
               class="form-input"
               placeholder="{{ isset($user) ? 'Laissez vide pour ne pas modifier' : 'Confirmez le mot de passe' }}"
               {{ !isset($user) ? 'required' : '' }}>
    </div>

    <!-- Type de compte -->
    <div class="form-group">
        <label for="type_compte_id" class="form-label">
            <i class="fas fa-user-tag"></i>Type de compte *
        </label>
        <select name="type_compte_id" 
                id="type_compte_id" 
                class="form-input @error('type_compte_id') error @enderror"
                required>
            <option value="">Sélectionnez un type de compte</option>
            @foreach($typesCompte as $typeCompte)
                <option value="{{ $typeCompte->id }}" 
                    {{ old('type_compte_id', $user->type_compte_id ?? '') == $typeCompte->id ? 'selected' : '' }}>
                    {{ $typeCompte->type }} (Niveau {{ $typeCompte->niveau_acces }})
                </option>
            @endforeach
        </select>
        @error('type_compte_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Définit les permissions et le niveau d'accès de l'utilisateur</p>
    </div>

    @if(isset($user))
    <!-- Informations de compte -->
    <div class="bg-gray-50 p-4 rounded-lg" style="border-radius: var(--border-radius);">
        <h3 class="text-sm font-medium text-gray-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>Informations du compte
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="text-gray-600">Date de création :</label>
                <p class="text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                <label class="text-gray-600">Dernière modification :</label>
                <p class="text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                <label class="text-gray-600">Email vérifié :</label>
                <p class="text-gray-900">
                    @if($user->email_verified_at)
                        <span class="text-green-600">Oui ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                    @else
                        <span class="text-red-600">Non</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif
</div>