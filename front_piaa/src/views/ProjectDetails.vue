<script setup>
import { useProjectStore } from '@/stores/projectStore';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { downloadFichier } from '@/services/api';
import Header from '@/components/Header.vue';
import PartenariatFinancier from '@/components/PartenariatFinancier.vue';
import PartiesPrenantes from '@/components/PartiesPrenantes.vue';
import ListeActualites from '@/components/ListeActualites.vue';
import DocumentCard from '@/components/DocumentCard.vue';
import LightboxModal from '@/components/LightboxModal.vue';
import Equipes from '@/components/Equipes.vue';
import { useAboutStore } from '@/stores/useAboutStore'
import { RouterLink, useRouter } from 'vue-router';

const projectStore = useProjectStore();
const { currentProjectArchive: projet } = storeToRefs(projectStore);
const showLightbox = ref(false);
const selectedMedia = ref(null);
const showRealisationModal = ref(false);
const selectedRealisation = ref(null);
const searchQuery = ref('');

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const ITEMS_PER_PAGE = 4;
const TESTIMONIALS_PER_PAGE = 3;

const pages = ref({
  mots: 1,
  realisations: 1,
  plans: 1,
  interviews: 1,
  fichiers: 1,
  temoignages: 1,
  actions: 1
});

// Réinitialiser la pagination si la recherche change
watch(searchQuery, () => {
  Object.keys(pages.value).forEach(key => {
    pages.value[key] = 1;
  });
});

const router = useRouter()
const aboutStore = useAboutStore()

const goToAbout = (projetData) => {
  aboutStore.setData(projetData)
  router.push('/about')
}

const filterList = (list, keys) => {
  if (!list) return [];
  if (!searchQuery.value) return list;
  
  const query = searchQuery.value.toLowerCase();
  
  return list.filter(item => {
    return keys.some(key => {
      const val = key.split('.').reduce((obj, property) => obj?.[property], item);
      
      return val && val.toString().toLowerCase().includes(query);
    });
  });
};

// Données filtrées (Computed)
const filteredActualites = computed(() => filterList(projet.value?.evenements, ['titre', 'description']));
const filteredMots = computed(() => filterList(projet.value?.mots, ['contenu', 'temoin', 'residence']));
const filteredRealisations = computed(() => filterList(projet.value?.realisations, ['titre', 'description', 'beneficiaires', 'impacts_potentiel']));
const filteredPlans = computed(() => filterList(projet.value?.plans_reperage, ['titre', 'description']));
const filteredInterviews = computed(() => filterList(projet.value?.interviews, ['titre', 'description']));
const filteredFichiers = computed(() => filterList(projet.value?.fichiers, ['titre', 'description', 'type_fichier']));
const filteredTemoignages = computed(() => filterList(projet.value?.temoignages, ['contenu', 'temoin', 'residence']));
const filteredActions = computed(() => filterList(projet.value?.actions_proximite, ['titre', 'description']));
const filteredEquipes = computed(() => filterList(projet.value?.projet_personnes, ['personnes.nom', 'personnes.prenom', 'personnes.poste']));

// Références pour les sections
const sectionRefs = ref([]);
const currentActiveSection = ref(0);

const visibleSections = ref([]);

const updateVisibleSections = () => {
  if (!projet.value) return;
  
  const allSections = [
    { id: 'actualites', title: 'Actualités', data: filteredActualites.value },
    { id: 'mots', title: 'Paroles responsables', data: filteredMots.value },
    { id: 'equipes', title: 'Équipes', data: filteredEquipes.value },
    { id: 'realisations', title: 'Réalisations', data: filteredRealisations.value },
    { id: 'plans', title: 'Investissements travaux', data: filteredPlans.value },
    { id: 'interviews', title: 'Interviews', data: filteredInterviews.value },
    { id: 'fichiers', title: 'Documentation', data: filteredFichiers.value },
    { id: 'temoignages', title: 'Témoignages', data: filteredTemoignages.value },
    { id: 'actions', title: 'Actions proximité', data: filteredActions.value },
    { id: 'partenariats', title: 'Partenariats', data: projet.value.partenariats_financiers },
    { id: 'parties-prenantes', title: 'Parties Prenantes', data: projet.value.parties_prenantes }
  ];
  
  visibleSections.value = allSections.filter(section => section.data && section.data.length > 0);
};

watch([
  filteredActualites, 
  filteredMots, 
  filteredEquipes,
  filteredRealisations, 
  filteredPlans, 
  filteredInterviews, 
  filteredFichiers, 
  filteredTemoignages, 
  filteredActions
], async () => {
  updateVisibleSections();  
  await nextTick();  
  setupScrollAnimation();  
  setupSectionObserver();
});

// Fonction pour naviguer vers une section
const scrollToSection = (index) => {
  const targetElement = sectionRefs.value[index];
  if (targetElement) {
    targetElement.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    currentActiveSection.value = index;
  }
};

// Fonction pour initialiser l'animation au scroll
let scrollObserver = null;

const setupScrollAnimation = () => {
  if (scrollObserver) {
    scrollObserver.disconnect();
  }

  const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
  
  scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        scrollObserver.unobserve(entry.target);
      }
    });
  }, observerOptions);

  const elements = document.querySelectorAll('.reveal-on-scroll');
  elements.forEach((el) => {
    if (!el.classList.contains('is-visible')) {
      scrollObserver.observe(el);
    }
  });
};

// Observer pour détecter la section active
const setupSectionObserver = () => {
  
  const sectionObserverOptions = { root: null, rootMargin: '-50% 0px -50% 0px', threshold: 0 };
  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-section-index'));
        if (!isNaN(index)) currentActiveSection.value = index;
      }
    });
  }, sectionObserverOptions);

  sectionRefs.value.forEach((section, index) => {
    if (section) {
      section.setAttribute('data-section-index', index);
      sectionObserver.observe(section);
    }
  });
};

const setSectionRef = (el, index) => { sectionRefs.value[index] = el; };

// Fonction générique pour paginer
const paginate = (list, currentPage, perPage) => {
  if (!list) return [];
  const start = (currentPage - 1) * perPage;
  return list.slice(start, start + perPage);
};

const totalPages = (list, perPage) => Math.ceil((list?.length || 0) / perPage);

const nextPage = (section, list, perPage) => {
  if (pages.value[section] * perPage < list.length) pages.value[section]++;
};

const prevPage = (section) => {
  if (pages.value[section] > 1) pages.value[section]--;
};

const paginatedMots = computed(() => paginate(filteredMots.value, pages.value.mots, TESTIMONIALS_PER_PAGE));
const paginatedRealisations = computed(() => paginate(filteredRealisations.value, pages.value.realisations, ITEMS_PER_PAGE));
const paginatedPlans = computed(() => paginate(filteredPlans.value, pages.value.plans, ITEMS_PER_PAGE));
const paginatedInterviews = computed(() => paginate(filteredInterviews.value, pages.value.interviews, ITEMS_PER_PAGE));
const paginatedFichiers = computed(() => paginate(filteredFichiers.value, pages.value.fichiers, ITEMS_PER_PAGE));
const paginatedTemoignages = computed(() => paginate(filteredTemoignages.value, pages.value.temoignages, TESTIMONIALS_PER_PAGE));
const paginatedActions = computed(() => paginate(filteredActions.value, pages.value.actions, ITEMS_PER_PAGE));

const openRealisation = (realisation) => {
  selectedRealisation.value = realisation;
  showRealisationModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeRealisation = () => {
  showRealisationModal.value = false;
  selectedRealisation.value = null;
  document.body.style.overflow = '';
};

const formatDate = (dateString) => {
  if (!dateString) return null;
  return new Date(dateString).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
};

const calculateEndDate = (startDate, years) => {
  if (!startDate || !years) return 'Non définie';
  try {
    const date = new Date(startDate);
    date.setFullYear(date.getFullYear() + parseInt(years));
    return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
  } catch (e) { return 'Date invalide'; }
};

const currentMedia = computed(() => selectedMedia.value || {});

const openDocument = (item) => {
  if (item) {
    selectedMedia.value = item;
    showLightbox.value = true;
    document.body.style.overflow = 'hidden';
  }
};

const closeLightbox = () => {
  showLightbox.value = false;
  selectedMedia.value = null;
  document.body.style.overflow = '';
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
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error);
    alert('Erreur lors du téléchargement du fichier');
  }
};

onMounted(async () => {
  await nextTick();
  setupScrollAnimation();
  updateVisibleSections();
  await nextTick();
  setupSectionObserver();
});
</script>

<template>
  <main class="page-detail">
    <Header />

    <div v-if="visibleSections.length > 0" class="stars-navigation">
      <button
        v-for="(section, index) in visibleSections"
        :key="`star-${section.id}`"
        class="star-button"
        :class="{ active: currentActiveSection === index }"
        @click="scrollToSection(index)"
        :aria-label="`Aller à la section ${section.title}`"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" 
                :stroke="currentActiveSection === index ? 'var(--color-accent)' : 'var(--vt-c-divider-dark-1)'"
                :stroke-width="currentActiveSection === index ? '2' : '1'"
                :fill="currentActiveSection === index ? 'var(--color-accent)' : 'transparent'"/>
        </svg>
        <span class="star-tooltip">{{ section.title }}</span>
      </button>
    </div>

    <div v-if="projet" class="detail-container">
      <section class="hero-section reveal-on-scroll">
        <div class="hero-content">
          <div class="hero-logo" v-if="projet.logo">
            <img :src="`${publicUrl}${projet.logo}`" :alt="projet.nom" />
          </div>
          <div class="hero-text">
            <span class="badge">{{ projet.devise }}</span>
            <h1>{{ projet.nom }} ({{ projet.acronymes }})</h1>
            <p class="project-description" v-if="projet.description">{{ projet.description }}</p>
            <button class="about-btn" @click="goToAbout(projet)">
                À propos de {{ projet.acronymes }}
            </button>
          </div>
        </div>
      </section>

      <section class="info-grid section-spacer reveal-on-scroll">
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-calendar-plus"/></span>
          <div class="info-data">
            <label>Début du projet</label>
            <strong>{{ formatDate(projet.debut) }}</strong>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-hourglass-half"/></span>
          <div class="info-data">
            <label>Durée totale</label>
            <strong>{{ projet.duree_annee }} {{ projet.duree_annee > 1 ? 'ans' : 'an' }}</strong>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-calendar-check"/></span>
          <div class="info-data">
            <label>Fin estimée</label>
            <strong>{{ calculateEndDate(projet.debut, projet.duree_annee) }}</strong>
          </div>
        </div>
      </section>

      <section class="search-section reveal-on-scroll">
        <div class="search-container">
            <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="search-icon" />
            <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher dans le projet..." 
            class="search-input"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear">
            <font-awesome-icon icon="fa-solid fa-xmark" />
            </button>
        </div>

        <div v-if="searchQuery" class="search-results-container">
            <p class="search-results-info">
            Résultats pour : <strong>"{{ searchQuery }}"</strong>
            </p>
            
            <div class="search-badges-list">
                <div 
                    v-for="section in visibleSections" 
                    :key="`badge-${section.id}`" 
                    class="section-count-badge"
                    @click="scrollToSection(visibleSections.indexOf(section))"
                >
                    <span class="badge-label">{{ section.title }}</span>
                    <span class="badge-number">{{ section.data.length }}</span>
                </div>
            </div>
        </div>
        </section>

      <div class="content-divider"></div>

      <section v-if="filteredActualites.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'actualites'))" 
               class="section-spacer reveal-on-scroll">
        <ListeActualites :actualites="filteredActualites" :title="'Actualités et Evénements'"/>
      </section>

      <section v-if="filteredMots.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'mots'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content">
          <div><h1 class="main-title">Paroles des responsables</h1></div>
        </div>
        <section class="testimonials-grid">
          <div v-for="testi in paginatedMots" :key="testi.id" class="testimonial-card">
            <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
            <p class="testimonial-content">"{{ testi.contenu }}"</p>
            <div class="testimonial-footer">
              <div class="testimonial-avatar" v-if="testi.image">
                <img :src="`${publicUrl}${testi.image}`" :alt="testi.temoin" />
              </div>
              <div class="testimonial-info">
                <span class="testimonial-name">{{ testi.temoin }}</span>
                <span class="testimonial-location"><i class="fa-solid fa-location-dot"></i> {{ testi.residence }}</span>
              </div>
            </div>
          </div>
        </section>
        <div class="section-pagination" v-if="filteredMots.length > TESTIMONIALS_PER_PAGE">
          <div class="pagination-controls">
            <button @click="prevPage('mots')" :disabled="pages.mots === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.mots }} / {{ totalPages(filteredMots, TESTIMONIALS_PER_PAGE) }}</span>
            <button @click="nextPage('mots', filteredMots, TESTIMONIALS_PER_PAGE)" :disabled="pages.mots === totalPages(filteredMots, TESTIMONIALS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredEquipes.length" 
            :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'equipes'))" 
            class="section-spacer reveal-on-scroll">
            <Equipes :projet_name="projet.acronymes" :personnes="filteredEquipes"/>
      </section>

      <section v-if="filteredRealisations.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'realisations'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content">
          <div>
            <h1 class="main-title">Réalisations</h1>
            <p class="subtitle">Découvrez les étapes clés et les succès concrets.</p>
          </div>
        </div>
        <div class="realisations-grid">
          <div v-for="real in paginatedRealisations" :key="real.id" class="realisation-card" @click="openRealisation(real)">
            <div class="real-image">
              <img :src="`${publicUrl}${real.image}`" :alt="real.titre" />
              <div class="real-overlay"><span class="view-btn">Voir les détails</span></div>
            </div>
            <div class="real-body">
              <span class="real-date">{{ formatDate(real.date_realisation) }}</span>
              <h3>{{ real.titre }}</h3>
              <p class="real-excerpt">{{ real.description }}</p>
            </div>
          </div>
        </div>
        <div class="section-pagination" v-if="filteredRealisations.length > ITEMS_PER_PAGE">
          <div class="pagination-controls">
            <button @click="prevPage('realisations')" :disabled="pages.realisations === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.realisations }} / {{ totalPages(filteredRealisations, ITEMS_PER_PAGE) }}</span>
            <button @click="nextPage('realisations', filteredRealisations, ITEMS_PER_PAGE)" :disabled="pages.realisations === totalPages(filteredRealisations, ITEMS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredPlans.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'plans'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content"><h1 class="main-title">Investissements travaux</h1></div>
        <div class="documents-grid">
          <DocumentCard v-for="item in paginatedPlans" :key="item.id" :item="item" :public-url="publicUrl" @click="openDocument(item)" />
        </div>
        <div class="section-pagination" v-if="filteredPlans.length > ITEMS_PER_PAGE">
           <div class="pagination-controls">
            <button @click="prevPage('plans')" :disabled="pages.plans === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.plans }} / {{ totalPages(filteredPlans, ITEMS_PER_PAGE) }}</span>
            <button @click="nextPage('plans', filteredPlans, ITEMS_PER_PAGE)" :disabled="pages.plans === totalPages(filteredPlans, ITEMS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredInterviews.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'interviews'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content">
          <div>
            <h1 class="main-title">Interviews et Vidéos</h1>
            <p class="subtitle">Témoignages des acteurs du projet.</p>
          </div>
        </div>
        <div class="documents-grid">
          <DocumentCard v-for="item in paginatedInterviews" :key="item.id" :item="item" :public-url="publicUrl" @click="openDocument(item)" />
        </div>
        <div class="section-pagination" v-if="filteredInterviews.length > ITEMS_PER_PAGE">
           <div class="pagination-controls">
            <button @click="prevPage('interviews')" :disabled="pages.interviews === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.interviews }} / {{ totalPages(filteredInterviews, ITEMS_PER_PAGE) }}</span>
            <button @click="nextPage('interviews', filteredInterviews, ITEMS_PER_PAGE)" :disabled="pages.interviews === totalPages(filteredInterviews, ITEMS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredFichiers.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'fichiers'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content">
          <div>
            <h1 class="main-title">Documentation</h1>
            <p class="subtitle">Documents techniques et rapports.</p>
          </div>
        </div>
        <div class="documents-grid">
          <DocumentCard v-for="item in paginatedFichiers" :key="item.id" :item="item" :public-url="publicUrl" @click="openDocument(item)" />
        </div>
        <div class="section-pagination" v-if="filteredFichiers.length > ITEMS_PER_PAGE">
          <div class="pagination-controls">
            <button @click="prevPage('fichiers')" :disabled="pages.fichiers === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.fichiers }} / {{ totalPages(filteredFichiers, ITEMS_PER_PAGE) }}</span>
            <button @click="nextPage('fichiers', filteredFichiers, ITEMS_PER_PAGE)" :disabled="pages.fichiers === totalPages(filteredFichiers, ITEMS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredTemoignages.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'temoignages'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content"><h1 class="main-title">Paroles de bénéficiaires</h1></div>
        <div class="testimonials-grid">
          <div v-for="testi in paginatedTemoignages" :key="testi.id" class="testimonial-card">
            <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
            <p class="testimonial-content">"{{ testi.contenu }}"</p>
            <div class="testimonial-footer">
              <div class="testimonial-avatar" v-if="testi.image">
                <img :src="`${publicUrl}${testi.image}`" :alt="testi.temoin" />
              </div>
              <div class="testimonial-info">
                <span class="testimonial-name">{{ testi.temoin }}</span>
                <span class="testimonial-location"><i class="fa-solid fa-location-dot"></i> {{ testi.residence }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="section-pagination" v-if="filteredTemoignages.length > TESTIMONIALS_PER_PAGE">
          <div class="pagination-controls">
            <button @click="prevPage('temoignages')" :disabled="pages.temoignages === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.temoignages }} / {{ totalPages(filteredTemoignages, TESTIMONIALS_PER_PAGE) }}</span>
            <button @click="nextPage('temoignages', filteredTemoignages, TESTIMONIALS_PER_PAGE)" :disabled="pages.temoignages === totalPages(filteredTemoignages, TESTIMONIALS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="filteredActions.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'actions'))" 
               class="section-spacer reveal-on-scroll">
        <div class="header-content">
          <div>
            <h1 class="main-title">Actions de proximité</h1>
            <p class="subtitle">Sensibilisation et participation citoyenne.</p>
          </div>
        </div>
        <div class="documents-grid">
          <DocumentCard v-for="item in paginatedActions" :key="item.id" :item="item" :public-url="publicUrl" @click="openDocument(item)" />
        </div>
        <div class="section-pagination" v-if="filteredActions.length > ITEMS_PER_PAGE">
          <div class="pagination-controls">
            <button @click="prevPage('actions')" :disabled="pages.actions === 1" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-left" /></button>
            <span class="page-indicator">{{ pages.actions }} / {{ totalPages(filteredActions, ITEMS_PER_PAGE) }}</span>
            <button @click="nextPage('actions', filteredActions, ITEMS_PER_PAGE)" :disabled="pages.actions === totalPages(filteredActions, ITEMS_PER_PAGE)" class="nav-btn"><font-awesome-icon icon="fa-solid fa-chevron-right" /></button>
          </div>
        </div>
      </section>

      <section v-if="projet.partenariats_financiers?.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'partenariats'))" 
               class="organizations-section section-spacer reveal-on-scroll">
        <PartenariatFinancier :organisationsData="projet.partenariats_financiers" />
      </section>
      
      <section v-if="projet.parties_prenantes?.length" 
               :ref="(el) => setSectionRef(el, visibleSections.findIndex(s => s.id === 'parties-prenantes'))" 
               class="organizations-section section-spacer reveal-on-scroll">
        <PartiesPrenantes :organisationsData="projet.parties_prenantes" />
      </section>

      <div v-if="visibleSections.length === 0 && searchQuery" class="no-results-message">
        <p>Aucun résultat trouvé pour "{{ searchQuery }}" dans ce projet.</p>
      </div>

      <LightboxModal :item="currentMedia" :show="showLightbox" :public-url="publicUrl" @close="closeLightbox" @download="downloadFile" />
      
      <Transition name="fade">
        <div v-if="showRealisationModal" class="custom-modal-overlay" @click.self="closeRealisation">
            <div class="realisation-modal-content">
              <button class="close-modal" @click="closeRealisation">&times;</button>
              <div class="modal-grid">
                  <div class="modal-image-side">
                    <img :src="`${publicUrl}${selectedRealisation.image}`" :alt="selectedRealisation.titre" />
                  </div>
                  <div class="modal-info-side">
                    <span class="modal-date">{{ formatDate(selectedRealisation.date_realisation) }}</span>
                    <h2 class="real-title">{{ selectedRealisation.titre }}</h2>
                    <div class="info-block">
                        <label><i class="fa-solid fa-users"></i> Bénéficiaires</label>
                        <p>{{ selectedRealisation.beneficiaires }}</p>
                    </div>
                    <div class="info-block">
                        <label><i class="fa-solid fa-circle-info"></i> Description</label>
                        <p>{{ selectedRealisation.description }}</p>
                    </div>
                    <div class="info-block impact">
                        <label><i class="fa-solid fa-bolt"></i> Impacts & Résultats</label>
                        <p class="pre-wrap">{{ selectedRealisation.impacts_potentiel }}</p>
                    </div>
                  </div>
              </div>
            </div>
        </div>
      </Transition>
    </div>
  </main>
</template>

<style scoped>
.detail-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 2rem 1.5rem 5rem 1.5rem;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 2.5rem auto;
}

.about-btn {
   background: var(--color-primary);
   color: var(--color-text-light);
   padding: 8px 16px;
   border-radius: var(--border-radius);
   border: none;
   text-decoration: none;
   font-size: 0.9rem;
   font-weight: 500;
   transition: all 0.3s ease;
   height: 40px;
   box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.about-btn:hover {
   background: var(--color-accent);
   transform: translateY(-2px);
   box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
   cursor: pointer;
}

.section-spacer {
  margin-top: 5rem;
  margin-bottom: 5rem;
}

/* Navigation par étoiles */
.stars-navigation {
  position: fixed;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  gap: 20px;
  z-index: 100;
  background: rgba(255, 255, 255, 0.05);
  padding: 10px;
  border-radius: 10px;
  backdrop-filter: blur(2px);
}

.star-button {
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.star-button svg {
  transition: all 0.3s ease;
  width: 14px;
  height: 14px;
}

.star-button:hover svg {
  transform: scale(1.2);
  fill: var(--color-accent);
  stroke: var(--color-accent);
}

.star-button.active svg {
  transform: scale(1.1);
}

.star-tooltip {
  position: absolute;
  left: 15px;
  background: rgba(0, 0, 0, 0.85);
  color: white;
  padding: 10px 15px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1001;
}

.star-tooltip::after {
  content: '';
  position: absolute;
  top: 50%;
  left: -5px;
  transform: translateY(-50%);
  border-width: 6px 6px 6px 0;
  border-style: solid;
  border-color: transparent rgba(0, 0, 0, 0.85) transparent transparent;
}

.star-button:hover .star-tooltip {
  opacity: 1;
  visibility: visible;
  left: 45px;
}

/* Hero Section */
.hero-section {
  background: white;
  padding: 3rem;
  border-radius: var(--border-radius);
  margin-bottom: 2rem;
  border: 1px solid #f0f0f0;
}

.hero-content {
  display: flex;
  gap: 2.5rem;
  align-items: center;
}

.hero-logo {
  width: 120px;
  height: 120px;
  background: #f8fafc;
  border-radius: var(--border-radius);
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.hero-logo img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.badge {
  display: inline-block;
  padding: 0.4rem 1rem;
  background: var(--color-accent-light, #eef2ff);
  color: var(--color-accent, #4f46e5);
  border-radius: 100px;
  font-weight: 600;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.hero-text h1 {
  font-size: 2rem;
  font-weight: 600;
  color: var(--text-color);
  line-height: 1.2;
  margin-bottom: 1rem;
}

.project-description {
  color: var(--text-color);
  font-size: 1rem;
  line-height: 1.6;
  max-width: 800px;
}

/* Info Grid */
.icon-wrapper {
  font-size: 1.2rem;
  background: #f8fafc;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: var(--color-accent, #4f46e5);
  flex-shrink: 0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 4rem;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  gap: 1.2rem;
  border: 1px solid #f1f5f9;
  transition: transform 0.2s ease;
}

.info-card:hover {
  transform: translateY(-3px);
  border-color: var(--color-accent-light);
}

.icon {
  font-size: 1.5rem;
  background: #f8fafc;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.info-data label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.5px;
  margin-bottom: 0.2rem;
}

.info-data strong {
  color: var(--text-color);
  font-size: 0.95rem;
  font-weight: 600;
}

.search-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 2rem auto 3rem;
  width: 100%;
  max-width: 600px;
}

.search-container {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
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
  border: 2px solid var(--color-accent);
  background-color: color-mix(in srgb, var(--gris) 95%, white);
  transform: translateY(-2px);
}

.search-icon {
  position: absolute;
  left: 1.2rem;
  color: var(--text-color);
  font-size: 1.1rem;
  pointer-events: none;
  z-index: 1;
}

.search-clear {
  position: absolute;
  right: 1.2rem;
  background: none;
  border: none;
  color: var(--text-color);
  cursor: pointer;
  font-size: 1rem;
  padding: 0.5rem;
  transition: color 0.2s;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 24px;
  width: 24px;
}

.search-clear:hover {
  color: #ef4444;
}

.search-results-info {
  margin-top: 0.8rem;
  font-size: 0.9rem;
  color: #64748b;
  text-align: center;
}

.search-badges-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.section-count-badge {
  display: inline-flex;
  align-items: center;
  background: var(--vt-c-bg-soft);
  border: 1px solid var(--vt-c-divider);
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.section-count-badge:hover {
  border-color: var(--color-accent);
  background: var(--color-accent-soft);
  transform: translateY(-2px);
}

.badge-label {
  margin-right: 8px;
  font-weight: 500;
}

.badge-number {
  background: var(--color-accent);
  color: white;
  border-radius: 10px;
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: bold;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
  100% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
}

input:focus, textarea:focus {
  animation: pulse 1.5s infinite;
}

.no-results-message {
  text-align: center;
  padding: 4rem 1rem;
  color: #64748b;
  font-size: 1.2rem;
}

/* Navigation Pagination */
.section-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #f1f5f9;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-btn {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 1px solid #e2e8f0;
  background: white;
  color: var(--color-accent);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-btn:hover:not(:disabled) {
  background: var(--color-accent);
  color: white;
  border-color: var(--color-accent);
  transform: translateY(-2px);
}

.nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  background: #f8fafc;
}

.page-indicator {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
  min-width: 40px;
  text-align: center;
}

/* Sections */
.section-header {
  margin-bottom: 2rem;
}

.section-header h2 {
  font-size: 1.75rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.section-header p {
  color: #64748b;
}

.organizations-section {
  margin-bottom: 5rem;
}

.content-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, #e2e8f0, transparent);
  margin: 4rem 0;
}

/* Empty State */
.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10rem 2rem;
  text-align: center;
}

.btn-back {
  display: inline-block;
  margin-top: 1.5rem;
  padding: 0.8rem 2rem;
  background: var(--color-accent, #4f46e5);
  color: white;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 600;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

/* Grille de témoignages */
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.testimonial-card {
  background: white;
  padding: 2.5rem;
  border-radius: var(--border-radius);
  border: 1px solid #f1f5f9;
  position: relative;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 400px; 
}

.testimonial-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border-color: var(--color-accent-light);
}

.quote-icon {
  color: var(--color-accent-light, #eef2ff);
  font-size: 2.5rem;
  position: absolute;
  top: 1rem;
  left: 1.5rem;
  z-index: 0;
  opacity: 0.6;
}

.testimonial-content {
  position: relative;
  z-index: 1;
  font-size: 1.05rem;
  font-style: italic;
  color: #475569;
  line-height: 1.7;
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 10px;
}

.testimonial-content::-webkit-scrollbar {
  width: 4px;
}

.testimonial-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.testimonial-content::-webkit-scrollbar-thumb {
  background: var(--color-accent, #eef2ff);
  border-radius: 10px;
}

.testimonial-content::-webkit-scrollbar-thumb:hover {
  background: var(--color-accent, #4f46e5);
}

.testimonial-footer {
  display: flex;
  align-items: center;
  gap: 1rem;
  border-top: 1px solid #f8fafc;
  padding-top: 1.5rem;
  background: white;
  flex-shrink: 0;
}

.testimonial-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  background: #f1f5f9;
  flex-shrink: 0;
  border: 2px solid white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.testimonial-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.testimonial-info {
  display: flex;
  flex-direction: column;
}

.testimonial-name {
  font-weight: 700;
  color: #1e293b;
  font-size: 1rem;
}

.testimonial-location {
  font-size: 0.85rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 4px;
}

.testimonial-location i {
  font-size: 0.75rem;
  color: var(--color-accent);
}

/* Grille de Réalisations */
.realisations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
}

.realisation-card {
  background: white;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.3s ease;
}

.realisation-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.real-image {
  height: 220px;
  position: relative;
  overflow: hidden;
}

.real-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.realisation-card:hover .real-image img {
  transform: scale(1.1);
}

.real-overlay {
  position: absolute;
  inset: 0;
  background: rgba(58, 58, 63, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.realisation-card:hover .real-overlay {
  opacity: 1;
}

.view-btn {
  color: white;
  border: 1px solid white;
  padding: 0.6rem 1.2rem;
  border-radius: 50px;
  font-weight: 400;
  font-size: 0.9rem;
}

.real-body {
  padding: 1.5rem;
}

.real-date {
  font-size: 0.8rem;
  color: var(--color-accent);
  font-weight: 400;
}

.real-body h3 {
  margin: 0.5rem 0;
  font-size: 1.1rem;
  line-height: 1.4;
  color: #1e293b;
}

.real-excerpt {
  color: #64748b;
  font-size: 0.9rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Modale de Détails */
.custom-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.85);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  backdrop-filter: blur(5px);
}

.realisation-modal-content {
  background: white;
  width: 100%;
  max-width: 1100px;
  max-height: 90vh;
  border-radius: var(--border-radius);
  position: relative;
  overflow-y: auto;
}

.close-modal {
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

.close-modal:hover {
  background: var(--color-accent);
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
}

.modal-image-side img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  min-height: 500px;
}

.modal-info-side {
  padding: 3rem;
}

.modal-date {
  display: block;
  margin-top: 1rem;
  color: #64748b;
}

.info-block {
  margin-top: 2rem;
}

.real-title {
    font-size: 2rem;
    font-weight: 300;
}

.info-block label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 400;
  color: #1e293b;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.info-block label i {
  color: var(--color-accent);
}

.info-block p {
  color: #475569;
  line-height: 1.6;
}

.pre-wrap {
  white-space: pre-wrap;
}

.realisation-modal-content::-webkit-scrollbar {
  width: 4px;
}

.realisation-modal-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.realisation-modal-content::-webkit-scrollbar-thumb {
  background: var(--color-accent, #eef2ff);
  border-radius: 10px;
}

.realisation-modal-content::-webkit-scrollbar-thumb:hover {
  background: var(--color-accent, #4f46e5);
}

.realisation-modal-content {
  scrollbar-width: thin;
  scrollbar-color: var(--color-accent) #f1f5f9;
}

/* Animation reveal-on-scroll */
.reveal-on-scroll {
  opacity: 0;
  transform: translateY(50px);
  transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
  will-change: opacity, transform;
}

.reveal-on-scroll.is-visible {
  opacity: 1;
  transform: translateY(0);
}

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* --- Responsive Design pour les étoiles --- */
@media (max-width: 768px) {
  .star-button svg {
    width: 12px;
    height: 12px;
  }
}

@media (max-width: 600px) {
  .stars-navigation {
    padding: 10px 15px;
    gap: 12px;
  }
  
  .star-button svg {
    width: 10px;
    height: 10px;
  }
}

@media (max-width: 480px) {
  .stars-navigation {
    padding: 8px 12px;
    gap: 10px;
  }
  
  .star-button {
    padding: 4px;
  }
  
  .star-button svg {
    width: 8px;
    height: 8px;
  }
  
  .star-tooltip {
    font-size: 0.75rem;
    padding: 6px 10px;
  }
}

/* Responsive pour le reste */
@media (max-width: 900px) {
  .modal-grid {
    grid-template-columns: 1fr;
  }
  .modal-image-side {
    height: 300px;
  }
}

@media (max-width: 1024px) {
  .documents-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
  }
}

@media (max-width: 768px) {
  .hero-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
  
  .hero-text h1 {
    font-size: 1.8rem;
  }
  
  .hero-section {
    padding: 2rem 1.5rem;
  }

  .documents-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

@media (max-width: 640px) {
  .testimonials-grid {
    grid-template-columns: 1fr;
  }
  
  .testimonial-card {
    padding: 1.5rem;
  }
}

@media (max-width: 480px) {
  .documents-grid {
    grid-template-columns: 1fr;
  }
  
  .pagination-controls {
    gap: 1rem;
  }
  
  .nav-btn {
    width: 40px;
    height: 40px;
  }
}
</style>