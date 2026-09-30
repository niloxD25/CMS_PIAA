<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projectStore';
import { logout as apiLogout } from '@/services/api';
import Header from '@/components/Header.vue';

// --- Initialisation ---
const router = useRouter();
const projectStore = useProjectStore();

const isLoggingOut = ref(false);

const user = computed(() => projectStore.user);
const loading = false
const error = ref('');;

// --- Méthodes ---

const formatDate = (dateString) => {
  if (!dateString) return 'Non disponible';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getTypeIcon = (niveauAcces) => {
  const icons = {
    0: 'user-shield',
    1: 'user-cog',
  };
  return ['fas', icons[niveauAcces] || 'user'];
};

const logout = async () => {
  isLoggingOut.value = true;
  try {
    await apiLogout();
  } catch (err) {
    console.error('Erreur côté serveur lors de la déconnexion', err);
    error.value = 'Erreur côté serveur lors de la déconnexion: ' + err;
  } finally {
    projectStore.user = null;
    localStorage.removeItem('token');
    
    await projectStore.loadData(true);

    router.push('/');
    isLoggingOut.value = false;
  }
};

onMounted(() => {
  if (!user.value) {
     console.warn("Aucun utilisateur détecté dans le store.");
     error.value = "Erreur interne, veuillez actualiser la page"
  }
});
</script>

<template>
  <Header />

  <main class="page-container">
    <transition name="fade" mode="out-in">
      
      <div v-if="loading" class="state-container" key="loading">
        <div class="spinner-ring"></div>
        <p>Chargement du profil...</p>
      </div>

      <div v-else-if="error" class="state-container error" key="error">
        <div class="icon-bubble error-bubble">
          <font-awesome-icon :icon="['fas', 'exclamation']" />
        </div>
        <h3>Oups !</h3>
        <p>{{ error }}</p>
        <button class="btn btn-primary" @click="loadUserData">
          <font-awesome-icon :icon="['fas', 'redo']" /> Réessayer
        </button>
      </div>

      <div v-else-if="user" class="profile-wrapper" key="content">
        
        <div class="profile-header-card">
          <div class="avatar-container">
            <font-awesome-icon :icon="['fas', 'user']" />
          </div>
          <div class="header-text">
            <h1 class="user-name">{{ user.name }}</h1>
            <span class="user-email">{{ user.email }}</span>
          </div>
          <div class="header-badge" :class="`badge-level-${user.type_compte?.niveau_acces}`">
            {{ user.type_compte?.type || 'Compte' }}
          </div>
        </div>

        <div class="details-grid">
          <section class="card info-card">
            <h2 class="card-title">
              <font-awesome-icon :icon="['fas', 'address-card']" class="title-icon" />
              À propos
            </h2>
            
            <div class="info-row">
              <div class="info-group">
                <label>Nom complet</label>
                <p>{{ user.name }}</p>
              </div>
              <div class="info-group">
                <label>Email</label>
                <p>{{ user.email }}</p>
              </div>
              <div class="info-group">
                <label>Membre depuis</label>
                <p>{{ formatDate(user.created_at) }}</p>
              </div>
            </div>
          </section>

          <section class="card account-card">
            <h2 class="card-title">
              <font-awesome-icon :icon="['fas', 'shield-alt']" class="title-icon" />
              Accès
            </h2>
            
            <div class="access-box">
              <div class="access-icon">
                 <font-awesome-icon :icon="getTypeIcon(user.type_compte.niveau_acces)" />
              </div>
              <div class="access-details">
                <h3>{{ user.type_compte.type }}</h3>
                <p>Niveau d'accès: {{ user.type_compte.niveau_acces }}</p>
              </div>
            </div>
          </section>
        </div>

        <div class="actions-footer">
          <button class="btn btn-danger-ghost" @click="logout" :disabled="isLoggingOut">
            <span v-if="isLoggingOut">
              <font-awesome-icon :icon="['fas', 'spinner']" spin /> Déconnexion...
            </span>
            <span v-else>
              <font-awesome-icon :icon="['fas', 'sign-out-alt']" /> Se déconnecter
            </span>
          </button>
        </div>

      </div>
    </transition>
  </main>
</template>

<style scoped>
/* --- Variables & Reset --- */
:root {
  --primary: var(--bleu-ge);
  --primary-soft: #e0e7ff;
  --text-main: var(--text-color);
  --text-muted: #6b7280;
  --bg-page: #f9fafb;
  --bg-card: #ffffff;
  --danger: var(--color-error);
  --radius: var(--border-radius);
  --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
}

.page-container {
  min-height: calc(100vh - 64px);
  background-color: var(--bg-page);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 20px;
  color: var(--text-main);
}

/* --- Loading & Error States --- */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 100px;
  text-align: center;
}

.spinner-ring {
  position: absolute;
  top: 50%; left: 50%;
  width: 50px; height: 50px;
  margin: -25px 0 0 -25px;
  border-top: 4px solid var(--color-accent, #4f46e5);
  border-radius: 50%;
  animation: spin 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  z-index: 2;
}

.error-bubble {
  width: 60px;
  height: 60px;
  background: #fee2e2;
  color: var(--danger);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

@keyframes spin { to { transform: rotate(360deg); } }

.profile-wrapper {
  width: 100%;
  max-width: 700px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.profile-header-card {
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 30px;
  box-shadow: var(--shadow);
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
  overflow: hidden;
}

.avatar-container {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--text-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
}

.header-text {
  flex: 1;
}

.user-name {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.025em;
}

.user-email {
  color: var(--text-muted);
  font-size: 0.95rem;
}

.header-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  background: #f3f4f6;
  color: var(--text-muted);
}

.badge-level-0 { background: #dbeafe; color: #1e40af; }
.badge-level-1 { background: #d1fae5; color: #065f46; }

.details-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.card {
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 24px;
  box-shadow: var(--shadow);
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: var(--shadow-hover);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-main);
}

.title-icon {
  color: #9ca3af;
  font-size: 0.9em;
}

/* Info Section */
.info-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
}

.info-group label {
  display: block;
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-bottom: 4px;
  font-weight: 500;
}

.info-group p {
  margin: 0;
  font-weight: 500;
  font-size: 1rem;
}

/* Access Section */
.access-box {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #f8fafc;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.access-icon {
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.access-details h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.access-details p {
  margin: 2px 0 0 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}

/* --- Buttons --- */
.actions-footer {
  margin-top: 10px;
  text-align: center;
}

.btn {
  border: none;
  border-radius: 10px;
  padding: 12px 24px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-danger-ghost {
  background: transparent;
  color: var(--text-muted);
  border: 1px solid #e5e7eb;
  width: 100%;
  justify-content: center;
}

.btn-danger-ghost:hover {
  background: #fee2e2;
  color: var(--danger);
  border-color: #fecaca;
}

@media (max-width: 600px) {
  .profile-header-card {
    flex-direction: column;
    text-align: center;
  }
  
  .header-badge {
    align-self: center;
  }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active { transition: all 0.4s ease-out; }
.slide-up-enter-from { opacity: 0; transform: translateY(20px); }
</style>