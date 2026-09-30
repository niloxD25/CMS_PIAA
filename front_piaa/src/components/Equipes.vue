<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';

const props = defineProps({
  projet_name: {
    type: String,
    default: null
  },
  personnes: {
    type: Array,
    default: () => []
  }
});

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const scrollContainer = ref(null);
const isPaused = ref(false);
let animationFrameId = null;

const duplicatedList = computed(() => {
  const list = props.personnes;
  if (!list || list.length <= 3) {
    return list;
  }
  return [...list, ...list];
});

const getImageUrl = (path) => {
  if (!path) return 'https://via.placeholder.com/400x500';
  return publicUrl ? `${publicUrl}${path}` : path;
};

const formatDate = (isoDate) => {
    if (!isoDate) return null;
    try {
        const date = new Date(isoDate);
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(date);
    } catch (e) {
        return null;
    }
};

const getParticipationPeriod = (item) => {
    const debut = formatDate(item.date_debut_participation);
    const fin = formatDate(item.date_fin_participation);
    let text = '';
    if (debut) text += `Depuis ${debut}`;
    if (fin) text += ` jusqu'au ${fin}`;
    return text;
};

const startAutoScroll = () => {
  const container = scrollContainer.value;
  if (!container) return;

  const scrollStep = () => {
    if (!isPaused.value && container) {
      container.scrollLeft += 0.8; 

      if (container.scrollLeft >= container.scrollWidth / 2) {
        container.scrollLeft = 0;
      }
    }
    animationFrameId = requestAnimationFrame(scrollStep);
  };

  // Lancer la boucle
  animationFrameId = requestAnimationFrame(scrollStep);
};

// Gestionnaires d'événements pour la souris/toucher
const pauseScroll = () => { isPaused.value = true; };
const resumeScroll = () => { isPaused.value = false; };

onMounted(() => {
  setTimeout(startAutoScroll, 100);
});

onBeforeUnmount(() => {
  if (animationFrameId) cancelAnimationFrame(animationFrameId);
});
</script>

<template>
  <div class="equipes-container">
    <div class="header-content">
      <h1 class="main-title">Le {{ projet_name }}, un travail d’équipe</h1>
      <p class="subtitle">Les composants de la cellule de gestion et les autres cadres du projet.</p>
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
        
        <div v-for="item in duplicatedList" :key="`a-${item.id}`" class="profile-card">
          <div class="image-wrapper">
            <img 
              :src="getImageUrl(item.personnes.photo)" 
              :alt="item.personnes.nom" 
              class="profile-photo"
            />
          </div>
          <div class="card-info">
            <h3 class="name">
              {{ item.personnes.prenom }} {{ item.personnes.nom }}
            </h3>
            <p class="role-line">
              <span class="role">{{ item.personnes.poste }}</span>
            </p>
            <p v-if="getParticipationPeriod(item)" class="participation-period">
              {{ getParticipationPeriod(item) }}
            </p>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>

<style scoped>
.equipes-container {
   max-width: 100%;
   padding: 2rem 0 2rem 2rem;
   overflow: hidden;
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

.profile-card {
   min-width: 280px;
   width: 280px;
   display: flex;
   flex-direction: column;
   gap: 1rem;
   user-select: none; 
}

.image-wrapper {
   position: relative;
   width: 100%;
   border-radius: 24px;
   overflow: hidden;
   background-color: #f0f0f0;
   transition: all 0.3s ease;
}

.profile-photo {
   width: 100%;
   height: 100%;
   object-fit: cover;
   display: block;
   pointer-events: none; 
}

.scrolling-content .profile-card:nth-of-type(odd) .image-wrapper {
   aspect-ratio: 1 / 1.25; 
}

.scrolling-content .profile-card:nth-of-type(even) .image-wrapper {
   aspect-ratio: 1 / 1; 
}

.name {
   font-size: 1.1rem;
   font-weight: 500;
   margin: 0;
   color: var(--text-color);
}

.role-line {
   font-size: 0.85rem;
   color: #666;
   margin: 0.5rem 0;
}

.participation-period {
    font-size: 0.75rem;
    color: #999;
    margin-top: -0.5rem;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

@media (max-width: 1200px) {
   .equipes-container { padding: 1.5rem 0 1.5rem 1.5rem; }
   .profile-card { min-width: 240px; width: 240px; }
   .scrolling-content { gap: 1.5rem; }
}

@media (max-width: 992px) {
   .header-content { margin-bottom: 1.5rem; padding-right: 1.5rem; }
   .profile-card { min-width: 220px; width: 220px; gap: 0.75rem; }
   .name { font-size: 1rem; }
   .role-line { font-size: 0.8rem; margin: 0.4rem 0; }
   .participation-period { font-size: 0.7rem; margin-top: -0.4rem; }
}

@media (max-width: 768px) {
   .equipes-container { padding: 1rem 0 1rem 1rem; }
   .header-content { margin-bottom: 1rem; padding-right: 1rem; }
   .cards-scroll-container { padding-bottom: 1.5rem; }
   .scrolling-content { gap: 1rem; }
   .profile-card { min-width: 180px; width: 180px; gap: 0.5rem; }
   .image-wrapper { border-radius: 16px; }
   .name { font-size: 0.9rem; }
   .role-line { font-size: 0.75rem; margin: 0.3rem 0; }
   .participation-period { font-size: 0.65rem; margin-top: -0.3rem; }
}

@media (max-width: 576px) {
   .equipes-container { padding: 0.75rem 0 0.75rem 0.75rem; }
   .header-content { margin-bottom: 0.75rem; padding-right: 0.75rem; }
   .cards-scroll-container { padding-bottom: 1rem; }
   .scrolling-content { gap: 0.75rem; }
   .profile-card { min-width: 150px; width: 150px; gap: 0.5rem; }
   .image-wrapper { border-radius: 12px; }
   .name { font-size: 0.8rem; }
   .role-line { font-size: 0.7rem; margin: 0.25rem 0; }
   .participation-period { font-size: 0.6rem; margin-top: -0.25rem; }
}

@media (max-width: 400px) {
   .profile-card { min-width: 130px; width: 130px; }
   .name { font-size: 0.75rem; }
   .role-line { font-size: 0.65rem; }
   .participation-period { font-size: 0.55rem; }
}
</style>