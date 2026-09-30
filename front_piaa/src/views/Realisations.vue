<script setup>
import { ref, computed, onMounted } from 'vue';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';
import { useProjectStore } from '@/stores/projectStore';

const projectStore = useProjectStore();

const activeIndex = ref(0);
const maxVisibleItems = ref(5);
const isTransitioning = ref(false);
const carousel = ref(null);

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const organisations = computed(() => {
  return projectStore.fullData?.parties_prenantes || [];
});

const realisations = computed(() => {
  return projectStore.fullData?.realisations || [];
});

const activeRealisation = computed(() => {
  return realisations.value[activeIndex.value] || null;
});

const visibleRealisations = computed(() => {
  if (!realisations.value.length) return [];

  const half = Math.floor(maxVisibleItems.value / 2);
  let startIndex = activeIndex.value - half;
  let endIndex = activeIndex.value + half;

  if (startIndex < 0) {
    endIndex += -startIndex;
    startIndex = 0;
  }
  if (endIndex > realisations.value.length - 1) {
    startIndex -= endIndex - (realisations.value.length - 1);
    endIndex = realisations.value.length - 1;
  }
  if (startIndex < 0) startIndex = 0;

  return realisations.value.slice(startIndex, endIndex + 1);
});

const leftEmptySpace = computed(() => {
  return Math.max(0, Math.floor((maxVisibleItems.value - visibleRealisations.value.length) / 2));
});

const rightEmptySpace = computed(() => {
  return leftEmptySpace.value;
});

const getOriginalIndex = (visibleIndex) => {
  const firstVisible = realisations.value.indexOf(visibleRealisations.value[0]);
  return firstVisible + visibleIndex;
};

const setActiveIndex = (index) => {
  if (index >= 0 && index < realisations.value.length && !isTransitioning.value) {
    activeIndex.value = index;
  }
};

const nextSlide = async () => {
  if (isTransitioning.value) return;
  
  isTransitioning.value = true;
  activeIndex.value = (activeIndex.value + 1) % realisations.value.length;
  
  await new Promise(resolve => setTimeout(resolve, 500));
  isTransitioning.value = false;
};

const prevSlide = async () => {
  if (isTransitioning.value) return;
  
  isTransitioning.value = true;
  activeIndex.value =
    (activeIndex.value - 1 + realisations.value.length) % realisations.value.length;
  
  await new Promise(resolve => setTimeout(resolve, 500));
  isTransitioning.value = false;
};

const getImageUrl = (filePath) => {
  return `${publicUrl}${filePath}`;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <Header />
  
  <div class="realisations-container">
    <div class="header-content">
      <h1 class="main-title">Réalisations et évolution des travaux</h1>
      <p class="subtitle">
        Suivez pas à pas l'avancement des chantiers et découvrez les réalisations concrètes. 
      </p>
    </div>

    <!-- Carousel d'images -->
    <div class="carousel-container">
      <div class="carousel-wrapper">
        <div class="carousel" ref="carousel">
          <!-- Espace vide gauche pour centrage -->
          <div class="carousel-placeholder" v-if="leftEmptySpace > 0" 
               :style="{ width: leftEmptySpace * 230 + 'px' }"></div>
          
          <!-- Éléments du carousel avec transition -->
          <transition-group name="carousel-slide" tag="div" class="carousel-items-container">
            <div 
              v-for="(realisation, index) in visibleRealisations" 
              :key="realisation.id" 
              class="carousel-item"
              :class="{ active: activeIndex === getOriginalIndex(index) }"
              @click="setActiveIndex(getOriginalIndex(index))"
            >
              <img 
                v-if="realisation.image"
                :src="getImageUrl(realisation.image)" 
                :alt="realisation.titre"
                class="carousel-image"
              />

              <img 
                v-else
                src="/logos/logo PIAA2.png" 
                :alt="realisation.titre"
                class="carousel-image"
              />

            </div>
          </transition-group>
          
          <!-- Espace vide droit pour centrage -->
          <div class="carousel-placeholder" v-if="rightEmptySpace > 0" 
               :style="{ width: rightEmptySpace * 230 + 'px' }"></div>
        </div>
        
        <!-- Boutons de navigation -->
        <button class="nav-button prev" @click="prevSlide" v-if="realisations.length > 1">
          &#10094;
        </button>
        <button class="nav-button next" @click="nextSlide" v-if="realisations.length > 1">
          &#10095;
        </button>
      </div>
    </div>
    
    <!-- Contenu de la réalisation active avec transition -->
    <transition name="fade-slide" mode="out-in">
      <div class="content-container" v-if="activeRealisation" :key="activeRealisation.id">
        <div class="title-section">
          <h2 class="realisation-title">{{ activeRealisation.titre }}</h2>
          <div class="realisation-date" v-if="activeRealisation.date_realisation">
            {{ formatDate(activeRealisation.date_realisation) }}
          </div>
        </div>
        
        <div class="realisation-details">
          <div class="detail-row">
            <span class="label">Bénéficiaires</span>
            <div class="value-container">
              <span class="value">{{ activeRealisation.beneficiaires }}</span>
            </div>
          </div>

          <div class="detail-row">
            <span class="label">Description</span>
            <div class="value-container">
              <span class="value">{{ activeRealisation.description }}</span>
            </div>
          </div>
          
          <div class="detail-row">
            <span class="label">Impacts potentiels</span>
            <div class="value-container">
              <span class="value">{{ activeRealisation.impacts_potentiel }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="no-data" key="no-data">
        Aucune réalisation à afficher.
      </div>
    </transition>
  </div>

  <PartiesPrenantesST :organisations-data="organisations"/>
</template>

<style scoped>
.realisations-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 20px;
}

.header-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto 4rem auto;
}

.carousel-container {
  margin-bottom: 40px;
}

.carousel-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.carousel {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 40px 0;
  overflow-x: hidden;
}

.carousel-items-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

.carousel-placeholder {
  height: 150px;
  flex-shrink: 0;
}

.carousel-item {
  width: 200px;
  height: 150px;
  margin: 0 15px;
  cursor: pointer;
  transition: all 0.4s ease;
  opacity: 0.7;
  border-radius: 8px;
  overflow: hidden;
  transform: scale(0.9);
  flex-shrink: 0;
}

.carousel-item.active {
  width: 320px;
  height: 250px;
  transform: scale(1.1);
  opacity: 1;
  z-index: 10;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.carousel-item:hover .carousel-image {
  transform: scale(1.05);
}

/* Boutons de navigation */
.nav-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: var(--color-primary);
  color: var(--color-text-light);
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  z-index: 20;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-button:hover {
  background-color: var(--color-accent);
  transform: translateY(-50%) scale(1.1);
}

.nav-button.prev {
  left: 10px;
}

.nav-button.next {
  right: 10px;
}

/* Styles pour le contenu */
.content-container {
  padding: 30px;
  border-radius: var(--border-radius);
  margin-top: 20px;
  background-color: white;
}

.title-section {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
}

.realisation-title {
  color: var(--text-color);
  max-width: 800px;
  margin: auto;
  margin-bottom: 1rem;
  font-size: 2rem;
  font-weight: 400;
}

.realisation-date {
  color: var(--vt-c-divider-dark-1);
  font-size: 1rem;
}

.realisation-details {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 15px;
  border-radius: var(--border-radius);
  margin-bottom: 32px;
}

.label {
  font-weight: 300;
  color: var(--text-color);
  font-size: 1.5rem;
  min-width: 180px;
  text-align: left;
}

.value-container {
  flex: 1;
  text-align: left;
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 50%;
}

.value {
  color: var(--text-color);
  display: inline-block;
  text-align: left;
  max-width: 100%;
  white-space: pre-line; /* Important pour les retours à la ligne */
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #777;
  font-style: italic;
  font-size: 1.2rem;
}

/* Animations pour le carousel */
.carousel-slide-enter-active,
.carousel-slide-leave-active {
  transition: all 0.5s ease;
}

.carousel-slide-enter-from {
  opacity: 0;
  transform: translateX(30px) scale(0.95);
}

.carousel-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px) scale(0.95);
}

.carousel-slide-move {
  transition: transform 0.5s ease;
}

/* Animation pour le contenu */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Responsive design */
@media (max-width: 768px) {
  .carousel-item {
    width: 150px;
    height: 120px;
  }
  
  .carousel-item.active {
    width: 220px;
    height: 170px;
  }
  
  .carousel-placeholder {
    height: 120px;
  }
  
  .nav-button {
    width: 30px;
    height: 30px;
    font-size: 16px;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 8px;
  }
  
  .label, .value-container {
    text-align: left;
    min-width: auto;
    max-width: 100%;
  }
}

@media (max-width: 576px) {
  .carousel-item {
    width: 120px;
    height: 100px;
    margin: 0 8px;
  }
  
  .carousel-item.active {
    width: 180px;
    height: 140px;
  }
  
  .carousel-placeholder {
    height: 100px;
  }
  
  .content-container {
    padding: 20px;
  }
  
  .realisation-title {
    font-size: 1.5rem;
  }
  
  /* Ajustements des animations pour mobile */
  .carousel-slide-enter-from {
    transform: translateX(20px) scale(0.95);
  }
  
  .carousel-slide-leave-to {
    transform: translateX(-20px) scale(0.95);
  }
}
</style>