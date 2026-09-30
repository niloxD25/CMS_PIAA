import axios from 'axios';

// La variable d'environnement pour l'URL de base.
const BASE_URL = import.meta.env.VITE_API_URL;

const apiClient = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Intercepteur pour ajouter le token d'authentification
apiClient.interceptors.request.use(
  (config) => {
    const token = JSON.parse(localStorage.getItem('token'));
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur de réponse pour logs les erreurs vers server
apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    const errorData = {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data,
      url: error.config?.url,
      method: error.config?.method,
    };

    logError(errorData);

    return Promise.reject(error);
  }
);

/**
 * Fonctions pour les routes publiques
 */

// Health Check
export const ping = () => apiClient.get('/ping');

export const logError = async (error, info = "") => {
  try {
    return await apiClient.post("/log-error", {
      message: error?.message || error,
      stack: error?.stack || null,
      status: error?.status || null,
      data: error?.data || null,
      url: error?.url || window.location.href,
      method: error?.method || null,
      info,
    });
  } catch { }
};

// Auth
export const login = (credentials) => apiClient.post('/login', credentials);
export const adminLogin = (credentials) => apiClient.post('/admin-login', credentials);
export const register = (userData) => apiClient.post('/register', userData);

// Projets
export const getProjetsActuel = () => apiClient.get('/projets/actuel');
export const getProjetActuelFullData = (niveauAcces) => apiClient.get('/projets/actuelFullData', {
  params: {
    niveau_acces: niveauAcces
  }
});
export const getOthersProjetsWithFullData = (niveauAcces) => apiClient.get('/projets/others', {
  params: {
    niveau_acces: niveauAcces
  }
});
export const getProjets = () => apiClient.get('/projets');
export const getProjet = (id) => apiClient.get(`/projets/${id}`);

// Événements
export const getEvenementSections = (id) => apiClient.get(`/evenements/${id}/sections`);
export const getEvenements = () => apiClient.get('/evenements');
export const getEvenement = (id) => apiClient.get(`/evenements/${id}`);

// États d'événements
export const getEtatsEvenement = () => apiClient.get('/etatsEvenement');
export const getEtatEvenement = (id) => apiClient.get(`/etatsEvenement/${id}`);

// Annonces
export const getAnnoncesActives = () => apiClient.get('/annonces/actives');
export const getAnnonces = () => apiClient.get('/annonces');
export const getAnnonce = (id) => apiClient.get(`/annonces/${id}`);

// Réalisations
export const getRealisations = () => apiClient.get('/realisations');
export const getRealisation = (id) => apiClient.get(`/realisations/${id}`);

// Témoignages
export const getTemoignages = () => apiClient.get('/temoignages');
export const getTemoignage = (id) => apiClient.get(`/temoignages/${id}`);

// Fichiers
export const downloadFichier = (id) => apiClient.get(`/fichiers/${id}/download`, { responseType: 'blob' });
export const getFichiers = () => apiClient.get('/fichiers');
export const getFichier = (id) => apiClient.get(`/fichiers/${id}`);

// Messages
export const createMessage = (messageData) => apiClient.post('/messages', messageData);

// Associations / Organisations / Types de Relation
export const getAssociations = () => apiClient.get('/associations');
export const getAssociation = (id) => apiClient.get(`/associations/${id}`);
export const getOrganisations = () => apiClient.get('/organisations');
export const getOrganisation = (id) => apiClient.get(`/organisations/${id}`);
export const getTypesRelation = () => apiClient.get('/types-relation');
export const getTypeRelation = (id) => apiClient.get(`/types-relation/${id}`);

/**
 * Fonctions pour les routes protégées
 */

// Auth
export const logout = () => apiClient.post('/logout');
export const getMe = () => apiClient.get('/me');

// Users
export const getUsers = () => apiClient.get('/users');
export const getUser = (id) => apiClient.get(`/users/${id}`);
export const updateUser = (id, userData) => apiClient.put(`/users/${id}`, userData);
export const deleteUser = (id) => apiClient.delete(`/users/${id}`);

// Projets
export const setProjetActuel = (id) => apiClient.post('/projets/actuel', { projet_id: id });
export const createProjet = (projetData) => apiClient.post('/projets', projetData);
export const updateProjet = (id, projetData) => apiClient.put(`/projets/${id}`, projetData);
export const deleteProjet = (id) => apiClient.delete(`/projets/${id}`);

// Événements
export const createEvenement = (evenementData) => apiClient.post('/evenements', evenementData);
export const updateEvenement = (id, evenementData) => apiClient.put(`/evenements/${id}`, evenementData);
export const deleteEvenement = (id) => apiClient.delete(`/evenements/${id}`);
export const addSectionToEvenement = (evenementId, sectionData) => apiClient.post(`/evenements/${evenementId}/sections`, sectionData);
export const updateSection = (evenementId, sectionId, sectionData) => apiClient.put(`/evenements/${evenementId}/sections/${sectionId}`, sectionData);
export const deleteSection = (evenementId, sectionId) => apiClient.delete(`/evenements/${evenementId}/sections/${sectionId}`);

// États Événement
export const createEtatEvenement = (etatData) => apiClient.post('/etatsEvenement', etatData);
export const updateEtatEvenement = (id, etatData) => apiClient.put(`/etatsEvenement/${id}`, etatData);
export const deleteEtatEvenement = (id) => apiClient.delete(`/etatsEvenement/${id}`);

// Associations / Organisations / Types de Relation
export const createAssociation = (associationData) => apiClient.post('/associations', associationData);
export const updateAssociation = (id, associationData) => apiClient.put(`/associations/${id}`, associationData);
export const deleteAssociation = (id) => apiClient.delete(`/associations/${id}`);
export const createOrganisation = (organisationData) => apiClient.post('/organisations', organisationData);
export const updateOrganisation = (id, organisationData) => apiClient.put(`/organisations/${id}`, organisationData);
export const deleteOrganisation = (id) => apiClient.delete(`/organisations/${id}`);
export const createTypeRelation = (typeData) => apiClient.post('/types-relation', typeData);
export const updateTypeRelation = (id, typeData) => apiClient.put(`/types-relation/${id}`, typeData);
export const deleteTypeRelation = (id) => apiClient.delete(`/types-relation/${id}`);

// Réalisations
export const createRealisation = (realisationData) => apiClient.post('/realisations', realisationData);
export const updateRealisation = (id, realisationData) => apiClient.put(`/realisations/${id}`, realisationData);
export const deleteRealisation = (id) => apiClient.delete(`/realisations/${id}`);

// Témoignages
export const createTemoignage = (temoignageData) => apiClient.post('/temoignages', temoignageData);
export const updateTemoignage = (id, temoignageData) => apiClient.put(`/temoignages/${id}`, temoignageData);
export const deleteTemoignage = (id) => apiClient.delete(`/temoignages/${id}`);

// Annonces
export const createAnnonce = (annonceData) => apiClient.post('/annonces', annonceData);
export const updateAnnonce = (id, annonceData) => apiClient.put(`/annonces/${id}`, annonceData);
export const deleteAnnonce = (id) => apiClient.delete(`/annonces/${id}`);

// Fichiers
export const createFichier = (fichierData) => apiClient.post('/fichiers', fichierData);
export const updateFichier = (id, fichierData) => apiClient.put(`/fichiers/${id}`, fichierData);
export const deleteFichier = (id) => apiClient.delete(`/fichiers/${id}`);

// Messages
export const getMessageTypes = () => apiClient.get('/messages/types');
export const createMessageType = (typeData) => apiClient.post('/messages/types', typeData);
export const updateMessageType = (id, typeData) => apiClient.put(`/messages/types/${id}`, typeData);
export const deleteMessageType = (id) => apiClient.delete(`/messages/types/${id}`);
export const getMessages = () => apiClient.get('/messages');
export const getMessage = (id) => apiClient.get(`/messages/${id}`);
export const updateMessage = (id, messageData) => apiClient.put(`/messages/${id}`, messageData);
export const deleteMessage = (id) => apiClient.delete(`/messages/${id}`);