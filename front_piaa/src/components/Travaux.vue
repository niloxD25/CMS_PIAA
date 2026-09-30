<script setup>
import iconHelmet from '@/assets/icons/helmet-safety-solid.svg';
import iconDigging from '@/assets/icons/person-digging-solid.svg';
import iconTrowel from '@/assets/icons/trowel-bricks-solid.svg';

// Données des travaux
const travaux = [
  {
    title: 'Construction',
    description: 'Mise en place de nouveaux ouvrages stratégiques et canalisations de refoulement pour un réseau étendu.',
    icon: iconHelmet
  },
  {
    title: 'Curage & Nettoyage',
    description: "Entretien rigoureux des canaux d'eaux usées, impératif pour garantir la salubrité et la sécurité de la ville.",
    icon: iconDigging
  },
  {
    title: 'Réhabilitation',
    description: "Modernisation des infrastructures existantes pour optimiser la performance globale du réseau d'assainissement.",
    icon: iconTrowel
  }
];
</script>

<template>
  <section class="nos-travaux">
    <div class="container">
      <div class="header-section">
        <h1 class="main-title">Nos expertises</h1>
        <p class="subtitle">Des solutions durables pour l'assainissement urbain.</p>
      </div>
      
      <div class="travaux-grid">
        <div 
          v-for="(item, index) in travaux" 
          :key="index" 
          class="travaux-card"
        >
          <div class="icon-wrapper">
            <img :src="item.icon" :alt="item.title" class="icon-svg">
          </div>
          
          <div class="card-content">
            <h2 class="card-title">{{ item.title }}</h2>
            <p class="card-description">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Variables locales pour un contrôle facile */
.nos-travaux {
  --primary: var(--text-color);
  --secondary: #64748b;
  --accent: var(--color-accent);
  --bg-card: #ffffff;
  --icon-bg: #eff6ff;
  
  padding: 6rem 2rem;
  background-color: var(--bg-section);
}

.container {
  max-width: 90%;
  margin: 0 auto;
}

.header-section {
  text-align: center;
  max-width: 600px;
  margin: 0 auto 4rem auto;
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
  letter-spacing: -0.02em;
}

.travaux-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2.5rem;
}

.travaux-card {
  background: var(--bg-card);
  padding: 3rem 2rem;
  border-radius: var(--border-radius);
  transition: all 0.4s ease;
  border: 1px solid transparent;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.travaux-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.05);
  border-color: rgba(59, 130, 246, 0.1);
}

.icon-wrapper {
  width: 64px;
  height: 64px;
  background-color: var(--icon-bg);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
  transition: all 0.3s ease;
}

.icon-svg {
  width: 32px;
  height: 32px;
  filter: invert(13%) sepia(20%) saturate(800%) hue-rotate(180deg) brightness(95%) contrast(90%);
  transition: all 0.3s ease;
}

.travaux-card:hover .icon-wrapper {
  background-color: var(--accent);
}

.travaux-card:hover .icon-svg {
  filter: brightness(0) invert(1);
  transform: scale(1.1);
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 1rem;
}

.card-description {
  color: var(--secondary);
  line-height: 1.7;
  font-size: 0.95rem;
  margin: 0;
}

@media (max-width: 768px) {
  .nos-travaux {
    padding: 4rem 1.5rem;
  }
  
  .main-title {
    font-size: 2rem;
  }
  
  .travaux-card {
    padding: 2rem;
    align-items: center;
    text-align: center;
  }
}
</style>