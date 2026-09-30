<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useProjectStore } from '@/stores/projectStore';
import { downloadFichier } from '@/services/api';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';
import DocumentCard from '@/components/DocumentCard.vue';
import LightboxModal from '@/components/LightboxModal.vue';

const projectStore = useProjectStore();

// Variables réactives pour l'interface
const activeFilters = ref(['all']);
const showLightbox = ref(false);
const currentMediaIndex = ref(0);
const currentPage = ref(1);
const itemsPerPage = 8;
const maxVisiblePages = 5;
const isUsingNativeControls = ref(false);
const searchQuery = ref('');

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const organisations = computed(() => {
  return projectStore.fullData?.parties_prenantes || [];
});

const items = computed(() => {
  const plans = projectStore.fullData?.plans_reperage || [];
  return plans.map(fichier => ({
    id: fichier.id || Date.now() + Math.random(),
    titre: fichier.titre || 'Document sans titre',
    description: fichier.description || 'Aucune description',
    type_fichier: (fichier.type_fichier || 'unknown').toLowerCase(),
    chemin: fichier.chemin || '',
    date_upload: fichier.date_upload || new Date().toISOString()
  }));
});

const filteredItems = computed(() => {
  let filtered = items.value;
  
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(item => {
      const titre = item.titre?.toLowerCase() || '';
      const description = item.description?.toLowerCase() || '';
      const type = item.type_fichier?.toLowerCase() || '';
      return titre.includes(query) || description.includes(query) || type.includes(query);
    });
  }
  
  if (activeFilters.value.includes('all') || activeFilters.value.length === 0) {
    return filtered;
  }
  
  return filtered.filter(item => {
    const type = item.type_fichier || '';
    return activeFilters.value.some(filter => {
      switch(filter) {
        case 'image': return isImageFile(type);
        case 'video': return isVideoFile(type);
        case 'audio': return isAudioFile(type);
        case 'pdf': return type === 'pdf';
        case 'document': return ['doc', 'docx', 'txt', 'odt', 'rtf'].includes(type);
        case 'spreadsheet': return ['xls', 'xlsx', 'csv', 'ods'].includes(type);
        case 'presentation': return ['ppt', 'pptx', 'odp'].includes(type);
        case 'archive': return ['zip', 'rar', '7z', 'tar', 'gz'].includes(type);
        default: return false;
      }
    });
  });
});

const isEmpty = computed(() => items.value.length === 0);
const noResults = computed(() => items.value.length > 0 && filteredItems.value.length === 0);
const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage));
const paginatedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  return filteredItems.value.slice(startIndex, startIndex + itemsPerPage);
});

const currentMedia = computed(() => filteredItems.value[currentMediaIndex.value] || {});

const visiblePages = computed(() => {
  const pages = [];
  let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);
  if (endPage - startPage + 1 < maxVisiblePages) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  for (let i = startPage; i <= endPage; i++) { pages.push(i); }
  return pages;
});

const leftEmptySpace = computed(() => {
  const startPage = visiblePages.value[0];
  return Math.max(0, Math.floor(maxVisiblePages / 2) - (currentPage.value - startPage));
});

const rightEmptySpace = computed(() => {
  const endPage = visiblePages.value[visiblePages.value.length - 1];
  return Math.max(0, Math.floor(maxVisiblePages / 2) - (endPage - currentPage.value));
});

// --- Méthodes ---

const resetAllFilters = () => { activeFilters.value = ['all']; };
const clearSearch = () => { searchQuery.value = ''; };

const downloadFile = async (item) => {
  try {
    const response = await downloadFichier(item.id);
    const blob = new Blob([response.data]);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${item.titre}.${item.type_fichier || 'file'}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const isImageFile = (type) => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(type.toLowerCase());
const isVideoFile = (type) => ['mp4', 'mov', 'avi', 'wmv', 'mkv', 'webm', 'flv', 'm4v'].includes(type.toLowerCase());
const isAudioFile = (type) => ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'].includes(type.toLowerCase());

const openDocument = (item) => {
  const index = filteredItems.value.findIndex(i => i.id === item.id);
  if (index !== -1) {
    currentMediaIndex.value = index;
    showLightbox.value = true;
    document.body.style.overflow = 'hidden';
  }
};

const closeLightbox = () => {
  showLightbox.value = false;
  document.body.style.overflow = '';
};

const handleKeydown = (e) => {
  if (showLightbox.value && e.key === 'Escape') closeLightbox();
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
  isUsingNativeControls.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});

// --- Watchers ---
watch([activeFilters, searchQuery], () => {
  currentPage.value = 1;
});
</script>

<template>
  <Header />
  
  <main class="plan-travaux-container">
    <div class="header-content">
      <h1 class="main-title">Investissements Travaux </h1>
    </div>

    <div v-if="isEmpty" class="empty-state">
      <div class="empty-icon">
        <font-awesome-icon :icon="['fas', 'images']" />
      </div>
      <h3>Aucun donnée disponible</h3>
      <p>Il n'y a actuellement aucun donnée à afficher.</p>
    </div>

    <div v-else class="gallery-container">
      <div class="cards-grid">
        <DocumentCard v-for="item in paginatedItems" :key="item.id" :item="item" :public-url="publicUrl" @click="openDocument(item)" />
      </div>
    </div>
    
    <div class="pagination-wrapper" v-if="totalPages > 1 && !isEmpty">
      <div class="pagination-container">
        <button class="pagination-arrow" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9.57 5.92969L3.5 11.9997L9.57 18.0697" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20.4999 12H3.66992" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div class="pagination-pages">
          <div v-if="leftEmptySpace > 0" class="empty-space" :style="{ width: leftEmptySpace * 45 + 'px' }"></div>
          <button 
            v-for="page in visiblePages" 
            :key="page"
            class="pagination-page"
            :class="{ 'pagination-page-active': page === currentPage, 'pagination-page-other': page !== currentPage }"
            @click="changePage(page)"
          >
            {{ page }}
          </button>
          <div v-if="rightEmptySpace > 0" class="empty-space" :style="{ width: rightEmptySpace * 45 + 'px' }"></div>
        </div>

        <button class="pagination-arrow" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M14.4302 5.92969L20.5002 11.9997L14.4302 18.0697" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3.5 12H20.33" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
      <div class="pagination-total">Page {{ currentPage }} sur {{ totalPages }}</div>
    </div>
    
    <LightboxModal :item="currentMedia" :show="showLightbox" :public-url="publicUrl" @close="closeLightbox" @download="downloadFile" />

    <PartiesPrenantesST :organisations-data="organisations"/>
  </main>
</template>

<style scoped>
.plan-travaux-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 3rem 1.5rem;
}

.gallery-container {
  width: 100%;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  width: 100%;
}

.pagination-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-top: 3rem;
}

.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  padding: 1rem;
}

.pagination-arrow {
  width: 40px;
  height: 40px;
  border: none;
  color: var(--color-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  background-color: transparent;
  padding: 0;
}

.pagination-arrow:hover:not(:disabled) {
  color: var(--color-accent);
  transform: scale(1.1);
}

.pagination-arrow:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.pagination-pages {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  position: relative;
  min-width: 300px;
  justify-content: center;
}

.empty-space {
  height: 45px;
  flex-shrink: 0;
}

.pagination-page {
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.pagination-page-other {
  background-color: var(--gris, #f0f0f0);
  color: var(--color-primary);
  font-size: 0.9rem;
  width: 35px;
  height: 35px;
}

.pagination-page-active {
  background: var(--color-accent);
  color: var(--color-text-light, white);
  font-size: 1rem;
  font-weight: bold;
  transform: scale(1.1);
  width: 45px;
  height: 45px;
}

.pagination-page:hover:not(.pagination-page-active) {
  background: #e0e0e0;
  transform: scale(1.05);
}

.pagination-total {
  font-size: 0.9rem;
  color: var(--vt-c-divider-dark-1);
  font-weight: 500;
  margin-top: 0.5rem;
}

/* --- EMPTY STATE --- */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  color: #666;
}

.empty-icon {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 20px;
}

/* --- RESPONSIVE --- */
@media (max-width: 1024px) {
  .cards-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .cards-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  .main-title {
    font-size: 1.8rem;
  }
  .pagination-pages { min-width: 200px; gap: 0.3rem; }
  .pagination-page { width: 35px; height: 35px; font-size: 0.8rem; }
  .empty-space { height: 35px; width: 35px !important; }
}

@media (max-width: 480px) {
  .cards-grid {
    grid-template-columns: 1fr;
  }
  .pagination-pages { min-width: 150px; }
  .pagination-page { width: 30px; height: 30px; font-size: 0.7rem; }
  .empty-space { height: 30px; width: 30px !important; }
}

.plan-card {
  animation: fadeUp 0.6s ease-out forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes fadeUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>