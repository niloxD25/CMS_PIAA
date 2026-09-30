<script setup>
import { computed } from 'vue';

const props = defineProps({
  interviews: {
    type: Array,
    required: true,
    default: () => []
  }
});

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

// Fonction pour générer l'URL complète
const getVideoUrl = (path) => {
  if (!path) return '';
  const baseUrl = publicUrl.endsWith('/') ? publicUrl.slice(0, -1) : publicUrl;
  const filePath = path.startsWith('/') ? path : `/${path}`;
  return `${baseUrl}${filePath}`;
};

const formatDate = (dateString) => {
  if (!dateString) return null;
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric', month: 'short', day: 'numeric',
    });
  } catch (error) {
    return dateString;
  }
}
</script>

<template>
  <div class="gallery-container">
    
    <div v-if="interviews.length === 0" class="empty-state">
      <p>Aucune vidéo disponible pour le moment.</p>
    </div>

    <div v-else class="video-grid">
      <article 
        v-for="video in interviews" 
        :key="video.id" 
        class="video-card"
      >
        <div class="video-wrapper">
          <video 
            controls 
            preload="metadata"
            class="video-player"
          >
            <source 
              :src="getVideoUrl(video.chemin)" 
            >
            Votre navigateur ne supporte pas la lecture de cette vidéo.
          </video>
        </div>

        <div class="video-details">
          <div class="details-text">
            <h3 class="video-title" :title="video.titre">
              {{ video.titre }}
            </h3>
            
            <div class="video-meta">
              <span class="video-date">{{ formatDate(video.date_upload) }}</span>
            </div>

            <p class="video-description">
              {{ video.description }}
            </p>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<style scoped>
.gallery-container {
  max-width: 90%;
  padding: 2rem;
  box-sizing: border-box;
}

.video-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 24px 16px;
  width: 100%;
}

@media (min-width: 600px) {
  .video-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 960px) {
  .video-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1280px) {
  .video-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Carte Vidéo */
.video-card {
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.video-wrapper {
  position: relative;
  width: 100%;
  padding-top: 56.25%;
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.video-player {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-details {
  display: flex;
  margin-top: 12px;
  padding-right: 24px;
}

.details-text {
  flex: 1;
}

.video-title {
  margin: 0 0 6px 0;
  font-size: 1rem;
  font-weight: 500;
  line-height: 1.4rem;
  color: var(--text-color);  
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.video-meta {
  font-size: 0.8rem;
  color: #606060;
  margin-bottom: 6px;
}

.video-description {
  font-size: 0.8rem;
  color: #606060;
  margin: 0;  
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #606060;
  font-size: 1.1rem;
}

@media (prefers-color-scheme: dark) {
  .video-title { color: #f1f1f1; }
  .video-meta, .video-description { color: #aaaaaa; }
}
</style>