<div class="space-y-6">
    <!-- Nom -->
    <div class="form-group">
        <label for="nom" class="form-label">
            <i class="fas fa-tag"></i>Nom du réseau social *
        </label>
        <input type="text" 
               name="nom" 
               id="nom" 
               value="{{ old('nom', $reseauSocial->nom ?? '') }}" 
               class="form-input @error('nom') error @enderror"
               placeholder="Ex: Facebook, Twitter, LinkedIn..."
               required>
        @error('nom')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Icône -->
    <div class="form-group">
        <label for="icone" class="form-label">
            <i class="fas fa-icons"></i>Nom de l'icône Font Awesome *
        </label>
        <input type="text" 
               name="icone" 
               id="icone" 
               value="{{ old('icone', $reseauSocial->icone ?? '') }}" 
               class="form-input @error('icone') error @enderror"
               placeholder="Ex: faLinkedinIn, faYoutube, faXTwitter, faFacebookF..."
               required>
        <p class="text-xs text-gray-500 mt-1">
            Utilisez les noms d'icônes Font Awesome compatibles avec Vue.js. Ex: "faLinkedinIn" pour LinkedIn, "faYoutube" pour YouTube
        </p>
        @error('icone')
            <p class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Aperçu de l'icône -->
    <div class="form-group">
        <label class="form-label">Aperçu de l'icône</label>
        <div class="p-4 bg-gray-50 rounded-lg flex items-center justify-center" style="border-radius: var(--border-radius);">
            <div id="iconePreview" class="social-icon-preview">
                @if(isset($reseauSocial) && $reseauSocial->icone)
                    <div class="icon-preview-wrapper" data-icon="{{ $reseauSocial->icone }}">
                        <!-- L'icône sera générée par JavaScript -->
                    </div>
                @else
                    <div class="icon-preview-wrapper" data-icon="faQuestion">
                        <!-- Icône par défaut -->
                    </div>
                @endif
            </div>
            <span id="iconeText" class="ml-3 text-gray-500">
                {{ isset($reseauSocial) ? $reseauSocial->icone : 'Aperçu' }}
            </span>
        </div>
    </div>
</div>

<script>
// Mapping des icônes Font Awesome compatibles
const iconMapping = {
    'faFacebookF': 'fab fa-facebook-f',
    'faXTwitter': 'fab fa-x-twitter', 
    'faYoutube': 'fab fa-youtube',
    'faLinkedinIn': 'fab fa-linkedin-in',
    'faInstagram': 'fab fa-instagram',
    'faTiktok': 'fab fa-tiktok',
    'faWhatsapp': 'fab fa-whatsapp',
    'faTelegram': 'fab fa-telegram',
    'faGithub': 'fab fa-github',
    'faDiscord': 'fab fa-discord',
    'faSlack': 'fab fa-slack',
    'faReddit': 'fab fa-reddit',
    'faPinterest': 'fab fa-pinterest',
    'faSnapchat': 'fab fa-snapchat',
    'faSkype': 'fab fa-skype',
    'faQuestion': 'fas fa-question'
};

// Aperçu de l'icône en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const iconeInput = document.getElementById('icone');
    const preview = document.getElementById('iconePreview');
    const text = document.getElementById('iconeText');
    
    function updatePreview(iconName) {
        const wrapper = preview.querySelector('.icon-preview-wrapper');
        if (!wrapper) {
            preview.innerHTML = '<div class="icon-preview-wrapper"></div>';
        }
        
        const currentWrapper = preview.querySelector('.icon-preview-wrapper');
        const normalizedIconName = iconName.trim();
        
        // Obtenir la classe Font Awesome correspondante
        const faClass = iconMapping[normalizedIconName] || iconMapping['faQuestion'];
        
        currentWrapper.innerHTML = `<i class="${faClass} text-2xl text-gray-600"></i>`;
        currentWrapper.setAttribute('data-icon', normalizedIconName);
        
        if (iconName) {
            text.textContent = normalizedIconName;
        } else {
            text.textContent = 'Aperçu';
        }
    }
    
    // Initialiser l'aperçu
    const initialIcon = "{{ isset($reseauSocial) ? $reseauSocial->icone : '' }}";
    if (initialIcon) {
        updatePreview(initialIcon);
    } else {
        updatePreview('faQuestion');
    }
    
    // Mettre à jour en temps réel
    if (iconeInput) {
        iconeInput.addEventListener('input', function(e) {
            updatePreview(e.target.value);
        });
        
        // Suggestions automatiques
        iconeInput.addEventListener('focus', function() {
            if (!this.getAttribute('data-suggestions-shown')) {
                this.setAttribute('data-suggestions-shown', 'true');
                // Vous pouvez ajouter une liste déroulante de suggestions ici si besoin
            }
        });
    }
});

// Fonction utilitaire pour obtenir la classe Font Awesome à partir du nom Vue.js
function getFontAwesomeClass(vueIconName) {
    return iconMapping[vueIconName] || iconMapping['faQuestion'];
}
</script>

<style>
.social-icon-preview {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
}

.icon-preview-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

/* Style pour la liste des icônes disponibles (optionnel) */
.icons-suggestions {
    position: absolute;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.icons-suggestions div {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.icons-suggestions div:hover {
    background: #f9fafb;
}

.icons-suggestions div:last-child {
    border-bottom: none;
}
</style>