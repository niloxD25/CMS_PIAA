<script setup>
import { computed } from 'vue';

const props = defineProps({
  item: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      titre: '',
      description: '',
      type_fichier: '',
      chemin: '',
      date_upload: ''
    })
  },
  show: {
    type: Boolean,
    default: false
  },
  publicUrl: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['close', 'download']);

// Configuration des icônes
const fileIcons = {
  'jpg': 'fa-solid fa-image',
  'jpeg': 'fa-solid fa-image',
  'png': 'fa-solid fa-image',
  'gif': 'fa-solid fa-image',
  'webp': 'fa-solid fa-image',
  'bmp': 'fa-solid fa-image',
  'svg': 'fa-solid fa-image',
  'pdf': 'fa-solid fa-file-pdf',
  'doc': 'fa-solid fa-file-word',
  'docx': 'fa-solid fa-file-word',
  'odt': 'fa-solid fa-file-word',
  'txt': 'fa-solid fa-file-alt',
  'xls': 'fa-solid fa-file-excel',
  'xlsx': 'fa-solid fa-file-excel',
  'csv': 'fa-solid fa-file-excel',
  'ppt': 'fa-solid fa-file-powerpoint',
  'pptx': 'fa-solid fa-file-powerpoint',
  'odp': 'fa-solid fa-file-powerpoint',
  'zip': 'fa-solid fa-file-archive',
  'rar': 'fa-solid fa-file-archive',
  '7z': 'fa-solid fa-file-archive',
  'tar': 'fa-solid fa-file-archive',
  'gz': 'fa-solid fa-file-archive',
  'mp4': 'fa-solid fa-video',
  'mov': 'fa-solid fa-video',
  'avi': 'fa-solid fa-video',
  'wmv': 'fa-solid fa-video',
  'mkv': 'fa-solid fa-video',
  'webm': 'fa-solid fa-video',
  'flv': 'fa-solid fa-video',
  'mp3': 'fa-solid fa-music',
  'wav': 'fa-solid fa-music',
  'ogg': 'fa-solid fa-music',
  'm4a': 'fa-solid fa-music',
  'aac': 'fa-solid fa-music',
  'flac': 'fa-solid fa-music',
  'default': 'fa-solid fa-file'
};

const isImageFile = (type) => {
  const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
  return imageTypes.includes(type?.toLowerCase());
};

const isVideoFile = (type) => {
  const videoTypes = ['mp4', 'mov', 'avi', 'wmv', 'mkv', 'webm', 'flv', 'm4v'];
  return videoTypes.includes(type?.toLowerCase());
};

const isAudioFile = (type) => {
  const audioTypes = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'];
  return audioTypes.includes(type?.toLowerCase());
};

const getFileIcon = (type) => {
  if (!type) return fileIcons.default;
  const fileType = type.toLowerCase();
  return fileIcons[fileType] || fileIcons.default;
};

const getImageUrl = (path) => {
  if (!path) return '';
  return `${props.publicUrl}${path}`;
};

const formatDate = (dateString) => {
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch {
    return 'Date inconnue';
  }
};

const handleClose = () => {
  emit('close');
};

const handleDownload = () => {
  emit('download', props.item);
};

const handleImageError = (event) => {
  event.target.style.display = 'none';
  event.target.parentElement.classList.add('image-error');
};

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    handleClose();
  }
};

// Exposer la fonction pour le parent
defineExpose({
  handleKeydown
});
</script>

<template>
  <div 
    v-if="show" 
    class="lightbox" 
    @click.self="handleClose"
    @keydown="handleKeydown"
    role="dialog" 
    aria-labelledby="lightbox-title"
  >
    <div class="lightbox-content">
      <button class="lightbox-close" @click="handleClose">×</button>
      
      <div class="lightbox-header">
        <div class="lightbox-icon">
          <font-awesome-icon :icon="getFileIcon(item.type_fichier)" />
        </div>
        <h2>{{ item.titre }}</h2>
        
        <button 
          class="download-btn"
          @click="handleDownload"
          :title="`Télécharger ${item.titre}`"
        >
          <font-awesome-icon :icon="['fas', 'download']" />
          Télécharger
        </button>
      </div>
      
      <div class="lightbox-body">
        <!-- Affichage des images -->
        <div class="lightbox-image-container" v-if="isImageFile(item.type_fichier)">
          <img 
            :src="getImageUrl(item.chemin)" 
            :alt="item.titre"
            class="lightbox-image"
            @error="handleImageError"
          >
        </div>
        
        <!-- Lecteur vidéo -->
        <div class="lightbox-media-container" v-else-if="isVideoFile(item.type_fichier)">
          <video 
            :src="getImageUrl(item.chemin)" 
            controls
            class="media-player"
          >
            Votre navigateur ne supporte pas la lecture de vidéos.
          </video>
        </div>
        
        <!-- Lecteur audio -->
        <div class="lightbox-media-container" v-else-if="isAudioFile(item.type_fichier)">
          <div class="audio-player">
            <audio 
              :src="getImageUrl(item.chemin)" 
              controls
              class="media-player"
            >
              Votre navigateur ne supporte pas la lecture audio.
            </audio>
          </div>
        </div>
        
        <!-- Autres types de documents -->
        <div class="lightbox-document" v-else>
          <div class="document-icon-large">
            <font-awesome-icon :icon="getFileIcon(item.type_fichier)" />
          </div>
          <p>Ce document ne peut pas être prévisualisé.</p>
          <p class="document-type-large">Type: {{ item.type_fichier }}</p>
        </div>
        
        <div class="lightbox-info">
          <p>{{ item.description }}</p>
          <span class="lightbox-date">{{ formatDate(item.date_upload) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.lightbox {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10003;
  padding: 2rem;
}

.lightbox-content {
  position: relative;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  background: var(--color-text-light);
  border-radius: var(--border-radius);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.lightbox-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: var(--color-primary-flou);
  color: var(--color-text-light);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  z-index: 10;
  transition: background 0.3s ease;
}

.lightbox-close:hover {
  background: var(--color-accent);
}

.lightbox-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 1.5rem 0;
}

.lightbox-icon {
  font-size: 2.5rem;
  color: var(--color-primary);
}

.lightbox-header h2 {
  color: var(--color-primary);
  margin: 0;
  font-size: 1.5rem;
  flex: 1;
}

.download-btn {
  display: flex;
  align-items: center;
  padding: 0.7rem 1.5rem;
  background: var(--color-primary);
  border: none;
  color: var(--color-text-light);
  text-decoration: none;
  border-radius: 6px;
  font-weight: 500;
  transition: background 0.3s ease;
  cursor: pointer;
  gap: 8px;
  max-width: 200px;
}

.download-btn:hover {
  background: var(--color-accent);
}

.lightbox-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.lightbox-image-container {
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
  min-height: 300px;
}

.lightbox-image {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain;
}

.lightbox-media-container {
  margin-bottom: 1.5rem;
}

.media-player {
  width: 100%;
  border-radius: var(--border-radius);
  background: #f5f5f5;
}

.lightbox-document {
  text-align: center;
  padding: 3rem 2rem;
  background: #f9f9f9;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
}

.document-icon-large {
  font-size: 4rem;
  color: var(--color-primary);
  margin-bottom: 1rem;
}

.document-type-large {
  color: var(--vt-c-divider-dark-1);
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.lightbox-info {
  border-top: 1px solid #eee;
  padding-top: 1.5rem;
}

.lightbox-info p {
  color: var(--vt-c-divider-dark-1);
  margin-bottom: 0.8rem;
  line-height: 1.5;
  white-space: pre-line;
  font-size: 1rem;
  font-weight: 300;
}

.lightbox-date {
  font-size: 0.8rem;
  color: var(--vt-c-divider-dark-2);
}

.image-error {
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
}

.image-error::after {
  content: "Image non disponible";
  color: var(--vt-c-divider-dark-1);
  font-size: 1rem;
  text-align: center;
  padding: 2rem;
}

.lightbox-body::-webkit-scrollbar {
  width: 6px;
}

.lightbox-body::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.lightbox-body::-webkit-scrollbar-thumb {
  background: var(--color-accent, #4f46e5);
  border-radius: 10px;
}

.lightbox-body::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary, #3730a3);
}

.lightbox-body {
  scrollbar-width: thin;
  scrollbar-color: var(--color-accent, #4f46e5) #f1f5f9;
  padding: 1.5rem;
  overflow-y: auto;
}

.lightbox-info {
  border-top: 1px solid #eee;
  padding-top: 1.5rem;
  margin-top: 0.5rem;
}

@media (max-width: 768px) {
  .lightbox {
    padding: 1rem;
  }
  
  .lightbox-content {
    width: 95%;
  }
  
  .lightbox-header {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
    padding: 1rem 1rem 0;
  }
  
  .lightbox-header h2 {
    font-size: 1.2rem;
  }
  
  .download-btn {
    width: 100%;
    justify-content: center;
    margin-top: 0.5rem;
  }
  
  .lightbox-body {
    padding: 1rem;
  }
  
  .lightbox-image-container {
    min-height: 200px;
  }
}
</style>