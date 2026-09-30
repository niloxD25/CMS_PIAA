<script setup>
import { ref, computed } from 'vue';
import { useProjectStore } from '@/stores/projectStore';
import { downloadFichier } from '@/services/api';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';
import DocumentCard from '@/components/DocumentCard.vue';
import LightboxModal from '@/components/LightboxModal.vue';

const projectStore = useProjectStore();

const showLightbox = ref(false);
const currentMediaIndex = ref(0);
const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const projet_name = computed(() => projectStore.fullData?.acronymes || 'projet');

const organisations = computed(() => projectStore.fullData?.parties_prenantes || []);

const documents = computed(() => {
  const interviews = projectStore.fullData?.interviews || [];
  return interviews.map(interview => ({
    id: interview.id,
    titre: interview.titre || 'Interview',
    description: interview.description || 'Aucune description',
    type_fichier: (interview.type_fichier || interview.chemin?.split('.').pop() || 'unknown').toLowerCase(),
    chemin: interview.chemin,
    date_upload: interview.date_upload || new Date().toISOString()
  }));
});

const currentMedia = computed(() => documents.value[currentMediaIndex.value] || {});

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

const downloadFile = async (item) => {
  try {
    const response = await downloadFichier(item.id);
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
  }
};
</script>

<template>
  <Header />

  <main class="documents-container">
    <div class="header-content">
      <h2 class="main-title">Interviews et Reportages vidéos</h2>
      <p class="subtitle">
        Découvrez les témoignages des acteurs du projet et les reportages sur le terrain. 
      </p>
    </div>

    <div class="documents-grid" v-if="documents.length > 0">
      <DocumentCard
        v-for="item in documents"
        :key="item.id"
        :item="item"
        :public-url="publicUrl"
        @click="openDocument(item)"
      />
    </div>

    <div v-else class="empty-state-wrapper">
      <div class="empty-state-card">
        <div class="icon-container">
          <font-awesome-icon :icon="['fas', 'video-slash']" class="main-icon" />
          <div class="icon-pulse"></div>
        </div>
        
        <div class="text-content">
          <h3>Silence on tourne... ou presque !</h3>
          <p>
            Il n'y a pas encore d'interviews ou de reportages disponibles pour le projet 
            <span class="project-highlight">{{ projet_name }}</span>.
          </p>
        </div>

        <router-link to="/evenements" class="cta-button">
          <font-awesome-icon :icon="['fas', 'arrow-left']" />
          Explorer les événements
        </router-link>
      </div>
    </div>

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

.documents-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

/* État vide */
.empty-state-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 5rem 1rem;
  min-height: 400px;
}

.empty-state-card {
  text-align: center;
  max-width: 450px;
  padding: 3rem;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

/* Animation de l'icône */
.icon-container {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 1rem;
}

.main-icon {
  font-size: 3.5rem;
  color: #d1d5db;
  z-index: 2;
}

.text-content h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.text-content p {
  color: #6b7280;
  line-height: 1.6;
}

.project-highlight {
  color: var(--color-accent);
  font-weight: 600;
}

/* Bouton d'action */
.cta-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 1.8rem;
  background: #f3f4f6;
  color: #374151;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-weight: 500;
  transition: all 0.3s ease;
}

.cta-button:hover {
  background: var(--color-primary);
  color: white;
  transform: translateY(-2px);
}

@media (max-width: 1024px) {
  .documents-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
  }
}

@media (max-width: 768px) {
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

  .empty-state-card {
    padding: 2rem;
    box-shadow: none;
    background: transparent;
  }
}

@media (max-width: 480px) {
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

</style>