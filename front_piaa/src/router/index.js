import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Realisations from '../views/Realisations.vue'
import PlanTravaux from '../views/PlanTravaux.vue'
import Evenements from '../views/Evenements.vue'
import Documentation from '../views/Documentation.vue'
import Profile from '../views/Profile.vue'
import Contact from '../views/Contact.vue'
import EvenementDetails from '@/views/EvenementDetails.vue'
import ProfileInfo from '../views/ProfileInfo.vue'
import Archives from '@/views/Archives.vue'
import About from '@/views/About.vue'
import Temoignages_portraits from '@/views/Temoignages_portraits.vue'
import ReportagesVideos from '@/views/ReportagesVideos.vue'
import ActionsProximite from '@/views/ActionsProximite.vue'
import ProjectDetails from '@/views/ProjectDetails.vue'

const routes = [
  {
    path: '/',
    name: 'HomeView',
    component: HomeView
  },
  {
    path: '/temoignages-portraits',
    name: 'Temoignages_portraits',
    component: Temoignages_portraits
  },
  {
    path: '/reportages-videos',
    name: 'ReportagesVideos',
    component: ReportagesVideos
  },
  {
    path: '/actions-proximite',
    name: 'ActionsProximite',
    component: ActionsProximite
  },
  {
    path: '/realisations',
    name: 'Realisations',
    component: Realisations
  },
  {
    path: '/plan-travaux',
    name: 'PlanTravaux',
    component: PlanTravaux
  },
  {
    path: '/evenements',
    name: 'Evenements',
    component: Evenements
  },
  {
    path: '/documentation',
    name: 'Documentation',
    component: Documentation
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile
  },
  {
    path: '/contact',
    name: 'Contact',
    component: Contact
  },
  { 
    path: '/evenements/:id/:slug', 
    name: 'evenement-details', 
    component: EvenementDetails,
    props: true
  },
  {
    path: '/profileInfo',
    name: 'ProfileInfo',
    component: ProfileInfo
  },
  {
    path: '/archives',
    name: 'Archives',
    component: Archives
  },
  {
    path: '/about',
    name: 'About',
    component: About,
    props: true
  },
  {
    path: '/projets/:slug',
    name: 'ProjectDetails',
    component: ProjectDetails,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'router-link-active',
  linkExactActiveClass: 'router-link-exact-active'
})

export default router
