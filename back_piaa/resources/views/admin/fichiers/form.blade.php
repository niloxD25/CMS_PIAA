<div class="space-y-6">
    <!-- Fichier -->
    <div class="form-group">
        <label for="fichier" class="form-label">
            <i class="fas fa-file-upload"></i>Fichier {{ isset($fichier) ? '(Laisser vide pour conserver le fichier actuel)' : '*' }}
        </label>
        @if(isset($fichier) && $fichier->chemin)
            <div class="mb-3 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Fichier actuel :</p>
                <div class="flex items-center space-x-3">
                    @php
                        $fileIcon = App\Models\Fichiers::getFileIconStatic($fichier->type_fichier);
                        $fileIconClass = App\Models\Fichiers::getFileIconClassStatic($fichier->type_fichier);
                    @endphp

                    <div class="file-icon {{ $fileIconClass }}">
                        <i class="{{ $fileIcon }}"></i>
                    </div>
                    <div>
                        @php
                            $fileSize = App\Models\Fichiers::getFileSizeStatic($fichier->chemin);
                        @endphp
                        
                        <p class="text-sm font-medium text-gray-900">{{ $fichier->titre ?? basename($fichier->chemin) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ strtoupper($fichier->type_fichier) }} • 
                            {{ $fileSize }} • 
                            Uploadé le {{ $fichier->date_upload ? $fichier->date_upload->format('d/m/Y') : $fichier->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <input type="file" 
               name="fichier" 
               id="fichier" 
               class="form-input @error('fichier') error @enderror"
               {{ !isset($fichier) ? 'required' : '' }}>
        <p class="text-xs text-gray-500 mt-1">Taille maximale : 10MB. Formats acceptés : tous types de fichiers</p>
        @error('fichier')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Titre -->
    <div class="form-group">
        <label for="titre" class="form-label">
            <i class="fas fa-heading"></i>Titre du fichier
        </label>
        <input type="text" 
               name="titre" 
               id="titre" 
               value="{{ old('titre', $fichier->titre ?? '') }}" 
               class="form-input @error('titre') error @enderror"
               placeholder="Titre du fichier (optionnel)">
        @error('titre')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Si vide, le nom du fichier sera utilisé</p>
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
                  placeholder="Description du fichier...">{{ old('description', $fichier->description ?? '') }}</textarea>
        @error('description')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Type d'entité -->
    <div class="form-group">
        <label for="entite_type_id" class="form-label">
            <i class="fas fa-tags"></i>Type d'entité *
        </label>
        <select name="entite_type_id" 
                id="entite_type_id" 
                class="form-input @error('entite_type_id') error @enderror"
                required>
            <option value="">Sélectionnez un type d'entité</option>
            @foreach($entitesFichier as $entite)
                <option value="{{ $entite->id }}" 
                    {{ old('entite_type_id', $fichier->entite_type_id ?? '') == $entite->id ? 'selected' : '' }}>
                    {{ $entite->nom }}
                </option>
            @endforeach
        </select>
        @error('entite_type_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- ID de l'entité -->
    <div class="form-group">
        <label for="entite_id" class="form-label">
            <i class="fas fa-hashtag"></i>ID de l'entité
        </label>
        <input type="number" 
               name="entite_id" 
               id="entite_id" 
               value="{{ old('entite_id', $fichier->entite_id ?? '') }}" 
               class="form-input @error('entite_id') error @enderror"
               placeholder="ID de l'entité associée (optionnel)"
               min="1">
        @error('entite_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">ID de l'entité spécifique associée à ce fichier (vide si documentation, plan de repérage et interview)</p>
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
                    {{ old('projet_id', $fichier->projet_id ?? '') == $projet->id ? 'selected' : '' }}>
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

    <!-- Niveau d'accès -->
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
                    {{ old('niveau_acces', $fichier->niveau_acces ?? '') == $typeCompte->niveau_acces ? 'selected' : '' }}>
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
        <p class="text-xs text-gray-500 mt-1">Définit qui peut accéder à ce fichier</p>
    </div>

    <!-- Archivé -->
    @if(isset($fichier))
    <div class="form-group">
        <label class="form-label">
            <i class="fas fa-archive"></i>Statut
        </label>
        <div class="flex items-center space-x-4">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="archived" 
                       value="1" 
                       {{ old('archived', $fichier->archived) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Marquer comme archivé</span>
            </label>
        </div>
        <p class="text-xs text-gray-500 mt-1">Les fichiers archivés ne sont pas accessibles publiquement</p>
    </div>
    @endif
</div>

<style>
.file-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.file-icon.pdf { background: #fee2e2; color: #dc2626; }
.file-icon.doc { background: #dbeafe; color: #1d4ed8; }
.file-icon.docx { background: #dbeafe; color: #1d4ed8; }
.file-icon.xls { background: #dcfce7; color: #16a34a; }
.file-icon.xlsx { background: #dcfce7; color: #16a34a; }
.file-icon.jpg { background: #fef3c7; color: #d97706; }
.file-icon.jpeg { background: #fef3c7; color: #d97706; }
.file-icon.png { background: #fef3c7; color: #d97706; }
.file-icon.gif { background: #fef3c7; color: #d97706; }
.file-icon.zip { background: #f3e8ff; color: #7c3aed; }
.file-icon.rar { background: #f3e8ff; color: #7c3aed; }
.file-icon.txt { background: #f3f4f6; color: #6b7280; }
.file-icon.default { background: #f3f4f6; color: #6b7280; }
</style>