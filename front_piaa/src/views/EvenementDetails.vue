<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getEvenement, downloadFichier as downloadFichierApi } from '@/services/api';
import { useProjectStore } from '@/stores/projectStore';
import Header from '@/components/Header.vue';
import PartiesPrenantesST from '@/components/PartiesPrenantesST.vue';
import DocumentCard from '@/components/DocumentCard.vue';
import LightboxModal from '@/components/LightboxModal.vue';

const projectStore = useProjectStore();

const route = useRoute();
const router = useRouter();
const evenement = ref(null);
const loading = ref(true);
const activeSectionIndex = ref(0);
const showLightbox = ref(false);
const currentMediaIndex = ref(0);
const documents = ref([]);

const organisations = computed(() => {
  return projectStore.fullData?.parties_prenantes || [];
});

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

// Fonction pour formater la date en français
const formatDateDisplay = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const setActiveSection = (index) => {
  activeSectionIndex.value = index;
};

const goBack = () => {
  router.push('/evenements');
};

const currentMedia = computed(() => {
  return documents.value[currentMediaIndex.value] || {};
});

const openDocument = (item) => {
  const index = documents.value.findIndex(doc => doc.id === item.id);
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


// Fonction de téléchargement
const downloadFile = async (item) => {
  try {
    const response = await downloadFichierApi(item.id);

    const blob = new Blob([response.data]);
    const url = window.URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = `${item.titre}.${item.type_fichier}`;

    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erreur téléchargement:', error);
    alert('Erreur lors du téléchargement');
  }
};


onMounted(async () => {
  try {
    const eventId = route.params.id;
    
    if (!eventId) {
      console.error('ID d\'événement manquant');
      loading.value = false;
      return;
    }

    // Charger les données de l'événement
    const response = await getEvenement(eventId);
    evenement.value = response.data.data;

    // Mapper les fichiers pour DocumentCard
    documents.value = (evenement.value.fichiers || []).map(fichier => ({
      id: fichier.id,
      titre: fichier.titre || 'Document sans titre',
      description: fichier.description || 'Aucune description',
      type_fichier: (fichier.type_fichier || fichier.chemin.split('.').pop()).toLowerCase(),
      chemin: fichier.chemin,
      date_upload: fichier.date_upload || new Date().toISOString()
    }));

  } catch (error) {
    console.error('Erreur lors du chargement des détails de l\'événement:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <Header />
    
    <div class="evenement-details-container" v-if="loading">
      <div class="loading">
        <div class="spinner"></div>
        Chargement des détails de l'événement...
      </div>
    </div>

    <div class="evenement-details-container" v-else-if="evenement">
      <!-- Carte principale de l'événement -->
      <div class="evenement-card">
        <!-- Image de l'événement -->
        <div class="event-image-large" v-if="evenement.image">
          <img :src="`${publicUrl}${evenement.image}`" :alt="evenement.titre || 'Image événement'">
        </div>

        <!-- Contenu principal -->
        <div class="event-content-details">
          <!-- Titre avec badge d'état -->
          <div class="event-header">
            <h1 class="event-title">{{ evenement.titre }}</h1>
            <span class="event-status-badge" :class="`status-${evenement.etat_courant?.etats_evenement?.etat?.toLowerCase().replace(' ', '-') || 'default'}`">
              {{ evenement.etat_courant?.etats_evenement?.etat || 'Non défini' }}
            </span>
          </div>

          <!-- Dates -->
          <div class="event-dates-details">
            <div class="date-range">
              <div class="date-item">
                <span class="date-label">Le :</span>
                <span class="date-value">{{ formatDateDisplay(evenement.date_debut) }}</span>
              </div>
              <div class="date-item" v-if="evenement.date_fin">
                <span class="date-label">Jusqu'au :</span>
                <span class="date-value">{{ formatDateDisplay(evenement.date_fin) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Description -->
          <div class="event-description-details" v-if="evenement.description">
            <p>{{ evenement.description }}</p>
          </div>
        </div>
      </div>

      <div class="sections-modern-showcase" v-if="evenement.sections && evenement.sections.length > 0">
        <h2 class="section-main-title">En savoir plus</h2>
        
        <div class="showcase-container">
          <div class="showcase-nav">
            <button 
              v-for="(section, index) in evenement.sections" 
              :key="section.id"
              class="nav-item"
              :class="{ active: activeSectionIndex === index }"
              @click="setActiveSection(index)"
            >
              <span class="nav-number">{{ (index + 1).toString().padStart(2, '0') }}</span>
              <span class="nav-text">{{ section.titre }}</span>
              <font-awesome-icon :icon="['fas', 'chevron-right']" class="nav-arrow" />
            </button>
          </div>

          <div class="showcase-content">
            <transition name="fade-slide" mode="out-in">
              <div :key="activeSectionIndex" class="content-wrapper">
                <div class="content-text">
                  <h3>{{ evenement.sections[activeSectionIndex].titre }}</h3>
                  <p v-if="evenement.sections[activeSectionIndex].description">
                    {{ evenement.sections[activeSectionIndex].description }}
                  </p>
                  <p v-else class="no-desc">Aucune description disponible pour cette section.</p>
                </div>
                
                <div class="content-visual">
                  <div v-if="evenement.sections[activeSectionIndex].image" class="image-frame">
                    <img 
                      :src="`${publicUrl}${evenement.sections[activeSectionIndex].image}`" 
                      :alt="evenement.sections[activeSectionIndex].titre"
                    >
                  </div>
                  <div v-else class="placeholder-visual">
                    <font-awesome-icon :icon="['fas', 'image']" />
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Fichiers multimédias -->
      <div class="fichiers-section" v-if="documents.length > 0">
      <h2 class="main-title">Fichiers multimédias</h2>

      <div class="documents-grid">
        <DocumentCard
          v-for="item in documents"
          :key="item.id"
          :item="item"
          :public-url="publicUrl"
          @click="openDocument(item)"
        />
      </div>
    </div>

    <LightboxModal
      :item="currentMedia"
      :show="showLightbox"
      :public-url="publicUrl"
      @close="closeLightbox"
      @download="downloadFile"
    />

      <!-- Section des parties prenantes -->
      <PartiesPrenantesST :organisations-data="organisations"/>
    </div>

    <div class="evenement-details-container" v-else>
      <div class="error-state">
        <font-awesome-icon :icon="['fas', 'exclamation-triangle']" />
        <h3>Événement non trouvé</h3>
        <button class="back-button" @click="goBack">
          Retour à la liste
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.evenement-details-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 2rem;
  text-align: center;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
  margin: 0 auto;
}

.back-button:hover {
  background: var(--color-accent);
  transform: translateY(-2px);
}

.evenement-card {
  margin-bottom: 3rem;
}

.event-image-large {
  width: 100%;
  max-width: 800px;
  height: 400px;
  margin: 0 auto 2rem;
  border-radius: var(--border-radius);
  overflow: hidden;
}

.event-image-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.event-content-details {
  padding: 0;
  max-width: 800px;
  margin: 0 auto;
}

/* En-tête avec titre et badge */
.event-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.event-title {
  font-size: 2rem;
  font-weight: 500;
  color: var(--text-color);
  margin: 0;
  line-height: 1.2;
}

.event-status-badge {
  padding: 0.5rem 1.5rem;
  border-radius: 25px;
  font-size: 0.8rem;
  font-weight: 500;
  letter-spacing: 0.5px;
  white-space: nowrap;
  margin-left: 3rem;
}

.status-terminé {
  background: #e8f5e8;
  color: var(--color-success);
  border: 1px solid #c8e6c9;
}

.status-en-cours {
  background: #e3f2fd;
  color: var(--bleu-ge);
  border: 1px solid #bbdefb;
}

.status-annulé {
  background: #ffebee;
  color: var(--color-error);
  border: 1px solid #ffcdd2;
}

.status-a-venir {
  background: #f5f5f5;
  color: var(--color-warning);
  border: 1px solid #e0e0e0;
}

.status-default {
  background: #f5f5f5;
  color: #666;
  border: 1px solid #e0e0e0;
}

.event-dates-details {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--gris);
}

.date-range {
  display: flex;
  justify-content: center;
  gap: 3rem;
  flex-wrap: wrap;
}

.date-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.date-label {
  font-size: 1rem;
  color: var(--vt-c-divider-dark-1);
  font-weight: 300;
}

.date-value {
  font-size: 1rem;
  font-weight: 300;
  color: var(--vt-c-divider-dark-1);
}

.event-description-details p {
  text-align: center;
  line-height: 1.6;
  color: var(--text-color);
  font-size: 1rem;
  margin: 0 auto 2rem;
  white-space: pre-line;
}

.sections-modern-showcase {
  margin: 4rem 0;
  text-align: left;
}

.section-main-title {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  color: var(--color-primary);
  font-weight: 600;
  position: relative;
  padding-left: 1rem;
}

.showcase-container {
  display: grid;
  grid-template-columns: 350px 1fr;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  overflow: hidden;
  min-height: 500px;
}

/* Navigation latérale */
.showcase-nav {
  background: #f8f9fa;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  border-right: 1px solid #eee;
}

.showcase-nav::-webkit-scrollbar {
  height: 4px;
}

.showcase-nav::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.showcase-nav::-webkit-scrollbar-thumb {
  background: var(--color-accent, #eef2ff);
  border-radius: 10px;
}

.showcase-nav::-webkit-scrollbar-thumb:hover {
  background: var(--color-accent, #4f46e5);
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 1.25rem;
  border: none;
  background: white;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: left;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.nav-number {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--color-accent);
  margin-right: 1rem;
  opacity: 0.5;
}

.nav-text {
  flex: 1;
  font-weight: 500;
  color: #444;
  font-size: 0.95rem;
}

.nav-arrow {
  font-size: 0.8rem;
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.nav-item.active {
  background: var(--color-primary);
}

.nav-item.active .nav-text,
.nav-item.active .nav-number {
  color: white;
  opacity: 1;
}

.nav-item.active .nav-arrow {
  opacity: 1;
  transform: translateX(0);
  color: white;
}

/* Zone de contenu */
.showcase-content {
  padding: 3rem;
  background: white;
  position: relative;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  height: 100%;
}

.content-text h3 {
  font-size: 1.8rem;
  color: var(--text-primary);
  margin-bottom: 1rem;
}

.content-text p {
  line-height: 1.7;
  color: #666;
  font-size: 1.05rem;
  white-space: pre-line;
}

.no-desc {
  font-style: italic;
  opacity: 0.6;
}

.content-visual {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9f9f9;
  border-radius: 15px;
  overflow: hidden;
  min-height: 250px;
}

.image-frame {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
}

.image-frame img {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain;
  transition: transform 0.5s ease;
}

.nav-item:hover ~ .showcase-content img {
    transform: scale(1.02);
}

.placeholder-visual {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #ccc;
  gap: 1rem;
}

.placeholder-visual svg {
  font-size: 4rem;
}

/* Animations de transition */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* États de chargement et d'erreur */
.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 1rem;
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

.error-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--color-error);
}

.error-state svg {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-state h3 {
  margin-bottom: 1rem;
  color: var(--color-error);
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 1024px) {
  .documents-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
  }
}

@media (max-width: 992px) {
  .showcase-container {
    grid-template-columns: 1fr;
  }
  
  .showcase-nav {
    flex-direction: row;
    overflow-x: auto;
    padding: 1rem;
    border-right: none;
    border-bottom: 1px solid #eee;
  }
  
  .nav-item {
    white-space: nowrap;
    min-width: fit-content;
  }
  
  .nav-arrow {
    display: none;
  }
}

@media (max-width: 768px) {
  .evenement-details-container {
    padding: 1rem;
  }

  .event-title {
    font-size: 2rem;
  }

  .event-header {
    flex-direction: column;
    gap: 1rem;
  }

  .date-range {
    flex-direction: column;
    gap: 1rem;
  }

  .event-image-large {
    height: 250px;
    margin-bottom: 1.5rem;
  }

  .accordion-layout {
    grid-template-columns: 1fr;
    gap: 1rem;
    min-height: auto;
  }

  .sections-list-scrollable {
    height: 300px;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
  }

  .section-image-right {
    min-height: 300px;
    padding: 1.5rem;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
  }

  .active-section-image img {
    max-height: 250px;
  }

  .section-title-content {
    padding: 1.25rem;
  }

  .section-title {
    font-size: 1rem;
  }

  .section-description-expanded,
  .no-description-expanded {
    padding: 0.75rem 1.25rem 1.25rem 1.25rem;
  }

  .section-description-expanded p {
    font-size: 0.9rem;
  }

  .documents-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .fichiers-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>