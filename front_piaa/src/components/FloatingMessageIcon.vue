<template>
  <div class="message-floating-icon" @click="redirectToContact">
    <div class="message-icon">
      <font-awesome-icon :icon="['fas', 'message']" />
    </div>
    <div class="message-pulse"></div>
    
    <!-- Message qui apparaît au survol -->
    <div class="hover-message">
      <span>Contactez nous</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloatingMessageIcon',
  methods: {
    redirectToContact() {
      this.$router.push('/contact');
    }
  }
}
</script>

<style scoped>
.message-floating-icon {
  position: fixed;
  bottom: 96px;
  right: 30px;
  z-index: 1000;
  cursor: pointer;
  transition: all 0.3s ease;
}

.message-icon {
  width: 60px;
  height: 60px;
  background: var(--color-primary-flou);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(0, 86, 179, 0.3);
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.message-icon svg {
  width: 28px;
  height: 28px;
  color: white;
}

.message-pulse {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 70px;
  height: 70px;
  background: rgba(0, 86, 179, 0.2);
  border-radius: 50%;
  z-index: 1;
  animation: pulse 2s infinite;
}

/* Message au survol */
.hover-message {
  position: absolute;
  right: 70px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.85);
  color: white;
  padding: 10px 15px;
  border-radius: 8px;
  font-family: Arial, sans-serif;
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Flèche du message */
.hover-message::after {
  content: '';
  position: absolute;
  top: 50%;
  right: -6px;
  transform: translateY(-50%);
  border-width: 6px 0 6px 6px;
  border-style: solid;
  border-color: transparent transparent transparent rgba(0, 0, 0, 0.85);
}

.message-floating-icon:hover .message-icon {
  transform: scale(1.1);
  background: var(--color-accent);
}

.message-floating-icon:hover .message-pulse {
  animation: pulse-hover 1.5s infinite;
}

/* Affichage du message au survol */
.message-floating-icon:hover .hover-message {
  opacity: 1;
  visibility: visible;
  right: 75px;
}

/* Animation de pulsation */
@keyframes pulse {
  0% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.7;
  }
  70% {
    transform: translate(-50%, -50%) scale(1.3);
    opacity: 0;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.3);
    opacity: 0;
  }
}

@keyframes pulse-hover {
  0% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.7;
  }
  50% {
    transform: translate(-50%, -50%) scale(1.4);
    opacity: 0.3;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.4);
    opacity: 0;
  }
}

@keyframes ping {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.2);
    opacity: 0.8;
  }
}

@media (max-width: 768px) {
  .message-floating-icon {
    bottom: 96px;
    right: 25px;
  }
  
  .message-icon {
    width: 50px;
    height: 50px;
  }
  
  .message-icon svg {
    width: 24px;
    height: 24px;
  }
  
  .message-pulse {
    width: 60px;
    height: 60px;
  }
  
}

@media (max-width: 480px) {
  .message-floating-icon {
    bottom: 96px;
    right: 25px;
  }
  
  .message-icon {
    width: 45px;
    height: 45px;
  }
  
  .message-icon svg {
    width: 22px;
    height: 22px;
  }
  
  .message-pulse {
    width: 55px;
    height: 55px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .message-pulse,
  .message-floating-icon::after {
    animation: none;
  }
  
  .message-floating-icon:hover .message-icon {
    transform: scale(1.05);
  }
  
  .hover-message {
    transition: none;
  }
}
</style>