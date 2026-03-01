import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/Emplist.vue'

const routes = [
 
  {
    path: '/emplist',
    name: 'emplist',
    component: () => import("../views//Emplist.vue"),
  },
  {
    path: '/showemp',
    name: 'showemp',
    component: () => import("../views//Showemp.vue"),
  }

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
