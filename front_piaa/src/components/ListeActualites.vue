<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { slugify } from '@/utils/stringUtils';

const props = defineProps({
  actualites: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Actualités'
  }
})

const router = useRouter()
const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const scrollContainer = ref(null);
const isPaused = ref(false);
let animationFrameId = null;

const duplicatedList = computed(() => {
  const list = props.actualites;
  if (!list || list.length <= 3) {
    return list;
  }
  return [...list, ...list];
});

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/images/placeholder-actualite.jpg';
  return publicUrl ? `${publicUrl}${imagePath}` : imagePath;
}

const formatDate = (dateString) => {
  if (!dateString) return 'Date non définie';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric', month: 'short', day: 'numeric',
    });
  } catch (error) {
    return dateString;
  }
}

const hasValidDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  return !isNaN(date.getTime());
}

const shouldShowDates = (actualite) => {
  return hasValidDate(actualite.date_debut) || hasValidDate(actualite.date_fin);
}

const formatDateRange = (actualite) => {
  const hasDebut = hasValidDate(actualite.date_debut);
  const hasFin = hasValidDate(actualite.date_fin);
  
  if (hasDebut && hasFin) {
    return `Du ${formatDate(actualite.date_debut)} au ${formatDate(actualite.date_fin)}`;
  } else if (hasDebut) {
    return `Le ${formatDate(actualite.date_debut)}`;
  } else if (hasFin) {
    return `Jusqu'au ${formatDate(actualite.date_fin)}`;
  }
  return '';
}

const truncateDescription = (text, maxLength = 100) => {
  if (!text || text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

// --- Navigation ---
const goToDetails = async (event) => {
  try {
    const eventSlug = slugify(event.titre);
    
    router.push({
      name: 'evenement-details',
      params: {slug: eventSlug, id: event.id}
    });
    
  } catch (error) {
    console.error('Erreur lors de la navigation:', error);
  }
};

const startAutoScroll = () => {
  const container = scrollContainer.value;
  if (!container) return;

  const scrollStep = () => {
    if (!isPaused.value && container) {
      // Vitesse de défilement
      container.scrollLeft += 0.8; 

      // Reset invisible pour l'infini
      if (container.scrollLeft >= container.scrollWidth / 2) {
        container.scrollLeft = 0;
      }
    }
    animationFrameId = requestAnimationFrame(scrollStep);
  };

  animationFrameId = requestAnimationFrame(scrollStep);
};

// Gestionnaires d'événements
const pauseScroll = () => { isPaused.value = true; };
const resumeScroll = () => { isPaused.value = false; };

onMounted(() => {
  setTimeout(startAutoScroll, 100);
})

onUnmounted(() => {
  if (animationFrameId) cancelAnimationFrame(animationFrameId);
})
</script>

<template>
  <div class="actualites-container" v-if="actualites.length > 0">
    <div class="header-content">
      <h2 class="main-title">{{ title }}</h2>
    </div>
    
    <div 
      class="cards-scroll-container"
      ref="scrollContainer"
      @mouseenter="pauseScroll"
      @mouseleave="resumeScroll"
      @touchstart="pauseScroll"
      @touchend="resumeScroll"
    >
      <div class="scrolling-content">
        
        <div 
          v-for="(item, index) in duplicatedList" 
          :key="`${item.id}-${index}`" 
          class="actualite-card"
          @click="goToDetails(item)"
        >

          <div class="image-wrapper">
            <img 
              :src="getImageUrl(item.image)" 
              :alt="item.titre"
              class="card-photo"
              @error="(e) => e.target.src = '/images/placeholder-actualite.jpg'"
            />
            <div v-if="item.archived" class="badge-archive">Archivé</div>
          </div>

          <div class="card-info">
            <h3 class="card-title">{{ item.titre }}</h3>
            
            <p class="card-desc">
              {{ truncateDescription(item.description) }}
            </p>

            <div v-if="shouldShowDates(item)" class="card-date-line">
               <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="date-icon"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19a2 2 0 002 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
               <span>{{ formatDateRange(item) }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.actualites-container {
  max-width: 90%;
  padding: 2rem 0 2rem 2rem;
  overflow: hidden;
}

.header-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 6rem auto;
}

.cards-scroll-container {
  display: flex;
  overflow-x: auto;
  padding-bottom: 2rem;
  padding-right: 0;
  
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.cards-scroll-container::-webkit-scrollbar {
  display: none;
}

.scrolling-content {
  display: flex;
  gap: 2rem;
  width: max-content;
}

.actualite-card {
  min-width: 280px;
  width: 280px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  cursor: pointer;
  user-select: none;
  transition: opacity 0.3s;
}

.actualite-card:hover {
  opacity: 0.8;
}

.image-wrapper {
  position: relative;
  width: 100%;
  border-radius: 24px;
  overflow: hidden;
  background-color: #f0f0f0;
  transition: all 0.3s ease;
}

.card-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  pointer-events: none;
}

.scrolling-content .actualite-card:nth-of-type(odd) .image-wrapper {
  aspect-ratio: 1 / 1; 
}

.scrolling-content .actualite-card:nth-of-type(even) .image-wrapper {
  aspect-ratio: 1 / 1.25; 
}

.badge-archive {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0,0,0,0.6);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  text-transform: uppercase;
}

.card-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0;
  color: var(--text-color);
  line-height: 1.3;
}

.card-desc {
  font-size: 0.85rem;
  color: #666;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-date-line {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #999;
  margin-top: auto;
  font-weight: 500;
}

.date-icon {
  color: #6b7280;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .actualites-container { padding: 1rem 0 1rem 1rem; }
  .scrolling-content { gap: 1rem; }
  .actualite-card { min-width: 200px; width: 200px; }
  .image-wrapper { border-radius: 16px; }
  .card-title { font-size: 1rem; }
  .card-desc { font-size: 0.8rem; }
}

@media (max-width: 480px) {
  .actualite-card { min-width: 160px; width: 160px; }
  .card-title { font-size: 0.9rem; }
}
</style>