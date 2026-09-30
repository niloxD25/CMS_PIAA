<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  temoignagesData: {
    type: Array,
    default: () => []
  },
  projet_name: {
    type: String,
    default: null
  }
});

// Références réactives pour la pagination
const currentPage = ref(1);
const itemsPerPage = ref(6);
const maxVisiblePages = ref(5);

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

// Propriété calculée pour les témoignages filtrés
const filteredTemoignages = computed(() => {
  return props.temoignagesData.filter(t => !t.archived && t.deleted_at === null);
});

// Propriétés calculées pour la logique de pagination
const totalPages = computed(() => {
  return Math.ceil(filteredTemoignages.value.length / itemsPerPage.value);
});

const paginatedTemoignages = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const paginatedItems = filteredTemoignages.value.slice(startIndex, startIndex + itemsPerPage.value);
  
  const result = [];
  for (let i = 0; i < paginatedItems.length; i += 2) {
    result.push(paginatedItems.slice(i, i + 2));
  }
  return result;
});

const visiblePages = computed(() => {
  const pages = [];
  let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages.value / 2));
  let endPage = Math.min(totalPages.value, startPage + maxVisiblePages.value - 1);
  
  if (endPage - startPage + 1 < maxVisiblePages.value) {
    startPage = Math.max(1, endPage - maxVisiblePages.value + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
});

const leftEmptySpace = computed(() => {
  const startPage = visiblePages.value[0];
  return Math.max(0, Math.floor(maxVisiblePages.value / 2) - (currentPage.value - startPage));
});

const rightEmptySpace = computed(() => {
  const endPage = visiblePages.value[visiblePages.value.length - 1];
  return Math.max(0, Math.floor(maxVisiblePages.value / 2) - (endPage - currentPage.value));
});

// Méthode de pagination
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Réinitialiser la page actuelle si les données changent
watch(() => props.temoignagesData, () => {
  currentPage.value = 1;
});
</script>

<template>
  <div class="testimonials-container" v-if="filteredTemoignages.length > 0">
    <div class="header-content">
      <h1 class="main-title">Témoignages des bénéficiaires du {{ projet_name }}</h1>
      <p class="subtitle">
        Découvrez les retours d'expérience et les impressions de ceux qui bénéficient directement du projet. 
      </p>
    </div>
    
    <div class="testimonials-grid">
      <template v-for="(pair, rowIndex) in paginatedTemoignages" :key="rowIndex">
        <template v-for="(temoignage, colIndex) in pair" :key="temoignage.id">
          <template v-if="rowIndex % 2 === 0">
            <!-- Image -->
            <div class="image-card">
              <div class="testimonial-image">
                <img :src="`${publicUrl}${temoignage.image}`" :alt="temoignage.temoin">
              </div>
            </div>

            <!-- Texte -->
            <div class="text-card">
              <div class="testimonial-content">
                <div class="quote-container">
                  <div class="quote-scrollable">
                    "{{ temoignage.contenu }}"
                  </div>
                  <!-- Indicateur de défilement pour les longs textes -->
                  <div class="scroll-indicator" v-if="temoignage.contenu.length > 200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M8 3.5a.5.5 0 0 0-1 0v9a.5.5 0 0 0 1 0v-9z"/>
                      <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8 2.207 5.354 4.854a.5.5 0 1 1-.708-.708l3-3zm0 13.708a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L8 13.793l2.646-2.647a.5.5 0 0 1 .708.708l-3 3z"/>
                    </svg>
                    Défiler pour lire la suite
                  </div>
                </div>
                <div class="author">
                  <div class="name">{{ temoignage.temoin }}</div>
                  <div class="location">{{ temoignage.residence }}</div>
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <!-- Texte -->
            <div class="text-card">
              <div class="testimonial-content">
                <div class="quote-container">
                  <div class="quote-scrollable">
                    "{{ temoignage.contenu }}"
                  </div>
                  <!-- Indicateur de défilement pour les longs textes -->
                  <div class="scroll-indicator" v-if="temoignage.contenu.length > 200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M8 3.5a.5.5 0 0 0-1 0v9a.5.5 0 0 0 1 0v-9z"/>
                      <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8 2.207 5.354 4.854a.5.5 0 1 1-.708-.708l3-3zm0 13.708a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L8 13.793l2.646-2.647a.5.5 0 0 1 .708.708l-3 3z"/>
                    </svg>
                    Défiler pour lire la suite
                  </div>
                </div>
                <div class="author">
                  <div class="name">{{ temoignage.temoin }}</div>
                  <div class="location">{{ temoignage.residence }}</div>
                </div>
              </div>
            </div>

            <!-- Image -->
            <div class="image-card">
              <div class="testimonial-image">
                <img :src="`${publicUrl}${temoignage.image}`" :alt="temoignage.temoin">
              </div>
            </div>
          </template>
        </template>
      </template>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper" v-if="totalPages > 1">
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
  </div>
</template>

<style scoped>
.testimonials-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 2rem;
  margin-bottom: 3rem;
  background-color: transparent;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.image-card, .text-card {
  background: var(--color-text-light);
  border-radius: var(--border-radius);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 400px;
}

.text-card {
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.image-card:hover, .text-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.testimonial-image {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.testimonial-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.image-card:hover .testimonial-image img {
  transform: scale(1.05);
}

.testimonial-content {
  padding: 1.5rem;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.quote-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  position: relative;
}

.quote-scrollable {
  font-style: italic;
  color: var(--text-color);
  line-height: 1.6;
  font-size: 1rem;
  position: relative;
  overflow-y: auto;
  max-height: 200px;
  padding-right: 8px;
  flex: 1;
}

/* Style de la barre de défilement */
.quote-scrollable::-webkit-scrollbar {
  width: 4px;
}

.quote-scrollable::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.quote-scrollable::-webkit-scrollbar-thumb {
  background: var(--color-accent);
  border-radius: 10px;
  opacity: 0.6;
}

.quote-scrollable::-webkit-scrollbar-thumb:hover {
  background: var(--color-accent);
  opacity: 0.8;
}

.scroll-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--vt-c-divider-dark-1);
  margin-top: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: rgba(0, 0, 0, 0.03);
  border-radius: 4px;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.scroll-indicator svg {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-3px);
  }
  60% {
    transform: translateY(-2px);
  }
}

.text-card:hover .scroll-indicator {
  opacity: 1;
}

.author {
  text-align: right;
  margin-top: 1rem;
  padding-top: 1rem;
}

.name {
  font-weight: bold;
  color: var(--text-color);
  font-size: 1.1rem;
}

.location {
  font-size: 0.9rem;
  color: var(--vt-c-divider-dark-1);
}

/* Pagination Wrapper */
.pagination-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
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

/* Tablette */
@media (max-width: 1024px) {
  .testimonials-grid {
    grid-template-columns: repeat(1, 1fr);
  }
  .image-card, .text-card {
    height: 300px;
  }
  
  .quote-scrollable {
    max-height: 150px;
  }
  
  .pagination-pages {
    min-width: 250px;
  }
  
  .pagination-page {
    width: 40px;
    height: 40px;
    font-size: 0.8rem;
  }
  
  .empty-space {
    height: 40px;
    width: 40px !important;
  }
}
</style>