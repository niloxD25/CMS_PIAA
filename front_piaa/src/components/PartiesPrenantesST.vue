<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  organisationsData: {
    type: Array,
    default: () => []
  }
});

const hoveredOrganisation = ref(null);
const isAutoScrolling = ref(true);
const scrollInterval = ref(null);
const scrollContainer = ref(null);
const scrollElement = ref(null);

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const setHoveredOrganisation = (organisation) => {
  hoveredOrganisation.value = organisation;
};

const handleImageError = (event) => {
  event.target.src = 'data:image/svg+xml;base64,' +
    btoa(`
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none">
        <rect width="24" height="24" fill="#f4f4f4" rx="4"/>
        <path d="M4 16L8 12L13 17L17 13L20 16V6C20 4.9 19.1 4 18 4H6C4.9 4 4 4.9 4 6V16Z" stroke="#8c8e8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="9" cy="9" r="2" fill="#8c8e8f"/>
      </svg>
      `);
  event.target.classList.add('logo-error');
};

const startAutoScroll = () => {
  isAutoScrolling.value = true;
  stopAutoScroll();
  
  scrollInterval.value = setInterval(() => {
    if (isAutoScrolling.value && scrollContainer.value) {
      scrollContainer.value.scrollLeft += 1;
    }
  }, 30);
};

const stopAutoScroll = () => {
  if (scrollInterval.value) {
    clearInterval(scrollInterval.value);
    scrollInterval.value = null;
  }
};

onMounted(() => {
  startAutoScroll();
});

onBeforeUnmount(() => {
  stopAutoScroll();
});
</script>

<template>
  <section class="parties-prenantes">
    <div class="container" v-if="organisationsData.length > 0">
      <div class="forCenter">
        <div class="partenaires-scroll-container" ref="scrollContainer">
          <div class="partenaires-scroll" ref="scrollElement">
            <div 
              v-for="organisation in organisationsData" 
              :key="organisation.id" 
              class="partenaire-scroll-item"
            >
              <a 
                :href="organisation.organisations.lien ? 'https://' + organisation.organisations.lien : '#'" 
                target="_blank" 
                class="logo-scroll-link"
                @mouseenter="setHoveredOrganisation(organisation)"
                @mouseleave="hoveredOrganisation = null"
              >
                <div class="logo-scroll-container">
                  <img 
                    :src="`${publicUrl}${organisation.organisations.logo}`" 
                    :alt="'Logo ' + organisation.organisations.nom" 
                    class="organisation-scroll-logo"
                    @error="handleImageError"
                  />
                  <div class="organisation-scroll-tooltip" :class="{ active: hoveredOrganisation && hoveredOrganisation.id === organisation.id }">
                    {{ organisation.organisations.nom }}
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.parties-prenantes {
  padding: 4rem 2rem;
  /* min-height: 50vh; */
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
}

.header-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 6rem auto;
}

.container {
  max-width: 80%;
  margin: 0 auto;
  position: relative;
  width: 100%;
  padding: 2rem;
}

.forCenter {
  position: relative;
  display: flex;
  align-items: center;
}

/* Conteneur de défilement - Centré */
.partenaires-scroll-container {
  position: relative;
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  mask-image: linear-gradient(
    to right,
    transparent,
    black 8%,
    black 92%,
    transparent
  );
  scroll-behavior: smooth;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.partenaires-scroll-container::-webkit-scrollbar {
  display: none;
}

/* Contenu défilant - Centré verticalement */
.partenaires-scroll {
  display: flex;
  padding: 0 1rem;
  width: max-content;
  align-items: center;
  min-height: 200px;
}

/* Items individuels - Parfaitement centrés */
.partenaire-scroll-item {
  flex: 0 0 auto;
  margin: 0 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-width: 140px;
}

/* Liens et conteneurs - Centrés */
.logo-scroll-link {
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.3s ease;
  position: relative;
}

.logo-scroll-link:hover {
  transform: translateY(-5px);
}

.logo-scroll-container {
  position: relative;
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-text-light);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.logo-scroll-link:hover .logo-scroll-container {
  border-color: var(--color-accent);
  box-shadow: 0 8px 30px rgba(0, 86, 179, 0.25);
  transform: scale(1.05);
}

.organisation-scroll-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: all 0.3s ease;
}

.logo-scroll-link:hover .organisation-scroll-logo {
  transform: scale(1.1);
}

/* Tooltip - Centré sous le logo */
.organisation-scroll-tooltip {
  position: absolute;
  bottom: -45px;
  left: 50%;
  transform: translateX(-50%) translateY(10px);
  background: var(--color-primary);
  color: var(--color-text-light);
  padding: 10px 15px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 100;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  pointer-events: none;
  text-align: center;
}

.organisation-scroll-tooltip::before {
  content: '';
  position: absolute;
  top: -6px;
  left: 50%;
  transform: translateX(-50%);
  border-width: 0 6px 6px 6px;
  border-style: solid;
  border-color: transparent transparent var(--color-primary) transparent;
}

.organisation-scroll-tooltip.active {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}

/* Indicateur de lien - Positionné précisément */
.logo-scroll-link::after {
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
  font-size: 0.7rem;
  font-weight: bold;
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.3s ease;
  z-index: 5;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.logo-scroll-link:hover::after {
  opacity: 1;
  transform: scale(1);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .partenaire-scroll-item {
    margin: 0 1.5rem;
    min-width: 120px;
  }
  
  .logo-scroll-container {
    width: 100px;
    height: 100px;
    padding: 1.2rem;
  }
}

@media (max-width: 768px) {
  .parties-prenantes {
    padding: 3rem 1.5rem;
  }
  
  .main-title {
    font-size: 2rem;
    margin-bottom: 2rem;
  }
  
  .partenaires-scroll-container {
    padding: 1.5rem 0;
    margin: 1.5rem auto;
  }
  
  .partenaires-scroll {
    min-height: 180px;
  }
  
  .partenaire-scroll-item {
    margin: 0 1rem;
    min-width: 100px;
  }
  
  .logo-scroll-container {
    width: 80px;
    height: 80px;
    padding: 1rem;
  }
  
  .organisation-scroll-tooltip {
    bottom: -40px;
    font-size: 0.8rem;
    padding: 8px 12px;
  }
}

@media (max-width: 480px) {
  .parties-prenantes {
    padding: 2rem 1rem;
  }
  
  .main-title {
    font-size: 1.8rem;
  }
  
  .partenaires-scroll-container {
    padding: 1rem 0;
    margin: 1rem auto;
  }
  
  .partenaires-scroll {
    min-height: 160px;
  }
  
  .partenaire-scroll-item {
    margin: 0 0.8rem;
    min-width: 80px;
  }
  
  .logo-scroll-container {
    width: 70px;
    height: 70px;
    padding: 0.8rem;
  }
  
  .organisation-scroll-tooltip {
    bottom: -35px;
    font-size: 0.75rem;
    padding: 6px 10px;
  }
  
  .logo-scroll-link::after {
    top: 5px;
    right: 5px;
    width: 18px;
    height: 18px;
    font-size: 0.6rem;
  }
}

/* Masquer la barre de défilement */
.partenaires-scroll-container::-webkit-scrollbar {
  display: none;
}

.partenaires-scroll-container {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.logo-error {
  filter: grayscale(100%) opacity(0.7);
  padding: 1.5rem;
  background: #f0f0f0;
  border-radius: 8px;
}
</style>
