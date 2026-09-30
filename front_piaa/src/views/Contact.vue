<script setup>
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import { createMessage } from '@/services/api';
import Header from '@/components/Header.vue';
import { useProjectStore } from '@/stores/projectStore';

const projectStore = useProjectStore();

// Variables réactives
const formData = reactive({
  nom: '',
  email: '',
  telephone: '',
  message: ''
});

const isFocused = reactive({
  nom: false,
  email: false,
  telephone: false,
  message: false
});

const organisations = computed(() => {
  return projectStore.fullData?.parties_prenantes || [];
});

const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Méthodes
const submitForm = async () => {
  isLoading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    const messageData = {
      nom: formData.nom,
      mail: formData.email,
      telephone: formData.telephone || null,
      message: formData.message
    };

    const response = await createMessage(messageData);
    
    console.log('Message envoyé avec succès:', response.data.data);
    successMessage.value = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
    
    // Réinitialiser le formulaire
    resetForm();
    
  } catch (error) {
    console.error('Erreur lors de l\'envoi du message:', error);
    
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else if (error.response && error.response.data && error.response.data.errors) {
      // Gérer les erreurs de validation
      const errors = error.response.data.errors;
      const errorMessages = [];
      
      for (const field in errors) {
        if (errors[field].length > 0) {
          const fieldName = getFieldLabel(field);
          errorMessages.push(`${fieldName}: ${errors[field].join(', ')}`);
        }
      }
      
      errorMessage.value = errorMessages.join('\n');
    } else {
      errorMessage.value = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.';
    }
  } finally {
    isLoading.value = false;
  }
};

const resetForm = () => {
  formData.nom = '';
  formData.email = '';
  formData.telephone = '';
  formData.message = '';
  
  // Réinitialiser les états de focus
  Object.keys(isFocused).forEach(key => {
    isFocused[key] = false;
  });
};

const setFocus = (field, isFocusedState) => {
  isFocused[field] = isFocusedState;
};

const getFieldLabel = (field) => {
  const labels = {
    nom: 'Nom',
    email: 'E-mail',
    telephone: 'Téléphone',
    message: 'Message'
  };
  return labels[field] || field;
};
</script>

<template>
  <main>
    <Header />
    
    <div class="contact-container">
      <div class="header-content">
        <h1 class="main-title">Contactez-nous</h1>
        <p class="subtitle">Si vous avez des questions ou si vous voulez nous envoyer un message</p>
      </div>

      <!-- Messages de statut -->
      <div v-if="successMessage" class="success-message">
        <font-awesome-icon :icon="['fas', 'check-circle']" />
        {{ successMessage }}
      </div>
      
      <div v-if="errorMessage" class="error-message">
        <font-awesome-icon :icon="['fas', 'exclamation-triangle']" />
        {{ errorMessage }}
      </div>
      
      <form class="contact-form" @submit.prevent="submitForm">
        <div class="form-group">
          <label for="nom" :class="{ focused: isFocused.nom }">Nom</label>
          <input 
            type="text" 
            id="nom" 
            v-model="formData.nom" 
            @focus="setFocus('nom', true)" 
            @blur="setFocus('nom', false)" 
            :disabled="isLoading"
            required
          >
        </div>
        
        <div class="form-group">
          <label for="email" :class="{ focused: isFocused.email }">E-mail</label>
          <input 
            type="email" 
            id="email" 
            v-model="formData.email" 
            @focus="setFocus('email', true)" 
            @blur="setFocus('email', false)" 
            :disabled="isLoading"
            required
          >
        </div>
        
        <div class="form-group">
          <label for="telephone" :class="{ focused: isFocused.telephone }">Numéro de téléphone</label>
          <input 
            type="tel" 
            id="telephone" 
            v-model="formData.telephone" 
            @focus="setFocus('telephone', true)" 
            @blur="setFocus('telephone', false)" 
            :disabled="isLoading"
          >
        </div>
        
        <div class="form-group full-width">
          <label for="message" :class="{ focused: isFocused.message }">Votre message</label>
          <textarea 
            id="message" 
            v-model="formData.message" 
            @focus="setFocus('message', true)" 
            @blur="setFocus('message', false)" 
            :disabled="isLoading"
            required
          ></textarea>
        </div>
        
        <button 
          type="submit" 
          class="submit-btn" 
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="loading-spinner">
            <font-awesome-icon :icon="['fas', 'spinner']" spin />
          </span>
          <span v-else>Envoyer</span>
        </button>
      </form>
    </div>

    <PartiesPrenantesST :organisations-data="organisations"/>
  </main>
</template>

<style scoped>
.contact-container {
  border-radius: 10px;
  padding: 2rem;
  margin: 2rem;
  max-width: 90%;
}

.header-content {
   text-align: center;
   max-width: 600px;
   margin: 0 auto 4rem auto;
}

.contact-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--color-primary);
  position: relative;
  padding-bottom: 1rem;
}

.contact-subtitle {
  line-height: 1.6;
  margin-bottom: 80px;
  color: var(--vt-c-divider-dark-1);
}

/* Messages de statut */
.success-message {
  color: var(--color-success);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.error-message {
  color: var(--color-error);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: pre-line;
}

.contact-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
  position: relative;
}

.form-group.full-width {
  grid-column: span 2;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--color-primary);
  transition: all 0.3s ease;
  transform-origin: left center;
}

label.focused {
  color: var(--color-accent);
  transform: translateY(-2px);
}

label[for="nom"]::after,
label[for="email"]::after,
label[for="message"]::after {
  content: "*";
  color: #e74c3c;
  margin-left: 0.2rem;
  transition: color 0.3s ease;
}

label.focused[for="nom"]::after,
label.focused[for="email"]::after,
label.focused[for="message"]::after {
  color: var(--color-accent);
}

input, textarea {
  width: 100%;
  height: 68px;
  padding: 0.8rem 1.2rem;
  border: 2px solid transparent;
  background-color: var(--gris);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

input:hover, textarea:hover {
  background-color: color-mix(in srgb, var(--gris) 90%, white);
  border-color: var(--color-accent);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

input:focus, textarea:focus {
  outline: none;
  border: 2px solid var(--color-accent);
  background-color: color-mix(in srgb, var(--gris) 95%, white);
  box-shadow: 0 0 0 4px rgba(var(--color-accent-rgb, 52, 152, 219), 0.15);
  transform: translateY(-2px);
}

input:disabled, textarea:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

input:disabled:hover, textarea:disabled:hover {
  transform: none;
  border-color: transparent;
  box-shadow: none;
}

textarea {
  min-height: 150px;
  resize: vertical;
}

.submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: var(--color-primary);
  color: var(--color-text-light);
  border: none;
  border-radius: var(--border-radius);
  font-size: 0.9rem;
  font-weight: 400;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  height: 60px;
  width: 150px;
  position: relative;
  overflow: hidden;
  margin-top: 10px;
  justify-self: flex-start;
}

.submit-btn:hover:not(:disabled) {
  background: var(--color-accent);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  gap: 0.5rem;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.submit-btn:hover::before {
  left: 100%;
}

.submit-btn:active:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
  100% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
}

input:focus, textarea:focus {
  animation: pulse 1.5s infinite;
}

.form-group {
  transition: transform 0.3s ease;
}

.form-group:hover {
  transform: translateX(5px);
}

@media (max-width: 768px) {
  .contact-form {
    grid-template-columns: 1fr;
  }
  
  .form-group.full-width {
    grid-column: span 1;
  }
  
  .submit-btn {
    grid-column: span 1;
    width: 100%;
  }

  .form-group:hover {
    transform: translateX(3px);
  }
  
  .contact-container {
    margin: 1rem;
    padding: 1.5rem;
  }
}
</style>