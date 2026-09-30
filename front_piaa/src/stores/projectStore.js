import { defineStore } from 'pinia';
import { ref } from 'vue';
import { getMe, getProjetActuelFullData } from '@/services/api';

export const useProjectStore = defineStore('project', () => {
  const currentProjectArchive = ref(null);
  function setCurrentProject(project) {
    currentProjectArchive.value = project;
  }

  const fullData = ref(null);
  const user = ref(null);
  const lastRefresh = ref(0);
  const isLoading = ref(false);

  const sessionExpired = ref(false);

  const REFRESH_INTERVAL = 4.5 * 60 * 1000; // 4.5 minutes

  // Action privée pour fetch les données projet
  async function fetchProjetActuel(niveauAcces = null) {
    try {
      const response = await getProjetActuelFullData(niveauAcces);
      fullData.value = response.data?.data || null;
    } catch (error) {
      console.error("Erreur fetchProjetActuel:", error);
      fullData.value = null;
    }
  }

  // Action principale pour charger les données
  async function loadData(force = false) {
    const now = Date.now();
    
    // Vérification de l'intervalle
    if (!force && (now - lastRefresh.value < REFRESH_INTERVAL)) {
      return;
    }

    isLoading.value = true;

    try {
      if (user.value) {
        try {
          const meResponse = await getMe();
          user.value = meResponse.data.data;
          sessionExpired.value = false;
          
          const niveau = user.value?.type_compte?.niveau_acces ?? null;
          await fetchProjetActuel(niveau);

        } catch (error) {
          if (error.response?.status === 401) {
            sessionExpired.value = true;
          }

          user.value = null;
          localStorage.removeItem('token');
          
          await fetchProjetActuel(null);
        }
      } else {
        await fetchProjetActuel(null);
      }
      
      lastRefresh.value = Date.now();
    } catch (err) {
      console.error("Erreur globale loadData:", err);
    } finally {
      isLoading.value = false;
    }
  }

  return { 
    currentProjectArchive, 
    setCurrentProject,
    fullData,
    sessionExpired,
    user,
    isLoading,
    loadData
  };
}, 
{
  persist: [
  {
    key: 'project-user',
    storage: localStorage,
    paths: ['user']
  },
  {
    key: 'project-archive',
    storage: localStorage,
    paths: ['currentProjectArchive']
  }
]
});