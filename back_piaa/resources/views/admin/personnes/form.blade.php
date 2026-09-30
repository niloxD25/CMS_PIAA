<div class="space-y-6">
    <!-- Informations personnelles -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nom -->
        <div class="form-group">
            <label for="nom" class="form-label">
                <i class="fas fa-user"></i>Nom *
            </label>
            <input type="text" 
                   name="nom" 
                   id="nom" 
                   value="{{ old('nom', $personne->nom ?? '') }}" 
                   class="form-input @error('nom') error @enderror"
                   placeholder="Entrez le nom"
                   required>
            @error('nom')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Prénom -->
        <div class="form-group">
            <label for="prenom" class="form-label">
                <i class="fas fa-user"></i>Prénom
            </label>
            <input type="text" 
                   name="prenom" 
                   id="prenom" 
                   value="{{ old('prenom', $personne->prenom ?? '') }}" 
                   class="form-input @error('prenom') error @enderror"
                   placeholder="Entrez le prénom">
            @error('prenom')
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <!-- Poste -->
    <div class="form-group">
        <label for="poste" class="form-label">
            <i class="fas fa-briefcase"></i>Poste ou Fonction
        </label>
        <input type="text" 
               name="poste" 
               id="poste" 
               value="{{ old('poste', $personne->poste ?? '') }}" 
               class="form-input @error('poste') error @enderror"
               placeholder="Ex: Chef de projet, Développeur, Designer...">
        @error('poste')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Photo -->
    <div class="form-group">
        <label for="photo" class="form-label">
            <i class="fas fa-camera"></i>Photo de profil
        </label>
        
        @if(isset($personne) && $personne->photo)
            <div class="mb-4 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset($personne->photo) }}" 
                             alt="Photo actuelle de {{ $personne->prenom }} {{ $personne->nom }}" 
                             class="h-20 w-20 object-cover rounded-full" 
                             style="border-radius: 50%;">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Photo actuelle</p>
                            <p class="text-xs text-gray-500">Cliquez sur "Choisir un fichier" pour la remplacer</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <input type="file" 
               name="photo" 
               id="photo" 
               class="form-input @error('photo') error @enderror"
               accept="image/jpeg,image/png,image/gif">
        <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF (max 10MB). Taille recommandée : 400x400px</p>
        @error('photo')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror

        <!-- Champ caché pour suppression de photo -->
        @if(isset($personne) && $personne->photo)
            <input type="hidden" name="remove_photo" id="remove_photo" value="0">
        @endif
    </div>

    <!-- Aperçu de la nouvelle photo -->
    <div id="photoPreview" class="hidden mb-4 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
        <p class="text-sm font-medium text-gray-700 mb-2">Aperçu de la nouvelle photo :</p>
        <img id="previewImage" class="h-20 w-20 object-cover rounded-full" style="border-radius: 50%;">
    </div>
</div>

@if(isset($personne) && $personne->projets->count() > 0)
<!-- Section des projets associés (en lecture seule) -->
<div class="mt-8 pt-6 border-t border-gray-200">
    <h3 class="text-lg font-medium text-gray-900 mb-4">
        <i class="fas fa-project-diagram mr-2"></i>Projets associés
    </h3>
    
    <div class="bg-gray-50 rounded-lg p-4" style="border-radius: var(--border-radius);">
        <div class="space-y-3">
            @foreach($personne->projets as $projet)
            <div class="flex items-center justify-between py-2 px-3 bg-white rounded border border-gray-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-folder text-blue-500"></i>
                    <div>
                        <p class="font-medium text-gray-900">{{ $projet->nom }}</p>
                        <p class="text-sm text-gray-500">
                            Participation : 
                            {{ $projet->pivot->date_debut_participation ? \Carbon\Carbon::parse($projet->pivot->date_debut_participation)->format('d/m/Y') : 'Non définie' }}
                            @if($projet->pivot->date_fin_participation)
                                - {{ \Carbon\Carbon::parse($projet->pivot->date_fin_participation)->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.projets.participants.index', $projet->id) }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Voir le projet
                </a>
            </div>
            @endforeach
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500">
                Pour modifier les participations aux projets, utilisez la gestion des participants depuis la page de chaque projet.
            </p>
        </div>
    </div>
</div>
@endif

<script>
// Aperçu de la nouvelle photo
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

// Confirmation de suppression de photo
function confirmRemovePhoto() {
    if (confirm('Êtes-vous sûr de vouloir supprimer la photo de profil ?')) {
        document.getElementById('remove_photo').value = '1';
        // Masquer l'affichage de la photo actuelle
        const photoDisplay = document.querySelector('.mb-4.p-4.bg-gray-50');
        if (photoDisplay) {
            photoDisplay.style.display = 'none';
        }
        // Afficher un message de confirmation
        alert('La photo sera supprimée lors de la mise à jour. Cliquez sur "Mettre à jour" pour confimer.');
    }
}

// Validation des champs obligatoires
function validateForm() {
    const nom = document.getElementById('nom').value.trim();
    const prenom = document.getElementById('prenom').value.trim();
    
    if (!nom) {
        alert('Le nom est obligatoire.');
        return false;
    }
    
    return true;
}
</script>

<style>
.rounded-full {
    border-radius: 50%;
}

.hidden {
    display: none;
}
</style>