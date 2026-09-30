<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { 
  faFacebookF, 
  faXTwitter, 
  faYoutube, 
  faLinkedinIn,
  faInstagram,
  faTiktok,
  faWhatsapp,
  faTelegram,
  faGithub,
  faDiscord,
  faSlack,
  faReddit,
  faPinterest,
  faSnapchat,
  faSkype
} from '@fortawesome/free-brands-svg-icons';
import { faQuestion } from '@fortawesome/free-solid-svg-icons';

const iconMap = {
  faFacebookF,
  faXTwitter,
  faYoutube,
  faLinkedinIn,
  faInstagram,
  faTiktok,
  faWhatsapp,
  faTelegram,
  faGithub,
  faDiscord,
  faSlack,
  faReddit,
  faPinterest,
  faSnapchat,
  faSkype,
  faQuestion
};

const props = defineProps({
  socialLinks: {
    type: Array,
    required: true,
    default: () => []
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'minimal', 'colored'].includes(value)
  }
});

const sizeClasses = {
  small: 'social-link--small',
  medium: 'social-link--medium',
  large: 'social-link--large'
};

const variantClasses = {
  default: 'social-link--default',
  minimal: 'social-link--minimal',
  colored: 'social-link--colored'
};

// Fonction pour obtenir l'objet icône
const getIcon = (iconName) => {
  return iconMap[iconName] || faQuestion;
};
</script>

<template>
  <div class="social-icons">
    <a 
      v-for="social in socialLinks" 
      :key="social.nom"
      :href="social.pivot.url" 
      :class="[
        'social-link', 
        social.nom.toLowerCase(),
        sizeClasses[size],
        variantClasses[variant]
      ]"
      target="_blank"
      rel="noopener noreferrer"
      :title="social.nom"
    >
      <FontAwesomeIcon :icon="getIcon(social.icone)" class="social-icon" />
    </a>
  </div>
</template>

<style scoped>
.social-icons {
  display: flex;
  gap: 10px;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

/* Tailles */
.social-link--small {
  width: 28px;
  height: 28px;
}

.social-link--medium {
  width: 36px;
  height: 36px;
}

.social-link--large {
  width: 44px;
  height: 44px;
}

/* Variantes */
.social-link--default {
  background: rgba(255, 255, 255, 0.9);
  color: var(--color-primary);
}

.social-link--minimal {
  background: transparent;
  color: currentColor;
  border: 2px solid currentColor;
}

.social-link--colored {
  background: currentColor;
  color: white;
}

/* Styles par défaut pour la variante default */
.social-link--default:hover {
  transform: translateY(-2px) scale(1.1);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.social-link--minimal:hover {
  transform: translateY(-2px);
  background: currentColor;
  color: white;
  border: none;
}

.social-link--colored:hover {
  transform: translateY(-2px);
  opacity: 0.9;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Couleurs spécifiques pour les réseaux sociaux */
.social-link.facebook:hover {
  background: #3b5998;
}

.social-link.twitter:hover {
  background: #1da1f2;
}

.social-link.youtube:hover {
  background: #ff0000;
}

.social-link.linkedin:hover {
  background: #0077b5;
}

.social-link.instagram:hover {
  background: #e4405f;
}

.social-link.tiktok:hover {
  background: #000000;
}

.social-link.whatsapp:hover {
  background: #25d366;
}

.social-link.telegram:hover {
  background: #0088cc;
}

/* Couleur par défaut pour les réseaux sociaux non reconnus */
.social-link:not(.facebook):not(.twitter):not(.youtube):not(.linkedin):not(.instagram):not(.tiktok):not(.whatsapp):not(.telegram):hover {
  background: var(--color-primary);
}

/* Tailles des icônes */
.social-link--small .social-icon {
  font-size: 0.8rem;
}

.social-link--medium .social-icon {
  font-size: 0.9rem;
}

.social-link--large .social-icon {
  font-size: 1.1rem;
}
</style>