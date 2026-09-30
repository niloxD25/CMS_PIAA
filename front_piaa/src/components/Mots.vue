<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  temoignagesData: {
    type: Array,
    default: () => []
  },
  projet_name: {
    type: String,
    default: null
  }
});

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const currentIndex = ref(0);
const isPlaying = ref(true);
const autoPlayInterval = ref(null);
const textContainerRef = ref(null);

const filteredTemoignages = computed(() => {
  return props.temoignagesData.filter(t => !t.archived && t.deleted_at === null);
});

const currentTemoignage = computed(() => {
  return filteredTemoignages.value[currentIndex.value] || null;
});

const preloadImages = () => {
  filteredTemoignages.value.forEach(t => {
    if (t.image) {
      const img = new Image();
      img.src = `${publicUrl}${t.image}`;
    }
  });
};

const resetScroll = () => {
  nextTick(() => {
    if (textContainerRef.value) {
      textContainerRef.value.scrollTop = 0;
    }
  });
};

const nextTemoignage = () => {
  if (filteredTemoignages.value.length <= 1) return;
  currentIndex.value = (currentIndex.value + 1) % filteredTemoignages.value.length;
  resetScroll();
};

const prevTemoignage = () => {
  if (filteredTemoignages.value.length <= 1) return;
  currentIndex.value = (currentIndex.value - 1 + filteredTemoignages.value.length) % filteredTemoignages.value.length;
  resetScroll();
};

const goToTemoignage = (index) => {
  if (index === currentIndex.value) return;
  currentIndex.value = index;
  resetScroll();
};

const toggleAutoPlay = () => {
  isPlaying.value = !isPlaying.value;
};

const startAutoPlay = () => {
  stopAutoPlay();
  autoPlayInterval.value = setInterval(() => {
    if (isPlaying.value && filteredTemoignages.value.length > 1) {
      nextTemoignage();
    }
  }, 6000);
};

const stopAutoPlay = () => {
  if (autoPlayInterval.value) {
    clearInterval(autoPlayInterval.value);
    autoPlayInterval.value = null;
  }
};

const pauseOnHover = () => stopAutoPlay();
const resumeOnLeave = () => {
  if (isPlaying.value) startAutoPlay();
};

onMounted(() => {
  preloadImages();
  startAutoPlay();
});

onUnmounted(() => {
  stopAutoPlay();
});

watch(isPlaying, (newVal) => {
  if (newVal) startAutoPlay();
  else stopAutoPlay();
});

watch(() => props.temoignagesData, () => {
  currentIndex.value = 0;
  preloadImages();
});
</script>

<template>
  <div 
    class="testimonials-modern-container" 
    v-if="filteredTemoignages.length > 0 && currentTemoignage"
    @mouseenter="pauseOnHover"
    @mouseleave="resumeOnLeave"
  >
    <div class="header-content">
      <h1 class="main-title">Mots des responsables</h1>
    </div>

    <div class="testimonial-main-card">
      
      <div class="testimonial-image-wrapper">
        <TransitionGroup name="image-fade">
          <div 
            v-for="(t, index) in filteredTemoignages" 
            :key="t.id"
            class="absolute-image-container"
            v-show="index === currentIndex"
          >
            <img 
              :src="`${publicUrl}${t.image}`" 
              :alt="t.temoin"
              class="profile-image"
            >
          </div>
        </TransitionGroup>
      </div>

      <div class="quote-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
          <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/>
        </svg>
      </div>

      <div class="testimonial-content">
        <div class="content-scrollable" ref="textContainerRef">
          <Transition name="text-slide" mode="out-in">
            <div :key="currentTemoignage.id" class="quote-text-inner">
              <p class="quote-text">
                "{{ currentTemoignage.contenu }}"
              </p>
            </div>
          </Transition>
        </div>

        <div class="bottom-section">
          <div class="author-info-wrapper">
            <Transition name="fade-up" mode="out-in">
              <div :key="currentTemoignage.id" class="author-info">
                <div class="author-name">{{ currentTemoignage.temoin }}</div>
                <div class="author-residence">{{ currentTemoignage.residence }}</div>
              </div>
            </Transition>
          </div>

          <div class="navigation-controls">
            <button class="nav-button prev-button" @click="prevTemoignage">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            
            <div class="dots-indicator" v-if="filteredTemoignages.length > 1">
              <button
                v-for="(_, index) in filteredTemoignages"
                :key="index"
                class="dot"
                :class="{ 'active': index === currentIndex }"
                @click="goToTemoignage(index)"
              />
            </div>
            
            <button class="nav-button next-button" @click="nextTemoignage">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </button>

            <button class="play-toggle" @click="toggleAutoPlay" :title="isPlaying ? 'Pause' : 'Play'">
              <span v-if="isPlaying">II</span>
              <span v-else>►</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.image-fade-enter-active, .image-fade-leave-active { 
  transition: opacity 0.8s ease-in-out; 
}
.image-fade-enter-from, .image-fade-leave-to { 
  opacity: 0; 
}

.text-slide-enter-active, .text-slide-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.text-slide-enter-from { opacity: 0; transform: translateX(20px); }
.text-slide-leave-to { opacity: 0; transform: translateX(-20px); }

.fade-up-enter-active, .fade-up-leave-active { transition: all 0.4s ease-out; }
.fade-up-enter-from { opacity: 0; transform: translateY(10px); }
.fade-up-leave-to { opacity: 0; transform: translateY(-10px); }

.testimonials-modern-container {
  max-width: 90%;
  margin: 50px auto;
  padding: 4rem 2rem;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

.testimonial-main-card {
  border-radius: var(--border-radius);
  padding: 3rem;
  position: relative;
  min-height: 400px;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.testimonial-image-wrapper {
  position: absolute;
  top: 2rem;
  right: 2rem;
  width: 200px;
  height: 200px;
  z-index: 2;
  overflow: hidden;
  border-radius: var(--border-radius);
  background-color: #f3f4f6;
}

.absolute-image-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.quote-icon {
  position: absolute;
  top: 10rem;
  left: 5rem;
  color: #e5e7eb;
  z-index: 1;
}

.testimonial-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 350px; 
  position: relative;
  z-index: 2;
  margin-right: 200px;
  margin-left: 100px;
  margin-top: 180px;
}

.content-scrollable {
  flex: 1; 
  height: 100%; 
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 2rem;
  padding-right: 1.5rem;
  width: 100%;
  max-width: 800px;
  scroll-behavior: smooth;
}

/* Scrollbar Style */
.content-scrollable::-webkit-scrollbar { width: 4px; }
.content-scrollable::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.content-scrollable::-webkit-scrollbar-thumb { background: var(--color-accent, #4ade80); border-radius: 10px; }

.quote-text {
  font-size: 1.1rem;
  font-weight: 300;
  line-height: 1.6;
  color: var(--text-color, #333);
  margin: 0;
}

.bottom-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: auto; 
}

.author-name { font-size: 1.3rem; font-weight: 700; color: var(--text-color); }
.author-residence { font-size: 0.95rem; color: #666; }

.navigation-controls {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #f9fafb;
  padding: 0.5rem 1rem;
  border-radius: 50px;
}

.nav-button {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: white;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.nav-button:hover {
  background: var(--color-accent, #4ade80);
  color: white;
  transform: scale(1.05);
}

.play-toggle {
  background: none; border: none; font-size: 0.7rem; color: #999; cursor: pointer; margin-left: 5px; width: 20px;
}
.play-toggle:hover { color: #333; }

.dots-indicator { display: flex; gap: 6px; }
.dot { width: 8px; height: 8px; border-radius: 50%; border: none; background: #d1d5db; cursor: pointer; transition: all 0.3s; }
.dot.active { background: var(--color-accent, #4ade80); width: 24px; border-radius: 4px; }

@media (max-width: 1024px) {
  .testimonial-content { margin: 50px 0 0 0; flex: none;}
  .testimonial-main-card { flex-direction: column; padding: 1rem; align-items: center; }
  .testimonial-image-wrapper { position: relative; top: 0; right: 0; margin-bottom: 1rem; width: 120px; height: 120px; }
  .quote-icon { top: 1rem; right: 1rem; }
  .bottom-section { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
</style>