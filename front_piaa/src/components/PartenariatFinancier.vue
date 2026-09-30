<script setup>
import { ref, computed } from 'vue';

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const props = defineProps({
  organisationsData: {
    type: Array,
    default: () => []
  }
});

const hoveredOrganisation = ref(null);

const organisations = computed(() => {
  return [...props.organisationsData];
});

const setHoveredOrganisation = (organisation) => {
  hoveredOrganisation.value = organisation;
};

const handleImageError = (event) => {
  event.target.src =
    'data:image/svg+xml;base64,' +
    btoa(`
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none">
        <rect width="24" height="24" fill="#f4f4f4" rx="4"/>
        <path d="M4 16L8 12L13 17L17 13L20 16V6C20 4.9 19.1 4 18 4H6C4.9 4 4 4.9 4 6V16Z" stroke="#8c8e8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="9" cy="9" r="2" fill="#8c8e8f"/>
      </svg>
    `);
  event.target.classList.add('logo-error');
};
</script>

<template>
  <section class="partenariat-financier">
    <div class="container" v-if="organisations.length > 0">
      <div class="header-content">
        <h1 class="main-title">Partenariat financier</h1>
        <p class="subtitle">
          Aperçu des acteurs financiers soutenant le projet.
        </p>
      </div>


      <div class="partenaires-grid">
        <div 
          v-for="organisation in organisations" 
          :key="organisation.organisations.id" 
          class="partenaire-item"
        >
          <a 
            :href="organisation.organisations.lien ? 'https://' + organisation.organisations.lien : '#'" 
            target="_blank" 
            class="logo-link"
            @mouseenter="setHoveredOrganisation(organisation)"
            @mouseleave="hoveredOrganisation = null"
          >
            <div class="logo-container">
              <img 
                :src="`${publicUrl}${organisation.organisations.logo}`" 
                :alt="'Logo ' + organisation.organisations.nom" 
                class="organisation-logo"
                @error="handleImageError"
              />
              <div class="organisation-tooltip" :class="{ active: hoveredOrganisation === organisation }">
                {{ organisation.organisations.nom }}
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.header-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 6rem auto;
}

.container {
  max-width: 80%;
  margin: 0 auto;
  padding: 2rem;
}

.partenaires-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  justify-items: center;
  align-items: center;
  border-radius: var(--border-radius);
}

.partenaire-item {
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo-link {
  text-decoration: none;
  display: block;
  transition: all 0.3s ease;
}

.logo-link:hover {
  transform: translateY(-5px);
}

.logo-container {
  position: relative;
  width: 160px;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.logo-link:hover .logo-container {
  border-color: var(--color-accent);
  box-shadow: 0 8px 25px rgba(0, 86, 179, 0.2);
}

.organisation-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: all 0.3s ease;
}

.logo-link:hover .organisation-logo {
  transform: scale(1.05);
}

/* Tooltip simple */
.organisation-tooltip {
  position: absolute;
  bottom: -40px;
  left: 50%;
  transform: translateX(-50%) translateY(10px);
  background: var(--color-primary);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.organisation-tooltip::before {
  content: '';
  position: absolute;
  top: -6px;
  left: 50%;
  transform: translateX(-50%);
  border-width: 0 6px 6px 6px;
  border-style: solid;
  border-color: transparent transparent var(--color-primary) transparent;
}

.organisation-tooltip.active {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}

.logo-error {
  filter: grayscale(100%) opacity(0.7);
  padding: 2rem;
}

.logo-link::after {
  content: '↗';
  position: absolute;
  top: 10px;
  right: 10px;
  background: var(--color-accent);
  color: var(--color-text-light);
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.3s ease;
  z-index: 2;
}

.logo-link:hover::after {
  opacity: 1;
  transform: scale(1);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .partenaires-grid {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1.5rem;
  }
  
  .logo-container {
    width: 140px;
    height: 140px;
    padding: 1.2rem;
  }
}

@media (max-width: 768px) {
  .partenariat-financier {
    padding: 3rem 1.5rem;
  }
  
  .main-title {
    font-size: 2rem;
    margin-bottom: 2rem;
  }
  
  .partenaires-grid {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1.2rem;
  }
  
  .logo-container {
    width: 120px;
    height: 120px;
    padding: 1rem;
    margin-bottom: 20px;
  }
  
  .organisation-tooltip {
    bottom: -35px;
    font-size: 0.8rem;
    padding: 6px 10px;
  }
}

@media (max-width: 480px) {
  .partenariat-financier {
    padding: 2rem 1rem;
  }
  
  .main-title {
    font-size: 1.8rem;
  }
  
  .partenaires-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .logo-container {
    width: 100px;
    height: 100px;
    padding: 0.8rem;
  }
  
  .organisation-tooltip {
    bottom: -30px;
    font-size: 0.75rem;
    padding: 5px 8px;
  }
  
  .logo-link::after {
    top: 5px;
    right: 5px;
    width: 18px;
    height: 18px;
    font-size: 0.7rem;
  }
}

/* Animations */
.partenaire-item {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.6s ease forwards;
}

.partenaire-item:nth-child(1) { animation-delay: 0.1s; }
.partenaire-item:nth-child(2) { animation-delay: 0.2s; }
.partenaire-item:nth-child(3) { animation-delay: 0.3s; }
.partenaire-item:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
