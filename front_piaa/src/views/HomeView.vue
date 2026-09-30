<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useProjectStore } from '@/stores/projectStore';
import Header from '@/components/Header.vue';
import Travaux from '../components/Travaux.vue';
import EquipesPIAA from '../components/Equipes.vue';
import Mots from '@/components/Mots.vue';
import ListeActualites from '@/components/ListeActualites.vue';
import PartiesPrenantes from '@/components/PartiesPrenantes.vue';
import PartenariatFinancier from '@/components/PartenariatFinancier.vue';

// --- Initialisation ---
const projectStore = useProjectStore();

const actualites = computed(() => projectStore.fullData?.actualites || []);
const mots = computed(() => projectStore.fullData?.mots || []);
const personnes = computed(() => projectStore.fullData?.projet_personnes || []);
const description = computed(() => projectStore.fullData?.description);
const devise = computed(() => projectStore.fullData?.devise);
const projet_name = computed(() => projectStore.fullData?.acronymes || 'le projet');

const organisations = computed(() => ({
  partenariats_financiers: projectStore.fullData?.partenariats_financiers || [],
  parties_prenantes: projectStore.fullData?.parties_prenantes || []
}));

// --- Gestion Dynamique des Sections ---

const hasSectionData = (sectionId) => {
  switch(sectionId) {
    case 'actualites': return actualites.value.length > 0;
    case 'travaux': return true;
    case 'mots': return mots.value.length > 0;
    case 'equipes': return personnes.value.length > 0;
    case 'partenariats': return organisations.value.partenariats_financiers.length > 0;
    case 'parties-prenantes': return organisations.value.parties_prenantes.length > 0;
    default: return false;
  }
};

const visibleSections = computed(() => {
  const allSections = [
    { id: 'actualites', title: 'Actualités', hasTitle: true },
    { id: 'travaux', title: 'Travaux', hasTitle: true },
    { id: 'mots', title: 'Mots des responsables', hasTitle: true },
    { id: 'equipes', title: 'Équipes', hasTitle: true },
    { id: 'partenariats', title: 'Partenariats', hasTitle: true },
    { id: 'parties-prenantes', title: 'Parties Prenantes', hasTitle: true }
  ];
  return allSections.filter(section => hasSectionData(section.id));
});

const sectionRefs = ref([]);
const currentActiveSection = ref(0);
const setSectionRef = (el, index) => { if (el) sectionRefs.value[index] = el; };

const scrollToSection = (index) => {
  const targetElement = sectionRefs.value[index];
  if (targetElement) {
    const el = targetElement.$el || targetElement;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    currentActiveSection.value = index;
  }
};

const setupScrollAnimation = () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.reveal-on-scroll').forEach((el) => observer.observe(el));
};

const setupSectionObserver = () => {
  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-section-index'));
        if (!isNaN(index)) currentActiveSection.value = index;
      }
    });
  }, { rootMargin: '-50% 0px -50% 0px' });

  sectionRefs.value.forEach((section, index) => {
    if (section) {
      const el = section.$el || section;
      el.setAttribute('data-section-index', index);
      sectionObserver.observe(el);
    }
  });
};

onMounted(async () => {
  await nextTick();
  setupScrollAnimation();
  setupSectionObserver();
});

watch(visibleSections, async () => {
  await nextTick();
  setupScrollAnimation();
  setupSectionObserver();
}, { deep: true });
</script>

<template>
  <main>
    <Header />

    <div v-if="visibleSections.length > 0" class="stars-navigation">
      <button
        v-for="(section, index) in visibleSections"
        :key="`star-${section.id}`"
        class="star-button"
        :class="{ 
          active: currentActiveSection === index
        }"
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

    <div class="description-container">
      <div class="description-content">
        <div class="text-section">
          <h1 class="main-title">{{ devise }}</h1>
          
          <div class="description-text">
            <p>{{ description }}</p>
          </div>

          <button class="testimonials-btn" @click="scrollToTestimonials">
            <span class="btn-text">Découvrir</span>
            <span class="btn-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M12 19L19 12M12 19L5 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </button>
        </div>
        
        <div class="image-section">
          <img src="/images/fond.png" alt="Ville protégée par le PIAA" class="description-image">
        </div>
      </div>
    </div>
    
    <!-- Les sections avec références - seulement celles qui ont des données -->
    <div v-if="hasSectionData('actualites')" :ref="(el) => setSectionRef(el, 0)">
      <ListeActualites ref="testimonialsSection" :actualites="actualites" class="reveal-on-scroll"/>
    </div>
    <div v-if="hasSectionData('travaux')" :ref="(el) => setSectionRef(el, 1)">
      <Travaux class="reveal-on-scroll"/>
    </div>
    <div v-if="hasSectionData('mots')" :ref="(el) => setSectionRef(el, 2)">
      <Mots :temoignagesData="mots" :projet_name="projet_name" class="reveal-on-scroll"/>
    </div>
    <div v-if="hasSectionData('equipes')" :ref="(el) => setSectionRef(el, 3)">
      <EquipesPIAA :projet_name="projet_name" :personnes="personnes" class="reveal-on-scroll"/>
    </div>
    <div v-if="hasSectionData('partenariats')" :ref="(el) => setSectionRef(el, 4)">
      <PartenariatFinancier :organisationsData="organisations.partenariats_financiers" class="reveal-on-scroll"/>
    </div>
    <div v-if="hasSectionData('parties-prenantes')" :ref="(el) => setSectionRef(el, 5)">
      <PartiesPrenantes :organisationsData="organisations.parties_prenantes" class="reveal-on-scroll"/>
    </div>
  </main>
</template>

<style scoped>
.description-container {
  max-width: 90%;
  height: 700px;
  margin: 0 auto;
  padding: 2rem;
  position: relative;
  align-items: center;
  overflow: hidden;
}

.main-title {
  text-align: left;
}

.description-content {
  display: flex;
  align-items: center;
  gap: 4rem;
  position: relative;
}

.text-section {
  flex: 1;
  z-index: 2;
}

.description-text p {
  line-height: 1.6;
  color: var(--color-text);
  margin-bottom: 1.5rem;
  font-size: 1.05rem;
}

.description-text p:last-child {
  margin-bottom: 2.5rem;
}

.testimonials-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  padding: 1rem 2rem;
  background: var(--color-primary);
  color: var(--color-text-light);
  border: none;
  border-radius: var(--border-radius);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  position: relative;
  overflow: hidden;
  margin-top: 10px;
}

.btn-text {
  transition: all 0.3s ease;
  transform: translateX(0);
  opacity: 1;
  white-space: nowrap;
}

.btn-icon {
  transition: all 0.3s ease;
  transform: translateX(20px);
  opacity: 0;
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
}

.testimonials-btn .btn-icon {
  width: 0;
  opacity: 0;
  transform: translateX(20px);
}

.testimonials-btn:hover {
  background: var(--color-accent);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  gap: 0.5rem;
}

.testimonials-btn:hover .btn-text {
  transform: translateX(-10px);
  opacity: 0.9;
  font-size: 0.9rem;
}

.testimonials-btn:hover .btn-icon {
  width: auto;
  opacity: 1;
  transform: translateX(0);
  position: static;
}

.testimonials-btn:active {
  transform: translateY(0);
}

.image-section {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  border-radius: 20px;
  height: 100%;
  scale: 1.5;
  position: relative;
}

.description-image {
  width: 100%;
  height: 120%;
  object-fit: cover;
  object-position: center;
  transition: transform 0.5s ease;
}

.image-section:hover .description-image {
  transform: scale(1.05);
}

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

/* --- Responsive Design --- */
@media (max-width: 1200px) {
  .description-content {
    gap: 3rem;
  }
  
  .main-title {
    font-size: 2.5rem;
  }
  
  .description-text p {
    font-size: 1.1rem;
  }
}

@media (max-width: 968px) {
  .description-content {
    flex-direction: column;
    gap: 2.5rem;
    text-align: center;
  }
  
  .text-section {
    order: 2;
  }
  
  .image-section {
    order: 1;
    height: 350px;
    width: 100%;
    scale: 1.2;
  }
  
  .main-title {
    font-size: 2.2rem;
  }
  
  .testimonials-btn {
    padding: 0.875rem 1.75rem;
    font-size: 1rem;
  }
  
  .testimonials-btn:hover {
    padding: 0.875rem 1.5rem;
  }
  
  .testimonials-btn:hover .btn-text {
    font-size: 0.95rem;
  }
}

@media (max-width: 990px) {
  .description-container {
    padding: 1.5rem;
    height: auto;
    min-height: 600px;
  }
  
  .main-title {
    font-size: 2rem;
    text-align: center;
  }
  
  .image-section {
    height: 300px;
    scale: 1.1;
  }
  
  .testimonials-btn {
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
  }
  
  .testimonials-btn:hover {
    padding: 0.75rem 1.25rem;
  }
  
  .testimonials-btn:hover .btn-text {
    font-size: 0.9rem;
  }
}

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
  
  .about-container {
    padding: 2rem 1rem;
  }
  
  .header-content {
    margin-bottom: 3rem;
  }
}

@media (max-width: 480px) {
  .main-title {
    font-size: 1.8rem;
  }
  
  .description-text p {
    font-size: 1rem;
  }
  
  .image-section {
    height: 250px;
    border-radius: 15px;
    scale: 1;
  }
  
  .description-container {
    padding: 1rem;
    min-height: 500px;
  }
  
  .testimonials-btn {
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
  }
  
  .testimonials-btn:hover {
    padding: 0.625rem 1rem;
  }
  
  .testimonials-btn:hover .btn-text {
    font-size: 0.85rem;
  }
  
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
</style>