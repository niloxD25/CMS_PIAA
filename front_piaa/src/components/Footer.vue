<script setup>
import SocialIcons from './SocialIcons.vue';
import { computed } from 'vue';

const props = defineProps({
  socialLinks: {
    type: Array,
    required: true,
    default: () => []
  },
  projetAdress: {
    type: String,
    default: 'Adresse non définie'
  },
  projetEmail: {
    type: String,
    default: 'contact@exemple.com'
  },
  projetPhone: {
    type: String,
    default: '+00 000 00 00'
  },
  projetName: {
    type: String,
    default: null
  },
});

const mapsUrl = computed(() => {
  const queryParts = [];

  if (props.projetName) queryParts.push(props.projetName);
  if (props.projetAdress) queryParts.push(props.projetAdress);

  const finalQuery = encodeURIComponent(queryParts.join(' '));

  return `https://www.google.com/maps/search/?api=1&query=${finalQuery}`;
});
</script>

<template>
  <footer class="footer-wrapper">
    <div class="footer-content">
      
      <div class="footer-grid">
        
        <div class="footer-column brand-column">
          <p class="funding-text">
            Le présent document a été élaboré avec l'aide financière de l'<strong>Union européenne</strong> 
            et de l'<strong>Agence Française de Développement</strong>. Les opinions exprimées 
            ne reflètent pas nécessairement la position officielle de ces institutions.
          </p>
          
          <div class="social-container">
            <SocialIcons 
              :social-links="socialLinks" 
              size="medium"
              variant="default"
              class="social-icons-custom"
            />
          </div>
        </div>

        <div class="footer-column info-column">
          <h3 class="column-title">Contact</h3>
          <ul class="info-list">
            <li v-if="projetPhone">
              <a :href="`tel:${projetPhone.replace(/\s/g, '')}`" class="info-link">
                {{ projetPhone }}
              </a>
            </li>
            <li v-if="projetEmail">
              <a :href="`mailto:${projetEmail}`" class="info-link email-link">
                {{ projetEmail }}
              </a>
            </li>
          </ul>
        </div>

        <div class="footer-column info-column">
          <h3 class="column-title">Bureau</h3>
          <address class="address-text">
            <a :href="mapsUrl" target="_blank" rel="noopener" class="address-link">
              {{ projetAdress }}
            </a>
          </address>
        </div>

      </div>

      <div class="footer-divider"></div>

      <div class="footer-bottom">
        <p class="copyright">© 2025 PIAA. Tous droits réservés.</p>
        <!-- <div class="legal-links">
          <span>Mentions légales</span>
          <span>Confidentialité</span>
        </div> -->
      </div>

    </div>
  </footer>
</template>

<style scoped>
.footer-wrapper {
  width: calc(100% - 4rem);
  max-width: 90%;
  margin: 0 auto 2rem auto;
  
  background: rgba(19, 18, 36, 0.95);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--border-radius);
  
  color: #e2e8f0;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.footer-content {
  padding: 4rem 3rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.5fr 0.8fr 0.8fr;
  gap: 4rem;
  margin-bottom: 3rem;
}

.column-title {
  font-size: 0.8rem;
  letter-spacing: 0.15em;
  color: rgb(255, 255, 255);
  margin-bottom: 1rem;
  font-weight: 500;
}

/* Colonne Marque / Funding */
.brand-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.funding-text {
  font-size: 0.8rem;
  line-height: 2;
  color: rgba(255, 255, 255, 0.7);
  max-width: 90%;
}

.funding-text strong {
  color: #fff;
  font-weight: 500;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-link {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 300;
  transition: color 0.2s ease, transform 0.2s ease;
  display: inline-block;
}

.info-link:hover {
  color: var(--color-accent);
  transform: translateX(4px);
}

.address-text {
  font-style: normal;
  font-size: 0.8rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.9);
  white-space: pre-line;
}

.social-container {
  margin-top: auto;
  padding-top: 1rem;
}

/* --- Bas de page --- */
.footer-divider {
  height: 1px;
  background: linear-gradient(90deg, 
    rgba(255,255,255,0) 0%, 
    rgba(255,255,255,0.1) 50%, 
    rgba(255,255,255,0) 100%
  );
  margin-bottom: 2rem;
}

.footer-bottom {
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.4);
}

.legal-links {
  display: flex;
  gap: 2rem;
}

.legal-links span {
  cursor: pointer;
  transition: color 0.2s;
}

.legal-links span:hover {
  color: #fff;
}

@media (max-width: 992px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
  }
  
  .brand-column {
    grid-column: 1 / -1;
    max-width: 600px;
  }
}

@media (max-width: 640px) {
  .footer-wrapper {
    width: 100%;
    margin: 0;
    border-radius: 24px 24px 0 0;
    border-left: none;
    border-right: none;
    border-bottom: none;
  }

  .footer-content {
    padding: 3rem 1.5rem;
  }

  .footer-grid {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }

  .footer-bottom {
    flex-direction: column-reverse;
    gap: 1.5rem;
    text-align: center;
  }
  
  .info-link:hover {
    transform: none;
  }
}
</style>