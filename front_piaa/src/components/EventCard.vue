<script setup>
import { computed } from 'vue';

const props = defineProps({
  event: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

const getDay = (dateString) => {
  if (!dateString) return 0;
  return new Date(dateString).getDate();
};
const getMonth = (dateString) => {
  if (!dateString) return 0;
  return new Date(dateString).getMonth() + 1;
};
const getYear = (dateString) => {
  if (!dateString) return 0;
  return new Date(dateString).getFullYear();
};
const formatTwoDigits = (number) => {
  if (number === undefined || number === null) return '00';
  return number < 10 ? '0' + number : number;
};

const publicUrl = import.meta.env.VITE_PUBLIC_URL;

const statusLabel = computed(() => {
  return props.event?.etat_courant?.etats_evenement?.etat || null;
});

const statusClass = computed(() => {
  const label = statusLabel.value?.toLowerCase() || '';
  
  if (label.includes('cours') || label.includes('actif')) return 'status-active';
  if (label.includes('venir') || label.includes('futur')) return 'status-upcoming';
  if (label.includes('termin') || label.includes('passé')) return 'status-finished';
  if (label.includes('annul')) return 'status-cancelled';
  
  return 'status-default';
});

const isActualite = computed(() => {
  return props.event?.est_actualite === true || props.event?.est_actualite === 1;
});
</script>

<template>
  <div class="event-card">
    <div class="event-content">
      <div class="event-dates">
        <div class="date-container">
          <div v-if="event.date_debut" class="date-number">{{ formatTwoDigits(getDay(event.date_debut)) }}.</div>
          <div v-if="event.date_debut" class="date-number">{{ formatTwoDigits(getMonth(event.date_debut)) }}.</div>
          <div v-if="event.date_debut" class="date-number">{{ getYear(event.date_debut) }}</div>
        </div>
        <div class="date-range-separator" v-if="event.date_fin">au</div>
        <div class="date-container">
          <div v-if="event.date_fin" class="date-number">{{ formatTwoDigits(getDay(event.date_fin)) }}.</div>
          <div v-if="event.date_fin" class="date-number">{{ formatTwoDigits(getMonth(event.date_fin)) }}.</div>
          <div v-if="event.date_fin" class="date-number">{{ getYear(event.date_fin) }}</div>
        </div>
      </div>
      
      <div class="event-info">
        <div class="status-wrapper">
          <!-- Badge Actualité -->
          <span v-if="isActualite" class="badge badge-actualite">
            <i class="fas fa-star"></i> Actualité
          </span>
          
          <!-- Badge État (avec espace si les deux sont présents) -->
          <span v-if="statusLabel" class="badge" :class="statusClass">
            {{ statusLabel }}
          </span>
        </div>

        <div class="event-header">
          <h2 v-if="event.titre">{{ event.titre }}</h2>
        </div>
        
        <div class="event-description">
          <p v-if="event.description">{{ event.description }}</p>
        </div>
      </div>
    </div>
    
    <div class="event-image" v-if="event.image">
      <img :src="`${publicUrl}${event.image}`" :alt="event.titre || 'Image événement'">
    </div>
  </div>
</template>

<style scoped>
.event-card {
  border-radius: var(--border-radius);
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  background: var(--bg-section);
  display: flex;
  flex-direction: column;
  height: 100%;
}
.event-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
  border: 1px solid var(--color-accent);
}
.event-card:hover .event-dates .date-number,
.event-card:hover .event-header h2 {
  color: var(--color-accent);
}
.event-content {
  display: flex;
  padding: 24px 24px 0 24px;
  flex: 1;
  flex-direction: row; 
}
.event-dates {
  display: flex;
  flex-direction: column;
  min-width: 90px;
  margin-right: 20px;
  padding: 10px 0;
  text-align: left;
  color: var(--text-color);
  flex-shrink: 0;
}
.date-container { 
  display: flex; 
  flex-direction: column; 
  align-items: start; 
}
.date-number { 
  font-size: 32px; 
  font-weight: 600; 
  line-height: 1; 
  transition: color 0.3s ease; 
}

.date-range-separator { 
  margin: 16px 0; 
  font-size: 24px; 
  color: var(--vt-c-divider-dark-1); 
  font-weight: 400; 
}

.event-info { 
  flex: 1; 
  text-align: right; 
  min-width: 0; 
  display: flex; 
  flex-direction: column; 
}

.status-wrapper {
  margin-bottom: 8px;
  display: flex;
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 8px;
}

/* Styles génériques pour tous les badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 400;
  letter-spacing: 0.5px;
  line-height: 1;
  white-space: nowrap;
}

/* Badge Actualité - toujours en premier */
.badge-actualite {
  background: #fef3c7;
  color: #c3a087;
  font-weight: 400;
  order: -1;
}

.badge-actualite i {
  font-size: 0.6rem;
}

.badge.status-default {
  background-color: #e0f2fe;
  color: #4b5563;
}

.badge.status-active {
  background-color: #ccedd7;
  color: var(--color-success);
}

.badge.status-upcoming {
  background-color: #fef3c7;
  color: #b45309;
}

.badge.status-finished {
  background-color: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.badge.status-cancelled {
  background-color: #fee2e2;
  color: var(--color-error);
}

.event-header { 
  margin-bottom: 12px; 
}

.event-header h2 { 
  margin: 0; 
  color: var(--text-color); 
  font-size: 1.8rem; 
  font-weight: 500; 
  line-height: 1.3; 
  transition: color 0.3s ease; 
  word-wrap: break-word; 
}

.event-description { 
  margin: 15px 0; 
  line-height: 1.5; 
  color: var(--text-color); 
  font-size: 14px; 
}

.event-image { 
  width: 100%; 
  padding: 0 24px 24px 24px; 
  margin-top: auto; 
}

.event-image img { 
  width: 100%; 
  height: 240px; 
  object-fit: cover; 
  border-radius: 8px; 
}

@media (max-width: 768px) {
  .event-content { padding: 20px 20px 0 20px; }
  .event-dates { min-width: auto; margin-right: 15px; padding: 5px 0; }
  .date-number { font-size: 24px; }
  .date-range-separator { margin: 10px 0; font-size: 18px; }
  .event-header h2 { font-size: 1.4rem; }
  .event-description { font-size: 13px; margin: 10px 0; }
  .event-image { padding: 0 20px 20px 20px; }
  .event-image img { height: 200px; }
  .status-wrapper {
    gap: 6px;
  }
  .badge {
    padding: 3px 10px;
    font-size: 0.65rem;
  }
}

@media (max-width: 480px) {
  .event-content { padding: 12px 12px 0 12px; }
  .event-dates { margin-right: 10px; }
  .date-number { font-size: 18px; }
  .date-range-separator { margin: 6px 0; font-size: 14px; }
  .event-header h2 { font-size: 1.1rem; margin-bottom: 6px; }
  .event-description { margin: 8px 0; font-size: 12px; line-height: 1.4; }
  .event-image { padding: 0 12px 12px 12px; }
  .event-image img { height: 150px; }
  .status-wrapper {
    gap: 4px;
    margin-bottom: 6px;
  }
  .badge {
    padding: 2px 8px;
    font-size: 0.6rem;
  }
  .badge-actualite i {
    font-size: 0.5rem;
  }
}
</style>