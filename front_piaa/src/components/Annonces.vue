<template>
  <div class="annonces-container">
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
    </div>

    <div v-else-if="annonces.length" class="annonces-slider-wrapper">
      <div class="annonces-slider" :style="sliderStyle">
        <div v-for="(annonce, index) in annoncesDoubled" :key="index" class="annonce-item">
          <span class="annonce-text">
            <span class="annonce-title">{{ annonce.titre }}</span> : {{ annonce.contenu }}
          </span>
          <span class="annonce-separator"> | </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getAnnoncesActives } from '@/services/api';

const annonces = ref([]);
const isLoading = ref(true);

const annoncesDoubled = computed(() => {
  return [...annonces.value, ...annonces.value];
});

const sliderStyle = computed(() => {
  const itemCount = annonces.value.length;
  return {
    'animation-duration': `${itemCount * 15}s` 
  };
});

const fetchAnnonces = async () => {
  try {
    const response = await getAnnoncesActives();
    annonces.value = response.data.data;
  } catch (error) {
    console.error("Erreur lors de la récupération des annonces:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchAnnonces);
</script>

<style scoped>
:root {
  --text-color: #e0e0e0;
  --secondary-text-color: #b0b0b0;
}

.annonces-container {
  width: 100%;
  margin: 8px auto;
  text-align: center;
  background-color: white;
  color: var(--text-color);
  border-radius: var(--border-radius);
  max-height: 60px;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: #fff;
}

.loading-state, .empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100px;
  color: var(--secondary-text-color);
  font-size: 1.2rem;
}

.spinner {
  position: absolute;
  top: 50%; left: 50%;
  width: 50px; height: 50px;
  margin: -25px 0 0 -25px;
  border-top: 4px solid var(--color-accent, #4f46e5);
  border-radius: 50%;
  animation: spin 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  z-index: 2;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.annonces-slider-wrapper {
  overflow: hidden;
  position: relative;
  font-size: 1rem;
  white-space: nowrap; 
  width: 100%;
  padding: 10px 0;
  mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,1) 15%, rgba(0,0,0,1) 85%, rgba(0,0,0,0) 100%);
}

.annonces-slider {
  display: inline-block;
  animation: scroll-annonces linear infinite;
}

.annonces-slider:hover {
  animation-play-state: paused;
}

.annonce-item {
  display: inline-block;
  vertical-align: top;
}

.annonce-text {
  font-weight: 400;
  color: var(--secondary-text-color);
  padding: 0 15px;
  border-radius: 20px;
  transition: all 0.3s ease;
}

.annonce-item:hover .annonce-text {
  color: var(--primary-color);
  font-weight: 500;
}

.annonce-title {
  font-weight: 600;
  color: var(--primary-color);
}

.annonce-separator {
  color: #555;
  margin: 0 5px;
}

/* Animation de défilement */
@keyframes scroll-annonces {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-50%);
  }
}
</style>
