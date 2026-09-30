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
  publicUrl: {
    type: String,
    default: ''
  }
});

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

const emit = defineEmits(['click']);

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

const truncateText = (text, length) => {
  if (!text || text.length <= length) return text;
  return text.substring(0, length) + '...';
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

const handleImageError = (event) => {
  event.target.style.display = 'none';
  event.target.parentElement.classList.add('image-error');
};

const handleClick = () => {
  emit('click', props.item);
};

// Computed pour les classes conditionnelles
const cardClass = computed(() => {
  return {
    'document-card': true,
    'image-file': isImageFile(props.item.type_fichier),
    'video-file': isVideoFile(props.item.type_fichier),
    'audio-file': isAudioFile(props.item.type_fichier),
    'document-file': !isImageFile(props.item.type_fichier) && !isVideoFile(props.item.type_fichier) && !isAudioFile(props.item.type_fichier)
  };
});
</script>

<template>
  <div :class="cardClass" @click="handleClick">
    <div class="document-preview">
      <img 
        v-if="isImageFile(item.type_fichier)" 
        :src="getImageUrl(item.chemin)" 
        :alt="item.titre"
        class="preview-image"
        @error="handleImageError"
      />

      <div v-else class="document-icon">
        <font-awesome-icon :icon="getFileIcon(item.type_fichier)" />
      </div>
    </div>
    <div class="document-info">
      <h3 class="document-title">{{ truncateText(item.titre, 60) }}</h3>
      <p class="document-description">{{ truncateText(item.description, 60) }}</p>
      <div class="document-meta">
        <span class="document-type">{{ item.type_fichier }}</span>
        <span class="document-date">{{ formatDate(item.date_upload) }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.document-card {
  background: var(--color-text-light);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid #f1f5f9;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}

.document-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.document-preview {
  position: relative;
  height: 160px;
  overflow: hidden;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.document-card:hover .preview-image {
  transform: scale(1.05);
}

.document-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f8 100%);
  font-size: 3.5rem;
  color: var(--text-color);
}

.image-error {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f8 100%);
}

.image-error::after {
  content: "Image non disponible";
  color: var(--vt-c-divider-dark-1);
  font-size: 0.9rem;
  text-align: center;
  padding: 1rem;
}

.document-info {
  padding: 1.2rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.document-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

.document-description {
  color: var(--vt-c-divider-dark-1);
  font-size: 0.8rem;
  font-weight: 300;
  margin-bottom: 1rem;
  flex-grow: 1;
  line-height: 1.4;
}

.document-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.document-type {
  font-size: 0.75rem;
  color: var(--text-color);
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-weight: 400;
}

.document-date {
  font-size: 0.8rem;
  color: var(--vt-c-divider-dark-2);
}

.document-card.image-file:hover {
  border-color: var(--color-accent);
}

.document-card.video-file:hover {
  border-color: #ff4757;
}

.document-card.audio-file:hover {
  border-color: #2ed573;
}

.document-card.document-file:hover {
  border-color: #3742fa;
}

/* Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Délais d'animation pour un effet séquentiel */
.document-card:nth-child(1) { animation-delay: 0.05s; }
.document-card:nth-child(2) { animation-delay: 0.1s; }
.document-card:nth-child(3) { animation-delay: 0.15s; }
.document-card:nth-child(4) { animation-delay: 0.2s; }
.document-card:nth-child(5) { animation-delay: 0.25s; }
.document-card:nth-child(6) { animation-delay: 0.3s; }
.document-card:nth-child(7) { animation-delay: 0.35s; }
.document-card:nth-child(8) { animation-delay: 0.4s; }

/* Responsive Design */
@media (max-width: 768px) {
  .document-preview {
    height: 140px;
  }
  
  .document-icon {
    font-size: 3rem;
  }
}

@media (max-width: 480px) {
  .document-preview {
    height: 120px;
  }
  
  .document-icon {
    font-size: 2.5rem;
  }
  
  .document-info {
    padding: 1rem;
  }
  
  .document-title {
    font-size: 0.9rem;
  }
  
  .document-description {
    font-size: 0.75rem;
  }
}
</style>