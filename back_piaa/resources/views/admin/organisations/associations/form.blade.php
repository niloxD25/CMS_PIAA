<div class="space-y-6">
    @error('combinaison')
        <p class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ $message }}
        </p>
    @enderror
    <!-- Organisation -->
    <div class="form-group">
        <label for="organisation_id" class="form-label">
            <i class="fas fa-building"></i>Organisation *
        </label>
        <select name="organisation_id" 
                id="organisation_id" 
                class="form-input @error('organisation_id') error @enderror"
                required>
            <option value="">Sélectionner une organisation</option>
            @foreach($organisations as $organisation)
                <option value="{{ $organisation->id }}" 
                    {{ old('organisation_id', $association->organisation_id ?? request('organisation_id')) == $organisation->id ? 'selected' : '' }}>
                    {{ $organisation->nom }}
                </option>
            @endforeach
        </select>
        @error('organisation_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Projet -->
    <div class="form-group">
        <label for="projet_id" class="form-label">
            <i class="fas fa-project-diagram"></i>Projet *
        </label>
        <select name="projet_id" 
                id="projet_id" 
                class="form-input @error('projet_id') error @enderror"
                required>
            <option value="">Sélectionner un projet</option>
            @foreach($projets as $projet)
                <option value="{{ $projet->id }}" 
                    {{ old('projet_id', $association->projet_id ?? '') == $projet->id ? 'selected' : '' }}>
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

    <!-- Type de relation -->
    <div class="form-group">
        <label for="type_relation_id" class="form-label">
            <i class="fas fa-link"></i>Type de relation *
        </label>
        <select name="type_relation_id" 
                id="type_relation_id" 
                class="form-input @error('type_relation_id') error @enderror"
                required>
            <option value="">Sélectionner un type de relation</option>
            @foreach($typesRelation as $type)
                <option value="{{ $type->id }}" 
                    {{ old('type_relation_id', $association->type_relation_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->type }}
                </option>
            @endforeach
        </select>
        @error('type_relation_id')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Aperçu de la sélection -->
    <div id="selectionPreview" class="hidden">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4" style="border-radius: var(--border-radius);">
            <h3 class="text-sm font-medium text-blue-800 mb-2">Aperçu de l'association :</h3>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Organisation :</span> <span id="previewOrganisation">-</span></p>
                <p><span class="font-medium">Projet :</span> <span id="previewProjet">-</span></p>
                <p><span class="font-medium">Type de relation :</span> <span id="previewType">-</span></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const organisationSelect = document.getElementById('organisation_id');
    const projetSelect = document.getElementById('projet_id');
    const typeSelect = document.getElementById('type_relation_id');
    const previewDiv = document.getElementById('selectionPreview');
    const previewOrganisation = document.getElementById('previewOrganisation');
    const previewProjet = document.getElementById('previewProjet');
    const previewType = document.getElementById('previewType');

    function updatePreview() {
        const hasSelection = organisationSelect.value && projetSelect.value && typeSelect.value;
        
        if (hasSelection) {
            previewOrganisation.textContent = organisationSelect.options[organisationSelect.selectedIndex].text;
            previewProjet.textContent = projetSelect.options[projetSelect.selectedIndex].text;
            previewType.textContent = typeSelect.options[typeSelect.selectedIndex].text;
            previewDiv.classList.remove('hidden');
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    organisationSelect.addEventListener('change', updatePreview);
    projetSelect.addEventListener('change', updatePreview);
    typeSelect.addEventListener('change', updatePreview);

    // Initialiser l'aperçu si des valeurs sont présentes
    updatePreview();
});
</script>

<style>
.hidden {
    display: none;
}
</style>