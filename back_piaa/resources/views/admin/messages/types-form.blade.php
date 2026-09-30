<div class="space-y-6">
    <!-- Type -->
    <div class="form-group">
        <label for="type" class="form-label">
            <i class="fas fa-tag"></i>Type de message *
        </label>
        <input type="text" 
               name="type" 
               id="type" 
               value="{{ old('type', $type->type ?? '') }}" 
               class="form-input @error('type') error @enderror"
               placeholder="Entrez le type de message"
               required>
        @error('type')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>
</div>