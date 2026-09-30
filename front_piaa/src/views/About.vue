<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router'
import { useAboutStore } from '@/stores/useAboutStore'
import Header from '@/components/Header.vue';

const projet = computed(() => aboutStore.data);
const projet_name = computed(() => projet.value?.acronymes);
const rawSections = computed(() => projet.value?.projet_apropos_sections);
const publicUrl = import.meta.env.VITE_PUBLIC_URL;

// Références pour les sections
const sectionRefs = ref([]);
const currentActiveSection = ref(0);

const router = useRouter()
const aboutStore = useAboutStore()

if (!aboutStore.data) {
  router.push('/')
}

const groupedSections = computed(() => {
  if (!rawSections.value || rawSections.value.length === 0) return [];

  const groups = [];
  let currentGroup = null;
  let groupIndex = 0;

  const sorted = [...rawSections.value].sort((a, b) => a.ordre - b.ordre);

  sorted.forEach((section, index) => {
    // Si la section a un titre OU un sous_titre, c'est un nouveau "Chapitre"
    if (section.titre || section.sous_titre) {
      groupIndex++;
      currentGroup = {
        id: section.id || `group-${index}`,
        titre: section.titre || '',
        sous_titre: section.sous_titre || '',
        hasTitle: !!section.titre,
        hasSubtitleOnly: !section.titre && !!section.sous_titre,
        parts: [] 
      };
      
      // On ajoute le contenu de cette section comme première partie
      if (section.description || section.image) {
        currentGroup.parts.push({
          id: section.id,
          description: section.description,
          image: section.image
        });
      }
      
      groups.push(currentGroup);
    } 
    // Si pas de titre ni sous_titre, c'est la suite du groupe actuel
    else if (currentGroup) {
      currentGroup.parts.push({
        id: section.id,
        description: section.description,
        image: section.image
      });
    }
    // Cas orphelin (rare): section sans titre au tout début
    else {
      groupIndex++;
      currentGroup = {
        id: 'intro',
        titre: 'Introduction',
        sous_titre: '',
        hasTitle: true,
        hasSubtitleOnly: false,
        parts: [{
          id: section.id,
          description: section.description,
          image: section.image
        }]
      };
      groups.push(currentGroup);
    }
  });

  return groups;
});

// Fonction pour obtenir le texte du tooltip
const getTooltipText = (group) => {
  if (group.titre && group.sous_titre) {
    return `${group.titre}: ${group.sous_titre}`;
  }
  return group.titre || group.sous_titre || `Section`;
};

// Fonction pour naviguer vers une section
const scrollToSection = (index) => {
  if (sectionRefs.value[index]) {
    sectionRefs.value[index].scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    currentActiveSection.value = index;
  }
};

// Fonction pour gérer l'animation au scroll
const setupScrollAnimation = () => {
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.15 
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  const elements = document.querySelectorAll('.reveal-on-scroll');
  elements.forEach((el) => observer.observe(el));
};

// Observer pour détecter la section active
const setupSectionObserver = () => {
  const sectionObserverOptions = {
    root: null,
    rootMargin: '-50% 0px -50% 0px',
    threshold: 0
  };

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-section-index'));
        if (!isNaN(index)) {
          currentActiveSection.value = index;
        }
      }
    });
  }, sectionObserverOptions);

  // Observer chaque section
  sectionRefs.value.forEach((section, index) => {
    if (section) {
      section.setAttribute('data-section-index', index);
      sectionObserver.observe(section);
    }
  });
};

// Initialiser les références
const setSectionRef = (el, index) => {
  sectionRefs.value[index] = el;
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

onMounted(async () => {
  await nextTick();
  setupScrollAnimation();
  setupSectionObserver();
});

watch(rawSections, async () => {
  await nextTick();
  setupScrollAnimation();
  setupSectionObserver();
});
</script>

<template>
  <main>
    <Header />

    <!-- Navigation par étoiles -->
    <div class="stars-navigation">
      <button
        v-for="(group, index) in groupedSections"
        :key="`star-${group.id}`"
        class="star-button"
        :class="{ 
          active: currentActiveSection === index,
          'subtitle-only': group.hasSubtitleOnly 
        }"
        @click="scrollToSection(index)"
        :aria-label="`Aller à la section ${group.titre || group.sous_titre || 'sans titre'}`"
      >
        <svg :width="group.hasSubtitleOnly ? '12' : '16'" 
             :height="group.hasSubtitleOnly ? '12' : '16'" 
             viewBox="0 0 24 24" 
             fill="none" 
             xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" 
                :stroke="currentActiveSection === index ? 'var(--color-accent)' : 'var(--vt-c-divider-dark-1)'"
                :stroke-width="currentActiveSection === index ? '2' : '1'"
                :fill="currentActiveSection === index ? 'var(--color-accent)' : 'transparent'"/>
        </svg>
        <span class="star-tooltip">{{ getTooltipText(group) }}</span>
      </button>
    </div>

    <div class="about-container">
      <div class="header-content reveal-on-scroll">
        <h1 class="main-title">À propos de <span class="highlight">{{ projet_name }}</span></h1>
      </div>

      <section class="info-grid section-spacer reveal-on-scroll">
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-calendar-plus"/></span>
          <div class="info-data">
            <label>Début du projet</label>
            <strong>{{ formatDate(projet?.debut) }}</strong>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-hourglass-half"/></span>
          <div class="info-data">
            <label>Durée totale</label>
            <strong>{{ projet?.duree_annee }} {{ projet?.duree_annee > 1 ? 'ans' : 'an' }}</strong>
          </div>
        </div>
        <div class="info-card">
          <span class="icon-wrapper"><font-awesome-icon icon="fa-solid fa-calendar-check"/></span>
          <div class="info-data">
            <label>Fin estimée</label>
            <strong>{{ calculateEndDate(projet?.debut, projet?.duree_annee) }}</strong>
          </div>
        </div>
      </section>

      <div class="story-flow">
        <article 
          v-for="(group, index) in groupedSections" 
          :key="group.id" 
          class="story-chapter reveal-on-scroll"
          :class="{ 'has-number': group.hasTitle }"
          :ref="(el) => setSectionRef(el, index)"
        >
          <div class="chapter-header">
            <div class="chapter-header-content">
              <h2 v-if="group.titre" class="chapter-title">{{ group.titre }}</h2>
              <h3 v-if="group.sous_titre" 
                  class="chapter-subtitle">
                {{ group.sous_titre }}
              </h3>
            </div>
            <span v-if="group.hasTitle" class="chapter-number">0{{ index + 1 }}</span>
          </div>

          <div class="chapter-content">
            <div v-for="part in group.parts" :key="part.id" class="content-block">
              <div v-if="part.description" class="text-content">
                <p>{{ part.description }}</p>
              </div>

              <figure v-if="part.image" class="media-content">
                <img :src="`${publicUrl}${part.image}`" :alt="group.titre || group.sous_titre" loading="lazy" />
              </figure>
            </div>
          </div>
        </article>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Styles existants conservés */
.about-container {
  max-width: 90%;
  margin: 0 auto;
  padding: 4rem 1.5rem;
}

.header-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 6rem auto;
}

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

.eyebrow {
  display: block;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--color-accent);
  margin-bottom: 1rem;
  font-weight: 600;
}

.highlight {
  color: var(--color-accent);
}

.story-flow {
  display: flex;
  flex-direction: column;
  gap: 6rem;
  margin-bottom: 6rem;
}

.story-chapter {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 4rem;
  align-items: start;
  scroll-margin-top: 100px;
}

.chapter-header {
  position: sticky;
  top: 120px;
  padding-right: 2rem;
  border-right: 1px solid rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  min-height: 100px;
}

.chapter-header-content {
  position: relative;
  z-index: 2;
}

.chapter-number {
  font-size: 5rem;
  font-weight: 900;
  color: rgba(0,0,0,0.05);
  line-height: 1;
  position: absolute;
  top: -3rem;
  left: -1rem;
  z-index: 1;
}

.chapter-title {
  font-size: 2rem;
  font-weight: 300;
  color: var(--text-color);
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 2;
}

.chapter-subtitle {
  font-size: 1.1rem;
  color: var(--vt-c-divider-dark-1);
  font-weight: 400;
  margin-top: 0;
  position: relative;
  z-index: 2;
}

.chapter-content {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

.content-block {
  animation: fadeIn 0.8s ease-out;
}

.text-content p {
  font-size: 1rem;
  font-weight: 300;
  line-height: 1.8;
  color: var(--text-color);
  white-space: pre-line;
  margin-bottom: 0;
}

.media-content {
  margin-top: 2rem;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.media-content img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.5s ease;
}

.media-content:hover img {
  transform: scale(1.02);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* --- Système reveal-on-scroll --- */
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

/* --- Navigation par étoiles --- */
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

/* Étoiles normales (avec titre) */
.star-button:not(.subtitle-only) svg {
  transition: all 0.3s ease;
  width: 14px;
  height: 14px;
}

/* Étoiles plus petites (sous-titres seulement) */
.star-button.subtitle-only svg {
  transition: all 0.3s ease;
  width: 10px;
  height: 10px;
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

/* Flèche du tooltip */
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

/* --- Responsive Design --- */
@media (max-width: 968px) {
  .star-button:not(.subtitle-only) svg {
    width: 14px;
    height: 14px;
  }
  
  .star-button.subtitle-only svg {
    width: 10px;
    height: 10px;
  }
  
  .story-chapter {
    grid-template-columns: 1fr;
    gap: 3rem;
    position: relative;
    scroll-margin-top: 80px;
  }

  .chapter-header {
    position: relative;
    top: 0;
    padding-right: 0;
    border-right: none;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding-bottom: 1.5rem;
    min-height: auto;
    margin-bottom: 1rem;
  }

  .chapter-number {
    font-size: 4rem;
    top: -1.5rem;
    left: -0.5rem;
  }

  .chapter-title {
    font-size: 1.8rem;
    margin-top: 1rem;
  }

  .chapter-subtitle {
    font-size: 1rem;
  }

  .chapter-subtitle.subtitle-only {
    font-size: 1.2rem;
    margin-top: 1rem;
  }

  .story-chapter:not(.has-number) .chapter-header {
    padding-top: 0;
    padding-bottom: 1rem;
  }

  .main-title {
    font-size: 2.2rem;
  }
}

@media (max-width: 768px) {  
  .star-button:not(.subtitle-only) svg {
    width: 12px;
    height: 12px;
  }
  
  .star-button.subtitle-only svg {
    width: 8px;
    height: 8px;
  }
}

@media (max-width: 600px) {
  .stars-navigation {
    padding: 10px 15px;
    gap: 12px;
  }
  
  .star-button:not(.subtitle-only) svg {
    width: 10px;
    height: 10px;
  }
  
  .star-button.subtitle-only svg {
    width: 7px;
    height: 7px;
  }
  
  .star-tooltip {
    font-size: 0.8rem;
    padding: 8px 12px;
  }
  
  .about-container {
    padding: 2rem 1rem;
  }
  
  .header-content {
    margin-bottom: 3rem;
  }

  .story-chapter {
    gap: 2rem;
  }

  .chapter-number {
    font-size: 3rem;
    top: -1rem;
  }

  .chapter-title {
    font-size: 1.6rem;
  }

  .chapter-subtitle {
    font-size: 0.95rem;
  }

  .chapter-subtitle.subtitle-only {
    font-size: 1.1rem;
  }

  .story-flow {
    gap: 4rem;
  }
}
</style>