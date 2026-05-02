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
      <RouterLink to="/videos" class="nav-item" :class="{ active: $route.name === 'videos' || $route.name === 'video' }">
        <span class="material-symbols-outlined">smart_display</span>
        <span>Videos</span>
      </RouterLink>
      <RouterLink to="/library" class="nav-item" :class="{ active: $route.name === 'library' }">
        <span class="material-symbols-outlined">library_music</span>
        <span>Your Library</span>
      </RouterLink>
      <RouterLink v-if="auth.isLoggedIn" to="/liked-songs" class="nav-item" :class="{ active: $route.name === 'liked-songs' }">
        <span class="material-symbols-outlined">favorite</span>
        <span>Liked Songs</span>
      </RouterLink>
    </nav>

    <!-- User Section -->
    <div class="user-section">
      <div v-if="auth.isLoggedIn" class="user-info">
        <div class="user-avatar-wrap">
          <img :src="auth.user.avatar" class="user-avatar" :alt="auth.user.display_name" />
        </div>
        <div class="user-meta">
          <p class="user-name">{{ auth.user.display_name }}</p>
          <button @click="auth.logout" class="logout-btn">Logout</button>
        </div>
      </div>
      <div v-else class="auth-prompt">
        <p>Log in to create playlists and save music.</p>
        <RouterLink to="/login" class="login-btn">Log In</RouterLink>
      </div>
    </div>

    <!-- Playlists -->
    <div class="playlists-section" v-if="auth.isLoggedIn">
      <div class="section-header">
        <p class="section-title">Playlists</p>
        <button @click="showCreateModal = true" class="add-playlist-btn" title="Create Playlist">
          <span class="material-symbols-outlined">add</span>
        </button>
      </div>
      <div class="playlists-list">
        <RouterLink
          v-for="pl in playlistStore.playlists"
          :key="pl.id"
          :to="`/playlist/${pl.id}`"
          class="playlist-item"
          :class="{ active: $route.params.id == pl.id }"
        >
          <span class="material-symbols-outlined">playlist_play</span>
          <span class="pl-name">{{ pl.name }}</span>
        </RouterLink>
      </div>
    </div>

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
        <p class="np-artist">
          <template v-if="player.currentTrack.artists && player.currentTrack.artists.length">
            {{ player.currentTrack.artists.map(a => a.name).join(', ') }}
          </template>
          <template v-else>
            {{ player.currentTrack.artist_name || 'Unknown' }}
          </template>
        </p>
      </div>
      <button @click="player.togglePlay()" class="np-btn">
        <span class="material-symbols-outlined filled">{{ player.isPlaying ? 'pause' : 'play_arrow' }}</span>
      </button>
    </div>

    <!-- Create Playlist Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal-content">
          <h3>Create New Playlist</h3>
          <div class="form-group">
            <label>Name</label>
            <input v-model="newPlaylistName" placeholder="My Playlist #1" @keyup.enter="handleCreatePlaylist" />
          </div>
          <div class="modal-actions">
            <button @click="showCreateModal = false" class="btn-cancel">Cancel</button>
            <button @click="handleCreatePlaylist" class="btn-create" :disabled="!newPlaylistName">Create</button>
          </div>
        </div>
      </div>
    </Teleport>
  </aside>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { usePlayerStore } from '@/stores/player'
import { useAuthStore } from '@/stores/auth'
import { usePlaylistStore } from '@/stores/playlists'
import { api } from '@/services/api'

const router = useRouter()
const player = usePlayerStore()
const auth = useAuthStore()
const playlistStore = usePlaylistStore()

const genres = ref([])
const playerName = api.config.playerName || 'VIBE'
const tagline = api.config.tagline || 'Live the Sound'

const showCreateModal = ref(false)
const newPlaylistName = ref('')

onMounted(async () => {
  try {
    genres.value = await api.genres()
  } catch (e) {}
  
  if (auth.isLoggedIn) {
    playlistStore.fetchPlaylists()
  }
})

watch(() => auth.isLoggedIn, (loggedIn) => {
  if (loggedIn) {
    playlistStore.fetchPlaylists()
  }
})

async function handleCreatePlaylist() {
  if (!newPlaylistName.value) return
  const pl = await playlistStore.createPlaylist(newPlaylistName.value)
  showCreateModal.value = false
  newPlaylistName.value = ''
  router.push(`/playlist/${pl.id}`)
}
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

/* User Section */
.user-section {
  padding: 16px 24px;
  border-top: 1px solid #1A1A1A;
  border-bottom: 1px solid #1A1A1A;
  margin-bottom: 8px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar-wrap {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
  background: #2a2a2a;
}

.user-avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-meta {
  flex: 1;
  overflow: hidden;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  background: none;
  border: none;
  color: #666;
  font-size: 11px;
  padding: 0;
  cursor: pointer;
}

.logout-btn:hover { color: #FF0000; text-decoration: underline; }

.auth-prompt {
  text-align: center;
}

.auth-prompt p {
  font-size: 11px;
  color: #666;
  margin-bottom: 10px;
}

.login-btn {
  display: block;
  background: #fff;
  color: #000;
  text-decoration: none;
  font-size: 12px;
  font-weight: 700;
  padding: 8px;
  border-radius: 20px;
  transition: transform 0.15s;
}

.login-btn:hover { transform: scale(1.03); }

/* Playlists */
.playlists-section {
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px;
  margin-bottom: 8px;
}

.add-playlist-btn {
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
}

.add-playlist-btn:hover { color: #fff; }

.playlists-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  max-height: 200px;
  overflow-y: auto;
}

.playlist-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 6px;
  color: #888;
  text-decoration: none;
  font-size: 13px;
  transition: all 0.15s;
}

.playlist-item:hover { color: #fff; background: #1A1A1A; }
.playlist-item.active { color: #fff; background: rgba(255, 255, 255, 0.05); }

.pl-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Genres */
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
  padding-top: 8px;
}

.genres-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
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

.genre-pill:hover { border-color: #FF0000; color: #FF0000; }
.genre-pill.active { background: #FF0000; border-color: #FF0000; color: #fff; }

/* Now Playing mini */
.now-playing {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: #1A1A1A;
  border-top: 1px solid #2a2a2a;
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
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: #181818;
  border: 1px solid #282828;
  border-radius: 12px;
  padding: 24px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
}

.modal-content h3 {
  font-family: 'Spline Sans', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  background: #2a2a2a;
  border: 1px solid transparent;
  border-radius: 4px;
  padding: 12px;
  color: #fff;
  font-size: 14px;
}

.form-group input:focus {
  outline: none;
  background: #333;
  border-color: #555;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancel {
  background: none;
  border: none;
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  padding: 12px 24px;
}

.btn-create {
  background: #FF0000;
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 12px 32px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
}

.btn-create:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
