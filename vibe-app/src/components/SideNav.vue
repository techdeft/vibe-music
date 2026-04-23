<template>
  <aside class="sidenav">
    <!-- Brand -->
    <div class="brand">
      <span class="brand-name">{{ playerName }}</span>
      <span class="brand-tagline">{{ tagline }}</span>
    </div>

    <!-- Navigation -->
    <nav class="nav">
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
        <span>Your Library</span>
      </RouterLink>
    </nav>

    <!-- Genres -->
    <div class="genres-section">
      <p class="section-title">Genres</p>
      <div class="genres-list">
        <RouterLink
          v-for="genre in genres"
          :key="genre.id"
          :to="`/genre/${genre.slug}`"
          class="genre-pill"
          :class="{ active: $route.params.slug === genre.slug }"
        >
          {{ genre.name }}
        </RouterLink>
      </div>
    </div>

    <!-- Now Playing mini -->
    <div class="now-playing" v-if="player.currentTrack">
      <img
        :src="player.currentTrack.cover || '/placeholder.png'"
        :alt="player.currentTrack.title"
        class="np-cover"
        @error="$event.target.src = 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%231A1A1A%22/></svg>'"
      />
      <div class="np-info">
        <p class="np-title">{{ player.currentTrack.title }}</p>
        <p class="np-artist">{{ player.currentTrack.artist_name || 'Unknown' }}</p>
      </div>
      <button @click="player.togglePlay()" class="np-btn">
        <span class="material-symbols-outlined filled">{{ player.isPlaying ? 'pause' : 'play_arrow' }}</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePlayerStore } from '@/stores/player'
import { api } from '@/services/api'

const player = usePlayerStore()
const genres = ref([])
const playerName = api.config.playerName || 'VIBE'
const tagline = api.config.tagline || 'Live the Sound'

onMounted(async () => {
  try {
    genres.value = await api.genres()
  } catch (e) {
    // silently fail
  }
})
</script>

<style scoped>
.sidenav {
  grid-column: 1;
  grid-row: 1 / 3;
  background: #121212;
  border-right: 1px solid #1A1A1A;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100vh;
  height: 100dvh;
}

.brand {
  padding: 28px 24px 20px;
  border-bottom: 1px solid #1A1A1A;
}

.brand-name {
  display: block;
  font-family: 'Spline Sans', sans-serif;
  font-size: 26px;
  font-weight: 900;
  font-style: italic;
  color: #FF0000;
  letter-spacing: -1px;
  line-height: 1;
}

.brand-tagline {
  display: block;
  font-size: 10px;
  color: #555;
  text-transform: uppercase;
  letter-spacing: 0.25em;
  margin-top: 4px;
}

.nav {
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 11px 14px;
  border-radius: 8px;
  color: #888;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.15s ease;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  color: #fff;
  background: #1A1A1A;
}

.nav-item.active {
  color: #FF0000;
  background: rgba(255, 0, 0, 0.08);
  border-left-color: #FF0000;
}

.nav-item .material-symbols-outlined { font-size: 20px; }

.genres-section {
  flex: 1;
  overflow-y: auto;
  padding: 8px 16px 16px;
}

.section-title {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #555;
  margin-bottom: 10px;
  padding-top: 8px;
}

.genres-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.genre-pill {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid #2a2a2a;
  color: #888;
  text-decoration: none;
  transition: all 0.15s;
  white-space: nowrap;
}

.genre-pill:hover {
  border-color: #FF0000;
  color: #FF0000;
}

.genre-pill.active {
  background: #FF0000;
  border-color: #FF0000;
  color: #fff;
}

/* Now Playing mini */
.now-playing {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: #1A1A1A;
  border-top: 1px solid #2a2a2a;
  margin: 0;
}

.np-cover {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  object-fit: cover;
  flex-shrink: 0;
}

.np-info {
  flex: 1;
  overflow: hidden;
}

.np-title {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.np-artist {
  font-size: 11px;
  color: #888;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.np-btn {
  background: none;
  border: none;
  color: #FF0000;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.np-btn .material-symbols-outlined { font-size: 22px; }
</style>
