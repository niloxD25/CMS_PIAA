<template>
    <button 
      class="back-to-top"
      :class="{ 'visible': isVisible }"
      @click="scrollToTop"
      aria-label="Retour en haut de la page"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 15l-6-6-6 6"/>
      </svg>
    </button>
  </template>
  
  <script>
  export default {
    name: 'BackToTop',
    data() {
      return {
        isVisible: false
      }
    },
    mounted() {
      window.addEventListener('scroll', this.handleScroll)
    },
    beforeDestroy() {
      window.removeEventListener('scroll', this.handleScroll)
    },
    methods: {
      handleScroll() {
        this.isVisible = window.scrollY > 300
      },
      scrollToTop() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        })
      }
    }
  }
  </script>
  
  <style scoped>
  .back-to-top {
    position: fixed;
    bottom: 200px;
    right: 35px;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    color: var(--color-primary);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 1000;
    background-color: transparent;
    border-color: transparent;
  }
  
  .back-to-top.visible {
    opacity: 1;
    transform: translateY(0);
  }
  
  .back-to-top:hover {
    background-color: var(--color-accent);
    color: var(--color-text-light);
    transform: translateY(-3px);
  }
  
  .back-to-top svg {
    width: 1.5rem;
    height: 1.5rem;
  }
  
  @media (max-width: 768px) {
    .back-to-top {
      width: 2.5rem;
      height: 2.5rem;
      bottom: 200px;
      right: 30px;
    }
  }
  </style>