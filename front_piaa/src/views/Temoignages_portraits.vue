<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProjectStore } from '@/stores/projectStore';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';
import Temoignages from '@/components/Temoignages.vue';

const projectStore = useProjectStore();

const organisations = computed(() => {
  return projectStore.fullData?.parties_prenantes || [];
});

const temoignages = computed(() => {
  return projectStore.fullData?.temoignages || [];
});

const projet_name = computed(() => {
  return projectStore.fullData?.acronymes;
});

const publicUrl = import.meta.env.VITE_PUBLIC_URL;
</script>

<template>
  <Header />

  <main class="testimonials-page">
    <Temoignages 
      v-if="temoignages.length > 0"
      ref="testimonialsSection" 
      :temoignagesData="temoignages" 
      :projet_name="projet_name"
    />

    <div v-else class="empty-state-wrapper">
      <div class="empty-state-card">
        <div class="icon-container">
          <font-awesome-icon :icon="['fas', 'quote-right']" class="main-icon" />
          <div class="icon-pulse"></div>
        </div>
        
        <div class="text-content">
          <h3>Les acteurs ont la parole... bientôt !</h3>
          <p>
            Nous n'avons pas encore recueilli de témoignages pour le projet 
            <span class="project-highlight">{{ projet_name }}</span>. 
            Revenez un peu plus tard pour découvrir les retours d'expérience.
          </p>
        </div>

        <router-link to="/" class="cta-button">
          <font-awesome-icon :icon="['fas', 'house']" />
          Retour à l'accueil
        </router-link>
      </div>
    </div>

    <PartiesPrenantesST :organisations-data="organisations"/>
  </main>
</template>

<style scoped>
.testimonials-page {
  max-width: 90%;
  margin: 0 auto;
  padding: 2rem 1rem;
}

/* État vide */
.empty-state-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 6rem 1rem;
  min-height: 500px;
}

.empty-state-card {
  text-align: center;
  max-width: 500px;
  padding: 3.5rem;
  border-radius: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.icon-container {
  position: relative;
  width: 110px;
  height: 110px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 0.5rem;
}

.main-icon {
  font-size: 3rem;
  color: #e5e7eb;
  z-index: 2;
  transform: rotate(-10deg);
}

.text-content h3 {
  font-size: 1.6rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.text-content p {
  color: #6b7280;
  line-height: 1.6;
}

.project-highlight {
  color: var(--color-accent);
  font-weight: 600;
  padding: 0 4px;
}

.cta-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.9rem 2rem;
  background: #f8fafc;
  color: #475569;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-top: 1rem;
}

.cta-button:hover {
  background: var(--color-primary);
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
  .empty-state-card {
    padding: 2.5rem 1.5rem;
    background: transparent;
    box-shadow: none;
  }
}
</style>