<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAboutStore } from '@/stores/useAboutStore';
import { useProjectStore } from '@/stores/projectStore';
import BTT from './components/BTT.vue';
import Annonces from './components/Annonces.vue';
import FloatingMessageIcon from './components/FloatingMessageIcon.vue';
import SocialIcons from './components/SocialIcons.vue';
import Footer from './components/Footer.vue';

// --- Initialisation ---
const router = useRouter();
const aboutStore = useAboutStore();
const projectStore = useProjectStore();

const loadError = ref(null);
let refreshInterval = null;
const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const projetData = computed(() => projectStore.fullData);
const socialLinks = computed(() => projetData.value?.reseaux_sociaux || []);

const projetAdress = computed(() => projetData.value?.adresse);
const projetEmail = computed(() => projetData.value?.email);
const projetPhone = computed(() => projetData.value?.telephone);
const projetName = computed(() => projetData.value?.nom);

const goToAbout = (data) => {
  aboutStore.setData(data);
  router.push('/about');
};

const startPolling = () => {
  if (refreshInterval) return;
  refreshInterval = setInterval(() => {
    projectStore.loadData();
  }, 5 * 60 * 1000);
};

const stopPolling = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

const handleVisibilityChange = async () => {
  if (document.visibilityState === 'visible') {
    await projectStore.loadData();
    startPolling();
  } else {
    stopPolling();
  }
};

onMounted(async () => {
  try {
    await projectStore.loadData(true);

    if (document.visibilityState === 'visible') {
      startPolling();
    }

    document.addEventListener('visibilitychange', handleVisibilityChange);

  } catch (error) {
    console.error('Failed to load project data: ', error);
    loadError.value = 'Une erreur est survenue lors du chargement des données.';
  }
});

onUnmounted(() => {
  stopPolling();
  document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

<template>
   <Transition name="slide-down">
    <div v-if="projectStore.sessionExpired" class="session-alert">
      <div class="alert-content">
        <span class="icon">⚠️</span>
        <p>Votre session a expiré. Veuillez vous reconnecter si besoin.</p>
        <div class="alert-actions">
          <RouterLink to="/profile" class="login-link" @click="projectStore.sessionExpired = false">
            Se reconnecter
          </RouterLink>
          <button class="close-alert" @click="projectStore.sessionExpired = false">×</button>
        </div>
      </div>
    </div>
  </Transition>

  <div v-if="projectStore.isLoading && !projetData" class="loading-container">
    <div class="spinner"></div>
  </div>
  
  <div v-else-if="loadError" class="error-container">
    <p class="error-text">{{ loadError }}</p>
  </div>

  <div v-else-if="projetData" class="app-container">
    <div class="top-banner">
      <div class="banner-content">
        <div class="banner-left">
          <RouterLink to="/" class="logo-link">
            <img :src="`${publicUrl}${projetData.logo}`" alt="Project Logo" class="banner-logo">
          </RouterLink>
          <div class="banner-text">
            <div class="main-title">{{ projetData.nom }}</div>
            <div class="slogan">{{ projetData.devise }}</div>
          </div>
        </div>
        
        <div class="banner-right">
          <button
            class="about-btn"
            @click="goToAbout(projetData)"
          >
            À propos de {{ projetData.acronymes }}
          </button>
          <SocialIcons 
            :social-links="socialLinks" 
            size="medium"
            variant="default"
          />
        </div>
      </div>
    </div>
    
    <div class="main-content">
      <main>
        <RouterView />
      </main>

      <Footer 
        :social-links="socialLinks" 
        :projet-adress="projetAdress" 
        :projet-email="projetEmail" 
        :projet-phone="projetPhone" 
        :projet-name="projetName"
      />

      <div class="annonces-fixed">
        <Annonces />
      </div>
      
      <BTT />
      <FloatingMessageIcon />
    </div>
  </div>
</template>

<style scoped>
.loading-container, .error-container {
   display: flex;
   flex-direction: column;
   justify-content: center;
   align-items: center;
   height: 100vh;
   color: #333;
   text-align: center;
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

.loading-text, .error-text {
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.5px;
  animation: pulseText 2s infinite ease-in-out;
}

.error-text {
   color: var(--color-error);
}

.error-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f9fafb;
  padding: 20px;
}

.error-text {
  background: white;
  padding: 40px;
  max-width: 450px;
  width: 100%;
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
  color: #4b5563;
  font-size: 1rem;
  line-height: 1.6;
  text-align: center;
  font-weight: 400;  
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.error-text::before {
  content: '!';
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background-color: #fef2f2;
  color: #ef4444;
  border-radius: 50%;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 8px;
  border: 4px solid #fff;
  box-shadow: 0 0 0 4px #fef2f2;
}

.app-container {
   display: flex;
   flex-direction: column;
   min-height: 100vh;
}

/* Bannière en haut */
.top-banner {
   position: relative;
   width: 100%;
   height: auto;
   background-image: url('/images/fond9.png');
   background-size: cover;
   background-position: center;
   background-repeat: no-repeat;
   color: var(--color-primary);
   padding: 32px;
   font-size: 1.1rem;
   font-weight: 600;
   letter-spacing: 0.5px;
}

.banner-content {
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 24px;
   max-width: 90%;
   height: 100%;
   margin: 0 auto;
   padding: 0 20px;
}

.banner-left {
   display: flex;
   align-items: center;
   gap: 40px;
}

.banner-logo {
   height: 40px;
   width: auto;
}

.banner-text {
   display: flex;
   flex-direction: column;
}

.main-title {
   margin: 0;
   font-size: 1.2rem;
   font-weight: 700;
   line-height: 1.2;
}

.slogan {
   font-size: 1rem;
   font-weight: 400;
   opacity: 0.9;
   margin-top: 8px;
   letter-spacing: 0.3px;
   color: var(--text-color);
}

.banner-right {
   display: flex;
   flex-direction: column;
   align-items: flex-end;
   gap: 15px;
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
   z-index: 900;
}

.about-btn:hover {
   background: var(--color-accent);
   transform: translateY(-2px);
   box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
   cursor: pointer;
}

.annonces-fixed {
   position: fixed;
   bottom: 0;
   left: 0;
   width: 100%;
   z-index: 10000;
   background: white;
   box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.main-content {
   width: 100%;
}

.session-alert {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background-color: #fff7ed;
  border-bottom: 1px solid #ffedd5;
  padding: 12px 20px;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.alert-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.alert-content p {
  margin: 0;
  color: #9a3412;
  font-size: 0.95rem;
  font-weight: 500;
}

.alert-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.login-link {
  background: #ea580c;
  color: white;
  padding: 6px 14px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.85rem;
  transition: background 0.2s;
}

.close-alert {
  background: none;
  border: none;
  font-size: 20px;
  color: #9a3412;
  cursor: pointer;
}

/* Animation de glissement */
.slide-down-enter-active, .slide-down-leave-active {
  transition: transform 0.4s ease;
}
.slide-down-enter-from, .slide-down-leave-to {
  transform: translateY(-100%);
}

/* Responsive - styles existants */
@media (max-width: 1000px) {
   .top-banner {
      font-size: 1rem;
      padding: 16px;
   }

   .banner-content {
      gap: 24px;
      padding: 0 15px;
   }

   .banner-left {
      gap: 24px;
   }

   .banner-logo {
      height: 40px;
   }

   .about-btn {
      font-size: 0.85rem;
      padding: 6px 12px;
   }

   .annonces-fixed {
      z-index: 1000;
   }
}

@media (max-width: 768px) {
   .top-banner {
      font-size: 0.9rem;
      padding: 16px;
      height: auto;
   }
   
   .banner-content {
      gap: 12px;
      padding: 0 10px;
      flex-direction: column;
      align-items: center;
      text-align: center;
   }

   .banner-left {
      flex-direction: column;
      gap: 12px;
   }

   .banner-logo {
      height: 40px;
   }

   .main-title {
      font-size: 1rem;
   }

   .slogan {
      font-size: 0.9rem;
   }

   .banner-right {
      flex-direction: row;
      align-items: center;
      gap: 10px;
   }

   .about-btn {
      font-size: 0.75rem;
      padding: 5px 10px;
   }
}
</style>