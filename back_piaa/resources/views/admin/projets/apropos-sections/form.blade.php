<div class="space-y-6">
    <!-- Titre -->
    <div class="form-group">
        <label for="titre" class="form-label">
            <i class="fas fa-heading"></i>Titre de la section
        </label>
        <input type="text" 
               name="titre" 
               id="titre" 
               value="{{ old('titre', $section->titre ?? '') }}" 
               class="form-input @error('titre') error @enderror"
               placeholder="Entrez le titre de la section"
               maxlength="255">
        @error('titre')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Sous-titre -->
    <div class="form-group">
        <label for="sous_titre" class="form-label">
            <i class="fas fa-subscript"></i>Sous-titre
        </label>
        <input type="text" 
               name="sous_titre" 
               id="sous_titre" 
               value="{{ old('sous_titre', $section->sous_titre ?? '') }}" 
               class="form-input @error('sous_titre') error @enderror"
               placeholder="Entrez un sous-titre"
               maxlength="255">
        <p class="text-xs text-gray-500 mt-1">Maximum 255 caractères</p>
        @error('sous_titre')
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
                  placeholder="Décrivez la section...">{{ old('description', $section->description ?? '') }}</textarea>
        @error('description')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Ordre -->
    <div class="form-group">
        <label for="ordre" class="form-label">
            <i class="fas fa-sort-numeric-down"></i>Ordre d'affichage
        </label>
        <input type="number" 
               name="ordre" 
               id="ordre" 
               value="{{ old('ordre', $section->ordre ?? '') }}" 
               class="form-input @error('ordre') error @enderror"
               placeholder="Entrez l'ordre d'affichage (1, 2, 3...)"
               min="1">
        <p class="text-xs text-gray-500 mt-1">
            Laissez vide pour ajouter automatiquement à la fin. Plus le nombre est petit, plus la section apparaîtra haut.
        </p>
        @error('ordre')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Image -->
    <div class="form-group">
        <label for="image" class="form-label">
            <i class="fas fa-image"></i>Image illustrative
        </label>
        
        @if(isset($section) && $section->image)
            <div class="mb-4 p-4 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                <p class="text-sm font-medium text-gray-700 mb-2">Image actuelle :</p>
                <div class="flex items-start space-x-4">
                    <img src="{{ asset($section->image) }}" 
                         alt="Image de la section {{ $section->titre }}" 
                         class="h-32 w-32 object-cover rounded-lg" 
                         style="border-radius: var(--border-radius);">
                    <div class="flex-1">
                        <div class="mb-2">
                            <button type="button" onclick="showImageModal('{{ asset($section->image) }}', '{{ $section->titre }}')" 
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-search mr-1"></i>Agrandir l'image
                            </button>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="remove_image" 
                                   id="remove_image" 
                                   class="h-4 w-4 text-red-600 border-gray-300 rounded">
                            <label for="remove_image" class="ml-2 text-sm text-red-600">
                                <i class="fas fa-trash-alt mr-1"></i>Supprimer cette image
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <input type="file" 
               name="image" 
               id="image" 
               class="form-input @error('image') error @enderror"
               accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
        <p class="text-xs text-gray-500 mt-1">
            Formats acceptés : JPG, PNG, GIF, WebP, SVG (max 10MB). 
            @if(isset($section) && $section->image)
                Sélectionnez une nouvelle image pour remplacer l'actuelle.
            @endif
        </p>
        @error('image')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
        
        <!-- Aperçu de l'image avant upload -->
        <div id="imagePreview" class="mt-3 hidden">
            <p class="text-sm font-medium text-gray-700 mb-2">Aperçu :</p>
            <img id="previewImage" class="h-32 w-32 object-cover rounded-lg" style="border-radius: var(--border-radius);">
        </div>
    </div>
</div>

<!-- Modal pour agrandir l'image -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-auto" style="border-radius: var(--border-radius);">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 id="modalTitle" class="text-lg font-semibold"></h3>
            <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <img id="modalImage" class="w-full h-auto rounded" style="border-radius: var(--border-radius);">
        </div>
    </div>
</div>

<script>
// Gestion de l'aperçu de l'image
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.classList.add('hidden');
    }
});

// Gestion de la suppression de l'image
if (document.getElementById('remove_image')) {
    document.getElementById('remove_image').addEventListener('change', function() {
        const imageInput = document.getElementById('image');
        if (this.checked) {
            imageInput.disabled = true;
        } else {
            imageInput.disabled = false;
        }
    });
}

// Modal pour agrandir l'image
function showImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalTitle').textContent = 'Image : ' + title;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Fermer le modal avec ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Fermer le modal en cliquant à l'extérieur
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target.id === 'imageModal') {
        closeImageModal();
    }
});
</script>

<style>
#imageModal img {
    max-height: 70vh;
    object-fit: contain;
}
</style>