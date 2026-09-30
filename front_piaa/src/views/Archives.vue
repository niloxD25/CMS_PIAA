<script setup>
import { useProjectStore } from '@/stores/projectStore';
import { ref, onMounted } from 'vue';
import { getOthersProjetsWithFullData } from '@/services/api';
import Header from '@/components/Header.vue';
import { useRouter } from 'vue-router';
import { slugify } from '@/utils/stringUtils';

const router = useRouter();
const projectStore = useProjectStore();

const isLoading = ref(true);
const loadError = ref(null);
const projetsData = ref([]);

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

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

const goToProject = (projet) => {
  projectStore.setCurrentProject(projet);
  
  const projectSlug = slugify(projet.nom);
  
  router.push({ 
    name: 'ProjectDetails', 
    params: { slug: projectSlug } 
  });
};

const loadData = async () => {
  try {
    const userString = localStorage.getItem('user');
    let niveauAcces = null;

    if (userString) {
      try {
        const user = JSON.parse(userString);
        if (user && user.type_compte) {
          niveauAcces = user.type_compte.niveau_acces;
        }
      } catch (e) {
        console.error("Erreur de parsing des données utilisateur depuis le localStorage", e);
      }
    }

    const response = await getOthersProjetsWithFullData(niveauAcces);
    const data = response.data;
    
    projetsData.value = data.data; 
  } catch (error) {
    console.error('Failed to load and save project data:', error);
    loadError.value = 'Une erreur est survenue lors du chargement des données. Veuillez réessayer plus tard.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadData();
});
</script>

<template>
  <main>
    <Header />

    <div class="header-content">
      <h1 class="main-title">Archives des projets</h1>
      <p class="subtitle">
        Découvrez l’ensemble de nos projets passés et en cours
      </p>
    </div>


    <div class="projects-container">
      <div v-if="isLoading" class="loading-state">
        <div class="loader-wrapper">
          <div class="spinner"></div>
          <div class="spinner-outer"></div>
        </div>
        <p class="loading-text">Récupération des archives...</p>
      </div>

      <div v-else-if="loadError" class="error-state">
        <div class="error-illustration">
          <font-awesome-icon icon="fa-solid fa-circle-exclamation" class="error-icon" />
        </div>
        <h3 class="error-title">Oups ! Une erreur est survenue</h3>
        <p class="error-description">{{ loadError }}</p>
        <button @click="loadData" class="btn-retry btn-error-retry">
          <font-awesome-icon icon="fa-solid fa-arrow-rotate-right" />
          Réessayer la connexion
        </button>
      </div>

      <div v-else-if="projetsData.length === 0" class="empty-state">
        <div class="empty-illustration">
          <font-awesome-icon icon="fa-solid fa-folder-open" class="empty-icon" />
          <div class="empty-circle"></div>
        </div>
        
        <h3 class="empty-title">Aucun projet à afficher</h3>
        <p class="empty-description">
          Il n'y a actuellement aucun projet archivé correspondant à votre profil ou aux critères de recherche.
        </p>
        
        <button @click="loadData" class="btn-retry">
          <font-awesome-icon icon="fa-solid fa-rotate-right" />
          Actualiser la liste
        </button>
      </div>

      <div v-else class="projects-grid">
        <div 
          v-for="projet in projetsData" 
          :key="projet.id" 
          class="project-card"
          @click="goToProject(projet)"
        >
          <div class="card-header">
            <div class="logo-wrapper">
              <img 
                v-if="projet.logo" 
                :src="`${publicUrl}${projet.logo}`" 
                :alt="projet.nom" 
                class="project-logo"
              />
              <div v-else class="logo-placeholder">
                {{ projet.acronymes?.substring(0, 2) || 'PR' }}
              </div>
            </div>
            <div class="project-badge">{{ projet.acronymes }}</div>
          </div>

          <div class="card-body">
            <h2 class="project-name">{{ projet.nom }}</h2>
            <p class="project-acronym">{{ projet.devise }}</p>
            
            <div class="project-meta">
              <div class="meta-item">
                <span class="label">Début</span>
                <span class="value">{{ projet.debut ? formatDate(projet.debut) : 'N/C' }}</span>
              </div>
              <div class="meta-divider"></div>
              <div class="meta-item">
                <span class="label">Durée</span>
                <span class="value">{{ projet.duree_annee }} {{ projet.duree_annee > 1 ? 'ans' : 'an' }}</span>
              </div>
            </div>
          </div>
          
          <div class="card-footer">
            <button class="btn-detail">Voir les détails</button>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.projects-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 40px 20px;
}

.header-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 6rem auto;
}

/* Grille */
.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
}

/* Carte Projet */
.project-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #f0f0f0;
  padding: 24px;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.project-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
  border-color: var(--color-accent, #4f46e5);
}

/* Header de la carte */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.logo-wrapper {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  overflow: hidden;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}

.project-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.logo-placeholder {
  font-weight: bold;
  color: #94a3b8;
  font-size: 1.2rem;
}

.project-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  background: #f1f5f9;
  color: #475569;
  border-radius: 20px;
  text-transform: uppercase;
}

/* Corps de la carte */
.card-body {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.project-name {
  font-size: 1.15rem;
  color: #1e293b;
  margin: 0 0 4px 0;
  font-weight: 600;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.project-acronym {
  color: var(--color-accent, #4f46e5);
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 20px;
}

.project-meta {
  margin-top: auto;
  display: flex;
  align-items: center;
  background: #f8fafc;
  padding: 12px;
  border-radius: 10px;
  gap: 15px;
}

.meta-item {
  display: flex;
  flex-direction: column;
}

.meta-item .label {
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.5px;
}

.meta-item .value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #334155;
}

.meta-divider {
  width: 1px;
  height: 25px;
  background: #e2e8f0;
}

/* Footer */
.card-footer {
  margin-top: 24px;
}

.btn-detail {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: none;
  background: #f8fafc;
  color: #1e293b;
  font-weight: 500;
  transition: background 0.2s;
}

.project-card:hover .btn-detail {
  background: var(--color-accent, #4f46e5);
  color: white;
}

/* --- ÉTATS DE CHARGEMENT --- */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100px 20px;
}

.loader-wrapper {
  position: relative;
  width: 80px;
  height: 80px;
  margin-bottom: 24px;
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

.loading-text {
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.5px;
  animation: pulseText 2s infinite ease-in-out;
}

/* --- ÉTAT D'ERREUR --- */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 60px 40px;
  background: #fffafa;
  border-radius: 24px;
  border: 1px solid #fee2e2;
  max-width: 600px;
  margin: 40px auto;
}

.error-illustration {
  margin-bottom: 20px;
}

.error-icon {
  font-size: 3.5rem;
  color: #ef4444;
  filter: drop-shadow(0 4px 6px rgba(239, 68, 68, 0.2));
}

.error-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #991b1b;
  margin-bottom: 10px;
}

.error-description {
  color: #b91c1c;
  opacity: 0.8;
  max-width: 400px;
  margin-bottom: 25px;
  line-height: 1.5;
}

.btn-error-retry {
  background: #ef4444 !important;
  color: white !important;
  border: none !important;
}

.btn-error-retry:hover {
  background: #dc2626 !important;
  transform: scale(1.02);
  box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.3);
}

@keyframes pulseText {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

/* État Vide */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 80px 40px;
  background: #fdfdfe;
  border-radius: 24px;
  border: 2px dashed #e2e8f0;
  max-width: 600px;
  margin: 40px auto;
}

.empty-illustration {
  position: relative;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-icon {
  font-size: 4rem;
  color: #94a3b8;
  z-index: 2;
}

.empty-circle {
  position: absolute;
  width: 100px;
  height: 100px;
  background: #f1f5f9;
  border-radius: 50%;
  z-index: 1;
  animation: pulse 2s infinite ease-in-out;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 12px;
}

.empty-description {
  color: #64748b;
  line-height: 1.6;
  max-width: 400px;
  margin-bottom: 30px;
}

.btn-retry {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 24px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  color: #475569;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-retry:hover {
  background: #f8fafc;
  border-color: var(--color-accent, #4f46e5);
  color: var(--color-accent, #4f46e5);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Animation douce pour le cercle de fond */
@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.5; }
  50% { transform: scale(1.05); opacity: 0.8; }
  100% { transform: scale(0.95); opacity: 0.5; }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .projects-grid {
    grid-template-columns: 1fr;
  }
}
</style>