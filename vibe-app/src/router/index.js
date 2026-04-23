import { createRouter, createWebHashHistory } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'

const routes = [
  {
    path: '/',
    component: AppLayout,
    children: [
      { path: '', name: 'home', component: () => import('@/pages/HomePage.vue') },
      { path: 'search', name: 'search', component: () => import('@/pages/SearchPage.vue') },
      { path: 'artist/:id', name: 'artist', component: () => import('@/pages/ArtistPage.vue') },
      { path: 'album/:id', name: 'album', component: () => import('@/pages/AlbumPage.vue') },
      { path: 'genre/:slug', name: 'genre', component: () => import('@/pages/GenrePage.vue') },
      { path: 'library', name: 'library', component: () => import('@/pages/LibraryPage.vue') },
    ]
  }
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0, behavior: 'smooth' }
  }
})
