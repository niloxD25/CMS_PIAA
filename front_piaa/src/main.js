import './assets/main.css'

import { logError } from '@/services/api';

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import App from './App.vue'
import router from './router'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { 
  faFilePdf, 
  faFileWord, 
  faImage,
  faFileExcel,
  faFilePowerpoint,
  faFileAlt,
  faFileArchive,
  faVideo,
  faFileAudio,
  faFile,
  faFolderOpen,
  faMusic,
  faDownload,
  faSearch,
  faTimes,
  faCalendarTimes,
  faRedo,
  faMessage,
  faSpinner,
  faCheckCircle,
  faImages,
  faExclamationTriangle,
  faUser,
  faUserShield,
  faEdit,
  faSignOutAlt,
  faArrowLeft,
  faMapMarkedAlt,
  faChevronRight,
  faChevronLeft,
  faEye,
  faExpand,
  faPlay,
  faUserCog,
  faExclamation,
  faAddressCard,
  faShieldAlt,
  faBackspace,
  faCalendarPlus,
  faCalendarCheck,
  faHourglassHalf,
  faCircleDot,
  faXmark,
  faVideoSlash,
  faHouse,
  faQuoteRight
} from '@fortawesome/free-solid-svg-icons'

library.add(
  faFilePdf, 
  faFileWord, 
  faImage,
  faFileExcel,
  faFilePowerpoint,
  faFileAlt,
  faFileArchive,
  faVideo,
  faFileAudio,
  faFile,
  faFolderOpen,
  faMusic,
  faDownload,
  faSearch,
  faTimes,
  faCalendarTimes,
  faRedo,
  faMessage,
  faSpinner,
  faCheckCircle,
  faImages,
  faExclamationTriangle,
  faUser,
  faUserShield,
  faEdit,
  faSignOutAlt,
  faArrowLeft,
  faMapMarkedAlt,
  faChevronRight,
  faChevronLeft,
  faEye,
  faExpand,
  faPlay,
  faUserCog,
  faExclamation,
  faAddressCard,
  faShieldAlt,
  faBackspace,
  faCalendarPlus,
  faCalendarCheck,
  faHourglassHalf,
  faCircleDot,
  faXmark,
  faVideoSlash,
  faHouse,
  faQuoteRight
)

const app = createApp(App)
const pinia = createPinia()

//Capturer erreurs Vue
app.config.errorHandler = (err, instance, info) => {
  console.error(info, err);
  logError(err, info);
};

//Capturer erreurs globales JS
window.onerror = function (message, source, lineno, colno, error) {
  console.error(message, error);
  logError(error || message);
};

pinia.use(piniaPluginPersistedstate)

app.component('font-awesome-icon', FontAwesomeIcon)

app.use(pinia)

app.use(router)

app.mount('#app')
