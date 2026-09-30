<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useProjectStore } from '@/stores/projectStore';

// --- Props ---
const props = defineProps({
  activeMenu: {
    type: String,
    default: ''
  }
});

// --- Initialisation ---
const route = useRoute();
const projectStore = useProjectStore();

const isSearchExpanded = ref(false);
const isMobileSearchExpanded = ref(false);
const isMenuOpen = ref(false);
const activeDropdown = ref(null);
const mobileDropdownOpen = ref(null);
const searchQuery = ref('');

// --- Données de Navigation ---
const navItems = [
  { path: '/', label: 'Accueil', exact: true },
  { 
    label: 'Impacts',
    children: [
      { path: '/reportages-videos', label: 'Reportages vidéos', exact: false },
      { path: '/temoignages-portraits', label: 'Témoignages/Portraits', exact: false },
      { path: '/realisations', label: 'Réalisations/Evolution des travaux', exact: false },
    ]
  },
  { label: 'Activités',
    children: [
      { path: '/plan-travaux', label: 'Investissements Travaux', exact: false },
      { path: '/actions-proximite', label: 'Actions de proximité', exact: false },
    ]
  },
  { path: '/evenements', label: 'Actualités/Evénements', exact: false },
  { path: '/documentation', label: 'Documentation', exact: false },
  { path: '/archives', label: 'Archives', exact: false }
];

const getProfilePath = computed(() => {
  return projectStore.user ? '/profileInfo' : '/profile';
});

// --- Méthodes ---

const isMenuItemActive = (item) => {
  if (item.children) {
    return item.children.some(child => checkPath(child));
  }
  return checkPath(item);
};

// Logique interne pour vérifier l'activité d'un lien
const checkPath = (item) => {
  if (props.activeMenu) {
    return props.activeMenu === item.path;
  }
  if (item.exact) {
    return route.path === item.path;
  }
  return route.path.startsWith(item.path);
};

const openDropdown = (index) => {
  activeDropdown.value = index;
};

const closeDropdown = () => {
  activeDropdown.value = null;
};

const toggleMobileDropdown = (index) => {
  mobileDropdownOpen.value = mobileDropdownOpen.value === index ? null : index;
};

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
  document.body.style.overflow = isMenuOpen.value ? 'hidden' : '';
  if (!isMenuOpen.value) {
    mobileDropdownOpen.value = null;
  }
};
</script>

<template>
  <header class="header" :class="{ 'mobile-menu-active': isMenuOpen }">
    <div class="logo-container">

    </div>

    <!-- Menu desktop -->
    <nav class="nav">
      <ul class="nav-list">
        <li class="nav-item" v-for="(item, index) in navItems" :key="index">
          <!-- Menu déroulant pour Impacts -->
          <div v-if="item.children" class="dropdown-container" @mouseenter="openDropdown(index)" @mouseleave="closeDropdown(index)">
            <div class="dropdown-trigger" :class="{ 'router-link-active': isMenuItemActive(item) }">
              {{ item.label }}
              <span class="active-indicator"></span>
              <svg class="dropdown-arrow" :class="{ 'rotated': activeDropdown === index }" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
              </svg>
            </div>
            
            <!-- Sous-menu déroulant -->
            <transition name="dropdown">
              <div v-if="activeDropdown === index" class="dropdown-menu" @mouseenter="openDropdown(index)" @mouseleave="closeDropdown(index)">
                <ul class="dropdown-list">
                  <li v-for="(child, childIndex) in item.children" :key="childIndex" class="dropdown-item">
                    <router-link 
                      :to="child.path" 
                      class="dropdown-link" 
                      :class="{ 'router-link-active': isMenuItemActive(child) }"
                      @click="closeDropdown(index)"
                    >
                      {{ child.label }}
                    </router-link>
                  </li>
                </ul>
              </div>
            </transition>
          </div>
          
          <!-- Lien normal pour les autres items -->
          <router-link 
            v-else
            :to="item.path" 
            class="nav-link" 
            :class="{ 'router-link-active': isMenuItemActive(item) }"
          >
            {{ item.label }}
            <span class="active-indicator"></span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="header-right">
      <!-- Icône profil desktop -->
      <router-link :to="getProfilePath" class="profile-icon" :class="{ 'router-link-active': activeMenu === getProfilePath }">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
        </svg>
        <span class="active-indicator"></span>
      </router-link>

      <!-- Bouton menu hamburger professionnel (mobile seulement) -->
      <div class="menu-toggle" @click="toggleMenu">
        <div class="hamburger-icon" :class="{ 'active': isMenuOpen }">
          <span class="hamburger-line line-1"></span>
          <span class="hamburger-line line-2"></span>
          <span class="hamburger-line line-3"></span>
        </div>
      </div>
    </div>

    <!-- Menu mobile (overlay) -->
    <div class="mobile-menu-overlay" :class="{ 'active': isMenuOpen }" @click="toggleMenu">
      <nav class="mobile-nav" @click.stop>
        <ul class="mobile-nav-list">
          <li class="mobile-nav-item" v-for="(item, index) in navItems" :key="index">
            <!-- Menu déroulant mobile pour Impacts -->
            <div v-if="item.children" class="mobile-dropdown-container">
              <div class="mobile-dropdown-trigger" @click="toggleMobileDropdown(index)">
                {{ item.label }}
                <svg class="mobile-dropdown-arrow" :class="{ 'rotated': mobileDropdownOpen === index }" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                </svg>
              </div>
              
              <!-- Sous-menu mobile -->
              <transition name="mobile-dropdown">
                <div v-if="mobileDropdownOpen === index" class="mobile-dropdown-menu">
                  <ul class="mobile-dropdown-list">
                    <li v-for="(child, childIndex) in item.children" :key="childIndex" class="mobile-dropdown-item">
                      <router-link 
                        :to="child.path" 
                        class="mobile-dropdown-link" 
                        :class="{ 'router-link-active': isMenuItemActive(child) }"
                        @click="toggleMenu"
                      >
                        {{ child.label }}
                        <span class="active-indicator"></span>
                      </router-link>
                    </li>
                  </ul>
                </div>
              </transition>
            </div>
            
            <!-- Lien normal mobile pour les autres items -->
            <router-link 
              v-else
              :to="item.path" 
              class="mobile-nav-link" 
              :class="{ 'router-link-active': isMenuItemActive(item) }"
              @click="toggleMenu"
            >
              {{ item.label }}
              <span class="active-indicator"></span>
            </router-link>
          </li>
        </ul>

        <!-- Icônes de recherche et profil dans le menu mobile -->
        <div class="mobile-menu-icons">
          <!-- Icône profil mobile -->
          <router-link 
            :to="getProfilePath" 
            class="mobile-profile-icon" 
            :class="{ 'router-link-active': activeMenu === getProfilePath }" 
            @click="toggleMenu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
            </svg>
            <span class="active-indicator"></span>
          </router-link>
        </div>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.header {
  background-color: transparent;
  border-bottom: none;
  padding: 0.5rem 1.5rem;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: var(--border-radius);
  gap: 20px;
  margin-bottom: 50px;
  position: relative;
}

.nav {
  display: flex;
  justify-content: center;
  flex: 1;
  min-width: 0;
}

.nav-list {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 40px;
  flex-wrap: nowrap;
  overflow: visible;
}

.nav-item {
  padding: 8px 12px;
  transition: background-color 0.3s ease;
  white-space: nowrap;
  flex-shrink: 0;
  position: relative;
}

/* Styles pour le dropdown desktop */
.dropdown-container {
  position: relative;
  cursor: pointer;
}

.dropdown-trigger {
  text-decoration: none;
  color: var(--text-color);
  font-size: 1rem;
  font-weight: 500;
  padding: 0.5rem 0;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  position: relative;
}

.dropdown-trigger:hover {
  color: var(--color-accent);
}

.dropdown-trigger.router-link-active {
  color: var(--color-accent);
}

.dropdown-arrow {
  transition: transform 0.3s ease;
  color: currentColor;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: white;
  min-width: 220px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  padding: 12px 0;
  margin-top: 10px;
  z-index: 1000;
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.dropdown-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.dropdown-item {
  padding: 0;
}

.dropdown-link {
  display: block;
  padding: 10px 20px;
  color: var(--color-primary);
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.dropdown-link:hover {
  background-color: var(--bg-section);
  color: var(--color-accent);
  padding-left: 25px;
}

.dropdown-link.router-link-active {
  color: var(--color-accent);
  background-color: var(--bg-section);
}

/* Lien normal pour les autres items */
.nav-link {
  text-decoration: none;
  color: var(--text-color);
  font-size: 1rem;
  font-weight: 500;
  padding: 0.5rem 0;
  transition: all 0.3s ease;
  display: inline-block;
  position: relative;
}

.nav-link:hover {
  color: var(--color-accent);
}

.active-indicator {
  position: absolute;
  bottom: -5px;
  left: 50%;
  width: 0;
  height: 2px;
  background-color: var(--color-accent);
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.router-link-active {
  color: var(--color-accent);
}

.router-link-active .active-indicator {
  width: 80%;
}

.nav-link:not(.router-link-active):hover .active-indicator,
.dropdown-trigger:not(.router-link-active):hover .active-indicator {
  width: 40%;
  background-color: var(--color-accent);
  opacity: 0.6;
}

.profile-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: var(--color-primary);
  text-decoration: none;
  transition: all 0.3s ease;
  flex-shrink: 0;
  position: relative;
}

.profile-icon:hover {
  background-color: var(--color-accent);
  color: var(--color-text-light);
}

.profile-icon.router-link-active {
  background-color: var(--color-accent);
  color: var(--color-text-light);
}

.profile-icon .active-indicator {
  bottom: -3px;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #07043c;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.profile-icon.router-link-active .active-indicator {
  opacity: 1;
}

/* Section droite avec recherche et profil */
.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

/* Menu toggle */
.menu-toggle {
  display: none;
  cursor: pointer;
  z-index: 10001;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  position: relative;
}

.mobile-menu-active .menu-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  background-color: rgba(255, 255, 255, 0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.menu-toggle:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Icône hamburger professionnel */
.hamburger-icon {
  width: 24px;
  height: 18px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.hamburger-line {
  display: block;
  height: 2px;
  width: 100%;
  background-color: var(--color-primary);
  border-radius: 1px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  transform-origin: center;
}

/* Animation de l'icône hamburger vers croix */
.hamburger-icon.active .line-1 {
  transform: translateY(8px) rotate(45deg);
  background-color: var(--color-accent);
}

.hamburger-icon.active .line-2 {
  opacity: 0;
  transform: scale(0);
}

.hamburger-icon.active .line-3 {
  transform: translateY(-8px) rotate(-45deg);
  background-color: var(--color-accent);
}

/* Effets au survol */
.menu-toggle:hover .hamburger-line {
  background-color: var(--color-accent);
}

.menu-toggle:hover .hamburger-icon {
  transform: scale(1.1);
}

/* Animation subtile au chargement */
.hamburger-line.line-1 {
  transition-delay: 0.1s;
}

.hamburger-line.line-2 {
  transition-delay: 0.2s;
}

.hamburger-line.line-3 {
  transition-delay: 0.3s;
}

.hamburger-icon.active .line-1 {
  transition-delay: 0.3s;
}

.hamburger-icon.active .line-2 {
  transition-delay: 0.2s;
}

.hamburger-icon.active .line-3 {
  transition-delay: 0.1s;
}

.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s ease;
  z-index: 9998;
}

.mobile-menu-overlay.active {
  opacity: 1;
  visibility: visible;
}

.mobile-nav {
  position: fixed;
  top: 0;
  right: 0;
  width: 80%;
  max-width: 300px;
  height: 100%;
  background-color: var(--text-color);
  padding: 80px 30px 30px;
  transform: translateX(100%);
  transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  overflow-y: auto;
}

.mobile-menu-overlay.active .mobile-nav {
  transform: translateX(0);
}

.mobile-nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.mobile-nav-item {
  padding: 10px 0;
  opacity: 0;
  transform: translateX(20px);
  transition: all 0.4s ease;
}

.mobile-menu-overlay.active .mobile-nav-item {
  opacity: 1;
  transform: translateX(0);
}

/* Animation en cascade pour les items du menu */
.mobile-menu-overlay.active .mobile-nav-item:nth-child(1) { transition-delay: 0.1s; }
.mobile-menu-overlay.active .mobile-nav-item:nth-child(2) { transition-delay: 0.2s; }
.mobile-menu-overlay.active .mobile-nav-item:nth-child(3) { transition-delay: 0.3s; }
.mobile-menu-overlay.active .mobile-nav-item:nth-child(4) { transition-delay: 0.4s; }
.mobile-menu-overlay.active .mobile-nav-item:nth-child(5) { transition-delay: 0.5s; }

/* Styles pour le dropdown mobile */
.mobile-dropdown-container {
  width: 100%;
}

.mobile-dropdown-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.5rem 0;
  color: var(--color-text-light);
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  background: none;
  text-align: left;
}

.mobile-dropdown-arrow {
  transition: transform 0.3s ease;
  color: currentColor;
}

.mobile-dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.mobile-dropdown-menu {
  margin-top: 10px;
  margin-left: 15px;
  border-left: 1px solid var(--color-accent);
  padding-left: 15px;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.mobile-dropdown-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.mobile-dropdown-item {
  padding: 0;
}

.mobile-dropdown-link {
  display: block;
  padding: 8px 0;
  color: var(--color-text-light);
  text-decoration: none;
  font-size: 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.mobile-dropdown-link:hover {
  color: var(--color-accent);
  transform: translateX(5px);
}

.mobile-dropdown-link.router-link-active {
  color: var(--color-accent);
}

/* Lien normal mobile pour les autres items */
.mobile-nav-link {
  text-decoration: none;
  color: var(--color-text-light);
  font-size: 1rem;
  font-weight: 500;
  padding: 0.5rem 0;
  display: block;
  position: relative;
  transition: all 0.3s ease;
}

.mobile-nav-link:hover {
  color: var(--color-accent);
  transform: translateX(5px);
}

.mobile-nav-link.router-link-active {
  color: var(--color-accent);
}

.mobile-nav-link.router-link-active .active-indicator,
.mobile-dropdown-link.router-link-active .active-indicator {
  width: 80%;
}

.mobile-menu-icons {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  align-items: center;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.4s ease;
}

.mobile-menu-overlay.active .mobile-menu-icons {
  opacity: 1;
  transform: translateY(0);
  transition-delay: 0.6s;
}

.mobile-profile-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: var(--color-text-light);
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
}

.mobile-profile-icon:hover {
  background-color: var(--color-accent);
  color: var(--color-text-light);
}

.mobile-profile-icon.router-link-active {
  background-color: var(--color-accent);
  color: var(--color-text-light);
}

/* Media Queries pour le responsive */
@media (max-width: 992px) {
  .header {
    background-color: transparent;
  }
  
  .nav,
  .profile-icon {
    display: none;
  }
  
  .menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .header-right {
    gap: 15px;
  }
}

@media (max-width: 576px) {
  .header {
    padding: 0.5rem 1rem;
  }
  
  .mobile-nav {
    width: 85%;
    padding: 70px 20px 20px;
  }
  
  .mobile-nav-link,
  .mobile-dropdown-trigger {
    font-size: 1.1rem;
  }
  
  .mobile-dropdown-link {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .header {
    padding: 0.5rem;
  }
  
  .mobile-nav {
    width: 100%;
    max-width: none;
  }
  
  .mobile-nav-link,
  .mobile-dropdown-trigger {
    font-size: 1rem;
  }
  
  .mobile-dropdown-link {
    font-size: 0.95rem;
  }
  
  .mobile-menu-icons {
    margin-top: 30px;
  }
}
</style>