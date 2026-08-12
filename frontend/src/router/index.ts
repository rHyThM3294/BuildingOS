import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
  },
  {
    path: '/parking',
    name: 'parking',
    component: () => import('@/views/ParkingView.vue'),
  },
  {
    path: '/packages',
    name: 'packages',
    component: () => import('@/views/PackageView.vue'),
  },
  {
    path: '/visitors',
    name: 'visitors',
    component: () => import('@/views/VisitorView.vue'),
  },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})
