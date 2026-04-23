<template>
  <nav class="mobile-nav">
    <RouterLink to="/" class="nav-item" :class="{ active: $route.name === 'home' }">
      <span class="material-symbols-outlined">explore</span>
      <span>Discovery</span>
    </RouterLink>
    <RouterLink to="/search" class="nav-item" :class="{ active: $route.name === 'search' }">
      <span class="material-symbols-outlined">search</span>
      <span>Search</span>
    </RouterLink>
    <RouterLink to="/library" class="nav-item" :class="{ active: $route.name === 'library' }">
      <span class="material-symbols-outlined">library_music</span>
      <span>Library</span>
    </RouterLink>
    <RouterLink v-if="!auth.isLoggedIn" to="/login" class="nav-item" :class="{ active: $route.name === 'login' }">
      <span class="material-symbols-outlined">account_circle</span>
      <span>Login</span>
    </RouterLink>
    <div v-else class="nav-item profile-item">
      <img :src="auth.user.avatar" class="mini-avatar" />
      <span>Profile</span>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
</script>

<style scoped>
.mobile-nav {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(18, 18, 18, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  grid-template-columns: repeat(4, 1fr);
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom);
}

@media (max-width: 768px) {
  .mobile-nav {
    display: grid;
  }
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: #888;
  text-decoration: none;
  font-size: 10px;
  font-weight: 600;
  transition: 0.2s;
}

.nav-item.active {
  color: #FF0000;
}

.nav-item .material-symbols-outlined {
  font-size: 24px;
}

.mini-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-item {
  opacity: 0.8;
}
</style>
