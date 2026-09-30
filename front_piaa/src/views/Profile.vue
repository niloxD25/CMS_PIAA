<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projectStore';
import { login, register } from '@/services/api';
import PartiesPrenantesST from '../components/PartiesPrenantesST.vue';
import Header from '@/components/Header.vue';

// --- Initialisation ---
const router = useRouter();
const projectStore = useProjectStore();

const isLoginMode = ref(true);
const errorMessage = ref('');
const isLoading = ref(false);

const formData = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const isFocused = reactive({
  name: false,
  email: false,
  password: false,
  password_confirmation: false
});

const organisations = computed(() => projectStore.fullData?.parties_prenantes || []);

const submitForm = async () => {
  errorMessage.value = '';
  if (isLoginMode.value) {
    await handleLogin();
  } else {
    await handleRegister();
  }
};

const handleLogin = async () => {
  isLoading.value = true;
  try {
    const credentials = {
      email: formData.email,
      password: formData.password
    };
    
    const response = await login(credentials);
    const { user, token } = response.data.data;

    // 1. Sauvegarder le token pour les futurs appels API (utilisé par apiClient)
    localStorage.setItem('token', JSON.stringify(token));

    // 2. Mettre à jour l'utilisateur dans le Store
    projectStore.user = user;

    // 3. Charger les données projet
    await projectStore.loadData(true);

    router.push('/');

  } catch (error) {
    console.error('Erreur de connexion:', error);
    errorMessage.value = error.response?.data?.message || 'Erreur de connexion. Veuillez réessayer.';
  } finally {
    resetForm();
    isLoading.value = false;
  }
};

const handleRegister = async () => {
  if (formData.password !== formData.password_confirmation) {
    errorMessage.value = 'Les mots de passe ne correspondent pas.';
    return;
  }

  isLoading.value = true;
  try {
    const userData = {
      name: formData.name,
      email: formData.email,
      password: formData.password,
      password_confirmation: formData.password_confirmation
    };
    
    const response = await register(userData);
    const { user, token } = response.data.data;

    // 1. Sauvegarder le token
    localStorage.setItem('token', JSON.stringify(token));

    // 2. Mettre à jour l'utilisateur dans le Store
    projectStore.user = user;
    
    // 3. Charger les données projet
    await projectStore.loadData(true);

    router.push('/');

  } catch (error) {
    console.error("Erreur d'inscription:", error);
    let message = error.response?.data?.message || "Erreur d'inscription.";
    if (error.response?.data?.errors) {
      const details = Object.values(error.response.data.errors).flat().join(', ');
      message += ` : ${details}`;
    }
    errorMessage.value = message;
  } finally {
    resetForm();
    isLoading.value = false;
  }
};

const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  resetForm();
};

const setFocus = (field, isFocusedState) => {
  isFocused[field] = isFocusedState;
};

const resetForm = () => {
  formData.name = '';
  formData.email = '';
  formData.password = '';
  formData.password_confirmation = '';
  Object.keys(isFocused).forEach(key => isFocused[key] = false);
};
</script>

<template>
 <main>
   <Header />

   <div class="auth-container" :class="{ 'loading-blur': isLoading }">
    <!-- Overlay de loading -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-large">
        <div class="spinner-circle"></div>
      </div>
    </div>

    <transition name="fade" mode="out-in">
      <div :key="isLoginMode ? 'login' : 'register'">
       <h1 class="auth-title">{{ isLoginMode ? 'Connexion' : 'Inscription' }}</h1>
       <p class="auth-subtitle">
         {{ isLoginMode ? 'Connectez-vous à votre compte' : 'Créez un nouveau compte' }}
       </p>
       
       <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

       <form class="auth-form" @submit.prevent="submitForm">
         <!-- Champ Nom (uniquement pour l'inscription) -->
         <transition name="slide-fade">
          <div class="form-group" v-if="!isLoginMode" key="name-field">
            <label for="name" :class="{ focused: isFocused.name }">Nom complet</label>
            <input 
              type="text" 
              id="name" 
              v-model="formData.name" 
              @focus="setFocus('name', true)" 
              @blur="setFocus('name', false)" 
              :disabled="isLoading"
              required
            >
          </div>
         </transition>
         
         <!-- Champ Email -->
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
         
         <!-- Champ Mot de passe -->
         <div class="form-group">
          <label for="password" :class="{ focused: isFocused.password }">Mot de passe</label>
          <input 
            type="password" 
            id="password" 
            v-model="formData.password" 
            @focus="setFocus('password', true)" 
            @blur="setFocus('password', false)" 
            :disabled="isLoading"
            required
          >
         </div>
         
         <!-- Confirmation mot de passe (uniquement pour l'inscription) -->
         <transition name="slide-fade">
          <div class="form-group" v-if="!isLoginMode" key="confirm-field">
            <label for="password_confirmation" :class="{ focused: isFocused.password_confirmation }">Confirmer le mot de passe</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="formData.password_confirmation" 
              @focus="setFocus('password_confirmation', true)" 
              @blur="setFocus('password_confirmation', false)" 
              :disabled="isLoading"
              required
            >
          </div>
         </transition>
         
         <button type="submit" class="submit-btn" :disabled="isLoading">
          <span v-if="isLoading" class="button-loading">
            <div class="button-spinner"></div>
            {{ isLoginMode ? 'Connexion...' : 'Inscription...' }}
          </span>
          <span v-else>
            {{ isLoginMode ? 'Se connecter' : 'S\'inscrire' }}
          </span>
         </button>
       </form>
      </div>
    </transition>
    
    <div class="auth-switch">
      <p>
       {{ isLoginMode ? 'Pas encore de compte ?' : 'Déjà un compte ?' }}
       <button 
         type="button" 
         class="switch-btn" 
         @click="toggleMode"
         :disabled="isLoading"
       >
         {{ isLoginMode ? 'Créer un compte' : 'Se connecter' }}
       </button>
      </p>
    </div>
   </div>

   <PartiesPrenantesST :organisations-data="organisations"/>
 </main>
</template>

<style scoped>
.auth-container {
 border-radius: 10px;
 padding: 2rem;
 margin: 2rem auto;
 max-width: 500px;
 position: relative;
 overflow: hidden;
 transition: all 0.3s ease;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
  border-radius: 10px;
}

.spinner-large {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.spinner-circle {
  position: absolute;
  top: 50%; left: 50%;
  width: 50px; height: 50px;
  margin: -25px 0 0 -25px;
  border-top: 4px solid var(--color-accent, #4f46e5);
  border-radius: 50%;
  animation: spin 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  z-index: 2;
}

.spinner-large p {
  color: var(--color-primary);
  font-weight: 500;
  font-size: 1.1rem;
}

/* Spinner dans le bouton */
.button-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.button-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
 color: var(--color-error);
 background-color: #fee2e2;
 padding: 1rem;
 border-radius: 8px;
 margin-bottom: 1.5rem;
 text-align: center;
 font-weight: 500;
 border: 1px solid #f87171;
}

.fade-enter-active, .fade-leave-active {
 transition: all 0.4s ease;
}

.fade-enter-from, .fade-leave-to {
 opacity: 0;
 transform: translateY(20px);
}

.fade-enter-to, .fade-leave-from {
 opacity: 1;
 transform: translateY(0);
}

.slide-fade-enter-active {
 transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
 transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
 transform: translateX(20px);
 opacity: 0;
}

.auth-title {
 font-size: 2.5rem;
 font-weight: 700;
 text-align: center;
 color: var(--color-primary);
 position: relative;
 padding-bottom: 1rem;
 transition: all 0.4s ease;
}

.auth-subtitle {
 line-height: 1.6;
 margin-bottom: 40px;
 color: var(--vt-c-divider-dark-1);
 transition: all 0.4s ease;
 text-align: center;
}

.auth-form {
 display: flex;
 flex-direction: column;
 gap: 1.5rem;
}

.form-group {
 margin-bottom: 1rem;
 position: relative;
 transition: all 0.3s ease;
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

input {
 width: 100%;
 height: 60px;
 padding: 0.8rem 1.2rem;
 border: 2px solid transparent;
 background-color: var(--gris);
 border-radius: var(--border-radius);
 font-size: 1rem;
 transition: all 0.3s ease;
 box-sizing: border-box;
}

input:hover:not(:disabled) {
 background-color: color-mix(in srgb, var(--gris) 90%, white);
 border-color: var(--color-accent);
 transform: translateY(-1px);
 box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

input:focus:not(:disabled) {
 outline: none;
 border: 2px solid var(--color-accent);
 background-color: color-mix(in srgb, var(--gris) 95%, white);
 box-shadow: 0 0 0 4px rgba(var(--color-accent-rgb, 52, 152, 219), 0.15);
 transform: translateY(-2px);
 animation: pulse 1.5s infinite;
}

input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

input:disabled:hover {
  transform: none;
  border-color: transparent;
  box-shadow: none;
}

.submit-btn {
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
 height: 60px;
 width: 100%;
 position: relative;
 overflow: hidden;
 margin-top: 10px;
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

@keyframes pulse {
 0% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0.4); }
 70% { box-shadow: 0 0 0 10px rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
 100% { box-shadow: 0 0 0 0 rgba(var(--color-accent-rgb, 52, 152, 219), 0); }
}

.auth-switch {
 text-align: center;
 margin-top: 2rem;
 padding-top: 1.5rem;
 border-top: 1px solid var(--gris);
}

.auth-switch p {
 color: var(--vt-c-divider-dark-1);
 margin: 0;
}

.switch-btn {
 background: none;
 border: none;
 color: var(--color-accent);
 cursor: pointer;
 font-weight: 600;
 text-decoration: underline;
 padding: 0.2rem 0.5rem;
 transition: all 0.3s ease;
 position: relative;
}

.switch-btn:hover:not(:disabled) {
 color: var(--color-primary);
 transform: translateY(-2px);
}

.switch-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.switch-btn:active:not(:disabled) {
 transform: translateY(0);
}

@media (max-width: 768px) {
 .auth-container {
   padding: 1.5rem;
   margin: 1rem;
 }
 
 .auth-title {
   font-size: 2rem;
 }
 
 .spinner-large p {
   font-size: 1rem;
 }
}
</style>