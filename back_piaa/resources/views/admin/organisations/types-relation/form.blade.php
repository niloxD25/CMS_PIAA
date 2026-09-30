<div class="space-y-6">
    <!-- Type -->
    <div class="form-group">
        <label for="type" class="form-label">
            <i class="fas fa-tag"></i>Type de relation *
        </label>
        <input type="text" 
               name="type" 
               id="type" 
               value="{{ old('type', $type->type ?? '') }}" 
               class="form-input @error('type') error @enderror"
               placeholder="Type de relation"
               required>
        @error('type')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Aperçu -->
    <div id="typePreview" class="hidden">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4" style="border-radius: var(--border-radius);">
            <h3 class="text-sm font-medium text-blue-800 mb-2">Aperçu :</h3>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5" id="previewType">
                    Type
                </span>
                <span class="text-sm text-gray-600">sera affiché comme ceci dans les listes</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInput = document.getElementById('type');
    const previewDiv = document.getElementById('typePreview');
    const previewType = document.getElementById('previewType');

    function updatePreview() {
        const typeValue = typeInput.value.trim();
        
        if (typeValue) {
            previewType.textContent = typeValue;
            previewDiv.classList.remove('hidden');
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    typeInput.addEventListener('input', updatePreview);

    // Initialiser l'aperçu si une valeur est présente
    updatePreview();
});
</script>

<style>
.hidden {
    display: none;
}
</style>