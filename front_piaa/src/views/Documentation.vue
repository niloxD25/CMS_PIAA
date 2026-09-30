<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useProjectStore } from '@/stores/projectStore';
import { downloadFichier } from '@/services/api';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';
import DocumentCard from '@/components/DocumentCard.vue';
import LightboxModal from '@/components/LightboxModal.vue';

const projectStore = useProjectStore();

// Variables réactives
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
  const fichiers = projectStore.fullData?.fichiers || [];
  return fichiers.map(fichier => ({
    id: fichier.id || Date.now() + Math.random(),
    titre: fichier.titre || 'Document sans titre',
    description: fichier.description || 'Aucune description',
    type_fichier: (fichier.type_fichier || 'unknown').toLowerCase(),
    chemin: fichier.chemin || '',
    date_upload: fichier.date_upload || new Date().toISOString()
  }));
});

// Filtres
const filters = [
  { value: 'all', label: 'Tous' },
  { value: 'image', label: 'Images' },
  { value: 'video', label: 'Vidéos' },
  { value: 'audio', label: 'Audios' },
  { value: 'pdf', label: 'PDF' },
  { value: 'document', label: 'Documents' },
  { value: 'spreadsheet', label: 'Tableurs' },
  { value: 'presentation', label: 'Présentations' },
  { value: 'archive', label: 'Archives' }
];

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
const activeFiltersDisplay = computed(() => {return activeFilters.value.filter(filter => filter !== 'all');});
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

// Méthode pour gérer les filtres multiples
const toggleFilter = (filterValue) => {
  if (filterValue === 'all') {
    activeFilters.value = ['all'];
  } else {
    const newFilters = activeFilters.value.filter(f => f !== 'all');
    
    if (newFilters.includes(filterValue)) {
      activeFilters.value = newFilters.filter(f => f !== filterValue);
    } else {
      activeFilters.value = [...newFilters, filterValue];
    }
    
    if (activeFilters.value.length === 0) {
      activeFilters.value = ['all'];
    }
  }
};

const isFilterActive = (filterValue) => {
  return activeFilters.value.includes(filterValue);
};

const resetAllFilters = () => {
  activeFilters.value = ['all'];
};

const clearSearch = () => {
  searchQuery.value = '';
};

const downloadFile = async (item) => {
  try {
    const response = await downloadFichier(item.id);
    
    const blob = new Blob([response.data]);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    
    const fileExtension = item.type_fichier || 'file';
    link.download = `${item.titre}.${fileExtension}`;
    
    document.body.appendChild(link);
    link.click();
    
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    console.log('Fichier téléchargé avec succès:', item.titre);
    
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error);
    alert('Erreur lors du téléchargement du fichier');
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const getFilterIcon = (filterType) => {
  const icons = {
    'all': 'fa-folder-open',
    'image': 'fa-image',
    'video': 'fa-video',
    'audio': 'fa-music',
    'pdf': 'fa-file-pdf',
    'document': 'fa-file-word',
    'spreadsheet': 'fa-file-excel',
    'presentation': 'fa-file-powerpoint',
    'archive': 'fa-file-archive'
  };
  return ['fas', icons[filterType] || 'fa-file'];
};

const isImageFile = (type) => {
  const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
  return imageTypes.includes(type.toLowerCase());
};

const isVideoFile = (type) => {
  const videoTypes = ['mp4', 'mov', 'avi', 'wmv', 'mkv', 'webm', 'flv', 'm4v'];
  return videoTypes.includes(type.toLowerCase());
};

const isAudioFile = (type) => {
  const audioTypes = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'];
  return audioTypes.includes(type.toLowerCase());
};

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
  if (!showLightbox.value) return;
  
  if (e.key === 'Escape') {
    closeLightbox();
  }
};

// Lifecycle
onMounted(() => {  
  window.addEventListener('keydown', handleKeydown);
  isUsingNativeControls.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
});

// --- Watchers ---
watch([activeFilters, searchQuery], () => {
  currentPage.value = 1;
});
</script>

<template>
  <Header />

  <main class="documents-container">
    <div class="header-content">
      <h1 class="main-title">Documentation</h1>
      <p class="subtitle">
        Accédez à l'ensemble des documents techniques, rapports d'études et supports de présentation
        relatifs au Schéma Directeur d'Assainissement.
      </p>
    </div>
    <!-- Barre de recherche -->
    <div class="search-container">
      <div class="search-input-wrapper">
        <font-awesome-icon :icon="['fas', 'search']" class="search-icon" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Rechercher par nom, description ou type..."
          class="search-input"
          @input="currentPage = 1"
        />
        <button
          v-if="searchQuery"
          @click="clearSearch"
          class="search-clear-btn"
          title="Effacer la recherche"
        >
          <font-awesome-icon :icon="['fas', 'times']" />
        </button>
      </div>
      
      <!-- Compteur de résultats -->
      <div v-if="!isEmpty" class="search-results-info">
        <span v-if="searchQuery && filteredItems.length !== items.length">
          {{ filteredItems.length }} résultat{{ filteredItems.length > 1 ? 's' : '' }} trouvé{{ filteredItems.length > 1 ? 's' : '' }}
        </span>
        <span v-else-if="searchQuery">
          {{ items.length }} document{{ items.length > 1 ? 's' : '' }} au total
        </span>
      </div>
    </div>
    
    <div class="filters" v-if="filters.length > 0 && !isEmpty">
      <button 
        v-for="filter in filters" 
        :key="filter.value" 
        :class="['filter-btn', { active: isFilterActive(filter.value) }]"
        @click="toggleFilter(filter.value)"
      >
        <font-awesome-icon :icon="getFilterIcon(filter.value)" class="filter-icon" />
        {{ filter.label }}
      </button>
    </div>
    
    <!-- Liste des filtres actifs -->
    <div v-if="activeFiltersDisplay.length > 0" class="active-filters">
      <span class="filters-label">Filtres actifs :</span>
      
      <span v-for="filter in activeFiltersDisplay" :key="filter" class="filter-tag">
        {{ filters.find(f => f.value === filter)?.label }}
        <button @click="toggleFilter(filter)" class="tag-remove">×</button>
      </span>
      
      <button v-if="activeFiltersDisplay.length > 0" class="reset-all-btn" @click="resetAllFilters">
        <font-awesome-icon :icon="['fas', 'times']" />
        Tout effacer
      </button>
    </div>
    
    <!-- État vide - aucun document disponible -->
    <div v-if="isEmpty" class="empty-state">
      <div class="empty-icon">
        <font-awesome-icon :icon="['fas', 'folder-open']" />
      </div>
      <h3>Aucun document disponible</h3>
      <p>Il n'y a actuellement aucun document à afficher.</p>
    </div>

    <!-- Aucun résultat avec la recherche -->
    <div v-else-if="noResults || (searchQuery && filteredItems.length === 0)" class="empty-state">
      <div class="empty-icon">
        <font-awesome-icon :icon="['fas', 'search']" />
      </div>
      <h3 v-if="searchQuery">Aucun résultat pour "{{ searchQuery }}"</h3>
      <h3 v-else>Aucun résultat trouvé</h3>
      <p v-if="searchQuery">Aucun document ne correspond à votre recherche.</p>
      <p v-else>Aucun document ne correspond aux filtres sélectionnés.</p>
      <div class="search-empty-actions">
        <button v-if="searchQuery" class="reset-search-btn" @click="clearSearch">
          <font-awesome-icon :icon="['fas', 'backspace']" />
          Effacer la recherche
        </button>
        <button v-if="activeFiltersDisplay.length > 0" class="reset-filter-btn" @click="resetAllFilters">
          <font-awesome-icon :icon="['fas', 'times']" />
          Réinitialiser les filtres
        </button>
      </div>
    </div>

    <!-- Documents disponibles -->
    <div v-else class="documents-grid">
      <DocumentCard 
        v-for="(item, index) in paginatedItems" 
        :key="item.id" 
        :item="item"
        :public-url="publicUrl"
        @click="openDocument(item)"
      />
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper" v-if="totalPages > 1 && !isEmpty">
      <div class="pagination-container">
        <!-- Flèche précédente -->
        <button 
          class="pagination-arrow" 
          :disabled="currentPage === 1" 
          @click="changePage(currentPage - 1)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9.57 5.92969L3.5 11.9997L9.57 18.0697" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20.4999 12H3.66992" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <!-- Pages -->
        <div class="pagination-pages">
          <div v-if="leftEmptySpace > 0" class="empty-space" :style="{ width: leftEmptySpace * 45 + 'px' }"></div>

          <!-- Pages visibles -->
          <button 
            v-for="page in visiblePages" 
            :key="page"
            class="pagination-page"
            :class="{
              'pagination-page-active': page === currentPage,
              'pagination-page-other': page !== currentPage
            }"
            @click="changePage(page)"
          >
            {{ page }}
          </button>

          <!-- Espace vide à droite pour centrer -->
          <div v-if="rightEmptySpace > 0" class="empty-space" :style="{ width: rightEmptySpace * 45 + 'px' }"></div>
        </div>

        <!-- Flèche suivante -->
        <button 
          class="pagination-arrow" 
          :disabled="currentPage === totalPages" 
          @click="changePage(currentPage + 1)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M14.4302 5.92969L20.5002 11.9997L14.4302 18.0697" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3.5 12H20.33" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <!-- Total des pages -->
      <div class="pagination-total">
        Page {{ currentPage }} sur {{ totalPages }}
      </div>
    </div>
    
    <!-- Lightbox -->
    <LightboxModal 
      :item="currentMedia"
      :show="showLightbox"
      :public-url="publicUrl"
      @close="closeLightbox"
      @download="downloadFile"
    />

    <PartiesPrenantesST :organisations-data="organisations"/>
  </main>
</template>

<style scoped>
.documents-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  font-size: 4rem;
  color: var(--vt-c-divider-dark-1);
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 10px;
  color: var(--text-color);
}

.empty-state p {
  font-size: 1.1rem;
  margin-bottom: 20px;
  max-width: 400px;
  line-height: 1.5;
}

.active-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 30px;
  padding: 15px;
  background: var(--gris-light);
  border-radius: var(--border-radius);
  align-items: center;
}

.filters-label {
  font-weight: 600;
  color: var(--text-color);
  font-size: 0.9rem;
  margin-right: 10px;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--text-color);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.tag-remove {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1rem;
  padding: 0;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s ease;
}

.tag-remove:hover {
  background: var(--color-accent);
}

.reset-all-btn {
  background: var(--vt-c-divider-dark-1);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
  margin-left: auto;
}

.reset-all-btn:hover {
  background: var(--color-error);
}

.reset-filter-btn {
  background-color: var(--color-primary);
  color: var(--color-text-light);
  border: none;
  padding: 10px 20px;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.reset-filter-btn:hover {
  background-color: var(--color-accent);
}

.filters {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-bottom: 2.5rem;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  border: none;
  background: var(--color-text-light);
  color: var(--color-primary);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.filter-btn:hover {
  background: var(--color-accent);
  color: var(--color-text-light);
}

.filter-btn.active {
  background: var(--color-accent);
  color: var(--color-text-light);
}

.filter-icon {
  font-size: 1.2rem;
}

/* Styles pour la barre de recherche */
.search-container {
  margin-bottom: 2rem;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
}

.search-input {
  width: 100%;
  padding: 1rem 3rem 1rem 3rem;
  border: 2px solid var(--gris);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: all 0.3s ease;
  background-color: var(--color-text-light);
  color: var(--color-primary);
}

.search-input:focus {
  outline: none;
  border-color: var(--color-accent);
  box-shadow: 0 2px 12px rgba(var(--color-accent-rgb), 0.2);
}

.search-input::placeholder {
  color: var(--vt-c-divider-dark-2);
  font-size: 0.95rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--vt-c-divider-dark-2);
  font-size: 1rem;
}

.search-clear-btn {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--vt-c-divider-dark-2);
  cursor: pointer;
  padding: 0.3rem;
  border-radius: 50%;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
}

.search-clear-btn:hover {
  background-color: var(--gris);
  color: var(--color-primary);
}

.search-results-info {
  margin-top: 0.5rem;
  font-size: 0.9rem;
  color: var(--vt-c-divider-dark-1);
  text-align: center;
  font-weight: 500;
}

/* État vide pour les résultats de recherche */
.search-empty-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

.reset-search-btn {
  background-color: var(--vt-c-divider-dark-1);
  color: var(--color-text-light);
  border: none;
  padding: 10px 20px;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.reset-search-btn:hover {
  background-color: var(--color-error);
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

/* Pagination Styles */
.pagination-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-top: 3rem;
  padding-top: 2rem;
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
  border-radius: 50%;
}

.pagination-arrow:hover:not(:disabled) {
  color: var(--color-accent);
  transform: scale(1.1);
  background-color: var(--vt-c-divider-light-2);
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
  background-color: var(--gris);
  color: var(--color-primary);
  font-size: 0.9rem;
  width: 35px;
  height: 35px;
}

.pagination-page-active {
  background: var(--color-accent);
  color: var(--color-text-light);
  font-size: 1rem;
  font-weight: bold;
  transform: scale(1.1);
  width: 45px;
  height: 45px;
}

.pagination-page:hover:not(.pagination-page-active) {
  background: #f0f0f0;
  transform: scale(1.05);
}

.pagination-total {
  font-size: 0.9rem;
  color: var(--vt-c-divider-dark-1);
  font-weight: 500;
  margin-top: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .documents-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
  }
}

@media (max-width: 768px) {
  .pagination-container {
    gap: 1rem;
  }
  
  .pagination-arrow {
    width: 35px;
    height: 35px;
  }
  
  .pagination-pages {
    min-width: 200px;
    gap: 0.3rem;
  }
  
  .pagination-page {
    width: 35px;
    height: 35px;
    font-size: 0.8rem;
  }
  
  .empty-space {
    height: 35px;
    width: 35px !important;
  }
  
  .pagination-total {
    font-size: 0.8rem;
  }

  .documents-container {
    padding: 1.5rem;
  }
  
  .main-title {
    font-size: 2rem;
  }
  
  .documents-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .pagination-pages {
    min-width: 150px;
  }
  
  .pagination-page {
    width: 30px;
    height: 30px;
    font-size: 0.7rem;
  }
  
  .empty-space {
    height: 30px;
    width: 30px !important;
  }
  
  .pagination-arrow {
    width: 30px;
    height: 30px;
  }
  
  .pagination-total {
    font-size: 0.75rem;
  }

  .documents-grid {
    grid-template-columns: 1fr;
  }
  
  .filters {
    gap: 0.5rem;
  }
  
  .filter-btn {
    padding: 0.5rem 0.8rem;
    font-size: 0.85rem;
  }
}

.document-card {
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}

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
</style>