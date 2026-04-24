import { createRouter, createWebHashHistory } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import AuthLayout from '@/components/AuthLayout.vue'

const routes = [
  // Auth routes (full-screen, no sidebar/player)
  {
    path: '/login',
    component: AuthLayout,
    children: [
      { path: '', name: 'login', component: () => import('@/pages/LoginPage.vue') },
    ]
  },
  {
    path: '/register',
    component: AuthLayout,
    children: [
      { path: '', name: 'register', component: () => import('@/pages/RegisterPage.vue') },
    ]
  },
  {
    path: '/forgot-password',
    component: AuthLayout,
    children: [
      { path: '', name: 'forgot-password', component: () => import('@/pages/ForgotPasswordPage.vue') },
    ]
  },

  // Main app routes (sidebar + player layout)
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
      { path: 'playlist/:id', name: 'playlist', component: () => import('@/pages/PlaylistPage.vue') },
      { path: 'liked-songs', name: 'liked-songs', component: () => import('@/pages/LikedSongsPage.vue') },
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
