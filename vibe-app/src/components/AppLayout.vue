<template>
  <div class="app-shell">
    <!-- Sidebar -->
    <SideNav class="desktop-sidebar" />

    <!-- Main scrollable area -->
    <main class="main-content" ref="mainRef">
      <RouterView v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" :key="$route.fullPath" />
        </Transition>
      </RouterView>
    </main>

    <!-- Fixed bottom player -->
    <MusicPlayer class="global-player" />

    <!-- Mobile Footer Nav -->
    <MobileNav />
  </div>
</template>

<script setup>
import SideNav from './SideNav.vue'
import MobileNav from './MobileNav.vue'
import MusicPlayer from './MusicPlayer.vue'
import { ref } from 'vue'

const mainRef = ref(null)
</script>

<style scoped>
.app-shell {
  display: grid;
  grid-template-columns: 256px 1fr;
  grid-template-rows: 1fr auto;
  height: 100vh;
  height: 100dvh;
  overflow: hidden;
  background: #0A0A0A;
}

.main-content {
  grid-column: 2;
  grid-row: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding-bottom: 80px; /* Space for player */
}

@media (max-width: 768px) {
  .app-shell {
    grid-template-columns: 1fr;
  }
  .desktop-sidebar {
    display: none;
  }
  .main-content {
    grid-column: 1;
    padding-bottom: 150px; /* Space for player + mobile nav */
  }
}
</style>
