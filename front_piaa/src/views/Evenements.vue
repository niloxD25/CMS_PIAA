<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projectStore';
import { getEtatsEvenement } from '@/services/api';
import { slugify } from '@/utils/stringUtils';

import Header from '@/components/Header.vue';
import EventCard from '@/components/EventCard.vue';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';

const router = useRouter();
const projectStore = useProjectStore();

// --- Données réactives liées au Store ---
const evenements = computed(() => projectStore.fullData?.evenements || []);
const organisations = computed(() => projectStore.fullData?.parties_prenantes || []);

const loadingStatus = ref(true);
const statusFilter = ref('all');
const searchQuery = ref('');
const dateDebutFilter = ref('');
const dateFinFilter = ref('');
const statusOptions = ref([]);
const onlyActualites = ref(false);

const isFocused = reactive({
  search: false,
  status: false,
  dateDebut: false,
  dateFin: false
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 4;
const maxVisiblePages = 5;

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

// --- Logique de Filtrage ---
const filteredEvenements = computed(() => {
  let filtered = evenements.value;
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(event => 
      event.etat_courant?.etats_evenement?.etat === statusFilter.value
    );
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(event => 
      (event.titre?.toLowerCase().includes(query)) ||
      (event.description?.toLowerCase().includes(query))
    );
  }
  
  if (dateDebutFilter.value) {
    const filterDate = new Date(dateDebutFilter.value).setHours(0, 0, 0, 0);
    filtered = filtered.filter(event => event.date_debut && new Date(event.date_debut) >= filterDate);
  }
  
  if (dateFinFilter.value) {
    const filterDate = new Date(dateFinFilter.value).setHours(23, 59, 59, 999);
    filtered = filtered.filter(event => event.date_fin && new Date(event.date_fin) <= filterDate);
  }

  if (onlyActualites.value) {
    filtered = filtered.filter(event => event.est_actualite === true);
  }
  
  return filtered;
});

// --- Pagination Computeds ---
const totalPages = computed(() => Math.ceil(filteredEvenements.value.length / itemsPerPage));
const paginatedEvents = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  return filteredEvenements.value.slice(startIndex, startIndex + itemsPerPage);
});

const visiblePages = computed(() => {
  const pages = [];
  let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);
  if (endPage - startPage + 1 < maxVisiblePages) startPage = Math.max(1, endPage - maxVisiblePages + 1);
  for (let i = startPage; i <= endPage; i++) pages.push(i);
  return pages;
});

const leftEmptySpace = computed(() => Math.max(0, Math.floor(maxVisiblePages / 2) - (currentPage.value - visiblePages.value[0])));
const rightEmptySpace = computed(() => Math.max(0, Math.floor(maxVisiblePages / 2) - (visiblePages.value[visiblePages.value.length - 1] - currentPage.value)));

// --- Méthodes ---
const setFocus = (field, isFocusedState) => { isFocused[field] = isFocusedState; };

const goToDetails = (event) => {
  router.push({
    name: 'evenement-details',
    params: { slug: slugify(event.titre), id: event.id }
  });
};

const resetAllFilters = () => {
  statusFilter.value = 'all';
  searchQuery.value = '';
  dateDebutFilter.value = '';
  dateFinFilter.value = '';
  onlyActualites.value = false;
  currentPage.value = 1;
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const formatDateDisplay = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getMaxDate = () => {
  const maxDate = new Date();
  maxDate.setFullYear(maxDate.getFullYear() + 5);
  return maxDate.toISOString().split('T')[0];
};

onMounted(async () => {
  try {
    const etatsData = await getEtatsEvenement();
    statusOptions.value = [...new Set(etatsData.data.data.map(etat => etat.etat))];
  } catch (error) {
    console.error('Erreur statuts:', error);
  } finally {
    loadingStatus.value = false;
  }
});
</script>

<template>
  <Header />
  
  <div class="evenements-container">
    <div class="header-content">
      <h1 class="main-title">Actualités et Événements</h1>
      <p class="subtitle">
        Restez informé des dernières nouvelles, rencontres et activités du projet. 
        Suivez l'actualité et participez aux prochains événements.
      </p>
    </div>
    
    <div class="filters-row">
      <div class="filter-group compact">
        <label for="status-filter" :class="{ focused: isFocused.status }">Statut</label>
        <select 
          id="status-filter" 
          v-model="statusFilter"
          @focus="setFocus('status', true)" 
          @blur="setFocus('status', false)"
        >
          <option value="all">Tous</option>
          <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
        </select>
      </div>
      
      <div class="filter-group compact">
        <label for="date-debut" :class="{ focused: isFocused.dateDebut }">Début</label>
        <input 
          id="date-debut" 
          type="date" 
          v-model="dateDebutFilter"
          :max="dateFinFilter || getMaxDate()"
          @focus="setFocus('dateDebut', true)" 
          @blur="setFocus('dateDebut', false)"
        >
      </div>
      
      <div class="filter-group compact">
        <label for="date-fin" :class="{ focused: isFocused.dateFin }">Fin</label>
        <input 
          id="date-fin" 
          type="date" 
          v-model="dateFinFilter"
          :min="dateDebutFilter"
          :max="getMaxDate()"
          @focus="setFocus('dateFin', true)" 
          @blur="setFocus('dateFin', false)"
        >
      </div>
      
      <div class="filter-group compact search-group">
        <label for="search" :class="{ focused: isFocused.search }">Recherche</label>
        <input 
          id="search" 
          type="text" 
          v-model="searchQuery" 
          placeholder="Nom ou description..."
          @focus="setFocus('search', true)" 
          @blur="setFocus('search', false)"
        >
      </div>

      <div class="checkbox-container">
        <label class="checkbox-label">
          <input 
            type="checkbox" 
            v-model="onlyActualites"
          >
          Actualités uniquement
        </label>
      </div>

      <div class="filter-actions">
        <button 
          v-if="dateDebutFilter || dateFinFilter" 
          class="reset-btn"
          @click="resetDateFilters"
          title="Réinitialiser les dates"
        >
          <font-awesome-icon :icon="['fas', 'times']" />
        </button>
        <button 
          v-if="statusFilter !== 'all' || searchQuery || dateDebutFilter || dateFinFilter" 
          class="reset-all-btn"
          @click="resetAllFilters"
          title="Réinitialiser tous les filtres"
        >
          <font-awesome-icon :icon="['fas', 'redo']" />
        </button>
      </div>
    </div>
    
    <!-- Résumé des filtres actifs -->
    <div v-if="statusFilter !== 'all' || dateDebutFilter || dateFinFilter || searchQuery" class="active-filters">
      <span class="filters-label">Filtres actifs :</span>
      
      <span v-if="statusFilter !== 'all'" class="filter-tag">
        Statut: {{ statusOptions.find(s => s === statusFilter) }}
        <button @click="statusFilter = 'all'" class="tag-remove">×</button>
      </span>
      
      <span v-if="dateDebutFilter" class="filter-tag">
        Début: {{ formatDateDisplay(dateDebutFilter) }}
        <button @click="dateDebutFilter = ''" class="tag-remove">×</button>
      </span>
      
      <span v-if="dateFinFilter" class="filter-tag">
        Fin: {{ formatDateDisplay(dateFinFilter) }}
        <button @click="dateFinFilter = ''" class="tag-remove">×</button>
      </span>
      
      <span v-if="searchQuery" class="filter-tag">
        "{{ searchQuery }}"
        <button @click="searchQuery = ''" class="tag-remove">×</button>
      </span>

      <span v-if="onlyActualites" class="filter-tag">
        Actualités
        <button @click="onlyActualites = false" class="tag-remove">×</button>
      </span>
    </div>
    
    <div v-if="loadingStatus" class="loading">
      <div class="spinner"></div>
      Chargement des événements...
    </div>
    
    <div v-else-if="!filteredEvenements || filteredEvenements.length === 0" class="no-results">
      <div class="no-results-icon">
        <font-awesome-icon :icon="['fas', 'calendar-times']" />
      </div>
      <h3>Aucun événement trouvé</h3>
    </div>
    
    <div v-else class="events-grid">
      <div 
        v-for="event in paginatedEvents" 
        :key="event.id" 
        @click="goToDetails(event)"
        role="button"
        tabindex="0"
      >

        <EventCard :event="event"/>

      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper" v-if="totalPages > 1 && filteredEvenements.length > 0">
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

    <PartiesPrenantesST :organisations-data="organisations"/>
  </div>
</template>

<style scoped>
.evenements-container {
  max-width: 70%;
  margin: 0 auto;
  padding: 20px;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

/* Filtres sur une ligne en desktop */
.filters-row {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 30px;
  padding: 20px;
  border-radius: var(--border-radius);
  align-items: end;
  flex-wrap: wrap;
}

.filter-group.compact {
  flex: 1;
  min-width: 120px;
  max-width: 200px;
}

.filter-group.compact.search-group {
  flex: 2;
  min-width: 200px;
  max-width: 300px;
}

.filter-group label {
  font-weight: 600;
  color: var(--text-color);
  transition: all 0.3s ease;
  font-size: 0.8rem;
  margin-bottom: 5px;
}

.filter-group label.focused {
  color: var(--color-accent);
}

/* Styles pour les inputs compacts */
.filter-group input,
.filter-group select {
  width: 100%;
  height: 45px;
  padding: 0.6rem 1rem;
  border: 2px solid transparent;
  background-color: var(--gris);
  border-radius: var(--border-radius);
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.filter-group input:hover,
.filter-group select:hover {
  border-color: var(--color-accent);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.filter-group input:focus,
.filter-group select:focus {
  outline: none;
  border: 2px solid var(--color-accent);
  box-shadow: 0 0 0 4px rgba(var(--color-accent-rgb, 52, 152, 219), 0.15);
  transform: translateY(-2px);
  animation: pulse 1.5s infinite;
}

/* Styles spécifiques pour le select */
.filter-group select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px;
  padding-right: 40px;
  cursor: pointer;
}

/* CHECKBOX STYLES */
.checkbox-container {
  display: flex;
  justify-content: center;
  width: 100%;
  margin-top: 10px;
  order: 100;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: var(--color-primary);
  cursor: pointer;
  padding: 8px 16px;
  background: var(--gris-light);
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.checkbox-label:hover {
  background: var(--gris);
  transform: translateY(-2px);
}

.checkbox-label input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: var(--color-accent);
  border-radius: 4px;
  transition: all 0.3s ease;
}

.checkbox-label input[type="checkbox"]:checked {
  accent-color: var(--color-accent);
  transform: scale(1.1);
}

.checkbox-label input[type="checkbox"]:focus {
  outline: 2px solid var(--color-accent);
  outline-offset: 2px;
}

/* Actions des filtres */
.filter-actions {
  display: flex;
  gap: 8px;
  align-items: end;
  height: 45px;
}

.reset-btn, .reset-all-btn {
  background: var(--vt-c-divider-dark-1);
  color: white;
  border: none;
  padding: 10px;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 45px;
  width: 45px;
}

.reset-btn:hover {
  background: var(--color-error);
  transform: translateY(-1px);
}

.reset-all-btn:hover {
  background: var(--color-accent);
  transform: translateY(-1px);
}

/* Filtres actifs */
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
  color: var(--color-primary);
  font-size: 0.9rem;
  margin-right: 10px;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--color-primary);
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

/* Animation pulse */
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
  100% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
}

.no-results {
  text-align: center;
  padding: 60px 40px;
  font-size: 18px;
  color: var(--vt-c-divider-dark-1);
  border-radius: var(--border-radius);
}

.no-results-icon {
  font-size: 4rem;
  color: var(--vt-c-divider-dark-1);
  margin-bottom: 20px;
}

.no-results h3 {
  color: var(--color-primary);
  margin-bottom: 10px;
}

.loading {
  text-align: center;
  padding: 40px;
  font-size: 18px;
  color: #7f8c8d;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
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

.events-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
}

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

@media (max-width: 992px) {
  .events-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-row {
    grid-template-columns: 1fr 1fr;
  }
}
</style>